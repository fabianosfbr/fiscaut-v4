<?php

namespace Tests\Unit;

use App\Models\Issuer;
use App\Models\NfeApurada;
use App\Models\NotaFiscalEletronica;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotaFiscalEletronicaApuracaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_toggle_apuracao_cria_registro_e_sincroniza_processed(): void
    {
        $user = User::factory()->create();

        $issuer = Issuer::create([
            'user_id' => $user->id,
            'cnpj' => '12345678000199',
            'razao_social' => 'Empresa Teste LTDA',
            'cod_municipio_ibge' => 3525904,
            'ambiente' => 2,
        ]);

        $nfe = NotaFiscalEletronica::create([
            'issuer_id' => $issuer->id,
            'chave' => 'NFE-TESTE',
            'processed' => false,
        ]);

        $this->assertFalse($nfe->isApuradaParaEmpresa($issuer));
        $this->assertFalse((bool) $nfe->processed);

        $this->assertTrue($nfe->toggleApuracao($issuer));

        $nfe->refresh();
        $this->assertTrue((bool) $nfe->processed);
        $this->assertDatabaseHas('nfe_apuradas', [
            'nfe_id' => $nfe->id,
            'issuer_id' => $issuer->id,
            'status' => true,
        ]);

        $this->assertFalse($nfe->toggleApuracao($issuer));

        $nfe->refresh();
        $this->assertFalse((bool) $nfe->processed);

        $apuracao = NfeApurada::where('nfe_id', $nfe->id)->where('issuer_id', $issuer->id)->latest('id')->first();
        $this->assertNotNull($apuracao);
        $this->assertFalse((bool) $apuracao->status);
    }
}
