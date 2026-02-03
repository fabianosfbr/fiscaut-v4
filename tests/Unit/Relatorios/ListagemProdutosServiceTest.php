<?php

namespace Tests\Unit\Relatorios;

use PHPUnit\Framework\TestCase;
use App\Services\Relatorios\ListagemProdutosService;

class ListagemProdutosServiceTest extends TestCase
{
    public function test_accumulate_agrega_por_identidade_de_produto_incluindo_cfop_e_ncm(): void
    {
        $accumulator = [];

        ListagemProdutosService::accumulate($accumulator, [
            [
                'cProd' => 'ABC',
                'xProd' => 'Produto A',
                'NCM' => '12345678',
                'CFOP' => '5102',
                'uCom' => 'UN',
                'qCom' => '2',
                'vProd' => '10.00',
            ],
            [
                'cProd' => 'ABC',
                'xProd' => 'Produto A',
                'NCM' => '12345678',
                'CFOP' => '5102',
                'uCom' => 'UN',
                'qCom' => '3',
                'vProd' => '15.00',
            ],
            [
                'xProd' => 'Produto Sem Codigo',
                'uCom' => 'KG',
                'qCom' => '1,5',
                'vProd' => '7,00',
            ],
        ]);

        ListagemProdutosService::accumulate($accumulator, [
            [
                'cProd' => 'ABC',
                'xProd' => 'Produto A',
                'NCM' => '12345678',
                'CFOP' => '6102',
                'uCom' => 'UN',
                'qCom' => '1',
                'vProd' => '5.00',
            ],
        ]);

        $this->assertCount(3, $accumulator);

        foreach ($accumulator as $id => $row) {
            $this->assertSame($id, $row['id']);
            $this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $id);
        }

        $rows = array_values($accumulator);

        $abc5102 = array_values(array_filter($rows, fn (array $r) => ($r['cProd'] ?? null) === 'ABC' && ($r['CFOP'] ?? null) === '5102'))[0];
        $this->assertSame(5.0, $abc5102['total_qCom']);
        $this->assertSame(25.0, $abc5102['total_vProd']);
        $this->assertSame(2, $abc5102['itens']);

        $abc6102 = array_values(array_filter($rows, fn (array $r) => ($r['cProd'] ?? null) === 'ABC' && ($r['CFOP'] ?? null) === '6102'))[0];
        $this->assertSame(1.0, $abc6102['total_qCom']);
        $this->assertSame(5.0, $abc6102['total_vProd']);
        $this->assertSame(1, $abc6102['itens']);

        $semCodigo = array_values(array_filter($rows, fn (array $r) => ($r['cProd'] ?? null) === null && ($r['xProd'] ?? null) === 'Produto Sem Codigo'))[0];
        $this->assertSame(1.5, $semCodigo['total_qCom']);
        $this->assertSame(7.0, $semCodigo['total_vProd']);
        $this->assertSame(1, $semCodigo['itens']);
    }
}

