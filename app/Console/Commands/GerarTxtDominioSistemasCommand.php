<?php

namespace App\Console\Commands;

use App\Models\Issuer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Models\NotaFiscalEletronica;
use App\Integrations\DominioSistemas\DominioSistemasService;
use App\Integrations\DominioSistemas\Records\RegistroFactory;
use App\Integrations\DominioSistemas\Records\Registro0000;
use App\Integrations\DominioSistemas\Records\Registro0020;
use App\Integrations\DominioSistemas\Records\Registro0030;
use App\Integrations\DominioSistemas\Records\Registro0100;
use App\Integrations\DominioSistemas\Records\Registro1010;
use App\Integrations\DominioSistemas\Records\Registro1015;
use App\Integrations\DominioSistemas\Records\Registro1020;

class GerarTxtDominioSistemasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dominio-sistemas:gerar-txt
                            {--empresa= : Inscrição da empresa (CNPJ/CPF)}
                            {--output= : Caminho do arquivo de saída}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera um arquivo TXT conforme o layout da Domínio Sistemas a partir de uma Collection';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $outputPath = $this->option('output') ?? storage_path('app/dominio_saida.txt');
        $inscricaoEmpresa = $this->option('empresa');

        $issuer = Issuer::where('cnpj', $inscricaoEmpresa)->first();

        if (!$issuer) {
            $this->error('Empresa não encontrada.');
            return 1;
        }

        // Obter os dados do banco de dados com eager loading para evitar N+1
        // Carrega o relacionamento 'tagged' que contém as tags e seus CategoryTags
        $collection = NotaFiscalEletronica::with('tagged.tag')
            ->whereIn('id', [522945])
            ->get();

        if ($collection->isEmpty()) {
            $this->error('A Collection está vazia.');
            return 1;
        }

        $service = new DominioSistemasService();
        $registros = [];

        // Adiciona o registro 0000 (cabeçalho) com o CNPJ da empresa
        $registro0000 = new Registro0000($inscricaoEmpresa);
        $registros[] = $registro0000;

        // Arrays para armazenar registros por tipo
        $registro0020s = [];
        $registro0030s = [];
        $registro0100s = [];
        $registro1000s = [];

        // Obtém o issuer
        $issuer = \App\Models\Issuer::where('cnpj', $inscricaoEmpresa)->first();

        // Processa cada nota fiscal para coletar registros por tipo
        foreach ($collection as $notaFiscal) {
            // Coleta registro 0020 para o emitente da nota fiscal
            $emitenteCnpj = $notaFiscal->emitente_cnpj;
            if (!isset($registro0020s[$emitenteCnpj])) {
                $registro0020 = new Registro0020($notaFiscal);
                $registro0020s[$emitenteCnpj] = $registro0020;
            }

            // Coleta registro 0030 para o transportador da nota fiscal
            if (isset($notaFiscal->transportador_cnpj)) {
                $transportadorCnpj = $notaFiscal->transportador_cnpj;
                if (!isset($registro0030s[$transportadorCnpj])) {
                    $registro0030 = new Registro0030($notaFiscal);
                    $registro0030s[$transportadorCnpj] = $registro0030;
                }
            }

            // Coleta registros 0100 para os produtos da nota fiscal
            $produtos = $notaFiscal->produtos;
            foreach ($produtos as $produto) {
                if (isset($produto['cProd']) && isset($produto['xProd'])) {
                    $registro0100 = new \App\Integrations\DominioSistemas\Records\Registro0100(
                        $notaFiscal,
                        $produto,
                        $inscricaoEmpresa
                    );

                    $registro0100s[] = $registro0100;

                    // Cria registro 0135 (Valor Unitário) para o produto
                    if (isset($produto['vUnCom'])) {

                        $registro0135 = new \App\Integrations\DominioSistemas\Records\Registro0135(
                            $notaFiscal->data_emissao,
                            (float)$produto['vUnCom']
                        );
                        $registro0100s[] = $registro0135;
                    }

                    // Cria registro 0150 (Unidade de Medida) para o produto
                    if (isset($produto['uCom'])) {
                        $registro0150 = new \App\Integrations\DominioSistemas\Records\Registro0150(
                            $produto['uCom'],
                            $produto['uCom'] // Descrição igual à sigla
                        );
                        $registro0100s[] = $registro0150;
                    }
                }
            }

            // Cria registros 1000 agregados por CFOP

            $taggeds = $notaFiscal->tagged ?? collect();
            $etiquetasValidas = $taggeds->filter(function ($tagged) {
                return $tagged->tag && $tagged->value > 0;
            });

            $numSegmento = count($etiquetasValidas) > 1 ? 1 : 0;
            // Cada CFOP gera um ou mais registros 1000, incrementando o segmento para cada etiqueta
            $registrosPorCfop = $this->agregarValoresPorCfop($notaFiscal, $issuer);

            foreach ($registrosPorCfop as $cfop => $valoresSegmento) {

                foreach ($etiquetasValidas as $tagged) {

                    $registro1000 = new \App\Integrations\DominioSistemas\Records\Registro1000(
                        $notaFiscal,
                        $valoresSegmento,
                        $issuer,
                        $tagged->tag->id, // Campo 5 - ID da Tag
                        $numSegmento // Campo 7 - Segmento
                    );

                    $numSegmento++;
                    $registro1000s[] = $registro1000;


                    // Cria registro 1010 (Informação Complementar) se houver
                    $registro1010 = new Registro1010($notaFiscal);
                    $registro1000s[] = $registro1010;

                    // Cria registro 1015 (Observação) se houver
                    $registro1015 = new Registro1015($notaFiscal);
                    $registro1000s[] = $registro1015;

                    // Cria registros 1020 (Impostos) para cada tipo de imposto
                    // Código 1 = ICMS, Código 2 = IPI, Código 8 = DIFAL

                    $registro1020Icms = new Registro1020(1, $valoresSegmento, $notaFiscal, $tagged, $issuer);
                    $registro1000s[] = $registro1020Icms;

                    $registro1020Ipi = new Registro1020(2, $valoresSegmento, $notaFiscal, $tagged, $issuer);
                    $registro1000s[] = $registro1020Ipi;

                    $registro1020Difal = new Registro1020(8, $valoresSegmento, $notaFiscal, $tagged, $issuer);
                    $registro1000s[] = $registro1020Difal;
                }
            }
        }

        // Adiciona os registros na ordem correta: 0000, 0020, 0030, 0100, 1000
        $registros = array_merge($registros, array_values($registro0020s));
        $registros = array_merge($registros, array_values($registro0030s));
        $registros = array_merge($registros, $registro0100s);
        $registros = array_merge($registros, $registro1000s);

        if (empty($registros)) {
            $this->error('Nenhum registro válido foi criado a partir da Collection.');
            return 1;
        }

        $this->info('Gerando arquivo TXT...');

        $success = $service->gerarArquivoTxt($registros, $outputPath, $inscricaoEmpresa);

        if ($success) {
            $this->info("Arquivo TXT gerado com sucesso: {$outputPath}");
            $this->info("Número de registros processados: " . count($registros));
            return 0;
        } else {
            $this->error('Falha ao gerar o arquivo TXT.');
            return 1;
        }
    }

    /**
     * Agrega os valores das notas fiscais por CFOP e gera registros 1000.
     * 
     * Para cada CFOP distinto na nota fiscal, calcula os valores proporcionais
     * das etiquetas e gera registros 1000 segmentados quando necessário.
     *
     * @param NotaFiscalEletronica $notaFiscal
     * @param Issuer $issuer
     * @return array Array de Registro1000
     */
    protected function agregarValoresPorCfop(NotaFiscalEletronica $notaFiscal, Issuer $issuer): array
    {
        $registros = [];
        $taggeds = $notaFiscal->tagged ?? collect();

        // Se não há etiquetas, retorna array vazio
        if ($taggeds->isEmpty()) {
            return $registros;
        }

        // Extrai produtos e agrupa por CFOP
        $produtos = $notaFiscal->produtos ?? [];
        $valoresPorCfop = $this->agruparValoresProdutosPorCfop($produtos);


        return $valoresPorCfop;
    }

    /**
     * Agrupa os valores dos produtos por CFOP, incluindo impostos.
     *
     * @param array $produtos
     * @return array Array associativo com CFOP como chave e valores agregados
     */
    protected function agruparValoresProdutosPorCfop(array $produtos): array
    {
        $valoresPorCfop = [];


        foreach ($produtos as $produto) {
            $cfop = $produto['CFOP'] ?? null;

            if (!$cfop) {
                continue;
            }

            // Inicializa o CFOP se não existir
            if (!isset($valoresPorCfop[$cfop])) {
                $valoresPorCfop[$cfop] = [
                    'cfop' => $cfop,
                    'csosn' => $produto['CSOSN'] ?? null,
                    'valor_base_calculo' => 0.0,
                    'valor_produtos' => 0.0,
                    'valor_frete' => 0.0,
                    'valor_seguro' => 0.0,
                    'valor_despesas' => 0.0,
                    'valor_desconto' => 0.0,
                    'quantidade_itens' => 0,
                    // Impostos
                    'valor_base_calculo' => 0.0,
                    'valor_cst' => 0,
                    'valor_icms' => 0.0,
                    'valor_ipi' => 0.0,
                    'valor_pis' => 0.0,
                    'valor_cofins' => 0.0,
                    'valor_icms_st' => 0.0,
                    'valor_base_calculo_icms_st' => 0.0,
                    'valor_outro' => 0.0,
                    'percentual_icms' => $produto['impostos']['pICMS'] ?? 0.0,
                    'percentual_cofins' => $produto['impostos']['pCOFINS'] ?? 0.0,
                    'percentual_pis' => $produto['impostos']['pPIS'] ?? 0.0,
                    
                    'percentual_st' => 0.0,

                ];
            }

            // Soma os valores do produto ao CFOP
            $valoresPorCfop[$cfop]['valor_base_calculo'] = (float) ($produto['impostos']['vBC'] ?? 0);
            $valoresPorCfop[$cfop]['valor_cst'] = (int) ($produto['impostos']['CST'] ?? 0);
            $valoresPorCfop[$cfop]['valor_produtos'] += (float) ($produto['vProd'] ?? 0);
            $valoresPorCfop[$cfop]['valor_icms'] += (float) ($produto['impostos']['vICMS'] ?? 0);
            $valoresPorCfop[$cfop]['valor_frete'] += (float) ($produto['vFrete'] ?? 0);
            $valoresPorCfop[$cfop]['valor_seguro'] += (float) ($produto['vSeg'] ?? 0);
            $valoresPorCfop[$cfop]['valor_despesas'] += (float) ($produto['vOutro'] ?? 0);
            $valoresPorCfop[$cfop]['valor_desconto'] += (float) ($produto['vDesc'] ?? 0);
            $valoresPorCfop[$cfop]['valor_icms_st'] += (float) ($produto['impostos']['vICMSSTRet'] ?? 0);
            $valoresPorCfop[$cfop]['valor_base_calculo_icms_st'] = (float) ($produto['impostos']['vBCSTRet'] ?? 0);
            $valoresPorCfop[$cfop]['valor_ipi'] += (float) ($produto['impostos']['vIPI'] ?? 0);
            $valoresPorCfop[$cfop]['valor_pis'] += (float) ($produto['impostos']['vPIS'] ?? 0);
            $valoresPorCfop[$cfop]['valor_cofins'] += (float) ($produto['impostos']['vCOFINS'] ?? 0);
            $valoresPorCfop[$cfop]['percentual_cofins'] = (float) ($produto['impostos']['pCOFINS'] ?? 0);
            $valoresPorCfop[$cfop]['percentual_pis'] = (float) ($produto['impostos']['pPIS'] ?? 0);
            if (($produto['impostos']['pICMS'] ?? null) !== null && (float) $produto['impostos']['pICMS'] != 0) {
                $valoresPorCfop[$cfop]['percentual_icms'] = (float) $produto['impostos']['pICMS'];
            }
            $valoresPorCfop[$cfop]['percentual_st'] = (float) ($produto['impostos']['pST'] ?? 0);


            $valoresPorCfop[$cfop]['quantidade_itens']++;
        }

        return $valoresPorCfop;
    }
}
