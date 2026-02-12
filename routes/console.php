<?php

use App\Models\Issuer;
use App\Events\NfeCancelada;
use App\Models\LogSefazNfeEvent;
use Illuminate\Support\Facades\Artisan;
use App\Services\Sefaz\SefazNfeDownloadService;
use App\Console\Scheduling\DynamicTaskCommandExecutor;

Artisan::command('play', function () {

    $issuer = Issuer::find(11);
    
    $service = new SefazNfeDownloadService($issuer);

    // Download documents in batch
    $result = $service->downloadNfeInBatch(nsu: 490140);


    // foreach ($logs as $log) {
    //     event(new NfseCancelada($log));
    // }
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
