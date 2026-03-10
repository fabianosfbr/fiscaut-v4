<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments;

use App\Filament\Condominio\Resources\IssuerDocuments\Pages\CreateIssuerDocument;
use App\Filament\Condominio\Resources\IssuerDocuments\Pages\EditIssuerDocument;
use App\Filament\Condominio\Resources\IssuerDocuments\Pages\ListIssuerDocuments;
use App\Filament\Condominio\Resources\IssuerDocuments\Schemas\IssuerDocumentForm;
use App\Filament\Condominio\Resources\IssuerDocuments\Tables\IssuerDocumentsTable;
use App\Models\IssuerDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class IssuerDocumentResource extends Resource
{
    protected static ?string $model = IssuerDocument::class;

    protected static ?string $modelLabel = 'Documento da Empresa';

    protected static ?string $pluralModelLabel = 'Documentos da Empresa';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 10;

   
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
            'create' => CreateIssuerDocument::route('/create'),
            'edit' => EditIssuerDocument::route('/{record}/edit'),
        ];
    }


}
