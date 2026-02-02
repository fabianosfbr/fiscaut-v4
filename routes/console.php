<?php

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use App\Events\NfseCancelada;
use App\Models\LogSefazNfseEvent;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Artisan;


Artisan::command('play', function () {

    $logs = LogSefazNfseEvent::get();

    foreach ($logs as $log) {
        event(new NfseCancelada($log));
    }
});

Artisan::command('schedule:run-dynamic {--force}', function () {

    $arguments = [];
    if ((bool) $this->option('force')) {
        $arguments['--force'] = true;
    }

    $exitCode = $this->call('schedule:run', $arguments);

    return $exitCode;
})->purpose('Executa o scheduler carregando tarefas dinâmicas do banco');

$argv = $_SERVER['argv'] ?? [];
$artisanCommand = $argv[1] ?? null;
app(DynamicTaskCommandExecutor::class)->registerFromDatabase(is_string($artisanCommand) ? $artisanCommand : null);
