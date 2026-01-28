<?php

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

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
