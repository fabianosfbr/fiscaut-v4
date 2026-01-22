<?php

namespace App\Filament\Resources\Issuers\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Usuários';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Usuários')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
            ])->headerActions([
                AttachAction::make()
                    ->recordTitleAttribute('name')
                    ->modalHeading(' Vincular Usuário')
                    ->recordSelectOptionsQuery(
                        fn ($query) => $query
                            ->where('tenant_id', $this->getOwnerRecord()->tenant_id)
                            ->whereDoesntHave('issuers', fn ($q) => $q->where('issuers.id', $this->getOwnerRecord()->id))
                    ),
            ])->recordActions([
                DetachAction::make(),
            ]);
    }
}
