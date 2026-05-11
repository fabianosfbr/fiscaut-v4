<?php

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use App\Jobs\SendCobrancaEmailJob;
use App\Models\Issuer;
use App\Models\SuperLogicaUnidade;
use App\Services\Xml\XmlIdentifierService;
use App\Services\Xml\XmlNfeReaderService;
use App\Services\SuperlogicaConnectionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('play', function () {
    $issuer = Issuer::find(62);

    $totalDocumentos = 0;
    $apiUrl = 'https://api.sieg.com/BaixarXmlsV2';
    $apiKey = 'ghQW%2bI2NaeKwM6iaAAYghw%3d%3d';
    $payload = [
        'XmlType' => 1,
        'CnpjEmit' => '67439638000285',
        'Take' => 50,
        'Skip' => 0,
        'DataEmissaoInicio' => '2026-05-08',
        'DataEmissaoFim' => '2026-05-08',
        'Downloadevent' => false,
    ];

    // Realizar a requisição para a API
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ])->post($apiUrl . '?api_key=' . $apiKey, $payload);

    if ($response->successful()) {
        $responseData = $response->json();

        if (isset($responseData['xmls']) && is_array($responseData['xmls'])) {
            $resultados = $responseData['xmls'];

            $totalDocumentos += count($resultados);

            // Verifica se retornou o número máximo de resultados, indicando que pode haver mais
            if (count($resultados) == $payload['Take']) {
                $this->skip += $payload['Take'];
                $temMaisResultados = true;
            } else {
                // Se retornou menos que o máximo, não há mais resultados
                $temMaisResultados = false;
            }

            foreach ($resultados as $value) {
                $xmlContents = base64_decode($value);

                if (1 == 1) {
                    $parsed =(new XmlNfeReaderService)
                        ->loadXml($xmlContents)
                        ->setOrigem('SIEG')
                        ->setIssuer($issuer)
                        ->parse();
               
                }
            }
        }
    }

    $issuer = Issuer::find(62);

    $diasConfig = [20];

    $service = new SuperlogicaConnectionService($issuer);

    $inadimplencias = $service
        ->receita()
        ->listarInadimplencia([
            'idCondominio' => $issuer->superlogica_condominio_id,
        ]);

    foreach ($inadimplencias as $record) {
        $titulosAtrasados = [];

        foreach ($record['recebimento'] as $recb) {
            $vencimentoStr = data_get($recb, 'dt_vencimento_recb');
            if (!$vencimentoStr) {
                continue;
            }

            try {
                $vencimento = Carbon::createFromFormat('m/d/Y H:i:s', $vencimentoStr)->startOfDay();
            } catch (Exception $e) {
                try {
                    $vencimento = Carbon::parse($vencimentoStr)->startOfDay();
                } catch (Exception $e) {
                    continue;
                }
            }

            $diasAtraso = data_get($recb, 'encargos.0.diasatraso');

            $valor = number_format((float) data_get($recb, 'encargos.0.valorcorrigido', data_get($recb, 'vl_emitido_recb', 0)), 2, ',', '.');
            $titulosAtrasados[] = "Vencimento: {$vencimento->format('d/m/Y')} - Valor: R\$ {$valor}";

            if (in_array((string) $diasAtraso, $diasConfig)) {
                $deveNotificar = true;
            }
        }

        $email = data_get($record, 'recebimento.0.contatosunidade.0.proprietario.0.email');

        if (!$email) {
            $idUnidade = data_get($record, 'id_unidade_uni') ?? data_get($record, 'st_unidade_uni');
            $unidade = SuperLogicaUnidade::where('id_unidade_uni', $idUnidade)
                ->where('id_condominio', $this->issuer->superlogica_condominio_id)
                ->first();

            $email = $unidade ? data_get($unidade, 'metadados.email_proprietario') : null;
        }

        if ($email) {
            $titulosHtml = '<ul>';
            foreach ($titulosAtrasados as $t) {
                $titulosHtml .= "<li>{$t}</li>";
            }
            $titulosHtml .= '</ul>';

            $unidadeData = [
                'numero_unidade' => data_get($record, 'st_unidade_uni', ''),
                'bloco_quadra' => data_get($record, 'st_bloco_uni', ''),
                'nome_morador' => data_get($record, 'st_sacado_uni', ''),
                'titulos_aberto' => $titulosHtml,
                'id_condominio_cond' => data_get($recb, 'id_condominio_cond'),
                'id_recebimento_recb' => data_get($recb, 'id_recebimento_recb'),
                'id_unidade_uni' => data_get($recb, 'id_unidade_uni'),
                'recebimento' => $recb,
            ];

            SendCobrancaEmailJob::dispatch($issuer->id, 'conib40105@inreur.com', $unidadeData);
            // sleep(10);

            dd($unidadeData);
        }
    }

    dd('parei');

    $unidade = SuperLogicaUnidade::find(800);
    //   dd($unidade->metadados);

    $mapa = [
        'st_unidade_uni' => 'numero_unidade',
        'st_bloco_uni' => 'bloco_quadra',
        'st_sacado_uni' => 'nome_morador',
    ];

    $unidadeData = [];
    foreach ($mapa as $chaveOriginal => $chaveNova) {
        if (isset($unidade->metadados[$chaveOriginal])) {
            $unidadeData[$chaveNova] = $unidade->metadados[$chaveOriginal];
        }
    }

    SendCobrancaEmailJob::dispatch($issuer->id, 'contato@fabianofernandes.adm.br;gerencia.cont@speedgrupo.com.br', $unidadeData);

    dd('enviado');

    $service = new SuperlogicaConnectionService($issuer);
    $condominios = $service
        ->despesa()
        ->listarFornecedor([
            'contatosDoTipo' => 'fornecedores',
            'itensPorPagina' => 50,
            'pagina' => 1,
        ]);

    dd($condominios[0]);

    $unidades = $service->unidade()->listar([
        'idCondominio' => 237,
        'exibirDadosDosContatos' => 1,
        'exibirGruposDasUnidades' => 1,
        'exibirInadimplencia' => 1,
        'pagina' => 1,
    ]);

    dd(count($unidades));

    dd($issuer->tenant()->first()->superlogica_base_url);
});

Artisan::command('schedule:run-dynamic {--force}', function (DynamicTaskCommandExecutor $executor) {
    if ((bool) $this->option('force')) {
        $this->info('Forçando a execução de todas as tarefas dinâmicas...');
        $executor->runAllNow();
        $this->info('Execução concluída.');

        return 0;
    }

    $this->info('Executando scheduler padrão para tarefas dinâmicas...');
    $exitCode = $this->call('schedule:run');

    return $exitCode;
})->purpose('Executa o scheduler carregando tarefas dinâmicas do banco');

$argv = $_SERVER['argv'] ?? [];
// Tenta encontrar o comando ignorando opções globais (ex: -v, --ansi)
$artisanCommand = collect($argv)
    ->slice(1)
    ->filter(fn($arg) => !str_starts_with($arg, '-'))
    ->first();

app(DynamicTaskCommandExecutor::class)->registerFromDatabase($artisanCommand);
