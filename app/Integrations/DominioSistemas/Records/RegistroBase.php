<?php

namespace App\Integrations\DominioSistemas\Records;

use App\Models\Cest;
use App\Models\EntradasImpostosEquivalente;
use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Classe abstrata base que define a estrutura comum a todos os registros
 * do layout da Domínio Sistemas.
 */
abstract class RegistroBase implements IRegistro
{
    protected const SEPARADOR_PADRAO = '|';

    protected const CODIFICACAO_ARQUIVO = 'Windows-1252';

    /**
     * Cache estático para armazenar os ProdutoFornecedor já consultados
     * Key: cnpj_num_nfe_codigo_produto (MD5 hash)
     * Value: ProdutoFornecedor model instance
     */
    protected static array $produtoFornecedorCache = [];

    /**
     * Cache estático para armazenar as verificações de zera ICMS já consultadas
     * Key: issuer_id_tag_code
     * Value: bool
     */
    protected static array $zeraIcmsCache = [];

    /**
     * Cache estático para armazenar as verificações de zera IPI já consultadas
     * Key: issuer_id_tag_code
     * Value: bool
     */
    protected static array $zeraIpiCache = [];

    /**
     * Retorna o tipo de registro (ex: 0000, 0010, 0100, etc.)
     */
    abstract public function getTipoRegistro(): string;

    /**
     * Converte o registro para uma linha no formato TXT
     */
    abstract public function converterParaLinhaTxt(): string;

    /**
     * Formata um campo de acordo com as regras do layout
     *
     * @param  mixed  $valor
     * @param  string  $tipo  (C=Caractere, N=Numérico inteiro, D=Decimal com 3 casas, D6=Decimal com 6 casas, X=Data)
     */
    protected function formatarCampo($valor, ?int $tamanhoMaximo = null, string $tipo = 'C'): string
    {
        // Para tipos numéricos/decimais, valor zero deve ser formatado como "0,00"
        // Para caracteres, null ou string vazia retorna vazio
        if ($valor === null || $valor === '') {
            // Para decimais, retorna "0,00" quando o valor é null/vazio
            if (in_array($tipo, ['D', 'D2', 'D6'])) {
                return '0,00';
            }

            return '';
        }

        // Converter para string e tratar conforme o tipo
        switch ($tipo) {
            case 'X': // Data: dd/mm/aaaa
                if (is_string($valor)) {
                    $date = \DateTime::createFromFormat('Y-m-d', $valor);
                    if ($date) {
                        $valor = $date->format('d/m/Y');
                    }
                } elseif ($valor instanceof \DateTime) {
                    $valor = $valor->format('d/m/Y');
                } else {
                    $valor = (string) $valor;
                }
                break;

            case 'N': // Numérico inteiro
                if (is_numeric($valor)) {
                    $valor = (string) (int) $valor;
                } else {
                    $valor = (string) $valor;
                }
                break;

            case 'D': // Decimal: usar vírgula como separador com 3 casas decimais
                if (is_numeric($valor)) {
                    // Formata com 3 casas decimais e usa vírgula como separador
                    $valor = number_format((float) $valor, 3, ',', '');
                } else {
                    $valor = (string) $valor;
                }
                break;

            case 'D2': // Decimal: usar vírgula como separador com 2 casas decimais
                if (is_numeric($valor)) {
                    // Formata com 2 casas decimais e usa vírgula como separador
                    $valor = number_format((float) $valor, 2, ',', '');
                } else {
                    $valor = (string) $valor;
                }
                break;

            case 'D6': // Decimal: usar vírgula como separador com 6 casas decimais
                if (is_numeric($valor)) {
                    // Formata com 6 casas decimais e usa vírgula como separador
                    $valor = number_format((float) $valor, 6, ',', '');
                } else {
                    $valor = (string) $valor;
                }
                break;

            case 'C': // Caractere
            default:
                $valor = (string) $valor;
                break;
        }

        // Aplicar tamanho máximo se especificado
        if ($tamanhoMaximo !== null) {
            $valor = mb_substr($valor, 0, $tamanhoMaximo);
        }

        return $valor;
    }

    /**
     * Codifica o conteúdo para Windows-1252 conforme exigido pelo layout
     */
    protected function codificarConteudo(string $conteudo): string
    {
        return mb_convert_encoding($conteudo, self::CODIFICACAO_ARQUIVO, 'UTF-8');
    }

    /**
     * Monta uma linha do arquivo TXT com os campos separados por pipe
     */
    protected function montarLinha(array $campos): string
    {
        $linha = self::SEPARADOR_PADRAO; // Começa com o separador
        foreach ($campos as $campo) {
            $linha .= $campo.self::SEPARADOR_PADRAO;
        }

        return $linha;
    }

