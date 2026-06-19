<?php

namespace Tests\Unit\Services\ValidacaoTributaria;

use App\Enums\SeveridadeValidacaoEnum;
use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Regras\ValidaCstVsRegime;
use PHPUnit\Framework\TestCase;

class ValidaCstVsRegimeTest extends TestCase
{
    private ValidaCstVsRegime $regra;

    protected function setUp(): void
    {
        $this->regra = new ValidaCstVsRegime;
    }

    public function test_nao_aponta_erro_quando_lucro_real_com_cst_valido(): void
    {
        $issuer = $this->criarIssuer('lucro_real');

        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto Teste',
                'CSOSN' => '',
                'impostos' => ['CST' => '00', 'vBC' => 100, 'pICMS' => 18, 'vICMS' => 18],
            ],
        ], [], $issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_aponta_erro_quando_lucro_real_tem_csosn(): void
    {
        $issuer = $this->criarIssuer('lucro_real');

        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto Teste',
                'CSOSN' => '101',
                'impostos' => ['CST' => '', 'vBC' => 100, 'pICMS' => 18, 'vICMS' => 18],
            ],
        ], [], $issuer);

        $this->assertCount(1, $resultados);
        $this->assertEquals('cst_vs_regime', $resultados[0]->regra);
        $this->assertEquals(SeveridadeValidacaoEnum::AVISO, $resultados[0]->severidade);
        $this->assertStringContainsString('CSOSN', $resultados[0]->mensagem);
    }

    public function test_aponta_erro_quando_simples_nacional_tem_cst(): void
    {
        $issuer = $this->criarIssuer('simples_nacional');

        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto Teste',
                'CSOSN' => '',
                'impostos' => ['CST' => '00', 'vBC' => 100, 'pICMS' => 18, 'vICMS' => 18],
            ],
        ], [], $issuer);

        $this->assertCount(1, $resultados);
        $this->assertEquals('cst_vs_regime', $resultados[0]->regra);
        $this->assertStringContainsString('CST', $resultados[0]->mensagem);
    }

    public function test_nao_aponta_erro_quando_simples_nacional_com_csosn(): void
    {
        $issuer = $this->criarIssuer('simples_nacional');

        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto Teste',
                'CSOSN' => '101',
                'impostos' => ['CST' => '0', 'vBC' => 100, 'pICMS' => 18, 'vICMS' => 18],
            ],
        ], [], $issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_aponta_erro_quando_simples_nacional_tem_csosn_invalido(): void
    {
        $issuer = $this->criarIssuer('simples_nacional');

        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto Teste',
                'CSOSN' => '999',
                'impostos' => ['CST' => '', 'vBC' => 100, 'pICMS' => 18, 'vICMS' => 18],
            ],
        ], [], $issuer);

        $this->assertCount(1, $resultados);
        $this->assertStringContainsString('CSOSN', $resultados[0]->mensagem);
        $this->assertStringContainsString('999', $resultados[0]->mensagem);
    }

    public function test_processa_multiplos_produtos(): void
    {
        $issuer = $this->criarIssuer('lucro_real');

        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto OK',
                'CSOSN' => '',
                'impostos' => ['CST' => '00'],
            ],
            [
                'nItem' => 2,
                'xProd' => 'Produto CSOSN',
                'CSOSN' => '101',
                'impostos' => ['CST' => ''],
            ],
        ], [], $issuer);

        $this->assertCount(1, $resultados);
        $this->assertEquals(2, $resultados[0]->nItem);
    }

    public function test_cst_zero_nao_dispara_aviso_para_simples_nacional(): void
    {
        $issuer = $this->criarIssuer('simples_nacional');

        $resultados = $this->regra->validar([
            [
                'nItem' => 1,
                'xProd' => 'Produto',
                'CSOSN' => '101',
                'impostos' => ['CST' => '0', 'vBC' => 100, 'pICMS' => 18, 'vICMS' => 18],
            ],
        ], [], $issuer);

        $this->assertCount(0, $resultados);
    }

    /**
     * @param  string  $regime  Valor do regime (simples_nacional, lucro_real, lucro_presumido)
     */
    private function criarIssuer(string $regime): Issuer
    {
        $issuer = $this->createMock(Issuer::class);
        $issuer->method('__get')
            ->with('regime')
            ->willReturn($regime);

        $issuer->regime = $regime;

        return $issuer;
    }
}
