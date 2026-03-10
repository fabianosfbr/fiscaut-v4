<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments;

use App\Filament\Condominio\Resources\IssuerDocuments\Pages\CreateIssuerDocument;
use App\Filament\Condominio\Resources\IssuerDocuments\Pages\EditIssuerDocument;
use App\Filament\Condominio\Resources\IssuerDocuments\Pages\ListIssuerDocuments;
use App\Filament\Condominio\Resources\IssuerDocuments\Schemas\IssuerDocumentForm;
use App\Filament\Condominio\Resources\IssuerDocuments\Tables\IssuerDocumentsTable;
use App\Models\IssuerDocument;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class IssuerDocumentResource extends Resource
{
    protected static ?string $model = IssuerDocument::class;

    protected static ?string $modelLabel = 'Documento';

    protected static ?string $pluralModelLabel = 'Documentos';

    protected static string|UnitEnum|null $navigationGroup = 'Documentos';

    public static function form(Schema $schema): Schema
    {
        return IssuerDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IssuerDocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIssuerDocuments::route('/'),
            // 'create' => CreateIssuerDocument::route('/create'),
            'edit' => EditIssuerDocument::route('/{record}/edit'),
        ];
    }
}
