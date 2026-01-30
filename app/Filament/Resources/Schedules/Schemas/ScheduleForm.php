<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Rules\CronValidation;
use Filament\Schemas\Schema;

use App\Services\CommandService;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Textarea;

class ScheduleForm
{
    public static Collection $commands;


    public static function configure(Schema $schema): Schema
    {
        static::$commands = CommandService::get();

        return $schema
            ->components([
                Select::make('command')->label('Comando')
                    ->options(
                        fn() =>
                        config('schedule.commands.enable_custom') ?
                            static::$commands->pluck('full_name', 'name')->prepend('custom') : static::$commands->pluck('full_name', 'name')
                    )
                    ->reactive()
                    ->searchable()
                    ->required()
                    ->afterStateUpdated(function ($set, $state) {
                        $set('params', static::$commands->firstWhere('name', $state)['arguments'] ?? []);
                        $set('options_with_value', collect(static::$commands->firstWhere('name', $state)['options']["withValue"] ?? [])->map(function ($item) {
                            return (array) $item;
                        })->toArray());
                    }),

                Textarea::make('description')
                    ->placeholder('Descrição')
                    ->label('Descrição')
                    ->maxLength(255),

                TextInput::make('command_custom')
                    ->placeholder('Comando personalizado')
                    ->label('Comando Personalizado')
                    ->visible(fn($get) => $get('command') === 'custom'),

                Repeater::make('params')->label('Argumentos')->extraAttributes(['class' => 'repeater--table-hidden-header'])
                    ->table([
                        TableColumn::make('value')->hiddenHeaderLabel(),
                    ])
                    ->schema([
                        TextInput::make('value')->prefix(fn($get) => ucfirst($get('name')))->required(fn($get) => $get('required'))->hiddenLabel(),
                        Hidden::make('name'),
                    ])->addable(false)->deletable(false)->reorderable(false)
                    ->visible(fn($get) => !empty(static::$commands->firstWhere('name', $get('command'))['arguments'])),

                Repeater::make('options_with_value')->label('Opções com valor')->extraAttributes(['class' => 'repeater--table-hidden-header'])
                    ->table([
                        TableColumn::make('value')->hiddenHeaderLabel(),
                    ])
                    ->schema([
                        TextInput::make('value')->prefix(fn($get) => ucfirst($get('name')))->required(fn($get) => $get('required'))->hiddenLabel(),
                        Hidden::make('type')->default('string'),
                        Hidden::make('name'),
                    ])->addable(false)->deletable(false)->reorderable(false)->default([])
                    ->visible(fn($state) => !empty($state)),

                CheckboxList::make('options')->label('Opções sem valor')
                    ->options(
                        fn($get) =>
                        collect(static::$commands->firstWhere('name', $get('command'))['options']['withoutValue'] ?? [])
                            ->mapWithKeys(function ($value) {
                                return [$value => $value];
                            }),
                    )
                    ->afterStateHydrated(function (CheckboxList $component, $state): void {
                        if (!is_array($state)) {
                            return;
                        }

                        if (in_array('verbose', $state, true) && !in_array('-v', $state, true)) {
                            $state[] = '-v';
                        }

                        $state = array_values(array_diff($state, ['verbose']));

                        $component->state($state);
                    })
                    ->afterStateUpdated(function (CheckboxList $component, $state): void {
                        if (!is_array($state)) {
                            return;
                        }

                        $verbosity = array_values(array_filter($state, fn (string $value) => in_array($value, ['-v', '-vv', '-vvv'], true)));
                        if (count($verbosity) <= 1) {
                            $component->state($state);
                            return;
                        }

                        $selected = collect($verbosity)->sortByDesc(fn (string $value) => strlen($value))->first();
                        $state = array_values(array_filter($state, fn (string $value) => !in_array($value, ['-v', '-vv', '-vvv'], true) || $value === $selected));
                        $component->state($state);
                    })
                    ->columns(3)
                    ->visible(fn(CheckboxList $component) => !empty($component->getOptions())),

                TextInput::make('expression')
                    ->placeholder('* * * * *')
                    ->rules([new CronValidation()])
                    ->label('Expressão Cron')
                    ->required()->helperText(fn() => new HtmlString(" <a href='https://crontab-generator.org' target='_blank'>Clique aqui para gerar a expressão cron</a>")),
                TagsInput::make('environments')
                    ->placeholder(null)
                    ->label('Ambientes'),
                TextInput::make('log_filename')
                    ->label('Nome do Arquivo de Log')
                    ->helperText('Nome do arquivo de log para armazenar as saídas do comando.'),
                TextInput::make('webhook_before')
                    ->label('Webhook Antes'),
                TextInput::make('webhook_after')
                    ->label('Webhook Depois'),
                TextInput::make('email_output')
                    ->label('Email de Saída'),

                Section::make('History')
                    ->label('Histórico')
                    ->columns(2)
                    ->schema([
                        Toggle::make('log_success')
                            ->label('Log de Sucesso')->default(true),
                        Toggle::make('log_error')
                            ->label('Log de Erro')->default(true),
                        Toggle::make('limit_history_count')
                            ->label('Limitar Contagem de Histórico')
                            ->live(),
                        TextInput::make('max_history_count')
                            ->label('Máximo de Histórico')
                            ->numeric()
                            ->default(10)
                            ->visible(fn($get): bool => $get('limit_history_count')),
                    ]),
                Toggle::make('sendmail_success')
                    ->label('Enviar Email de Sucesso'),
                Toggle::make('sendmail_error')
                    ->label('Enviar Email de Erro'),
                Toggle::make('even_in_maintenance_mode')
                    ->label('Executar mesmo em Modo de Manutenção'),
                Toggle::make('without_overlapping')
                    ->label('Sem sobreposição'),
                Toggle::make('on_one_server')
                    ->label('Executar em um Servidor'),
                Toggle::make('run_in_background')
                    ->label('Executar em Fundo'),
            ])->columns(1);
    }
}
