<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments\Tables;

use App\Enums\IssuerDocumentTypeEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class IssuerDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('issuer_id', currentIssuer()->id);
            })
            ->recordUrl(null)
            ->columns([
                TextColumn::make('document_type')
                    ->label('Tipo de Documento')
                    ->formatStateUsing(fn ($state) => IssuerDocumentTypeEnum::tryFrom($state)?->getLabel() ?? $state)
                    ->searchable()
                    ->badge()
                    ->sortable(),

                TextColumn::make('user_name')
                    ->label('Nome do Documento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('original_name')
                    ->label('Nome Original')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('file_size')
                    ->label('Tamanho')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2).' KB' : '-')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Data de Envio')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                DeleteAction::make()
                    ->before(function ($record) {
                        if ($record->file_path && Storage::disk('local')->exists($record->file_path)) {
                            Storage::disk('local')->delete($record->file_path);
                        }
                    }),

            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
