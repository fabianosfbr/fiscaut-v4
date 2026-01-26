<?php

namespace App\Filament\Resources\SimplesNacionalAnexos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SimplesNacionalAnexosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('anexo')
                    ->label('Anexo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'I' => 'success',
                        'II' => 'info',
                        'III' => 'warning',
                        'IV' => 'danger',
                        'V' => 'gray',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }

                        return $state;
                    })
                    ->searchable()
                    ->wrap(),

                IconColumn::make('ativo')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('aliquotas_count')
                    ->label('Alíquotas')
                    ->counts('aliquotas')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('cnaes_count')
                    ->label('CNAEs')
                    ->counts('cnaes')
                    ->badge()
                    ->color('warning')
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
                SelectFilter::make('anexo')
                    ->label('Anexo')
                    ->options([
                        'I' => 'Anexo I - Comércio',
                        'II' => 'Anexo II - Indústria',
                        'III' => 'Anexo III - Serviços e Locação de Bens Móveis',
                        'IV' => 'Anexo IV - Serviços (sujeito à partilha do ICMS)',
                        'V' => 'Anexo V - Serviços (sujeito à partilha do ISS)',
                    ])
                    ->multiple(),

                TernaryFilter::make('ativo')
                    ->label('Status')
                    ->placeholder('Todos os status')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos'),

                Filter::make('com_aliquotas')
                    ->label('Com alíquotas cadastradas')
                    ->query(fn (Builder $query): Builder => $query->has('aliquotas')),

                Filter::make('com_cnaes')
                    ->label('Com CNAEs cadastrados')
                    ->query(fn (Builder $query): Builder => $query->has('cnaes')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->label('Excluir')
                    ->requiresConfirmation()
                    ->modalHeading('Excluir Anexo')
                    ->modalDescription('Tem certeza que deseja excluir o anexo selecionado? Esta ação não pode ser desfeita.')
                    ->modalSubmitActionLabel('Sim, excluir'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Excluir selecionados')
                        ->requiresConfirmation()
                        ->modalHeading('Excluir Anexos')
                        ->modalDescription('Tem certeza que deseja excluir o(s) anexo(s) selecionado(s)? Esta ação não pode ser desfeita.')
                        ->modalSubmitActionLabel('Sim, excluir'),
                ]),
            ]);
    }
}
