<?php

namespace Tests\Feature;

use App\Models\GeneralSetting;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralSettingCacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_invalidates_per_key_cache_after_update(): void
    {
        $tenant = Tenant::create(['name' => 'Tenant 1']);

        GeneralSetting::setValue('configuracoes_gerais', ['foo' => 'a', 'bar' => 'b'], null, $tenant->id);

        $valueA = GeneralSetting::getValue('configuracoes_gerais', 'foo', null, null, $tenant->id);
        $this->assertSame('a', $valueA);

        GeneralSetting::setValue('configuracoes_gerais', ['foo' => 'c'], null, $tenant->id);

        $valueC = GeneralSetting::getValue('configuracoes_gerais', 'foo', null, null, $tenant->id);
        $this->assertSame('c', $valueC);
    }

    public function test_it_invalidates_per_key_cache_after_remove(): void
    {
        $tenant = Tenant::create(['name' => 'Tenant 1']);

        GeneralSetting::setValue('configuracoes_gerais', ['bar' => 'x'], null, $tenant->id);

        $valueX = GeneralSetting::getValue('configuracoes_gerais', 'bar', null, null, $tenant->id);
        $this->assertSame('x', $valueX);

        $removed = GeneralSetting::removeValue('configuracoes_gerais', 'bar', null, $tenant->id);
        $this->assertTrue($removed);

        $valueDefault = GeneralSetting::getValue('configuracoes_gerais', 'bar', 'default', null, $tenant->id);
        $this->assertSame('default', $valueDefault);
    }
}
