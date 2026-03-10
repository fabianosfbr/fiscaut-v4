<?php

namespace App\Filament\Condominio\Resources\Issuers\RelationManagers;

use App\Enums\IssuerDocumentTypeEnum;
use App\Models\IssuerDocument;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IssuerDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_name')
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Tipo de Documento')
                    ->formatStateUsing(fn ($state) => IssuerDocumentTypeEnum::tryFrom($state)?->getLabel() ?? $state)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_name')
                    ->label('Nome do Documento')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('original_name')
                    ->label('Nome Original')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('format')
                    ->label('Formato')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('Tamanho')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2) . ' KB' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Upload')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Download')
                    ->icon('heroicon-o-download')
                    ->action(function ($record) {
                        return response()->download(storage_path('app/private/' . $record->file_path), $record->original_name);
                    }),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil'),

                Tables\Actions\DeleteAction::make()
                    ->label('Excluir')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
