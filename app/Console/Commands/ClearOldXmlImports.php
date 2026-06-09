<?php

namespace App\Console\Commands;

use App\Models\XmlImportJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearOldXmlImports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-old-xml-import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove registros de importação XML mais antigos que 24 horas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $retentionDays = config('admin.schedule_history_retention_days', 7);

        $cutoffDate = Carbon::now()->subDays($retentionDays);

        $this->info("Removendo jobs de importação XML anteriores a {$cutoffDate->toDateTimeString()}...");

        $deletedCount = XmlImportJob::where('created_at', '<', $cutoffDate)->delete();
        $deletedCount += XmlImportJob::where('status', XmlImportJob::STATUS_PENDING)
            ->orWhere('status', XmlImportJob::STATUS_PROCESSING)
            ->delete();

        $this->info("{$deletedCount} registros excluídos com sucesso.");
    }
}
