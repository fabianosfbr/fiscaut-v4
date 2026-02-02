<?php

namespace Tests\Unit;

use App\Enums\XmlImportJobType;
use App\Jobs\Sefaz\SefazNfseProcessDocumentJob;
use App\Models\Issuer;
use App\Models\Tenant;
use App\Models\User;
use App\Models\XmlImportJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SefazNfseProcessDocumentJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_increments_counters_for_nfse_document(): void
    {
        $nfseXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<NFSe>
  <infNFSe>
    <nNFSe>1</nNFSe>
    <valores>
      <vLiq>10.00</vLiq>
    </valores>
    <emit>
      <CNPJ>12345678000199</CNPJ>
      <xNome>Prestador</xNome>
      <IM>123</IM>
    </emit>
    <DPS>
      <infDPS>
        <codVerif>ABC</codVerif>
        <dhEmi>2026-01-01T00:00:00-03:00</dhEmi>
        <cLocEmi>3550308</cLocEmi>
        <toma>
          <CNPJ>99999999000199</CNPJ>
          <xNome>Tomador</xNome>
          <IM>999</IM>
        </toma>
      </infDPS>
    </DPS>
  </infNFSe>
</NFSe>
XML;

        $tenant = Tenant::create(['name' => 'Tenant Test']);
        $user = User::create([
            'name' => 'User Test',
            'email' => 'user-test-3@example.com',
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

        $importJob = XmlImportJob::create([
            'tenant_id' => $tenant->id,
            'issuer_id' => $issuer->id,
            'owner_id' => $issuer->id,
            'owner_type' => $issuer::class,
            'import_type' => XmlImportJobType::SYSTEM,
            'status' => XmlImportJob::STATUS_PENDING,
            'processed_files' => 0,
            'imported_files' => 0,
            'error_files' => 0,
            'total_files' => 1,
            'errors' => [],
        ]);

        $job = new SefazNfseProcessDocumentJob([
            'nsu' => 1,
            'tipo_documento' => 'NFSE',
            'chave_acesso' => 'NFSECHAVE123',
            'xml_content' => $nfseXml,
        ], $issuer, $importJob);

        $job->handle();

        $importJob->refresh();

        $this->assertSame(1, $importJob->num_documents);
        $this->assertSame(0, $importJob->num_events);
        $this->assertSame(1, $importJob->processed_files);
        $this->assertSame(1, $importJob->imported_files);
        $this->assertSame(0, $importJob->error_files);
    }

    public function test_increments_counters_for_event_document(): void
    {
        $eventoXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<EventoNFSe>
  <infEvento>
    <pedRegEvento>
      <infPedReg>
        <e105102>
          <cMotivo>135</cMotivo>
          <xDesc>Cancelamento</xDesc>
          <xMotivo>Cancelado</xMotivo>
          <chSubstituta>SUB123</chSubstituta>
        </e105102>
      </infPedReg>
    </pedRegEvento>
  </infEvento>
</EventoNFSe>
XML;

        $tenant = Tenant::create(['name' => 'Tenant Test']);
        $user = User::create([
            'name' => 'User Test',
            'email' => 'user-test-4@example.com',
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

        $importJob = XmlImportJob::create([
            'tenant_id' => $tenant->id,
            'issuer_id' => $issuer->id,
            'owner_id' => $issuer->id,
            'owner_type' => $issuer::class,
            'import_type' => XmlImportJobType::SYSTEM,
            'status' => XmlImportJob::STATUS_PENDING,
            'processed_files' => 0,
            'imported_files' => 0,
            'error_files' => 0,
            'total_files' => 1,
            'errors' => [],
        ]);

        $job = new SefazNfseProcessDocumentJob([
            'nsu' => 1,
            'tipo_documento' => 'EVENTO',
            'chave_acesso' => 'NFSECHAVE456',
            'data_hora_geracao' => '2026-01-02T00:00:00-03:00',
            'xml_content' => $eventoXml,
        ], $issuer, $importJob);

        $job->handle();

        $importJob->refresh();

        $this->assertSame(0, $importJob->num_documents);
        $this->assertSame(1, $importJob->num_events);
        $this->assertSame(1, $importJob->processed_files);
        $this->assertSame(1, $importJob->imported_files);
        $this->assertSame(0, $importJob->error_files);
    }
}
