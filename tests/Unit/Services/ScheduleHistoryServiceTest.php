<?php

namespace Tests\Unit\Services;

use App\Services\ScheduleHistoryService;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class ScheduleHistoryServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_prune_keeps_latest_n_histories_and_deletes_the_rest(): void
    {
        $relationForKeepIds = Mockery::mock();
        $relationForKeepIds->shouldReceive('select')->once()->with('id')->andReturnSelf();
        $relationForKeepIds->shouldReceive('latest')->once()->andReturnSelf();
        $relationForKeepIds->shouldReceive('take')->once()->with(2)->andReturnSelf();
        $relationForKeepIds->shouldReceive('pluck')->once()->with('id')->andReturn(new Collection([10, 11]));

        $relationForDelete = Mockery::mock();
        $relationForDelete->shouldReceive('whereNotIn')->once()->with('id', [10, 11])->andReturnSelf();
        $relationForDelete->shouldReceive('delete')->once()->andReturn(3);

        $schedule = new class($relationForKeepIds, $relationForDelete) {
            public int $max_history_count = 2;

            private array $relations;

            public function __construct(...$relations)
            {
                $this->relations = $relations;
            }

            public function histories()
            {
                return array_shift($this->relations);
            }
        };

        ScheduleHistoryService::prune($schedule);

        $this->assertTrue(true);
    }
}

