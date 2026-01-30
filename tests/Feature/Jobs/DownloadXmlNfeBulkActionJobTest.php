<?php

namespace Tests\Feature\Jobs;

use App\Jobs\BulkAction\DownloadXmlNfeBulkActionJob;
use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use App\Models\TempFile;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class DownloadXmlNfeBulkActionJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_cria_zip_e_notifica_usuario(): void
    {
        Storage::fake('local');

        $tenant = Tenant::create(['name' => 'Tenant Teste']);
        $user = User::factory()->create();

        $issuer = Issuer::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'cnpj' => '00000000000191',
            'razao_social' => 'EMPRESA TESTE',
            'ult_nsu_nfe' => 0,
        ]);

        $user->update(['issuer_id' => $issuer->id]);

        $nfe1 = NotaFiscalEletronica::create([
            'issuer_id' => $issuer->id,
            'chave' => 'NFE-1',
            'data_emissao' => now(),
            'xml' => gzcompress('<nfe>1</nfe>'),
        ]);

        $nfe2 = NotaFiscalEletronica::create([
            'issuer_id' => $issuer->id,
            'chave' => 'NFE-2',
            'data_emissao' => now(),
            'xml' => gzcompress('<nfe>2</nfe>'),
        ]);

        $this->assertDatabaseCount('nfes', 2);

        (new DownloadXmlNfeBulkActionJob([$nfe1->id, $nfe2->id], $user->id, $issuer->id))->handle();

        $this->assertDatabaseCount('temp_files', 1);
        $tempFile = TempFile::query()->firstOrFail();

        Storage::disk($tempFile->disk)->assertExists($tempFile->file_path);

        $zipAbsolutePath = Storage::disk($tempFile->disk)->path($tempFile->file_path);
        $zip = new ZipArchive;
        $openResult = $zip->open($zipAbsolutePath);
        $this->assertTrue($openResult === true);

        $this->assertNotFalse($zip->locateName('NFE-1.xml'));
        $this->assertNotFalse($zip->locateName('NFE-2.xml'));

        $zip->close();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => $user::class,
        ]);
    }
}
