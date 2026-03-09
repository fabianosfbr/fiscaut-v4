<?php

namespace App\Filament\Condominio\Resources\IssuerAreaResponsibles\Tables;

use App\Enums\AreaAtendimentoEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class IssuerAreaResponsiblesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('issuer_id', currentIssuer()->id);
            })
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('area')
                    ->label('Área de Atendimento')
                    ->badge()
                    ->formatStateUsing(fn ($state): ?string => AreaAtendimentoEnum::tryFrom($state)?->getLabel() ?? $state)
                    ->sortable(),
            ])
            ->filters([
                //
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
