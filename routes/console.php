<?php

use App\Models\Issuer;
use App\Models\UploadFile;
use App\Events\NfeCancelada;
use App\Models\LogSefazNfeEvent;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Services\Sefaz\SefazNfeDownloadService;
use App\Console\Scheduling\DynamicTaskCommandExecutor;

Artisan::command('play', function () {

    $nfe = NotaFiscalEletronica::where('id', 522935, 522935 )->get();
    $nfe = NotaFiscalEletronica::whereId(522811 )->first();
    dd($nfe->produtos);
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
