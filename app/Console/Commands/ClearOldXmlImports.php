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
        $cutoffDate = Carbon::now()->subDay(24);

        $this->info("Removendo jobs de importação XML anteriores a {$cutoffDate->toDateTimeString()}...");

        $deletedCount = XmlImportJob::where('created_at', '<', $cutoffDate)->delete();

        $this->info("{$deletedCount} registros excluídos com sucesso.");
    }
}
