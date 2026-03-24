<?php

namespace App\Filament\Condominio\Resources\IssuerControlRecorrencies\Tables;

use App\Enums\IssuerControlFrequencyEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class IssuerControlRecorrenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(function (Builder $query) {
                $query->where('issuer_id', currentIssuer()->id);
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('titulo_template')
                    ->label('Nome Ciclo de Recorrência')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('typeControl.nome')
                    ->label('Tipo de Controle')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('frequencia')
                    ->label('Frequência')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('intervalo')
                    ->label('Intervalo')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(function ($state, $record) {
                        return $state . ' ' . match ($record->frequencia) {
                            'diaria' => $state == 1 ? 'dia' : 'dias',
                            'semanal' => $state == 1 ? 'semana' : 'semanas',
                            'mensal' => $state == 1 ? 'mês' : 'meses',
                            'anual' => $state == 1 ? 'ano' : 'anos',
                            default => '',
                        };
                    }),

                TextColumn::make('data_inicio')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('data_fim')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->placeholder('Indefinido')
                    ->sortable(),

                TextColumn::make('proxima_geracao')
                    ->label('Próxima Geração')
                    ->date('d/m/Y')
                    ->placeholder('Não calculada')
                    ->sortable()
                    ->color(function ($record) {
                        if (!$record->proxima_geracao) return null;
                        if ($record->proxima_geracao <= now()) {
                            return 'warning';
                        }
                        return null;
                    }),

                IconColumn::make('ativo')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('gerar_dias_antecedencia')
                    ->label('Antecedência')
                    ->numeric()
                    ->suffix(' dias')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('dia_mes')
                    ->label('Dia do Mês')
                    ->numeric()
                    ->placeholder('N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('dia_semana')
                    ->label('Dia da Semana')
                    ->numeric()
                    ->placeholder('N/A')
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return 'N/A';
                        return match ($state) {
                            0 => 'Domingo',
                            1 => 'Segunda',
                            2 => 'Terça',
                            3 => 'Quarta',
                            4 => 'Quinta',
                            5 => 'Sexta',
                            6 => 'Sábado',
                            default => $state,
                        };
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mes')
                    ->label('Mês')
                    ->numeric()
                    ->placeholder('N/A')
                    ->formatStateUsing(function ($state) {
                        if ($state === null) return 'N/A';
                        return match ($state) {
                            1 => 'Janeiro',
                            2 => 'Fevereiro',
                            3 => 'Março',
                            4 => 'Abril',
                            5 => 'Maio',
                            6 => 'Junho',
                            7 => 'Julho',
                            8 => 'Agosto',
                            9 => 'Setembro',
                            10 => 'Outubro',
                            11 => 'Novembro',
                            12 => 'Dezembro',
                            default => $state,
                        };
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ultima_geracao')
                    ->label('Última Geração')
                    ->date('d/m/Y')
                    ->placeholder('Nunca executada')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ])
            ->filters([
                SelectFilter::make('frequencia')
                    ->label('Frequência')
                    ->options(IssuerControlFrequencyEnum::class)
                    ->native(false),

                SelectFilter::make('type_control_id')
                    ->label('Tipo de Controle')
                    ->relationship('typeControl', 'nome')
                    ->searchable()
                    ->preload()
                    ->native(false),

                TernaryFilter::make('ativo')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Ativos')
                    ->falseLabel('Inativos')
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
