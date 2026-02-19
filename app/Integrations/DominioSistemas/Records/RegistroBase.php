<?php

namespace App\Integrations\DominioSistemas\Records;

use App\Models\Cest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Classe abstrata base que define a estrutura comum a todos os registros
 * do layout da Domínio Sistemas.
 */
abstract class RegistroBase implements IRegistro
{
    protected const SEPARADOR_PADRAO = '|';
    protected const CODIFICACAO_ARQUIVO = 'Windows-1252';

    /**
     * Retorna o tipo de registro (ex: 0000, 0010, 0100, etc.)
     *
     * @return string
     */
    abstract public function getTipoRegistro(): string;

    /**
     * Converte o registro para uma linha no formato TXT
     *
     * @return string
     */
    abstract public function converterParaLinhaTxt(): string;

    /**
     * Formata um campo de acordo com as regras do layout
     *
     * @param mixed $valor
     * @param int|null $tamanhoMaximo
     * @param string $tipo (C=Caractere, N=Numérico inteiro, D=Decimal com 3 casas, D6=Decimal com 6 casas, X=Data)
     * @return string
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
                    $valor = (string)$valor;
                }
                break;

            case 'N': // Numérico inteiro
                if (is_numeric($valor)) {
                    $valor = (string)(int)$valor;
                } else {
                    $valor = (string)$valor;
                }
                break;
                
            case 'D': // Decimal: usar vírgula como separador com 3 casas decimais
                if (is_numeric($valor)) {
                    // Formata com 3 casas decimais e usa vírgula como separador
                    $valor = number_format((float)$valor, 3, ',', '');
                } else {
                    $valor = (string)$valor;
                }
                break;

            case 'D2': // Decimal: usar vírgula como separador com 2 casas decimais
                if (is_numeric($valor)) {
                    // Formata com 2 casas decimais e usa vírgula como separador
                    $valor = number_format((float)$valor, 2, ',', '');
                } else {
                    $valor = (string)$valor;
                }
                break;
                
            case 'D6': // Decimal: usar vírgula como separador com 6 casas decimais
                if (is_numeric($valor)) {
                    // Formata com 6 casas decimais e usa vírgula como separador
                    $valor = number_format((float)$valor, 6, ',', '');
                } else {
                    $valor = (string)$valor;
                }
                break;

            case 'C': // Caractere
            default:
                $valor = (string)$valor;
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
     *
     * @param string $conteudo
     * @return string
     */
    protected function codificarConteudo(string $conteudo): string
    {
        return mb_convert_encoding($conteudo, self::CODIFICACAO_ARQUIVO, 'UTF-8');
    }

    /**
     * Monta uma linha do arquivo TXT com os campos separados por pipe
     *
     * @param array $campos
     * @return string
     */
    protected function montarLinha(array $campos): string
    {
        $linha = self::SEPARADOR_PADRAO; // Começa com o separador
        foreach ($campos as $campo) {
            $linha .= $campo . self::SEPARADOR_PADRAO;
        }

        return $linha;
    }

    /**
     * Extrai dados do XML da nota fiscal
     *
     * @param mixed $notaFiscal
     * @param array $buscas
     * @return array
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
            $xmlService = new \App\Services\Xml\XmlReaderService();
            $dados = $xmlService->read($xmlContent);

            $resultados = [];
            foreach ($buscas as $nome => $chaves) {
                $resultados[$nome] = $this->procurarNoArray($dados, $chaves);
            }

            return $resultados;
        } catch (\Exception $e) {
            // Em caso de erro na leitura do XML, retornar array vazio
            Log::warning("Erro ao processar XML da NFe: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Procura por chaves específicas em um array multidimensional
     *
     * @param array $array
     * @param array $chaves
     * @return array
     */
    protected function procurarNoArray(array $array, array $chaves): array
    {
        foreach ($chaves as $chave) {
            if (isset($array[$chave])) {
                return $array[$chave];
            }
        }

        // Procurar recursivamente
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $resultado = $this->procurarNoArray($value, $chaves);
                if (!empty($resultado)) {
                    return $resultado;
                }
            }
        }

        return [];
    }

    /**
     * Valida se o registro está em conformidade com o layout
     *
     * @return bool
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

            return false !== stripos($item->ncm, $valueSearch);
        });

        return $value->first()?->cest;
    }
}
