<?php

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use App\Models\Issuer;
use Illuminate\Support\Facades\Artisan;

Artisan::command('play', function () {

    $issuer = Issuer::find(60);

    $service = new \App\Services\SuperlogicaConnectionService($issuer);
    $condominios = $service->condominio()->listar([
        'id' => -1,
        'somenteCondominiosAtivos' => 1,
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
    ->filter(fn($arg) => ! str_starts_with($arg, '-'))
    ->first();

app(DynamicTaskCommandExecutor::class)->registerFromDatabase($artisanCommand);
