<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use App\Models\ScheduleHistory;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ViewSchedule extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithRecord;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static string $resource = ScheduleResource::class;

    protected string $view = 'filament.resources.schedules.pages.view-schedule';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return 'Mostrar histórico de execução';
    }

    protected function getActions(): array
    {
        return [
            Action::make('clearHistory')
                ->label('Limpar histórico')
                ->requiresConfirmation()
                ->modalHeading('Limpar histórico')
                ->modalDescription('Tem certeza que deseja excluir todos os registros de execução do agendamento?')
                ->modalSubmitActionLabel('Sim, excluir tudo')
                ->modalIcon('heroicon-m-trash')
                ->visible($this->record->histories->count())
                ->color('danger')
                ->icon('heroicon-m-trash')
                ->action(function () {
                    $this->record->histories()->delete();
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return ScheduleHistory::where('schedule_id', $this->record->id)->orderBy('created_at', 'desc');
            })
            ->columns([
                Split::make([
                    TextColumn::make('command')
                        ->label('Comando'),
                    TextColumn::make('created_at')
                        ->label('Criado em')
                        ->dateTime('d/m/Y H:i:s'),

                    TextColumn::make('output')
                        ->label('Saídas')
                        ->formatStateUsing(function ($state) {
                            if (count(explode('<br />', nl2br($state))) - 1 == 0) {
                                return 'Nenhuma saída';
                            }

                            if (count(explode('<br />', nl2br($state))) - 1 == 1) {
                                return '1 linha de saída';
                            }

                            return (count(explode('<br />', nl2br($state))) - 1).' linhas de saída';
                        }),

                ]),
                Panel::make([
                    TextColumn::make('output')->extraAttributes(['class' => '!max-w-max'], true)
                        ->formatStateUsing(function ($state) {
                            return new HtmlString(nl2br($state));
                        }),
                ])->collapsible()
                    ->collapsed(),
            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                // ...
            ])
            ->toolbarActions([
                // ...
            ]);
    }
}
