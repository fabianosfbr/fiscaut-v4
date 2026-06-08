<?php

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('play', function () {
    $retentionDays = 9;
    $endDate = Carbon::now()->subDays($retentionDays);

    $eventos = DB::table('log_sefaz_nfe_events')
        ->where('tp_evento', 110111)
        ->where('dh_evento', '>=', $endDate)
        ->where('is_verificado_sefaz', false)
        ->where('issuer_id', 11)
        ->distinct()
        ->get();

    foreach ($eventos as $key => $evento) {
        // code...
        dd((array) $evento);
    }
    dd($eventos->count());
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
