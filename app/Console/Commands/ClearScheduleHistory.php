<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\ScheduleHistory;
use Carbon\Carbon;

class ClearScheduleHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:schedule-clear-history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa o histórico de agendamentos com base nos dias de retenção definidos no .env';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $retentionDays = config('admin.schedule_history_retention_days', 7);
        $cutoffDate = Carbon::now()->subDays($retentionDays);

        $this->info("Limpando histórico de agendamentos anteriores a {$cutoffDate->toDateTimeString()} ({$retentionDays} dias)...");

        $deletedCount = ScheduleHistory::where('created_at', '<', $cutoffDate)->delete();

        $this->info("{$deletedCount} registros excluídos com sucesso.");
    }
}
