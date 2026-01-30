<?php

namespace Tests\Unit\Console;

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use App\Models\Schedule as DbSchedule;
use App\Services\ScheduleService;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Mockery;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

class DynamicTaskCommandExecutorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_does_not_register_when_command_is_not_schedule(): void
    {
        $scheduleService = Mockery::mock(ScheduleService::class);
        $scheduleService->shouldNotReceive('getActives');

        $schedule = Mockery::mock(Schedule::class);
        $logger = Mockery::mock(LoggerInterface::class);
        $files = Mockery::mock(Filesystem::class);
        $config = Mockery::mock(ConfigRepository::class);

        $executor = new DynamicTaskCommandExecutor(
            $scheduleService,
            $schedule,
            $logger,
            $files,
            $config,
        );

        $executor->registerFromDatabase('migrate');

        $this->assertTrue(true);
    }

    public function test_it_registers_event_and_persists_history_on_success(): void
    {
        $dbSchedule = Mockery::mock(DbSchedule::class)->makePartial();
        $dbSchedule->id = 1;
        $dbSchedule->command = 'app:sync-nfe-sefaz';
        $dbSchedule->command_custom = null;
        $dbSchedule->expression = '* * * * *';
        $dbSchedule->params = [];
        $dbSchedule->environments = null;
        $dbSchedule->even_in_maintenance_mode = false;
        $dbSchedule->without_overlapping = false;
        $dbSchedule->on_one_server = false;
        $dbSchedule->run_in_background = false;
        $dbSchedule->webhook_before = null;
        $dbSchedule->webhook_after = null;
        $dbSchedule->email_output = null;
        $dbSchedule->sendmail_success = false;
        $dbSchedule->sendmail_error = false;
        $dbSchedule->log_success = true;
        $dbSchedule->log_error = true;
        $dbSchedule->log_filename = null;

        $dbSchedule->shouldReceive('getOptions')->andReturn(['-v']);

        $historyRelation = Mockery::mock();
        $historyRelation->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload) {
                $this->assertSame(1, $payload['schedule_id']);
                $this->assertSame('app:sync-nfe-sefaz', $payload['command']);
                $this->assertSame([], $payload['params']);
                $this->assertSame(['-v'], $payload['options']);
                $this->assertIsString($payload['output']);
                $this->assertStringStartsWith('[success]', $payload['output']);

                return true;
            }));

        $dbSchedule->shouldReceive('histories')->andReturn($historyRelation);

        $scheduleService = Mockery::mock(ScheduleService::class);
        $scheduleService->shouldReceive('getActives')->once()->andReturn(new Collection([$dbSchedule]));

        $event = Mockery::mock(Event::class);
        $event->shouldReceive('cron')->once()->with('* * * * *')->andReturnSelf();
        $event->shouldReceive('description')->once()->with('DB #1 app:sync-nfe-sefaz')->andReturnSelf();
        $event->shouldReceive('sendOutputTo')->once()->with(Mockery::type('string'))->andReturnSelf();
        $event->shouldReceive('before')->once()->with(Mockery::type('callable'))->andReturnSelf();

        $successCallback = null;
        $event->shouldReceive('onSuccess')
            ->once()
            ->with(Mockery::on(function ($callback) use (&$successCallback) {
                $successCallback = $callback;

                return is_callable($callback);
            }))
            ->andReturnSelf();

        $event->shouldReceive('onFailure')
            ->once()
            ->with(Mockery::type('callable'))
            ->andReturnSelf();

        $schedule = Mockery::mock(Schedule::class);
        $schedule->shouldReceive('command')
            ->once()
            ->with('app:sync-nfe-sefaz', ['-v'])
            ->andReturn($event);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->atLeast()->once();
        $logger->shouldReceive('error')->zeroOrMoreTimes();

        $files = Mockery::mock(Filesystem::class);
        $files->shouldReceive('exists')->andReturn(true);
        $files->shouldReceive('get')->andReturn("output\n");
        $files->shouldReceive('append')->zeroOrMoreTimes();

        $config = Mockery::mock(ConfigRepository::class);
        $config->shouldReceive('get')->andReturnNull();

        $executor = new DynamicTaskCommandExecutor(
            $scheduleService,
            $schedule,
            $logger,
            $files,
            $config,
        );

        $executor->registerFromDatabase('schedule:run');

        $this->assertNotNull($successCallback);
        $successCallback();
    }
}
