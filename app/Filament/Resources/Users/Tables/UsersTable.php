<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Grupos')
                    ->badge(),
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Impersonate::make()
                    ->hiddenLabel()
                    ->visible(function () {
                        return Auth::user()->hasRole('super-admin', 'admin', 'contabilidade');
                    })
                    ->tooltip('Entrar como usuário'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
