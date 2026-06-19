<?php

namespace Tests\Unit\Services\ValidacaoTributaria;

use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Regras\ValidaCalculoPis;
use PHPUnit\Framework\TestCase;

class ValidaCalculoPisTest extends TestCase
{
    private ValidaCalculoPis $regra;

    private Issuer $issuer;

    protected function setUp(): void
    {
        $this->regra = new ValidaCalculoPis(tolerancia: 0.01);
        $this->issuer = $this->createMock(Issuer::class);
    }

    public function test_nao_aponta_erro_quando_calculo_correto(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto',
                'vProd' => 1000,
                'impostos' => ['vPIS' => 16.50, 'pPIS' => 1.65],
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
                'impostos' => ['vPIS' => 10.00, 'pPIS' => 1.65],
            ],
        ], [], $this->issuer);

        $this->assertCount(1, $resultados);
        $this->assertEquals('calculo_pis', $resultados[0]->regra);
    }

    public function test_ignora_produtos_sem_pis(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto',
                'vProd' => 1000,
                'impostos' => ['vPIS' => 0, 'pPIS' => 0],
            ],
        ], [], $this->issuer);

        $this->assertCount(0, $resultados);
    }
}
