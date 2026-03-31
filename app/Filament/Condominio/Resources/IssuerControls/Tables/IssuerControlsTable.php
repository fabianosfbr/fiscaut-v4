<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Tables;

use App\Enums\IssuerControlPriorityEnum;
use App\Enums\IssuerControlStatusEnum;
use App\Enums\IssuerControlTypeEnum;
use App\Filament\Infolists\Components\IssuerControlLogEntry;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class IssuerControlsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(function (Builder $query) {
                $query->where('issuer_id', currentIssuer()->id);
            })
            ->defaultSort('data_programada', 'desc')
            ->columns([
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('typeControl.nome')
                    ->label('Tipo de Controle')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('prioridade')
                    ->label('Prioridade')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('data_programada')
                    ->label('Data Programada')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->data_programada < now() && $record->status !== 'concluida') {
                            return 'danger';
                        }

                        return null;
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('usuario_responsavel')
                    ->label('Responsável')
                    ->placeholder('Não definido')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('fornecedor.nome')
                    ->label('Fornecedor')
                    ->placeholder('Não definido')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('local')
                    ->label('Local')
                    ->placeholder('Não definido')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('equipamento')
                    ->label('Equipamento')
                    ->placeholder('Não definido')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('custo_estimado')
                    ->label('Custo Est.')
                    ->money('BRL')
                    ->placeholder('R$ 0,00')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('custo_real')
                    ->label('Custo Real')
                    ->money('BRL')
                    ->placeholder('R$ 0,00')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('data_execucao')
                    ->label('Executada em')
                    ->dateTime('d/m/Y')
                    ->placeholder('Não executada')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('data_conclusao')
                    ->label('Concluída em')
                    ->dateTime('d/m/Y')
                    ->placeholder('Não concluída')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('created_at')
                    ->label('Criada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Atualizada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Excluída em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(IssuerControlStatusEnum::class)
                    ->native(false),

                SelectFilter::make('prioridade')
                    ->label('Prioridade')
                    ->options(IssuerControlPriorityEnum::class)
                    ->native(false),

                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(IssuerControlTypeEnum::class)
                    ->native(false),

                SelectFilter::make('type_control_id')
                    ->label('Tipo de Controle')
                    ->relationship('typeControl', 'nome')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Filter::make('atrasadas')
                    ->label('Atrasadas')
                    ->query(fn (Builder $query): Builder => $query->where('data_programada', '<', now())->where('status', '!=', 'concluida'))
                    ->toggle(),

                Filter::make('proximas')
                    ->label('Próximas (7 dias)')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('data_programada', [now(), now()->addDays(7)]))
                    ->toggle(),

            ])
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('log')
                        ->label('Log')
                        ->icon('heroicon-o-document-text')
                        ->modalHeading('Log de Alterações')
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->modalWidth(Width::Large)
                        ->schema([
                            IssuerControlLogEntry::make('log')
                                ->hiddenLabel(),
                        ])
                        ->disabled(fn ($record) => $record->logs()->count() === 0),
                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ])
            ->emptyStateHeading('Nenhum controle encontrado')
            ->emptyStateDescription('Comece criando seu primeiro controle programado.')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
