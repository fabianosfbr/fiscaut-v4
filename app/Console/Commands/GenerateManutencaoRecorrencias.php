<?php

namespace App\Console\Commands;

use App\Jobs\GenerateManutencaoRecorrenciasJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateManutencaoRecorrencias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manutencao:generate-recorrencias
                            {--force : Forçar a geração mesmo fora do horário programado}
                            {--queue= : Nome da fila para processamento (padrão: default)}
                            {--delay=0 : Delay em segundos antes de processar (padrão: 0)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera manutenções recorrentes automaticamente';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Iniciando geração de manutenções recorrentes...');

        // Verificar se está no horário permitido (se não for forçado)
        if (!$this->option('force')) {
            $horaAtual = now()->hour;
            $horarioPermitido = in_array($horaAtual, [0, 1, 2, 3, 4, 5, 22, 23]); // Madrugada e final de expediente

            if (!$horarioPermitido) {
                $this->warn('A geração automática só deve ser executada fora do horário comercial (00h-06h ou 22h-23h)');
                $confirmar = $this->confirm('Deseja continuar mesmo assim?', false);

                if (!$confirmar) {
                    $this->info('Operação cancelada pelo usuário.');
                    return self::FAILURE;
                }
            }
        }

        try {
            $queue = $this->option('queue') ?: 'default';
            $delay = (int) $this->option('delay');

            // Disparar o job na fila
            if ($delay > 0) {
                GenerateManutencaoRecorrenciasJob::dispatch()->onQueue($queue)->delay(now()->addSeconds($delay));
                $this->info("Job agendado para execução em {$delay} segundos na fila: {$queue}");
            } else {
                GenerateManutencaoRecorrenciasJob::dispatch()->onQueue($queue);
                $this->info("Job disparado na fila: {$queue}");
            }

            Log::info("Command manutencao:generate-recorrencias executado. Fila: {$queue}, Delay: {$delay}s");

            $this->info('Comando executado com sucesso! Verifique os logs para acompanhar o progresso.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erro ao disparar o job: ' . $e->getMessage());
            Log::error('Erro no command manutencao:generate-recorrencias: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
