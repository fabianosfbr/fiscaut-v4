<?php

namespace App\Filament\Resources\Schedules\Tables;

use Carbon\Carbon;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use App\Enums\ScheduleStatusEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;

class SchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('command')->getStateUsing(function ($record) {
                    if ($record->command == 'custom')
                        return $record->command_custom;
                    return $record->command;
                })
                    ->label('Comando')
                    ->tooltip(fn($record) => $record->description)
                    ->searchable()->sortable(),
                TextColumn::make('params')->label('Argumentos')
                    ->getStateUsing(function ($record, $component) {
                        $tags = $record->params;
                        if (is_array($tags)) {
                            return collect($tags)->filter(fn($value) => !empty($value['value']))->map(fn($value, $key) => ($value['name'] ?? $key) . '=' . $value['value'])->toArray();
                        }

                        if (!($separator = $component->getSeparator())) {
                            return [];
                        }

                        $tags = explode($separator, $tags);

                        if (count($tags) === 1 && blank($tags[0])) {
                            $tags = [];
                        }
                        return $tags;
                    })->separator(",")->searchable()->sortable(),
                TextColumn::make('options')->label('Opções')->searchable()->sortable()->getStateUsing(fn($record) => $record->getOptions())->separator(',')->badge(),
                TextColumn::make('expression')->label('Expressão')->searchable()->sortable(),
                TextColumn::make('environments')->label('Ambientes')->separator(',')->searchable()->sortable()->badge()->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')->label('Criado em')->searchable()->sortable()
                    ->dateTime('d/m/Y H:i:s')
                    ->wrap()
                    ->toggleable(true, isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')->getStateUsing(fn($record) => $record->created_at == $record->updated_at ? 'Nunca' : $record->updated_at)
                    ->wrap()->formatStateUsing(static function ($column, $state) use ($table): ?string {
                        $format = $table->getDefaultDateTimeDisplayFormat();
                        if (blank($state) || $state == 'Nunca') {
                            return $state;
                        }
                        return Carbon::parse($state)
                            ->setTimezone($timezone ?? $column->getTimezone())
                            ->translatedFormat($format);
                    })->label('Atualizado em')->searchable()->sortable()->toggleable(true, isToggledHiddenByDefault: false),

            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->hidden(fn($record) => $record->trashed())
                        ->tooltip('Editar agendamento')
                        ->button()
                        ->hiddenLabel(),
                    ViewAction::make()
                        ->button()
                        ->hiddenLabel()
                        ->color('info')
                        ->tooltip('Ver histórico')
                        ->visible(fn($record) => $record->histories()->count()),
                    ForceDeleteAction::make(),
                    Action::make('toggle')
                        ->button()
                        ->icon(fn($record) => $record->status == ScheduleStatusEnum::Active ? 'heroicon-o-pause' : 'heroicon-o-play')
                        ->color(fn($record) => $record->status == ScheduleStatusEnum::Active ? 'danger' : 'success')
                        ->tooltip(fn($record) => $record->status == ScheduleStatusEnum::Active ? 'Pausar' : 'Retomar')
                        ->hiddenLabel()
                        ->action(function ($record) {
                            $record->status = $record->status == ScheduleStatusEnum::Active ? ScheduleStatusEnum::Inactive : ScheduleStatusEnum::Active;
                            $record->save();
                        }),
                    DeleteAction::make()
                        ->button()
                        ->hiddenLabel(),
                ])
            ])
            ->toolbarActions([]);
    }
}