    /**
     * Extrai dados do XML da nota fiscal
     *
     * @param  mixed  $notaFiscal
     */
    protected function extrairDadosDoXml($notaFiscal, array $buscas): array
    {
        if (empty($notaFiscal->xml)) {
            return [];
        }

        try {
            // Descompactar o XML
            $xmlContent = gzuncompress($notaFiscal->xml);

            // Usar o serviço de leitura de XML para converter para array
            $xmlService = new \App\Services\Xml\XmlReaderService;
            $dados = $xmlService->read($xmlContent);

            $resultados = [];
            foreach ($buscas as $nome => $chaves) {
                $resultados[$nome] = $this->procurarNoArray($dados, $chaves);
            }

            return $resultados;
        } catch (\Exception $e) {
            // Em caso de erro na leitura do XML, retornar array vazio
            Log::warning('Erro ao processar XML da NFe: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Procura por chaves específicas em um array multidimensional
     */
    protected function procurarNoArray(array $array, array $chaves): array
    {
        foreach ($chaves as $chave) {
            if (isset($array[$chave]) && is_array($array[$chave])) {
                return $array[$chave];
            }
        }

        // Procurar recursivamente
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $resultado = $this->procurarNoArray($value, $chaves);
                if (! empty($resultado)) {
                    return $resultado;
                }
            }
        }

        return [];
    }

    /**
     * Valida se o registro está em conformidade com o layout
     */
    public function isValid(): bool
    {
        // Por padrão, assumimos que o registro é válido
        // Classes filhas podem sobrescrever este método para validações específicas
        return true;
    }

    protected static function getCest($ncm)
    {
        $valueSearch = substr($ncm, 0, 4);

        $cests = Cache::remember('tabela_cest', 3600, function () {

            return Cest::all();
        });

        $value = $cests->filter(function ($item) use ($valueSearch) {

            return stripos($item->ncm, $valueSearch) !== false;
        });

        return $value->first()?->cest;
    }

    protected function converterCSTICMS($cst)
    {
        $result = '000';

        switch ($cst) {
            case '00':
            case '100':
            case '200':
            case '300':
            case '400':
            case '500':
            case '600':
            case '700':
            case '800':
                $result = '000';
                break;

            case '10':
            case '110':
            case '210':
            case '310':
            case '410':
            case '510':
            case '610':
            case '710':
            case '810':
                $result = '010';
                break;

            case '20':
            case '120':
            case '220':
            case '320':
            case '420':
            case '520':
            case '620':
            case '720':
            case '820':
                $result = '020';
                break;

            case '30':
            case '130':
            case '230':
            case '330':
            case '430':
            case '530':
            case '630':
            case '730':
            case '830':
                $result = '030';
                break;

            case '40':
            case '140':
            case '240':
            case '340':
            case '440':
            case '540':
            case '640':
            case '740':
            case '840':
                $result = '040';
                break;

            case '41':
            case '141':
            case '241':
            case '341':
            case '441':
            case '541':
            case '641':
            case '741':
            case '841':
                $result = '041';
                break;

            case '50':
            case '150':
            case '250':
            case '350':
            case '450':
            case '550':
            case '650':
            case '750':
            case '850':
                $result = '050';
                break;

            case '51':
            case '151':
            case '251':
            case '351':
            case '451':
            case '551':
            case '651':
            case '751':
            case '851':
                $result = '051';
                break;

            case '60':
            case '260':
            case '360':
            case '460':
            case '560':
            case '760':
            case '860':
                $result = '060';
                break;

            case '70':
            case '270':
            case '370':
            case '470':
            case '570':
            case '770':
            case '870':
                $result = '070';
                break;

            case '90':
            case '190':
            case '290':
            case '390':
            case '490':
            case '590':
            case '690':
            case '790':
            case '890':
                $result = '090';
                break;
        }

        return $result;
    }

    protected function converterCSTIPI($cst)
    {
        $result = '0,00';

        switch ($cst) {
            case '50':
                $result = '00';
                break;

            case '51':
                $result = '01';
                break;

            case '52':
                $result = '02';
                break;

            case '53':
                $result = '03';
                break;

            case '54':
                $result = '04';
                break;

            case '55':
                $result = '05';
                break;

            case '99':
                $result = '49';
                break;
        }

        return $result;
    }

    /**
     * Verifica se o IPI deve ser zerado para a tag/issuer atual
     * Utiliza cache estático para evitar consultas repetidas ao banco de dados
     */
    protected function isZeraIpi(Issuer $issuer, int $tagId): bool
    {
        // Gera a chave de cache única para esta combinação issuer/tag
        $cacheKey = "entradas_imposto_equivalente_{$issuer->id}_{$tagId}";

        // Verifica se já está em cache
        if (isset(self::$zeraIpiCache[$cacheKey])) {
            return self::$zeraIpiCache[$cacheKey];
        }

        // Realiza a consulta no banco de dados
        $check = EntradasImpostosEquivalente::where('tag_id', $tagId)
            ->where('issuer_id', $issuer->id)
            ->where('status_ipi', true)
            ->first();

        // Armazena o resultado em cache
        $result = $check !== null;
        self::$zeraIpiCache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Verifica se o ICMS deve ser zerado para a tag/issuer atual
     * Utiliza cache estático para evitar consultas repetidas ao banco de dados
     */
    protected function isZeraIcms(Issuer $issuer, int $tagId): bool
    {
        // Gera a chave de cache única para esta combinação issuer/tag
        $cacheKey = "entradas_imposto_equivalente_{$issuer->id}_{$tagId}";

        // Verifica se já está em cache
        if (isset(self::$zeraIcmsCache[$cacheKey])) {
            return self::$zeraIcmsCache[$cacheKey];
        }

        // Realiza a consulta no banco de dados
        $check = EntradasImpostosEquivalente::where('tag_id', $tagId)
            ->where('issuer_id', $issuer->id)
            ->where('status_icms', true)
            ->first();

        // Armazena o resultado em cache
        $result = $check !== null;
        self::$zeraIcmsCache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Obtém o identificador do produto (campo 91)
     * Se a nota fiscal é entrada própria, usa o código do produto
     * Caso contrário, usa o external_id do ProdutoFornecedor
     */
    protected function obterIdentificador(NotaFiscalEletronica $notaFiscal, array $produto, ?string $issuerCnpj): ?string
    {
        // Verifica se é entrada própria
        // Entrada própria: emitente_cnpj == destinatario_cnpj (ou issuer_cnpj)
        $isEntradaPropria = false;

        if ($issuerCnpj) {
            $isEntradaPropria = ($notaFiscal->emitente_cnpj === $issuerCnpj);
        } else {
            $isEntradaPropria = ($notaFiscal->emitente_cnpj === $notaFiscal->destinatario_cnpj);
        }

        // Se for entrada própria, usa o código do produto
        if ($isEntradaPropria) {
            return $produto['cProd'] ?? null;
        }

        // Caso contrário, busca o external_id no ProdutoFornecedor
        return $this->obterExternalIdProduto($notaFiscal, $produto);
    }

    /**
     * Busca o external_id do ProdutoFornecedor
     * Utiliza cache estático e findOrCreate para otimizar as consultas
     */
    protected function obterExternalIdProduto(NotaFiscalEletronica $notaFiscal, array $produto): ?string
    {
        $codigoProduto = $produto['cProd'] ?? '';

        if (empty($codigoProduto) || empty($notaFiscal->emitente_cnpj) || empty($notaFiscal->nNF)) {
            return null;
        }

        // Cria a chave do cache
        $cacheKey = $this->getProdutoFornecedorCacheKey(
            $notaFiscal->emitente_cnpj,
            $notaFiscal->nNF,
            $codigoProduto
        );

        // Verifica se já está em cache
        if (isset(self::$produtoFornecedorCache[$cacheKey])) {
            $produtoFornecedor = self::$produtoFornecedorCache[$cacheKey];

            return $produtoFornecedor?->external_id;
        }

        // Busca pelo produto do fornecedor usando cnpj, num_nfe e codigo_produto
        // Se não encontrar, cria com os dados adicionais (incluindo external_id aleatório)
        $produtoFornecedor = \App\Models\ProdutoFornecedor::firstOrCreate(
            [
                'cnpj' => $notaFiscal->emitente_cnpj,
                'num_nfe' => $notaFiscal->nNF,
                'serie_nfe' => $notaFiscal->serie,
                'codigo_produto' => $codigoProduto,
            ],
            [
                'external_id' => str()->random(14),
                'descricao_produto' => $produto['xProd'] ?? $codigoProduto,
                'unidade_comercializada' => $produto['uCom'] ?? 'UN',
            ]
        );

        // Armazena em cache
        self::$produtoFornecedorCache[$cacheKey] = $produtoFornecedor;

        // Retorna o external_id se existir
        return $produtoFornecedor->external_id ?? null;
    }

    /**
     * Gera a chave única para o cache de ProdutoFornecedor
     */
    private function getProdutoFornecedorCacheKey(string $cnpj, string $numNfe, string $codigoProduto): string
    {
        return md5("{$cnpj}_{$numNfe}_{$codigoProduto}");
    }
}
