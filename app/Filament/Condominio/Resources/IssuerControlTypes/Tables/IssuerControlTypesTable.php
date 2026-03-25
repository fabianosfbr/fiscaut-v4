<?php

namespace App\Filament\Condominio\Resources\IssuerControlTypes\Tables;

use App\Enums\IssuerControlPriorityEnum;
use App\Enums\IssuerControlTypeEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class IssuerControlTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(function (Builder $query) {
                $query->where('issuer_id', currentIssuer()->id);
            })
            ->defaultSort('nome', 'asc')
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome do Tipo')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('categoria')
                    ->label('Categoria')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('alerta_dias_antecedencia')
                    ->label('Alerta')
                    ->numeric()
                    ->suffix(function ($state) {
                        if ($state == 1) {
                            return ' dia';
                        }
                        if ($state == 0) {
                            return null;
                        }

                        return ' dias';
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state == 0) {
                            return 'N/A';
                        }

                        return $state;
                    })
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('prioridade')
                    ->label('Prioridade')
                    ->badge()
                    ->sortable(),

                TextColumn::make('responsavel_padrao')
                    ->label('Responsável')
                    ->placeholder('Não definido')
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 20) {
                            return null;
                        }

                        return $state;
                    }),

                IconColumn::make('ativo')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('categoria')
                    ->label('Categoria')
                    ->options(IssuerControlTypeEnum::class)
                    ->native(false),

                SelectFilter::make('prioridade')
                    ->label('Prioridade')
                    ->options(IssuerControlPriorityEnum::class)
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
            ])
            ->emptyStateHeading('Nenhum tipo de controle encontrado')
            ->emptyStateDescription('Comece criando seu primeiro tipo de controle.')
            ->striped();
    }
}
