<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-old-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove registros de notificações antigas maiores que 7 dias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subDays(7);

        $this->info("Removendo notificações anteriores a {$cutoffDate->toDateTimeString()}...");

        $deletedCount = DB::table('notifications')->where('created_at', '<', $cutoffDate)->delete();

        $this->info("{$deletedCount} registros excluídos com sucesso.");
    }
}
