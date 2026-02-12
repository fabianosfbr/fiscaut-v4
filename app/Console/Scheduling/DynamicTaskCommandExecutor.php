<?php

namespace App\Console\Scheduling;

use App\Models\Schedule as DbSchedule;
use App\Services\ScheduleService;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Psr\Log\LoggerInterface;
use Throwable;

class DynamicTaskCommandExecutor
{
    public function __construct(
        private readonly ScheduleService $scheduleService,
        private readonly Schedule $schedule,
        private readonly LoggerInterface $logger,
        private readonly Filesystem $files,
        private readonly ConfigRepository $config,
    ) {}

    public function registerFromDatabase(?string $artisanCommand): void
    {
        if (! $this->shouldRegister($artisanCommand)) {
            return;
        }

        try {
            $schedules = $this->scheduleService->getActives();

            $this->logger->info('Schedules dinâmicos carregados do banco', [
                'count' => $schedules->count(),
            ]);

            foreach ($schedules as $dbSchedule) {
                try {
                    $this->registerEvent($dbSchedule);
                } catch (Throwable $e) {
                    $this->logger->error('Erro ao registrar schedule dinâmico', [
                        'schedule_id' => $dbSchedule->id ?? null,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }
        } catch (Throwable $e) {
            $this->logger->error('Erro ao carregar schedules dinâmicos do banco', [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function runAllNow(): void
    {
        try {
            $schedules = $this->scheduleService->getActives();

            foreach ($schedules as $dbSchedule) {
                $commandName = $this->resolveCommandName($dbSchedule);
                if ($commandName === '') {
                    continue;
                }

                $parameters = $this->buildArtisanCallParameters($dbSchedule);

                $this->logger->info('Executando schedule dinâmico imediatamente (force)', [
                    'schedule_id' => $dbSchedule->id,
                    'command' => $commandName,
                    'parameters' => $parameters,
                ]);

                \Illuminate\Support\Facades\Artisan::call($commandName, $parameters);
            }
        } catch (Throwable $e) {
            $this->logger->error('Erro ao executar todos os schedules dinâmicos', [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    private function buildArtisanCallParameters(DbSchedule $dbSchedule): array
    {
        $parameters = [];

        // Adiciona argumentos posicionais
        foreach ($this->buildArgumentTokens($dbSchedule) as $value) {
            $parameters[] = $value;
        }

        // Adiciona opções
        $options = $dbSchedule->options ?? [];
        foreach ($options as $key => $value) {
            if (is_numeric($key)) {
                // Opções simples como ['verbose'] ou ['-v']
                $optionName = str_starts_with($value, '-') ? $value : "--{$value}";
                $parameters[$optionName] = true;
            } else {
                // Opções com valor como ['name' => 'value']
                $optionName = str_starts_with($key, '-') ? $key : "--{$key}";
                $parameters[$optionName] = $value;
            }
        }

        // Adiciona opções com valor do campo específico
        $optionsWithValue = $dbSchedule->options_with_value ?? [];
        foreach ($optionsWithValue as $key => $config) {
            $name = $config['name'] ?? $key;
            $val = $config['value'] ?? null;
            if ($val !== null) {
                $optionName = str_starts_with($name, '-') ? $name : "--{$name}";
                $parameters[$optionName] = $val;
            }
        }

        return $parameters;
    }

    public function registerEvent(DbSchedule $dbSchedule): void
    {
        $commandName = $this->resolveCommandName($dbSchedule);

        if ($commandName === '') {
            $this->logger->error('Schedule dinâmico inválido: comando vazio', [
                'schedule_id' => $dbSchedule->id,
                'command' => $dbSchedule->command,
                'command_custom' => $dbSchedule->command_custom,
            ]);

            return;
        }

        $expression = (string) ($dbSchedule->expression ?? '');
        if ($expression === '' || ! CronExpression::isValidExpression($expression)) {
            $this->logger->error('Schedule dinâmico inválido: expressão cron inválida', [
                'schedule_id' => $dbSchedule->id,
                'command' => $commandName,
                'expression' => $expression,
            ]);

            return;
        }

        $argumentTokens = $this->buildArgumentTokens($dbSchedule);
        $optionTokens = array_values(array_map('strval', $dbSchedule->getOptions()));
        $parameters = array_merge($argumentTokens, $optionTokens);

        $event = $this->schedule
            ->command($commandName, $parameters)
            ->cron($expression)
            ->description("DB #{$dbSchedule->id} {$commandName}");

        $historyOutputPath = storage_path("logs/schedule-history-{$dbSchedule->id}.log");
        $event->sendOutputTo($historyOutputPath);

        $logFilename = is_string($dbSchedule->log_filename) ? trim($dbSchedule->log_filename) : '';
        $logFilePath = $logFilename !== '' ? storage_path('logs/'.basename($logFilename)) : null;

        $environments = $this->normalizeEnvironments($dbSchedule->environments);
        if ($environments !== []) {
            $event->environments($environments);
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

        $context = [
            'schedule_id' => $dbSchedule->id,
            'command' => $commandName,
            'expression' => $expression,
        ];

        $event->before(fn () => $this->logger->info('Executando schedule dinâmico', $context));

        $this->attachHistoryCallbacks(
            event: $event,
            dbSchedule: $dbSchedule,
            commandName: $commandName,
            argumentTokens: $argumentTokens,
            optionTokens: $optionTokens,
            historyOutputPath: $historyOutputPath,
            logFilePath: $logFilePath,
            context: $context,
        );
    }

    private function shouldRegister(?string $artisanCommand): bool
    {
        if (! is_string($artisanCommand)) {
            return false;
        }

        // Se for o comando de execução dinâmica, registramos
        if ($artisanCommand === 'schedule:run-dynamic') {
            return true;
        }

        // Se for qualquer comando do scheduler nativo do Laravel
        return str_starts_with($artisanCommand, 'schedule:');
    }

    private function resolveCommandName(DbSchedule $dbSchedule): string
    {
        return $dbSchedule->command === 'custom'
            ? (string) ($dbSchedule->command_custom ?? '')
            : (string) $dbSchedule->command;
    }

    private function buildArgumentTokens(DbSchedule $dbSchedule): array
    {
        $argumentTokens = [];

        foreach (($dbSchedule->params ?? []) as $argument) {
            if (! is_array($argument)) {
                continue;
            }

            $value = $argument['value'] ?? null;
            if ($value === null || $value === '') {
                continue;
            }

            if (($argument['type'] ?? null) === 'function') {
                $value = eval('return (string) '.$value.';');
            }

            $argumentTokens[] = (string) $value;
        }

        return $argumentTokens;
    }

    private function normalizeEnvironments(mixed $environments): array
    {
        if (is_array($environments)) {
            return array_values(array_filter(array_map('strval', $environments)));
        }

        if (! is_string($environments) || trim($environments) === '') {
            return [];
        }

        $decoded = json_decode($environments, true);

        return is_array($decoded)
            ? array_values(array_filter(array_map('strval', $decoded)))
            : array_values(array_filter(array_map('trim', explode(',', $environments))));
    }

    private function attachHistoryCallbacks(
        Event $event,
        DbSchedule $dbSchedule,
        string $commandName,
        array $argumentTokens,
        array $optionTokens,
        string $historyOutputPath,
        ?string $logFilePath,
        array $context,
    ): void {
        $createHistory = function (string $result) use ($dbSchedule, $commandName, $argumentTokens, $optionTokens, $historyOutputPath, $logFilePath) {
            $output = '';
            if ($this->files->exists($historyOutputPath)) {
                $output = (string) $this->files->get($historyOutputPath);
            }

            if ($logFilePath !== null && $logFilePath !== '' && $logFilePath !== $historyOutputPath && $output !== '') {
                $this->files->append($logFilePath, $output);
            }

            if ($result !== '') {
                $output = '['.$result."]\n".$output;
            }

            $max = 50000;
            if (mb_strlen($output) > $max) {
                $output = mb_substr($output, 0, $max);
            }

            $dbSchedule->histories()->create([
                'schedule_id' => $dbSchedule->id,
                'command' => $commandName,
                'params' => $argumentTokens,
                'options' => $optionTokens,
                'output' => $output,
            ]);
        };

        if ($dbSchedule->log_success) {
            $event->onSuccess(function () use ($context, $createHistory) {
                $createHistory('success');
                $this->logger->info('Schedule dinâmico executado com sucesso', $context);
            });
        }

        if ($dbSchedule->log_error) {
            $event->onFailure(function () use ($context, $createHistory) {
                $createHistory('failure');
                $this->logger->error('Schedule dinâmico executado com erro', $context);
            });
        }
    }
}
