<?php

use App\Services\ScheduleService;
use Cron\CronExpression;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('schedule:run-dynamic {--force}', function () {
    Log::info('Executando schedule:run-dynamic');

    $arguments = [];
    if ((bool) $this->option('force')) {
        $arguments['--force'] = true;
    }

    $exitCode = $this->call('schedule:run', $arguments);

    Log::info('Finalizado schedule:run-dynamic', ['exit_code' => $exitCode]);

    return $exitCode;
})->purpose('Executa o scheduler carregando tarefas dinâmicas do banco');

$registerDynamicSchedules = function (): void {
    $argv = $_SERVER['argv'] ?? [];
    $artisanCommand = $argv[1] ?? null;

    if (!is_string($artisanCommand) || !str_starts_with($artisanCommand, 'schedule:')) {
        return;
    }

    try {
        /** @var \Illuminate\Support\Collection<int, \App\Models\Schedule> $schedules */
        $schedules = app(ScheduleService::class)->getActives();

        foreach ($schedules as $dbSchedule) {
            try {
                $commandName = $dbSchedule->command === 'custom'
                    ? (string) ($dbSchedule->command_custom ?? '')
                    : (string) $dbSchedule->command;

                if ($commandName === '') {
                    Log::error('Schedule dinâmico inválido: comando vazio', [
                        'schedule_id' => $dbSchedule->id,
                        'command' => $dbSchedule->command,
                        'command_custom' => $dbSchedule->command_custom,
                    ]);
                    continue;
                }

                $expression = (string) ($dbSchedule->expression ?? '');
                if ($expression === '' || !CronExpression::isValidExpression($expression)) {
                    Log::error('Schedule dinâmico inválido: expressão cron inválida', [
                        'schedule_id' => $dbSchedule->id,
                        'command' => $commandName,
                        'expression' => $expression,
                    ]);
                    continue;
                }

                $argumentTokens = [];
                foreach (($dbSchedule->params ?? []) as $argument) {
                    if (!is_array($argument)) {
                        continue;
                    }

                    $value = $argument['value'] ?? null;
                    if ($value === null || $value === '') {
                        continue;
                    }

                    if (($argument['type'] ?? null) === 'function') {
                        $value = eval('return (string) ' . $value . ';');
                    }

                    $argumentTokens[] = (string) $value;
                }

                $optionTokens = array_values(array_map('strval', $dbSchedule->getOptions()));

                $parameters = array_merge($argumentTokens, $optionTokens);

                $event = Schedule::command($commandName, $parameters)
                    ->cron($expression)
                    ->description("DB #{$dbSchedule->id} {$commandName}");

                $historyOutputPath = storage_path("logs/schedule-history-{$dbSchedule->id}.log");
                $event->sendOutputTo($historyOutputPath);

                $environments = $dbSchedule->environments;
                if (is_string($environments)) {
                    $decoded = json_decode($environments, true);
                    $environments = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $environments)));
                }

                if (is_array($environments) && $environments !== []) {
                    $event->environments(array_values($environments));
                }

                if ($dbSchedule->even_in_maintenance_mode) {
                    $event->evenInMaintenanceMode();
                }

                if ($dbSchedule->without_overlapping) {
                    $event->withoutOverlapping();
                }

                if ($dbSchedule->on_one_server) {
                    $event->onOneServer();
                }

                if ($dbSchedule->run_in_background) {
                    $event->runInBackground();
                }

                if (is_string($dbSchedule->webhook_before) && $dbSchedule->webhook_before !== '') {
                    $event->pingBefore($dbSchedule->webhook_before);
                }

                if (is_string($dbSchedule->webhook_after) && $dbSchedule->webhook_after !== '') {
                    $event->thenPing($dbSchedule->webhook_after);
                }

                if (is_string($dbSchedule->email_output) && $dbSchedule->email_output !== '') {
                    if ($dbSchedule->sendmail_success) {
                        $event->emailOutputTo($dbSchedule->email_output);
                    }

                    if ($dbSchedule->sendmail_error) {
                        $event->emailOutputOnFailure($dbSchedule->email_output);
                    }
                }

                if (is_string($dbSchedule->log_filename) && $dbSchedule->log_filename !== '') {
                    $filename = basename($dbSchedule->log_filename);
                    if ($filename !== '') {
                        $event->appendOutputTo(storage_path('logs/' . $filename));
                    }
                }

                $context = [
                    'schedule_id' => $dbSchedule->id,
                    'command' => $commandName,
                    'expression' => $expression,
                ];

                $createHistory = function (string $result) use ($dbSchedule, $commandName, $argumentTokens, $optionTokens, $historyOutputPath) {
                    $output = '';
                    if (is_file($historyOutputPath)) {
                        $output = (string) file_get_contents($historyOutputPath);
                    }

                    if ($result !== '') {
                        $output = '[' . $result . "]\n" . $output;
                    }

                    if (mb_strlen($output) > 50000) {
                        $output = mb_substr($output, 0, 50000);
                    }

                    $dbSchedule->histories()->create([
                        'schedule_id' => $dbSchedule->id,
                        'command' => $commandName,
                        'params' => $argumentTokens,
                        'options' => $optionTokens,
                        'output' => $output,
                    ]);
                };

                $event->before(fn () => Log::info('Executando schedule dinâmico', $context));

                if ($dbSchedule->log_success) {
                    $event->onSuccess(function () use ($context, $createHistory) {
                        $createHistory('success');
                        Log::info('Schedule dinâmico executado com sucesso', $context);
                    });
                }

                if ($dbSchedule->log_error) {
                    $event->onFailure(function () use ($context, $createHistory) {
                        $createHistory('failure');
                        Log::error('Schedule dinâmico executado com erro', $context);
                    });
                }
            } catch (Throwable $e) {
                Log::error('Erro ao registrar schedule dinâmico', [
                    'schedule_id' => $dbSchedule->id ?? null,
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    } catch (Throwable $e) {
        Log::error('Erro ao carregar schedules dinâmicos do banco', [
            'exception' => $e->getMessage(),
        ]);
    }
};

$registerDynamicSchedules();
