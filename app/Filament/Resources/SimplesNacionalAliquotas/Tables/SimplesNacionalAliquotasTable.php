<?php

namespace App\Filament\Resources\SimplesNacionalAliquotas\Tables;

use App\Models\SimplesNacionalAliquota;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SimplesNacionalAliquotasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(fn (Builder $query) => $query->with('anexoModel'))
            ->defaultSort('anexo', 'asc')
            ->columns([
                TextColumn::make('anexo')
                    ->label('Anexo')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state, SimplesNacionalAliquota $record): string {
                        $anexo = $record->anexoModel;

                        if (! $anexo) {
                            return (string) $state;
                        }

                        return "{$anexo->anexo} - {$anexo->descricao}";
                    }),
                TextColumn::make('faixa_inicial')
                    ->label('Faixa inicial')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'R$ '.number_format((float) $state, 2, ',', '.')),
                TextColumn::make('faixa_final')
                    ->label('Faixa final')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'R$ '.number_format((float) $state, 2, ',', '.')),
                TextColumn::make('aliquota')
                    ->label('Alíquota')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 4, ',', '.').' %'),
                TextColumn::make('valor_deduzir')
                    ->label('Valor a deduzir')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'R$ '.number_format((float) $state, 2, ',', '.')),
                TextColumn::make('irpj_percentual')
                    ->label('IRPJ')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2, ',', '.').' %' : '-'),
                TextColumn::make('csll_percentual')
                    ->label('CSLL')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2, ',', '.').' %' : '-'),
                TextColumn::make('cofins_percentual')
                    ->label('COFINS')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2, ',', '.').' %' : '-'),
                TextColumn::make('pis_percentual')
                    ->label('PIS')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2, ',', '.').' %' : '-'),
                TextColumn::make('cpp_percentual')
                    ->label('CPP')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2, ',', '.').' %' : '-'),
                TextColumn::make('ipi_percentual')
                    ->label('IPI')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2, ',', '.').' %' : '-'),
                TextColumn::make('icms_percentual')
                    ->label('ICMS')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2, ',', '.').' %' : '-'),
                TextColumn::make('iss_percentual')
                    ->label('ISS')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => filled($state) ? number_format((float) $state, 2, ',', '.').' %' : '-'),
            ])
            ->filters([
                SelectFilter::make('anexo')
                    ->label('Anexo')
                    ->options(fn () => SimplesNacionalAliquota::query()
                        ->select('anexo')
                        ->distinct()
                        ->orderBy('anexo')
                        ->pluck('anexo', 'anexo')
                        ->all()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
