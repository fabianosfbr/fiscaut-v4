<?php

namespace App\Filament\Condominio\Resources\ManutencaoHistoricos\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManutencaoHistoricosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('manutencao', function (Builder $query) {
                    $query->where('issuer_id', currentIssuer()->id);
                });
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('manutencao.titulo')
                    ->label('Manutenção')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('acao')
                    ->label('Ação')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'criada' => 'success',
                        'atualizada' => 'info',
                        'iniciada' => 'warning',
                        'concluida' => 'success',
                        'cancelada' => 'danger',
                        'reagendada' => 'warning',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status_anterior')
                    ->label('Status Anterior')
                    ->badge()
                    ->color('gray')
                    ->placeholder('N/A')
                    ->searchable(),

                TextColumn::make('status_novo')
                    ->label('Status Novo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'programada' => 'gray',
                        'em_andamento' => 'warning',
                        'concluida' => 'success',
                        'cancelada' => 'danger',
                        'atrasada' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('usuario.name')
                    ->label('Usuário')
                    ->searchable()
                    ->sortable()
                    ->limit(20),

                TextColumn::make('observacoes')
                    ->label('Observações')
                    ->placeholder('Sem observações')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (!$state || strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->weight('medium'),
            ])
            ->filters([
                SelectFilter::make('acao')
                    ->label('Ação')
                    ->options([
                        'criada' => 'Criada',
                        'atualizada' => 'Atualizada',
                        'iniciada' => 'Iniciada',
                        'concluida' => 'Concluída',
                        'cancelada' => 'Cancelada',
                        'reagendada' => 'Reagendada',
                    ])
                    ->native(false),

                SelectFilter::make('manutencao_id')
                    ->label('Manutenção')
                    ->relationship('manutencao', 'titulo')
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('usuario_id')
                    ->label('Usuário')
                    ->relationship('usuario', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Visualizar'),
            ])
            ->toolbarActions([
                // Sem ações de criação/edição - apenas leitura
            ])
            ->emptyStateHeading('Nenhum histórico encontrado')
            ->emptyStateDescription('O histórico será criado automaticamente conforme as ações forem realizadas.')
            ->striped()
            ->paginated([25, 50, 100]);
    }
}
