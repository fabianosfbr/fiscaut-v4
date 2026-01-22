<?php

namespace App\Filament\Resources\Tenants\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class TenantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()
                ->label('Código'),
                TextColumn::make('name')
                ->searchable()
                ->label('Nome'),
                IconColumn::make('active')
                ->boolean()
                ->label('Status'),
                TextColumn::make('owner.name')->label('Proprietário'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
