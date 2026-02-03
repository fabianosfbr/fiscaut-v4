<?php

namespace Tests\Unit;

use App\Models\NotaFiscalServico;
use Tests\TestCase;

class NotaFiscalServicoXmlExtractionTest extends TestCase
{
    public function test_extracts_service_and_iss_fields_from_nfse_xml(): void
    {
        $xmlPath = base_path('xml-nfse.xml');
        $xml = file_get_contents($xmlPath);

        $this->assertIsString($xml);
        $this->assertNotSame('', trim($xml));

        $nfse = new NotaFiscalServico;
        $nfse->xml_content = $xml;

        $this->assertIsString($nfse->xml_extraido);
        $this->assertStringContainsString('<NFSe', $nfse->xml_extraido);

        $this->assertSame('118066300', $nfse->nfse_codigo_servico_extraido);
        $this->assertSame(
            'Prestação de serviços e eventos na área de alimentação, empresa optante simples nacional.',
            $nfse->nfse_descricao_servico_extraida,
        );
        $this->assertIsString($nfse->nfse_discriminacao_extraida);
        $this->assertStringContainsString('Empresa Optante pelo Simples Nacional', $nfse->nfse_discriminacao_extraida);

        $this->assertSame(9171.10, $nfse->nfse_base_calculo_iss_extraida);
        $this->assertSame(0.0, $nfse->nfse_aliquota_iss_extraida);
        $this->assertSame(0.0, $nfse->nfse_valor_iss_extraido);
        $this->assertNull($nfse->nfse_iss_retido_extraido);

        $this->assertIsString($nfse->nfse_valores_xml);
        $this->assertStringContainsString('"vBC"', $nfse->nfse_valores_xml);
        $this->assertStringContainsString('"9171.10"', $nfse->nfse_valores_xml);
    }
}
