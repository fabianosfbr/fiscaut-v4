<?php

namespace Tests\Unit;

use App\Filament\Pages\Relatorio\EntradaVsImposto;
use App\Models\Issuer;
use Mockery;
use PHPUnit\Framework\TestCase;

class EntradaVsImpostoPageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_generate_data_normalizes_missing_keys(): void
    {
        $mock = Mockery::mock('alias:App\\Filament\\Widgets\\StatisticData');
        $mock
            ->shouldReceive('entradaVsImpostoMensal')
            ->once()
            ->andReturn([
                '01-2026' => [
                    'faturamento' => '100',
                    'icms' => 10,
                ],
            ]);

        $page = new EntradaVsImposto();
        $page->generateData(new Issuer());

        $this->assertArrayHasKey('01-2026', $page->faturamento);

        $row = $page->faturamento['01-2026'];

        $this->assertSame(100.0, $row['faturamento']);
        $this->assertSame(0.0, $row['faturamento-nfse']);
        $this->assertSame(10.0, $row['icms']);
        $this->assertSame(0.0, $row['icmsST']);
        $this->assertSame(0.0, $row['ipi']);
        $this->assertSame(0.0, $row['pis']);
        $this->assertSame(0.0, $row['cofins']);
        $this->assertSame(0.0, $row['cprb']);
        $this->assertSame(0.0, $row['csll']);
        $this->assertSame(0.0, $row['irpj']);
        $this->assertSame(0.0, $row['faturamentoLiquido']);
    }

    public function test_generate_data_with_null_issuer_keeps_empty_array(): void
    {
        $page = new EntradaVsImposto();
        $page->generateData(null);

        $this->assertSame([], $page->faturamento);
    }
}

