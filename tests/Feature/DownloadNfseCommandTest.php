<?php

namespace Tests\Feature;

use App\Jobs\Sefaz\SefazNfseDownloadAndProcessBatchJob;
use App\Models\Issuer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DownloadNfseCommandTest extends TestCase
{
    use RefreshDatabase;

    private function getProtectedProperty(object $object, string $property)
    {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setAccessible(true);

        return $reflection->getValue($object);
    }

    public function test_dispatches_job_for_single_issuer(): void
    {
        Queue::fake();

        $tenant = Tenant::create(['name' => 'Tenant Test']);
        $user = User::create([
            'name' => 'User Test',
            'email' => 'user-test@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
        ]);

        $issuer = Issuer::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'cnpj' => '00000000000191',
            'razao_social' => 'Empresa Teste LTDA',
            'validade_certificado' => now()->addDay(),
            'is_enabled' => true,
            'nfse_servico' => true,
        ]);

        $this->artisan('sefaz:download-nfse', [
            '--issuer' => $issuer->id,
            '--nsu' => '123',
        ])->assertExitCode(0);

        Queue::assertPushed(SefazNfseDownloadAndProcessBatchJob::class, function (SefazNfseDownloadAndProcessBatchJob $job) use ($issuer) {
            return $this->getProtectedProperty($job, 'issuer')->is($issuer);
        });
    }

    public function test_dispatches_job_for_all_eligible_issuers_when_no_issuer_option(): void
    {
        Queue::fake();

        $tenant = Tenant::create(['name' => 'Tenant Test']);
        $user = User::create([
            'name' => 'User Test',
            'email' => 'user-test-2@example.com',
            'password' => bcrypt('password'),
            'tenant_id' => $tenant->id,
        ]);

        $issuer1 = Issuer::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'cnpj' => '00000000000191',
            'razao_social' => 'Empresa Teste 1 LTDA',
            'validade_certificado' => now()->addDay(),
            'is_enabled' => true,
            'nfse_servico' => true,
        ]);

        $issuer2 = Issuer::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'cnpj' => '00000000000272',
            'razao_social' => 'Empresa Teste 2 LTDA',
            'validade_certificado' => now()->addDay(),
            'is_enabled' => true,
            'nfse_servico' => true,
        ]);

        Issuer::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'cnpj' => '00000000000353',
            'razao_social' => 'Empresa Ignorada LTDA',
            'validade_certificado' => now()->subDay(),
            'is_enabled' => true,
            'nfse_servico' => true,
        ]);

        $this->artisan('sefaz:download-nfse', [
            '--nsu' => '123',
        ])->assertExitCode(0);

        Queue::assertPushed(SefazNfseDownloadAndProcessBatchJob::class, 2);
        Queue::assertPushed(SefazNfseDownloadAndProcessBatchJob::class, function (SefazNfseDownloadAndProcessBatchJob $job) use ($issuer1) {
            return $this->getProtectedProperty($job, 'issuer')->is($issuer1);
        });
        Queue::assertPushed(SefazNfseDownloadAndProcessBatchJob::class, function (SefazNfseDownloadAndProcessBatchJob $job) use ($issuer2) {
            return $this->getProtectedProperty($job, 'issuer')->is($issuer2);
        });
    }
}
