<?php

namespace Tests\Unit\Services;

use App\Services\CommandService;
use Illuminate\Contracts\Console\Kernel;
use Mockery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Tests\TestCase;

class CommandServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_excludes_commands_configured_in_exclude_list_including_wildcards(): void
    {
        config()->set('schedule.commands.exclude', [
            'help',
            'route:*',
        ]);

        $help = new class extends Command
        {
            public function __construct()
            {
                parent::__construct('help');
                $this->setDescription('Help');
            }
        };

        $routeList = new class extends Command
        {
            public function __construct()
            {
                parent::__construct('route:list');
                $this->setDescription('List routes');
            }
        };

        $appRun = new class extends Command
        {
            public function __construct()
            {
                parent::__construct('app:run');
                $this->setDescription('Run app');
            }
        };

        $this->mock(Kernel::class)
            ->shouldReceive('all')
            ->once()
            ->andReturn([
                'help' => $help,
                'route:list' => $routeList,
                'app:run' => $appRun,
            ]);

        $result = CommandService::get();

        $this->assertTrue($result->has('app:run'));
        $this->assertFalse($result->has('help'));
        $this->assertFalse($result->has('route:list'));
    }

    public function test_it_returns_arguments_and_options_metadata(): void
    {
        config()->set('schedule.commands.exclude', []);

        $command = new class extends Command
        {
            public function __construct()
            {
                parent::__construct('app:run');
                $this->setDescription('Run app');
            }

            protected function configure(): void
            {
                $this->addArgument('id', InputArgument::REQUIRED);
                $this->addArgument('type', InputArgument::OPTIONAL, '', 'default-type');

                $this->addOption('foo', null, InputOption::VALUE_REQUIRED, '', 'bar');
                $this->addOption('flag', null, InputOption::VALUE_NONE);
            }
        };

        $this->mock(Kernel::class)
            ->shouldReceive('all')
            ->once()
            ->andReturn([
                'app:run' => $command,
            ]);

        $result = CommandService::get();
        $data = $result->get('app:run');

        $this->assertIsArray($data);
        $this->assertSame('app:run', $data['name']);
        $this->assertSame('Run app', $data['description']);
        $this->assertSame('app:run (Run app)', $data['full_name']);

        $this->assertIsString($data['signature']);
        $this->assertStringContainsString('app:run', $data['signature']);

        $this->assertSame([
            ['name' => 'id', 'default' => null, 'required' => true],
            ['name' => 'type', 'default' => 'default-type', 'required' => false],
        ], $data['arguments']);

        $this->assertIsArray($data['options']);
        $this->assertArrayHasKey('withValue', $data['options']);
        $this->assertArrayHasKey('withoutValue', $data['options']);

        $this->assertCount(1, $data['options']['withValue']);
        $this->assertSame('foo', $data['options']['withValue'][0]->name);
        $this->assertSame('bar', $data['options']['withValue'][0]->default);
        $this->assertTrue($data['options']['withValue'][0]->required);

        $this->assertContains('flag', $data['options']['withoutValue']);
        $this->assertContains('-v', $data['options']['withoutValue']);
        $this->assertContains('-vv', $data['options']['withoutValue']);
        $this->assertContains('-vvv', $data['options']['withoutValue']);
    }
}
