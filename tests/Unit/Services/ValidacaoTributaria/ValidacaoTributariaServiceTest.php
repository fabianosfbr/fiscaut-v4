<?php

namespace Tests\Unit\Services\ValidacaoTributaria;

use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use App\Services\ValidacaoTributaria\ValidacaoTributariaService;
use Tests\TestCase;

class ValidacaoTributariaServiceTest extends TestCase
{
    private ValidacaoTributariaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ValidacaoTributariaService;
    }

    public function test_tolerancia_padrao(): void
    {
        $this->assertEquals(0.01, $this->service->getTolerancia());
    }

    public function test_validar_retorna_resultados_para_produtos_com_icms_divergente(): void
    {
        $issuer = $this->criarIssuer('lucro_real');
        $nfe = $this->criarNfe([
            'vProd' => 100,
            'vICMS' => 15.00,
            'endereco' => ['UF' => 'SP'],
        ], [
            [
                'nItem' => '1',
                'xProd' => 'Produto Teste',
                'vProd' => '100.00',
                'CFOP' => '5102',
                'CSOSN' => '',
                'impostos' => [
                    'CST' => '00',
                    'vBC' => 100.00,
                    'pICMS' => 18.00,
                    'vICMS' => 15.00,
                    'vIPI' => 0,
                    'vPIS' => 0,
                    'pPIS' => 0,
                    'vCOFINS' => 0,
                    'pCOFINS' => 0,
                ],
            ],
        ]);

        $resultados = $this->service->validar($nfe, $issuer);

        $this->assertGreaterThanOrEqual(1, $resultados);

        $icmsIssues = array_filter($resultados, fn ($r) => $r->regra === 'calculo_icms');
        $this->assertCount(1, $icmsIssues);
    }

    public function test_validar_retorna_vazio_para_nfe_sem_inconsistencias(): void
    {
        $issuer = $this->criarIssuer('lucro_real');
        $nfe = $this->criarNfe([
            'vProd' => 100,
            'vICMS' => 18.00,
            'endereco' => ['UF' => 'SP'],
        ], [
            [
                'nItem' => '1',
                'xProd' => 'Produto correto',
                'vProd' => '100.00',
                'CFOP' => '5102',
                'CSOSN' => '',
                'impostos' => [
                    'CST' => '00',
                    'vBC' => 100.00,
                    'pICMS' => 18.00,
                    'vICMS' => 18.00,
                    'vIPI' => 0,
                    'vPIS' => 0,
                    'pPIS' => 0,
                    'vCOFINS' => 0,
                    'pCOFINS' => 0,
                ],
            ],
        ]);

        $resultados = $this->service->validar($nfe, $issuer);

        $this->assertCount(0, $resultados);
    }

    public function test_nfe_559586_eh_legitimamente_limpa(): void
    {
        $issuer = $this->criarIssuer('lucro_real');
        $nfe = $this->criarNfe([
            'chave' => '35260654802798000114550010001104211000670286',
            'nNF' => '110421',
            'vProd' => 100,
            'vNfe' => 100,
            'vICMS' => 0,
            'vIPI' => 0,
            'vPIS' => 0,
            'vCOFINS' => 0,
            'vICMSUFDest' => 0,
            'endereco' => ['emitente' => ['UF' => 'SP'], 'destinatario' => ['UF' => 'SP']],
            'Emitente' => ['UF' => 'SP'],
        ], [
            [
                'nItem' => '1',
                'xProd' => 'GASOLINA C COMUM',
                'vProd' => '100.00',
                'CFOP' => '5656',
                'CSOSN' => '',
                'impostos' => [
                    'CST' => '61',
                    'vBC' => 0,
                    'pICMS' => 0,
                    'vICMS' => 0,
                    'vIPI' => 0,
                    'vPIS' => 0,
                    'pPIS' => 0,
                    'vCOFINS' => 0,
                    'pCOFINS' => 0,
                ],
            ],
        ]);

        $resultados = $this->service->validar($nfe, $issuer);

        $this->assertCount(0, $resultados);
    }

    /**
     * @param  array<string, mixed>  $headerData
     * @param  array<int, array<string, mixed>>  $produtos
     */
    private function criarNfe(array $headerData, array $produtos): NotaFiscalEletronica
    {
        $nfe = $this->createMock(NotaFiscalEletronica::class);

        $data = [
            'id' => 1,
            'chave' => $headerData['chave'] ?? 'teste',
            'nNF' => $headerData['nNF'] ?? '123',
            'vNfe' => $headerData['vNfe'] ?? 100,
            'vProd' => $headerData['vProd'] ?? 100,
            'vBC' => $headerData['vBC'] ?? 0,
            'vICMS' => $headerData['vICMS'] ?? 0,
            'vBCST' => $headerData['vBCST'] ?? 0,
            'vST' => $headerData['vST'] ?? 0,
            'vIPI' => $headerData['vIPI'] ?? 0,
            'vPIS' => $headerData['vPIS'] ?? 0,
            'vCOFINS' => $headerData['vCOFINS'] ?? 0,
            'vICMSUFDest' => $headerData['vICMSUFDest'] ?? 0,
            'vFrete' => $headerData['vFrete'] ?? 0,
            'vSeg' => $headerData['vSeg'] ?? 0,
            'vDesc' => $headerData['vDesc'] ?? 0,
            'vOutro' => $headerData['vOutro'] ?? 0,
            'vFCP' => $headerData['vFCP'] ?? 0,
            'vTotTrib' => $headerData['vTotTrib'] ?? 0,
            'emitente_cnpj' => $headerData['emitente_cnpj'] ?? '00000000000000',
            'enderEmit_UF' => $headerData['enderEmit_UF'] ?? ($headerData['endereco']['emitente']['UF'] ?? 'SP'),
            'enderDest_UF' => $headerData['enderDest_UF'] ?? ($headerData['endereco']['destinatario']['UF'] ?? 'SP'),
            'tpNf' => $headerData['tpNf'] ?? 1,
            'data_emissao' => $headerData['data_emissao'] ?? now(),
        ];

        $nfe->method('__get')
            ->willReturnCallback(function ($key) use ($data, $produtos) {
                if ($key === 'produtos') {
                    return $produtos;
                }

                return $data[$key] ?? null;
            });

        $nfe->method('__isset')
            ->willReturnCallback(function ($key) use ($data, $produtos) {
                if ($key === 'produtos') {
                    return true;
                }

                return isset($data[$key]);
            });

        $nfe->method('offsetGet')
            ->willReturnCallback(function ($key) use ($data, $produtos) {
                if ($key === 'produtos') {
                    return $produtos;
                }

                return $data[$key] ?? null;
            });

        return $nfe;
    }

    private function criarIssuer(string $regime): Issuer
    {
        $issuer = $this->createMock(Issuer::class);
        $issuer->id = 18;
        $issuer->regime = $regime;
        $issuer->tenant_id = 2;

        $issuer->method('__get')
            ->willReturnCallback(function ($key) use ($issuer) {
                return $issuer->$key ?? null;
            });

        $issuer->method('__isset')
            ->willReturnCallback(function ($key) use ($issuer) {
                return isset($issuer->$key);
            });

        return $issuer;
    }
}
