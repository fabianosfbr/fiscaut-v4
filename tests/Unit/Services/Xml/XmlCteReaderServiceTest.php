<?php

namespace Tests\Unit\Services\Xml;

use App\Services\Xml\XmlCteReaderService;
use Mockery;
use PHPUnit\Framework\TestCase;

class XmlCteReaderServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_it_parses_cte_completa_correctly(): void
    {
        $xml = file_get_contents(__DIR__ . '/../../../../xml-cte.xml');
        $this->assertNotFalse($xml);

        $service = new XmlCteReaderService();
        $service->loadXml($xml)->parse();
        
        $data = $service->getData();
        
        $this->assertArrayHasKey('cteProc', $data);
        $this->assertSame('230195685', $data['cteProc']['CTe']['infCte']['ide']['nCT'] ?? null);
        $this->assertSame('CTe35260103007331008045571572301956851471759822', $data['cteProc']['CTe']['infCte']['@attributes']['Id'] ?? null);
    }

    public function test_it_parses_cte_evento_correctly(): void
    {
        $xml = <<<XML
<procEventoCTe xmlns="http://www.portalfiscal.inf.br/cte" versao="4.00">
    <eventoCTe versao="4.00">
        <infEvento Id="ID1101113526010300733100804557157230195685147175982201">
            <chCTe>35260103007331008045571572301956851471759822</chCTe>
            <tpEvento>110111</tpEvento>
        </infEvento>
    </eventoCTe>
</procEventoCTe>
XML;

        $service = new XmlCteReaderService();
        $service->loadXml($xml)->parse();
        
        $data = $service->getData();
        
        $this->assertArrayHasKey('procEventoCTe', $data);
        $this->assertSame('110111', $data['procEventoCTe']['eventoCTe']['infEvento']['tpEvento'] ?? null);
    }
}
