<?php

namespace Tests\Unit;

use App\Filament\Widgets\StatisticData;
use PHPUnit\Framework\TestCase;

class StatisticDataFinancialStatsTest extends TestCase
{
    public function test_normalize_money_handles_null_and_non_numeric(): void
    {
        $this->assertSame(0.0, StatisticData::normalizeMoney(null));
        $this->assertSame(0.0, StatisticData::normalizeMoney('abc'));
    }

    public function test_normalize_money_accepts_dot_decimal_format(): void
    {
        $this->assertSame(1234.56, StatisticData::normalizeMoney('1234.56'));
    }

    public function test_normalize_money_accepts_comma_decimal_format(): void
    {
        $this->assertSame(1234.56, StatisticData::normalizeMoney('1234,56'));
    }

    public function test_normalize_money_accepts_thousands_and_comma_decimal_format(): void
    {
        $this->assertSame(1234.56, StatisticData::normalizeMoney('1.234,56'));
    }

    public function test_compute_financial_stats_returns_total_average_trend_and_comparisons(): void
    {
        $series = [
            '2025-01' => 100,
            '2025-02' => 200,
            '2025-03' => 300,
        ];

        $stats = StatisticData::computeFinancialStats($series);

        $this->assertSame(600.0, $stats['total']);
        $this->assertSame(200.0, $stats['media']);

        $this->assertSame('alta', $stats['tendencia']['direcao']);
        $this->assertGreaterThan(0, $stats['tendencia']['slope']);

        $this->assertSame(100.0, $stats['comparativos']['mom']['delta']);
        $this->assertSame(50.0, $stats['comparativos']['mom']['pct']);

        $this->assertSame(0.0, $stats['comparativos']['yoy']['delta']);
        $this->assertSame(0.0, $stats['comparativos']['yoy']['pct']);
    }

    public function test_compute_financial_stats_with_empty_series_returns_zeros(): void
    {
        $stats = StatisticData::computeFinancialStats([]);

        $this->assertSame(0.0, $stats['total']);
        $this->assertSame(0.0, $stats['media']);
        $this->assertSame('estavel', $stats['tendencia']['direcao']);
        $this->assertSame(0.0, $stats['tendencia']['slope']);
        $this->assertSame(0.0, $stats['comparativos']['mom']['delta']);
        $this->assertSame(0.0, $stats['comparativos']['mom']['pct']);
        $this->assertSame(0.0, $stats['comparativos']['yoy']['delta']);
        $this->assertSame(0.0, $stats['comparativos']['yoy']['pct']);
    }
}

