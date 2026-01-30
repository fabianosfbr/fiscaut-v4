<?php

namespace Tests\Feature\Sefaz;

use App\Jobs\Sefaz\SefazNfeDownloadAndProcessBatchJob;
use App\Models\Issuer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SefazNfePipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_pipeline_salva_documentos_vindos_do_mock_da_sefaz(): void
    {
        config()->set('queue.default', 'sync');
        config()->set('sefaz.distdfe.mock.enabled', true);
        config()->set('sefaz.distdfe.mock.path', base_path('resources/mocks/sefaz/distdfe.xml'));

        $tenant = Tenant::create(['name' => 'Tenant Teste']);
        $user = User::factory()->create();

        $issuer = Issuer::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'cnpj' => '00000000000191',
            'razao_social' => 'EMPRESA TESTE',
            'ult_nsu_nfe' => 0,
        ]);

        (new SefazNfeDownloadAndProcessBatchJob($issuer))->handle();

        $this->assertDatabaseCount('xml_import_jobs', 1);
        $this->assertDatabaseCount('log_sefaz_nfe_contents', 2);
        $this->assertDatabaseCount('log_sefaz_resumo_nfes', 1);
        $this->assertDatabaseCount('log_sefaz_nfe_events', 1);

        $this->assertSame(2, (int) $issuer->fresh()->ult_nsu_nfe);
    }
}
