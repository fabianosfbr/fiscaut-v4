<?php

namespace Tests\Unit;

use App\Services\Sefaz\Traits\HasNfe;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class HasNfeTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = Container::getInstance();
        $container->instance('config', new Repository([
            'admin' => [
                'aliqs' => [
                    'UF' => ['SP'],
                    'SP' => [0],
                ],
            ],
        ]));
    }

    public function test_prepara_dados_nfe_reads_expected_fields_from_array_xml(): void
    {
        $xml = file_get_contents(__DIR__ . '/../../xml-nfe.xml');
        $this->assertNotFalse($xml);

        $reader = loadXmlReader($xml);

        $subject = new class
        {
            use HasNfe;
        };

        $params = $subject->preparaDadosNfe($reader);

        $this->assertSame('88123', (string) ($params['nNF'] ?? ''));
        $this->assertSame('35260105552129000126550010000881231004400199', $params['chave'] ?? null);
        $this->assertSame('05552129000126', $params['emitente_cnpj'] ?? null);
        $this->assertNotEmpty($params['data_emissao'] ?? null);

        $this->assertIsArray($params['cfops'] ?? null);
        $this->assertNotEmpty($params['cfops'] ?? []);

        $this->assertSame(28, (int) ($params['num_produtos'] ?? 0));
    }

    public function test_prepara_dados_produtos_reads_first_det_item(): void
    {
        $xml = file_get_contents(__DIR__ . '/../../xml-nfe.xml');
        $this->assertNotFalse($xml);

        $reader = loadXmlReader($xml);

        $subject = new class
        {
            use HasNfe;
        };

        $detList = xml_list($reader['nfeProc']['NFe']['infNFe']['det'] ?? null);
        $this->assertNotEmpty($detList);

        $product = $subject->preparaDadosProdutos($detList[0]);

        $this->assertArrayHasKey('codigo_produto', $product);
        $this->assertArrayHasKey('descricao_produto', $product);
        $this->assertArrayHasKey('cfop', $product);
        $this->assertArrayHasKey('valor_total', $product);
    }

    public function test_check_is_type_works_with_array_reader(): void
    {
        $xml = file_get_contents(__DIR__ . '/../../xml-nfe.xml');
        $this->assertNotFalse($xml);

        $reader = loadXmlReader($xml);

        $subject = new class
        {
            use HasNfe;
        };

        $this->assertTrue($subject->checkIsType($reader, 'nfeProc'));
        $this->assertFalse($subject->checkIsType($reader, 'naoExiste'));
    }
}
