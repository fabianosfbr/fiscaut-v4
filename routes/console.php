<?php

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use App\Models\Issuer;
use App\Services\SuperlogicaConnectionService;
use Illuminate\Support\Facades\Artisan;

Artisan::command('play', function () {
    $issuer = Issuer::find(60);

    $service = new SuperlogicaConnectionService($issuer->tenant);

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
    ->filter(fn ($arg) => ! str_starts_with($arg, '-'))
    ->first();

app(DynamicTaskCommandExecutor::class)->registerFromDatabase($artisanCommand);
