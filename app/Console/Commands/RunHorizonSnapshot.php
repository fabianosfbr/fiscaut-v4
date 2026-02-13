<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunHorizonSnapshot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-horizon-snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executa o snapshot do Horizon';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        Artisan::call('horizon:snapshot');

        return Command::SUCCESS;
    }
}
