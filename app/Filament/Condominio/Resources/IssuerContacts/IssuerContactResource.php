<?php

namespace App\Filament\Condominio\Resources\IssuerContacts;

use App\Filament\Condominio\Resources\IssuerContacts\Pages\CreateIssuerContact;
use App\Filament\Condominio\Resources\IssuerContacts\Pages\EditIssuerContact;
use App\Filament\Condominio\Resources\IssuerContacts\Pages\ListIssuerContacts;
use App\Filament\Condominio\Resources\IssuerContacts\Schemas\IssuerContactForm;
use App\Filament\Condominio\Resources\IssuerContacts\Tables\IssuerContactsTable;
use App\Models\IssuerContact;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class IssuerContactResource extends Resource
{
    protected static ?string $model = IssuerContact::class;

     protected static ?string $modelLabel = 'Contato';

    protected static ?string $pluralModelLabel = 'Contatos';

    protected static string|UnitEnum|null $navigationGroup = 'Contatos';

    public static function form(Schema $schema): Schema
    {
        return IssuerContactForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IssuerContactsTable::configure($table);
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
            'index' => ListIssuerContacts::route('/'),
            'create' => CreateIssuerContact::route('/create'),
            'edit' => EditIssuerContact::route('/{record}/edit'),
        ];
    }
}
