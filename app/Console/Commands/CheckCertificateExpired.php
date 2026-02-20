<?php

namespace App\Console\Commands;

use App\Jobs\ExpiredCertificateNotificationJob;
use Illuminate\Console\Command;

class CheckCertificateExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-certificate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifica os admins sobre certificados expirados';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        ExpiredCertificateNotificationJob::dispatch();

    }
}
