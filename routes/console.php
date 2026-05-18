<?php

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use App\Models\Issuer;
use App\Services\SuperlogicaConnectionService;
use Illuminate\Support\Facades\Artisan;
use League\Uri\Http;

Artisan::command('play', function () {
    // $response = Http::withHeaders([
    //     'app_token' => 'ea3d95dc-0497-4ff1-8ced-d8f975a7fa0d',
    //     'access_token' => '8bf684b7-ef02-46e6-9038-bd9842f06d26',
    // ])
    //     ->timeout(0)  // Sem timeout (0 = infinito)
    //     ->get('https://api.superlogica.net/v2/condor/publico/downloadarquivo/?id=739167&hash=93dd66c71600e72a15d647cb7c4aa7947b66d278');

    // // Retorna o corpo da resposta como string
    // dd($response->body());

    $issuer = Issuer::find(155);

    $service = new SuperlogicaConnectionService($issuer->tenant);

    $params = [
        'id' => 739167,
        'hash' => '93dd66c71600e72a15d647cb7c4aa7947b66d278',
    ];

    $documento = $service
        ->documento()
        ->download($params);

    dd($documento);

    $params = [
        'idCondominio' => $issuer->superlogica_condominio_id,
        'dtInicio' => '05/01/2026',
        'comStatus' => 'pendentes',
    ];

    $despesas = $service
        ->despesa()
        ->listarDespesa($params);

    ds($despesas[0]);
    dd($despesas[0]);

    $params = [
        'NM_PROCESSO_PROC' => 'S/N',
        'ID_UNIDADE_UNI' => '26797',
        'ID_CONDOMINIO_COND' => '253',
        'DT_ABERTURA_PROC' => now()->format('m/d/Y'),
        'FL_STATUS_PROC' => '1',
        'COBRANCAS[0][ID_CONDOMINIO_COND]' => '253',
        'COBRANCAS[0][ID_RECEBIMENTO_RECB]' => '1161235',
        'COBRANCAS[0][DT_VENCIMENTO_RECB]' => '11/05/2026',
        'COBRANCAS[0][VL_EMITIDO_RECB]' => '377.58',
    ];

    $processo = $service
        ->receita()
        ->novoProcessoJudicial($params);

    dd($processo);

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
