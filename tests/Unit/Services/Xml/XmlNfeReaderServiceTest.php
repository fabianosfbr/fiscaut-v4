<?php

namespace Tests\Unit\Services\Xml;

use App\Services\Xml\XmlNfeReaderService;
use Mockery;
use PHPUnit\Framework\TestCase;

class XmlNfeReaderServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_it_parses_nfe_completa_correctly(): void
    {
        $xml = file_get_contents(__DIR__ . '/../../../../xml-nfe.xml');
        $this->assertNotFalse($xml);

        $service = new XmlNfeReaderService();
        $service->loadXml($xml)->parse();
        
        $data = $service->getData();
        
        $this->assertArrayHasKey('nfeProc', $data);
        $this->assertSame('88123', $data['nfeProc']['NFe']['infNFe']['ide']['nNF'] ?? null);
        $this->assertSame('35260105552129000126550010000881231004400199', $data['nfeProc']['protNFe']['infProt']['chNFe'] ?? null);
    }

    public function test_it_parses_nfe_resumo_correctly(): void
    {
        $xml = <<<XML
<resNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="1.01">
    <chNFe>35260105552129000126550010000881231004400199</chNFe>
    <CNPJ>05552129000126</CNPJ>
    <xNome>PIZZINATTO INDUSTRIA DE METAIS LTDA</xNome>
    <vNF>32876.57</vNF>
</resNFe>
XML;

        $service = new XmlNfeReaderService();
        $service->loadXml($xml)->parse();
        
        $data = $service->getData();
        
        $this->assertArrayHasKey('resNFe', $data);
        $this->assertSame('35260105552129000126550010000881231004400199', $data['resNFe']['chNFe'] ?? null);
    }

    public function test_it_parses_nfe_evento_correctly(): void
    {
        $xml = <<<XML
<procEventoNFe xmlns="http://www.portalfiscal.inf.br/nfe" versao="1.00">
    <evento versao="1.00">
        <infEvento Id="ID1101113526010555212900012655001000088123100440019901">
            <chNFe>35260105552129000126550010000881231004400199</chNFe>
            <tpEvento>110111</tpEvento>
        </infEvento>
    </evento>
</procEventoNFe>
XML;

        $service = new XmlNfeReaderService();
        $service->loadXml($xml)->parse();
        
        $data = $service->getData();
        
        $this->assertArrayHasKey('procEventoNFe', $data);
        $this->assertSame('110111', $data['procEventoNFe']['evento']['infEvento']['tpEvento'] ?? null);
    }
}
