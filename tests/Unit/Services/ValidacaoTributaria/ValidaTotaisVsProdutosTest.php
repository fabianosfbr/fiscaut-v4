<?php

namespace Tests\Unit\Services\ValidacaoTributaria;

use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Regras\ValidaTotaisVsProdutos;
use PHPUnit\Framework\TestCase;

class ValidaTotaisVsProdutosTest extends TestCase
{
    private ValidaTotaisVsProdutos $regra;

    private Issuer $issuer;

    protected function setUp(): void
    {
        $this->regra = new ValidaTotaisVsProdutos(tolerancia: 0.01);
        $this->issuer = $this->createMock(Issuer::class);
    }

    public function test_nao_aponta_erro_quando_totais_conferem(): void
    {
        $produtos = [
            [
                'vProd' => 100,
                'impostos' => ['vBC' => 80, 'vICMS' => 14.40, 'vIPI' => 0, 'vPIS' => 1.65, 'vCOFINS' => 7.60],
            ],
            [
                'vProd' => 200,
                'impostos' => ['vBC' => 160, 'vICMS' => 28.80, 'vIPI' => 0, 'vPIS' => 3.30, 'vCOFINS' => 15.20],
            ],
        ];
        $nota = ['vProd' => 300, 'vICMS' => 43.20, 'vIPI' => 0, 'vPIS' => 4.95, 'vCOFINS' => 22.80];

        $resultados = $this->regra->validar($produtos, $nota, $this->issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_aponta_erro_quando_vprod_divergente(): void
    {
        $produtos = [
            ['vProd' => 100, 'impostos' => ['vBC' => 80, 'vICMS' => 0, 'vIPI' => 0, 'vPIS' => 0, 'vCOFINS' => 0]],
            ['vProd' => 50, 'impostos' => ['vBC' => 40, 'vICMS' => 0, 'vIPI' => 0, 'vPIS' => 0, 'vCOFINS' => 0]],
        ];
        $nota = ['vProd' => 200, 'vICMS' => 0, 'vIPI' => 0, 'vPIS' => 0, 'vCOFINS' => 0];

        $resultados = $this->regra->validar($produtos, $nota, $this->issuer);

        $this->assertCount(1, $resultados);
        $this->assertEquals('150,00', $resultados[0]->valorEsperado);
        $this->assertEquals('200,00', $resultados[0]->valorEncontrado);
    }

    public function test_aponta_erro_quando_icms_divergente(): void
    {
        $produtos = [
            ['vProd' => 100, 'impostos' => ['vBC' => 80, 'vICMS' => 14.40, 'vIPI' => 0, 'vPIS' => 0, 'vCOFINS' => 0]],
        ];
        $nota = ['vProd' => 100, 'vICMS' => 10.00, 'vIPI' => 0, 'vPIS' => 0, 'vCOFINS' => 0];

        $resultados = $this->regra->validar($produtos, $nota, $this->issuer);

        $this->assertCount(1, $resultados);
        $this->assertEquals('totais_vs_produtos', $resultados[0]->regra);
        $this->assertEquals('ICMS', $resultados[0]->tipoImposto);
    }

    public function test_ignora_produtos_sem_preco(): void
    {
        $resultados = $this->regra->validar([], ['vProd' => 0, 'vICMS' => 0, 'vIPI' => 0, 'vPIS' => 0, 'vCOFINS' => 0], $this->issuer);

        $this->assertCount(0, $resultados);
    }
}
