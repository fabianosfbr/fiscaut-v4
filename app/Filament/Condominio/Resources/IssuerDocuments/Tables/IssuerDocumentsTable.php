<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments\Tables;

use App\Enums\IssuerDocumentTypeEnum;
use App\Models\Issuer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IssuerDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('issuer.razao_social')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('document_type')
                    ->label('Tipo de Documento')
                    ->formatStateUsing(fn ($state) => IssuerDocumentTypeEnum::tryFrom($state)?->getLabel() ?? $state)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user_name')
                    ->label('Nome do Documento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('original_name')
                    ->label('Nome Original')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('format')
                    ->label('Formato')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('file_size')
                    ->label('Tamanho')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2) . ' KB' : '-')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Data de Upload')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('issuer_id')
                    ->label('Empresa')
                    ->options(function () {
                        return Issuer::where('tenant_id', currentIssuer()->tenant_id)
                            ->pluck('razao_social', 'id');
                    }),
                
                SelectFilter::make('document_type')
                    ->label('Tipo de Documento')
                    ->options(IssuerDocumentTypeEnum::getDocumentTypes()),

                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Visualizar')
                    ->icon('heroicon-o-eye')
                    ->action(function ($record) {
                        // Download logic will be implemented in the view action
                        return response()->download(storage_path('app/private/' . $record->file_path), $record->original_name);
                    }),

                TableEditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil'),

                DeleteAction::make()
                    ->label('Excluir')
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->whereHas('issuer', function ($query) {
                    $query->where('tenant_id', currentIssuer()->tenant_id);
                });
            });
    }
}
