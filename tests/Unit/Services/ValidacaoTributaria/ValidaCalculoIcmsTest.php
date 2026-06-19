<?php

namespace Tests\Unit\Services\ValidacaoTributaria;

use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Regras\ValidaCalculoIcms;
use PHPUnit\Framework\TestCase;

class ValidaCalculoIcmsTest extends TestCase
{
    private ValidaCalculoIcms $regra;

    private Issuer $issuer;

    protected function setUp(): void
    {
        $this->regra = new ValidaCalculoIcms(tolerancia: 0.01);
        $this->issuer = $this->createMock(Issuer::class);
    }

    public function test_nao_aponta_erro_quando_calculo_correto(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto',
                'CSOSN' => '',
                'impostos' => ['CST' => '00', 'vBC' => 100, 'pICMS' => 18, 'vICMS' => 18.00],
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
                'CSOSN' => '',
                'impostos' => ['CST' => '00', 'vBC' => 100, 'pICMS' => 18, 'vICMS' => 15.00],
            ],
        ], [], $this->issuer);

        $this->assertCount(1, $resultados);
        $this->assertEquals('calculo_icms', $resultados[0]->regra);
        $this->assertSame('18,00', $resultados[0]->valorEsperado);
        $this->assertSame('15,00', $resultados[0]->valorEncontrado);
    }

    public function test_nao_aponta_erro_para_cst_isento(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto Isento',
                'CSOSN' => '',
                'impostos' => ['CST' => '40', 'vBC' => 0, 'pICMS' => 0, 'vICMS' => 0],
            ],
        ], [], $this->issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_nao_aponta_erro_para_csosn_isento(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto SN Isento',
                'CSOSN' => '102',
                'impostos' => ['CST' => '', 'vBC' => 0, 'pICMS' => 0, 'vICMS' => 0],
            ],
        ], [], $this->issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_nao_aponta_erro_para_cst_st(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Gasolina',
                'CSOSN' => '',
                'impostos' => ['CST' => '61', 'vBC' => 0, 'pICMS' => 0, 'vICMS' => 0],
            ],
        ], [], $this->issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_aceita_diferenca_menor_que_tolerancia(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto',
                'CSOSN' => '',
                'impostos' => ['CST' => '00', 'vBC' => 100, 'pICMS' => 18, 'vICMS' => 18.00],
            ],
        ], [], $this->issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_ignora_produtos_sem_vbc(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto',
                'CSOSN' => '',
                'impostos' => ['CST' => '00', 'vBC' => 0, 'pICMS' => 0, 'vICMS' => 0],
            ],
        ], [], $this->issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_cst_20_com_reducao_nao_eh_isento(): void
    {
        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Carne',
                'CSOSN' => '',
                'impostos' => ['CST' => '20', 'vBC' => 80.54, 'pICMS' => 12, 'vICMS' => 9.66],
            ],
        ], [], $this->issuer);

        $this->assertCount(0, $resultados);
    }
}
