<?php

namespace Tests\Unit;

use App\Models\NotaFiscalEletronica;
use PHPUnit\Framework\TestCase;

class NotaFiscalEletronicaProdutosXmlTest extends TestCase
{
    public function test_listar_produtos_do_xml_extrai_itens_do_xml_comprimido(): void
    {
        $xml = file_get_contents(__DIR__ . '/../../xml-nfe.xml');
        $this->assertNotFalse($xml);    

        $nfe = new NotaFiscalEletronica();
        $nfe->xml = gzcompress($xml);

        $produtos = $nfe->getProdutos();

        $this->assertIsArray($produtos);
        $this->assertCount(28, $produtos);

        $primeiro = $produtos[0];
        $this->assertArrayHasKey('cProd', $primeiro);
        $this->assertArrayHasKey('xProd', $primeiro);
        $this->assertArrayHasKey('CFOP', $primeiro);
        $this->assertArrayHasKey('impostos', $primeiro);
        $this->assertIsArray($primeiro['impostos']);
    }

    public function test_endereco_destinatario_completo_extrai_do_xml_comprimido(): void
    {
        $xml = file_get_contents(__DIR__ . '/../../xml-nfe.xml');
        $this->assertNotFalse($xml);

        $nfe = new NotaFiscalEletronica();
        $nfe->xml = gzcompress($xml);

        $this->assertSame(
            'AV COMENDADOR ANTONIO BORIN, 1800 - JARDIM ROSAURA - JUNDIAI/SP - CEP: 13218-641',
            $nfe->endereco_destinatario_completo,
        );
    }
}
