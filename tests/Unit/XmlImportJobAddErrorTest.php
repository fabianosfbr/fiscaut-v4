<?php

namespace Tests\Unit;

use App\Models\XmlImportJob;
use PHPUnit\Framework\TestCase;

class XmlImportJobAddErrorTest extends TestCase
{
    public function test_add_error_sanitizes_invalid_utf8_and_does_not_throw(): void
    {
        $job = new class extends XmlImportJob
        {
            public function save(array $options = [])
            {
                return true;
            }
        };

        $job->errors = [];
        $job->error_files = 0;

        $invalidUtf8 = "Falha: \xC3\x28";

        $job->addError($invalidUtf8);

        $this->assertIsArray($job->errors);
        $this->assertCount(1, $job->errors);
        $this->assertSame(1, $job->error_files);

        $stored = $job->errors[0];
        $this->assertIsString($stored);
        $this->assertSame(1, preg_match('//u', $stored));
    }
}
