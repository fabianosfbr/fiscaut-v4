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
            ->whereIn('id', [522935, 522936])
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

            // Cria registros 1000 para cada etiqueta aplicada à nota fiscal
            // com valores proporcionais ao valor aplicado a cada etiqueta
            $taggeds = $notaFiscal->tagged ?? collect();
            
            if ($taggeds->isNotEmpty() && $issuer) {
                // Calcula o valor total aplicado às etiquetas
                $valorTotalEtiquetas = $taggeds->sum('value');
                
                // Cria um registro 1000 para cada etiqueta com valores proporcionais
                foreach ($taggeds as $tagged) {                    
                    if ($tagged->tag && $tagged->value > 0) {                       
                        $registro1000 = new \App\Integrations\DominioSistemas\Records\Registro1000(
                            $notaFiscal,
                            $issuer,
                            $tagged->tag->id // Campo 5 - ID da Tag
                        );
                        
                        // Calcula o fator de proporcionalidade
                        $fatorProporcionalidade = $valorTotalEtiquetas > 0 
                            ? ($tagged->value / $valorTotalEtiquetas) 
                            : 1.0;
                        
                        // Aplica o fator de proporcionalidade aos valores
                        $registro1000->setFatorProporcionalidade($fatorProporcionalidade);
                        
                        $registro1000s[] = $registro1000;
                    }
                }
            } elseif ($issuer) {
                // Se não houver etiquetas, cria um único registro 1000 com todos os valores
                $registro1000 = new \App\Integrations\DominioSistemas\Records\Registro1000(
                    $notaFiscal,
                    $issuer,
                    null
                );
                $registro1000s[] = $registro1000;
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

}