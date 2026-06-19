<?php

namespace Tests\Unit\Services\ValidacaoTributaria;

use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Regras\ValidaCalculoIpi;
use PHPUnit\Framework\TestCase;

class ValidaCalculoIpiTest extends TestCase
{
    private ValidaCalculoIpi $regra;

    private Issuer $issuer;

    protected function setUp(): void
    {
        $this->regra = new ValidaCalculoIpi(tolerancia: 0.01);
        $this->issuer = $this->createMock(Issuer::class);
    }

    public function test_nao_aponta_erro_quando_calculo_correto(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto',
                'vProd' => 200,
                'impostos' => ['vIPI' => 10.00, 'pIPI' => 5.00, 'CST_IPI' => '50'],
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
                'vProd' => 200,
                'impostos' => ['vIPI' => 15.00, 'pIPI' => 5.00, 'CST_IPI' => '50'],
            ],
        ], [], $this->issuer);

        $this->assertCount(1, $resultados);
        $this->assertEquals('calculo_ipi', $resultados[0]->regra);
        $this->assertEquals('10,00', $resultados[0]->valorEsperado);
        $this->assertEquals('15,00', $resultados[0]->valorEncontrado);
    }

    public function test_ignora_produtos_sem_ipi(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto',
                'vProd' => 200,
                'impostos' => ['vIPI' => 0, 'pIPI' => 0, 'CST_IPI' => ''],
            ],
        ], [], $this->issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_ignora_produtos_sem_pipi_declarado(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto',
                'vProd' => 200,
                'impostos' => ['vIPI' => 10.00, 'CST_IPI' => '50'],
            ],
        ], [], $this->issuer);

        $this->assertCount(0, $resultados);
    }
}
