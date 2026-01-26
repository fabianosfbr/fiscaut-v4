<?php

namespace Tests\Unit;

use App\Services\Xml\XmlReaderService;
use PHPUnit\Framework\TestCase;

class XmlReaderServiceTest extends TestCase
{
    public function test_it_reads_cte_xml_as_array(): void
    {
        $xml = file_get_contents(__DIR__ . '/../../xml-cte.xml');
        $this->assertNotFalse($xml);

        $data = (new XmlReaderService())->read($xml);

        $this->assertArrayHasKey('cteProc', $data);
        $this->assertSame('230195685', $data['cteProc']['CTe']['infCte']['ide']['nCT'] ?? null);
        $this->assertSame('0', $data['cteProc']['CTe']['infCte']['ide']['tpCTe'] ?? null);
        $this->assertSame('03007331008045', $data['cteProc']['CTe']['infCte']['emit']['CNPJ'] ?? null);
    }

    public function test_it_reads_nfe_xml_as_array(): void
    {
        $xml = file_get_contents(__DIR__ . '/../../xml-nfe.xml');
        $this->assertNotFalse($xml);

        $data = (new XmlReaderService())->read($xml);

        $this->assertArrayHasKey('nfeProc', $data);
        $this->assertSame('88123', $data['nfeProc']['NFe']['infNFe']['ide']['nNF'] ?? null);
        $this->assertSame('05552129000126', $data['nfeProc']['NFe']['infNFe']['emit']['CNPJ'] ?? null);
        $this->assertSame('35260105552129000126550010000881231004400199', $data['nfeProc']['protNFe']['infProt']['chNFe'] ?? null);

        $det = $data['nfeProc']['NFe']['infNFe']['det'] ?? null;
        $this->assertIsArray($det);
        $this->assertGreaterThanOrEqual(1, \is_array($det) ? (\array_is_list($det) ? \count($det) : 1) : 0);
    }

    public function test_it_preserves_attributes_and_content_for_doczip_list(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<retDistDFeInt>
  <loteDistDFeInt>
    <docZip NSU="1">AAA</docZip>
    <docZip NSU="2">BBB</docZip>
  </loteDistDFeInt>
</retDistDFeInt>
XML;

        $data = (new XmlReaderService())->read($xml);

        $this->assertArrayHasKey('retDistDFeInt', $data);
        $docZip = $data['retDistDFeInt']['loteDistDFeInt']['docZip'] ?? null;
        $this->assertIsArray($docZip);
        $this->assertTrue(\array_is_list($docZip));
        $this->assertCount(2, $docZip);
        $this->assertSame('1', $docZip[0]['@attributes']['NSU'] ?? null);
        $this->assertSame('AAA', $docZip[0]['@content'] ?? null);
        $this->assertSame('2', $docZip[1]['@attributes']['NSU'] ?? null);
        $this->assertSame('BBB', $docZip[1]['@content'] ?? null);
    }
}

