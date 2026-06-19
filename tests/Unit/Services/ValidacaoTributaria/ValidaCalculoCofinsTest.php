<?php

namespace Tests\Unit\Services\ValidacaoTributaria;

use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Regras\ValidaCalculoCofins;
use PHPUnit\Framework\TestCase;

class ValidaCalculoCofinsTest extends TestCase
{
    private ValidaCalculoCofins $regra;

    private Issuer $issuer;

    protected function setUp(): void
    {
        $this->regra = new ValidaCalculoCofins(tolerancia: 0.01);
        $this->issuer = $this->createMock(Issuer::class);
    }

    public function test_nao_aponta_erro_quando_calculo_correto(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto',
                'vProd' => 1000,
                'impostos' => ['vCOFINS' => 76.00, 'pCOFINS' => 7.60],
            ],
        ], [], $this->issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_aponta_erro_quando_calculo_divergente(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto',
                'vProd' => 1000,
                'impostos' => ['vCOFINS' => 50.00, 'pCOFINS' => 7.60],
            ],
        ], [], $this->issuer);

        $this->assertCount(1, $resultados);
        $this->assertEquals('calculo_cofins', $resultados[0]->regra);
    }
}
