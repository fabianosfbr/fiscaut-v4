<?php

namespace Tests\Unit;

use App\Filament\Widgets\StatisticData;
use PHPUnit\Framework\TestCase;

class StatisticDataProdutosMaisVendidosTest extends TestCase
{
    public function test_produtos_mais_vendidos_from_xml_itens_agrega_por_codigo_ou_descricao(): void
    {
        $produtosPorNfe = [
            [
                ['cProd' => 'ABC', 'xProd' => 'Produto A', 'qCom' => '2', 'vProd' => '10.00'],
                ['xProd' => 'Produto Sem Codigo', 'qCom' => '1', 'vProd' => '5.00'],
            ],
            [
                ['cProd' => 'ABC', 'xProd' => 'Produto A', 'qCom' => '3', 'vProd' => '15.00'],
                ['xProd' => 'produto sem codigo', 'qCom' => '2', 'vProd' => '7.00'],
            ],
        ];

        $rows = StatisticData::produtosMaisVendidosFromXmlItens($produtosPorNfe, 15);

        $this->assertCount(2, $rows);

        $this->assertSame('Produto A', $rows[0]['label']);
        $this->assertSame(5.0, $rows[0]['amount']);
        $this->assertSame(25.0, $rows[0]['total']);

        $this->assertSame('Produto Sem Codigo', $rows[1]['label']);
        $this->assertSame(3.0, $rows[1]['amount']);
        $this->assertSame(12.0, $rows[1]['total']);
    }

    public function test_produtos_mais_vendidos_from_xml_itens_respeita_limit(): void
    {
        $produtosPorNfe = [
            [
                ['cProd' => 'A', 'xProd' => 'A', 'qCom' => '10', 'vProd' => '10.00'],
                ['cProd' => 'B', 'xProd' => 'B', 'qCom' => '1', 'vProd' => '1.00'],
            ],
        ];

        $rows = StatisticData::produtosMaisVendidosFromXmlItens($produtosPorNfe, 1);

        $this->assertCount(1, $rows);
        $this->assertSame('A', $rows[0]['label']);
    }
}
