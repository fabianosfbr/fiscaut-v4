<?php

namespace Tests\Feature\Sefaz;

use App\Jobs\Sefaz\SefazNfeDownloadAndProcessBatchJob;
use App\Models\Issuer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ConsultaNfeEmLoteCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_comando_dispara_job_para_uma_unica_empresa(): void
    {
        $tenant = Tenant::create(['name' => 'Tenant Teste']);
        $user = User::factory()->create();

        Issuer::unguard();
        Issuer::create([
            'id' => 11,
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'cnpj' => '00000000000191',
            'razao_social' => 'EMPRESA TESTE',
            'ult_nsu_nfe' => 0,
        ]);
        Issuer::reguard();

        Queue::fake();

        $this->artisan('app:sync-nfe-sefaz')->assertExitCode(0);

        Queue::assertPushed(SefazNfeDownloadAndProcessBatchJob::class, 1);
    }
}
