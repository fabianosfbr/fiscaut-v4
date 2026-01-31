<?php

namespace Tests\Unit;

use App\Models\ConhecimentoTransporteEletronico;
use PHPUnit\Framework\TestCase;

class ConhecimentoTransporteEletronicoXmlAccessorsTest extends TestCase
{
    public function test_it_handles_minimal_xml_cte(): void
    {
        $xml = file_get_contents(__DIR__.'/../../xml-cte.xml');
        $this->assertNotFalse($xml);

        $cte = new ConhecimentoTransporteEletronico;
        $cte->xml = $xml;

        $this->assertNull($cte->cfop);
        $this->assertNull($cte->serie);
        $this->assertNull($cte->tipo_tomador);
        $this->assertNull($cte->tomador_razao_social);
        $this->assertNull($cte->tomador_cnpj);
        $this->assertNull($cte->emitente_logradouro);
        $this->assertNull($cte->valor_servico);
        $this->assertNull($cte->valor_receber);
    }

    public function test_it_extracts_fields_from_xml_cte_1(): void
    {
        $xml = file_get_contents(__DIR__.'/../../xml-cte-1.xml');
        $this->assertNotFalse($xml);

        $cte = new ConhecimentoTransporteEletronico;
        $cte->xml = $xml;

        $this->assertSame(5352, $cte->cfop);
        $this->assertSame('1', $cte->serie);
        $this->assertSame('Destinatário', $cte->tipo_tomador);
        $this->assertSame('JERUEL PLASTICOS INDUSTRIA E COMERCIO LTDA', $cte->tomador_razao_social);
        $this->assertSame('08357463000117', $cte->tomador_cnpj);

        $this->assertSame('JUNDIAI', $cte->emitente_municipio);
        $this->assertSame('SP', $cte->emitente_uf);
        $this->assertSame('13218050', $cte->emitente_cep);
        $this->assertNotNull($cte->emitente_logradouro);
        $this->assertStringContainsString('Rua Joaquim Nabuco', $cte->emitente_logradouro);
        $this->assertStringContainsString('344', $cte->emitente_logradouro);

        $this->assertSame('1143587333', $cte->remetente_telefone);
        $this->assertSame('SAO BERNARDO DO CAMPO', $cte->remetente_municipio);
        $this->assertSame('SP', $cte->remetente_uf);
        $this->assertSame('09840000', $cte->remetente_cep);
        $this->assertNotNull($cte->remetente_logradouro);
        $this->assertStringContainsString('ESTRADA DOS CASA', $cte->remetente_logradouro);

        $this->assertSame('1145849922', $cte->destinatario_telefone);
        $this->assertSame('JUNDIAI', $cte->destinatario_municipio);
        $this->assertSame('SP', $cte->destinatario_uf);
        $this->assertSame('13218641', $cte->destinatario_cep);
        $this->assertNotNull($cte->destinatario_logradouro);
        $this->assertStringContainsString('AVENIDA COMENDADOR ANTONIO BORIN', $cte->destinatario_logradouro);

        $this->assertEqualsWithDelta(156.53, $cte->valor_servico, 0.0001);
        $this->assertEqualsWithDelta(156.53, $cte->valor_receber, 0.0001);

        $this->assertNull($cte->base_calculo_icms);
        $this->assertNull($cte->aliquota_icms);
        $this->assertNull($cte->valor_icms);
    }

    public function test_it_extracts_fields_from_xml_cte_2(): void
    {
        $xml = file_get_contents(__DIR__.'/../../xml-cte-2.xml');
        $this->assertNotFalse($xml);

        $cte = new ConhecimentoTransporteEletronico;
        $cte->xml = $xml;

        $this->assertSame(5353, $cte->cfop);
        $this->assertSame('157', $cte->serie);
        $this->assertSame('Remetente', $cte->tipo_tomador);
        $this->assertSame('DON COMERCIO E IMPORTACAO DE EMBALAGENS UNIPESSOAL LTDA', $cte->tomador_razao_social);
        $this->assertSame('20606930000109', $cte->tomador_cnpj);

        $this->assertSame('Guarulhos', $cte->emitente_municipio);
        $this->assertSame('SP', $cte->emitente_uf);
        $this->assertSame('07232151', $cte->emitente_cep);
        $this->assertNotNull($cte->emitente_logradouro);
        $this->assertStringContainsString('Avenida Orlanda Bergamo', $cte->emitente_logradouro);
        $this->assertStringContainsString('800', $cte->emitente_logradouro);

        $this->assertSame('Sao Paulo', $cte->remetente_municipio);
        $this->assertSame('SP', $cte->remetente_uf);
        $this->assertSame('03441030', $cte->remetente_cep);
        $this->assertNotNull($cte->remetente_logradouro);
        $this->assertStringContainsString('Rua Trapicheiro', $cte->remetente_logradouro);

        $this->assertSame('Jundiai', $cte->destinatario_municipio);
        $this->assertSame('SP', $cte->destinatario_uf);
        $this->assertSame('13218641', $cte->destinatario_cep);
        $this->assertNotNull($cte->destinatario_logradouro);
        $this->assertStringContainsString('Avenida Comendador Antonio Borin', $cte->destinatario_logradouro);

        $this->assertEqualsWithDelta(5.07, $cte->valor_servico, 0.0001);
        $this->assertEqualsWithDelta(5.07, $cte->valor_receber, 0.0001);
        $this->assertEqualsWithDelta(5.07, $cte->base_calculo_icms, 0.0001);
        $this->assertEqualsWithDelta(12.00, $cte->aliquota_icms, 0.0001);
        $this->assertEqualsWithDelta(0.61, $cte->valor_icms, 0.0001);
    }
}
