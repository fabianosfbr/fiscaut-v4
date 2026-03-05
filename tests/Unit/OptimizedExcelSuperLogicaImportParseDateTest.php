<?php

namespace Tests\Unit;

use App\Imports\OptimizedExcelSuperLogicaImport;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OptimizedExcelSuperLogicaImportParseDateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.timezone' => 'UTC']);
        date_default_timezone_set('UTC');
    }

    private function parseDateViaReflection(mixed $value): ?Carbon
    {
        $ref = new \ReflectionClass(OptimizedExcelSuperLogicaImport::class);
        $instance = $ref->newInstanceWithoutConstructor();
        $method = $ref->getMethod('parseDate');
        $method->setAccessible(true);

        /** @var ?Carbon $result */
        $result = $method->invoke($instance, $value);

        return $result;
    }

    public function test_parses_excel_serial_date_as_carbon(): void
    {
        $target = Carbon::create(2026, 2, 27, 0, 0, 0, 'UTC');
        $base = Carbon::create(1899, 12, 31, 0, 0, 0, 'UTC');

        $diffDays = $base->diffInDays($target);
        $excelSerial = $diffDays + ($diffDays >= 60 ? 1 : 0);

        $parsed = $this->parseDateViaReflection($excelSerial + 0.5); // 12:00

        $this->assertInstanceOf(Carbon::class, $parsed);
        $this->assertSame('2026-02-27 12:00:00', $parsed->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $parsed->timezoneName);
    }

    public function test_parses_yyyymmdd_integer_date_as_carbon(): void
    {
        $parsed = $this->parseDateViaReflection(20260227);

        $this->assertInstanceOf(Carbon::class, $parsed);
        $this->assertSame('2026-02-27 00:00:00', $parsed->setTimezone('UTC')->format('Y-m-d H:i:s'));
    }
}
