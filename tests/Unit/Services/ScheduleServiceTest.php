<?php

namespace Tests\Unit\Services;

use App\Models\Schedule;
use App\Services\ScheduleService;
use Mockery;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ScheduleServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    private function injectModel(ScheduleService $service, Schedule $model): void
    {
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('model');
        $property->setAccessible(true);
        $property->setValue($service, $model);
    }

    public function test_it_returns_only_active_schedules_when_cache_is_disabled(): void
    {
        config()->set('schedule.cache.enabled', false);

        $expected = collect([(object) ['id' => 10, 'command' => 'app:run']]);

        $builder = Mockery::mock();
        $builder->shouldReceive('get')->once()->andReturn($expected);

        $model = Mockery::mock(Schedule::class);
        $model->shouldReceive('active')->once()->andReturn($builder);

        $service = new ScheduleService();
        $this->injectModel($service, $model);

        $result = $service->getActives();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(1, $result);
        $this->assertSame(10, $result->first()->id);
        $this->assertSame('app:run', $result->first()->command);
    }

    public function test_it_caches_active_schedules_and_clear_cache_forces_reload(): void
    {
        config()->set('schedule.cache.enabled', true);
        config()->set('schedule.cache.store', 'array');
        config()->set('schedule.cache.key', 'schedule_cache_test');
        config()->set('schedule.cache.ttl', 600);

        $first = collect([(object) ['id' => 1, 'command' => 'app:first']]);
        $second = collect([
            (object) ['id' => 1, 'command' => 'app:first'],
            (object) ['id' => 2, 'command' => 'app:second'],
        ]);

        $builder = Mockery::mock();
        $builder->shouldReceive('get')->once()->andReturn($first);
        $builder->shouldReceive('get')->once()->andReturn($second);

        $model = Mockery::mock(Schedule::class);
        $model->shouldReceive('active')->twice()->andReturn($builder);

        $service = new ScheduleService();
        $this->injectModel($service, $model);

        $cached = $service->getActives();
        $this->assertCount(1, $cached);
        $this->assertSame('app:first', $cached->first()->command);

        $stillCached = $service->getActives();
        $this->assertCount(1, $stillCached);
        $this->assertSame('app:first', $stillCached->first()->command);

        $service->clearCache();

        $reloaded = $service->getActives();
        $this->assertCount(2, $reloaded);
        $this->assertSame(
            ['app:first', 'app:second'],
            $reloaded->pluck('command')->sort()->values()->toArray(),
        );
    }
}
