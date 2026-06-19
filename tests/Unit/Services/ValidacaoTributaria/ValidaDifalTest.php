<?php

namespace Tests\Unit\Services\ValidacaoTributaria;

use App\Models\Issuer;
use App\Services\ValidacaoTributaria\Regras\ValidaDifal;
use PHPUnit\Framework\TestCase;

class ValidaDifalTest extends TestCase
{
    private ValidaDifal $regra;

    private Issuer $issuer;

    protected function setUp(): void
    {
        $this->regra = new ValidaDifal;
        $this->issuer = $this->createMock(Issuer::class);
    }

    public function test_nao_aponta_erro_para_operacao_mesmo_estado(): void
    {
        $nota = ['emitente_uf' => 'SP', 'destinatario_uf' => 'SP', 'vICMSUFDest' => 0, 'tpNf' => '1', 'id' => 0, 'chave' => ''];

        $resultados = $this->regra->validar([], $nota, $this->issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_nao_aponta_erro_quando_difal_ja_calculado(): void
    {
        $nota = ['emitente_uf' => 'SP', 'destinatario_uf' => 'RJ', 'vICMSUFDest' => 50.00, 'tpNf' => '1', 'id' => 0, 'chave' => ''];

        $resultados = $this->regra->validar([], $nota, $this->issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_aponta_erro_quando_interestadual_sem_difal(): void
    {
        $nota = ['emitente_uf' => 'SP', 'destinatario_uf' => 'RJ', 'vICMSUFDest' => 0, 'tpNf' => '1', 'id' => 0, 'chave' => '', 'cfops' => ['6102']];
        $produtos = [
            [
                'impostos' => ['CST' => '00', 'vICMS' => 18.00],
            ],
        ];

        $resultados = $this->regra->validar($produtos, $nota, $this->issuer);

        $this->assertCount(1, $resultados);
        $this->assertEquals('difal', $resultados[0]->regra);
        $this->assertStringContainsString('interestadual', $resultados[0]->mensagem);
    }

    public function test_nao_aponta_erro_para_tpNf_entrada_1_sem_cfop_interestadual(): void
    {
        $nota = ['emitente_uf' => 'SP', 'destinatario_uf' => 'RJ', 'vICMSUFDest' => 0, 'tpNf' => '0', 'id' => 0, 'chave' => ''];

        $resultados = $this->regra->validar([], $nota, $this->issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_nao_aponta_erro_quando_uf_vazia(): void
    {
        $nota = ['emitente_uf' => '', 'destinatario_uf' => '', 'vICMSUFDest' => 0, 'tpNf' => '1', 'id' => 0, 'chave' => ''];

        $resultados = $this->regra->validar([], $nota, $this->issuer);

        $this->assertCount(0, $resultados);
    }
}
