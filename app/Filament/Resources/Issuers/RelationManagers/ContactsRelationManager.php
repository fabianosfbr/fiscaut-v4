<?php

namespace App\Filament\Resources\Issuers\RelationManagers;

use App\Filament\Condominio\Resources\IssuerContacts\Schemas\IssuerContactForm;
use App\Filament\Condominio\Resources\IssuerContacts\Tables\IssuerContactsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'Contatos';

    protected static ?string $modelLabel = 'Contato';

    protected static ?string $pluralModelLabel = 'Contatos';

    public function form(Schema $schema): Schema
    {
        return IssuerContactForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return IssuerContactsTable::configure($table);
    }
}
