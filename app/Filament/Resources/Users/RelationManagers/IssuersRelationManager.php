<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Users\UserResource;
use App\Models\Issuer;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IssuersRelationManager extends RelationManager
{
    protected static string $relationship = 'issuers';

    protected static ?string $relatedResource = UserResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Empresas com permissão de acesso')
            ->columns([
                TextColumn::make('razao_social')
                    ->label('Razão Social')
                    ->searchable(),
                TextColumn::make('cnpj')
                    ->label('CNPJ')
                    ->searchable(),
            ])->headerActions([
                AttachAction::make()
                    ->recordTitleAttribute('razao_social')
                    ->modalHeading(' Vincular Empresa')
                    ->recordSelectOptionsQuery(fn ($query) => $query
                        ->where('tenant_id', $this->getOwnerRecord()->tenant_id)
                        ->whereDoesntHave('users', fn ($q) => $q->where('users.id', $this->getOwnerRecord()->id))
                    )
                    ->after(function (Issuer $record, AttachAction $action): void {
                        $user = $action->getTable()->getRelationship()->getParent();
                        $user->issuer_id = $record->id;
                        $user->save();
                    }),
            ])->recordActions([
                DetachAction::make(),
            ]);
    }
}
