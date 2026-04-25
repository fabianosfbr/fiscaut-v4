<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Models\UserPanelPermission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->searchPlaceholder('Nome, Email, Grupos')
            ->searchDebounce('750ms')
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
                    ->searchable()
                    ->badge(),

                TextColumn::make('panels')
                    ->label('Painéis')
                    ->getStateUsing(function (User $record) {
                        return UserPanelPermission::where('user_id', $record->id)
                            ->pluck('panel')
                            ->toArray();
                    })
                    ->badge(),
                TextColumn::make('tenant.name')
                    ->label('Empresa')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tenant')
                    ->label('Empresa')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->query(fn ($query) => $query->distinct()),

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
