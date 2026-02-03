<?php

namespace Tests\Feature;

use App\Models\ConhecimentoTransporteEletronico;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CteNfeChaveJsonLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('ctes');

        Schema::create('ctes', function (Blueprint $table) {
            $table->id();
            $table->string('tomador_cnpj')->nullable()->index();
            $table->text('nfe_chave')->nullable();
            $table->timestamps();
        });
    }

    public function test_busca_cte_por_chave_dentro_do_array_de_objetos_json(): void
    {
        $tomadorCnpj = '12345678000199';
        $chaveNfe = '35260120606930000109550070000714521279737274';

        ConhecimentoTransporteEletronico::create([
            'tomador_cnpj' => $tomadorCnpj,
            'nfe_chave' => [
                [
                    'chave' => $chaveNfe,
                    'dPrev' => '2026-01-26',
                ],
            ],
        ]);

        ConhecimentoTransporteEletronico::create([
            'tomador_cnpj' => $tomadorCnpj,
            'nfe_chave' => [
                [
                    'chave' => '00000000000000000000000000000000000000000000',
                    'dPrev' => '2026-01-27',
                ],
            ],
        ]);

        $ctes = ConhecimentoTransporteEletronico::query()
            ->where('tomador_cnpj', $tomadorCnpj)
            ->whereNfeChave($chaveNfe)
            ->get();

        $this->assertCount(1, $ctes);
    }
}
