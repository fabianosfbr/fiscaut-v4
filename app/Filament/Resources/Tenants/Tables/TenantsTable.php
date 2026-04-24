<?php

namespace App\Filament\Resources\Tenants\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                TextColumn::make('issuers_count')
                    ->label('Empresas')
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('Usuários')
                    ->sortable(),
                IconColumn::make('active')
                    ->boolean()
                    ->label('Status'),
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
