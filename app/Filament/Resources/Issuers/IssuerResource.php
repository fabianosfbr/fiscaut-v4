<?php

namespace App\Filament\Resources\Issuers;

use UnitEnum;
use BackedEnum;
use App\Models\Issuer;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\Issuers\Pages\EditIssuer;
use App\Filament\Resources\Issuers\Pages\ListIssuers;
use App\Filament\Resources\Issuers\Pages\CreateIssuer;
use App\Filament\Resources\Issuers\Schemas\IssuerForm;
use App\Filament\Resources\Issuers\Tables\IssuersTable;
use App\Filament\Resources\Issuers\RelationManagers\UsersRelationManager;

class IssuerResource extends Resource
{
    protected static ?string $model = Issuer::class;

    protected static ?string $modelLabel = 'Empresa';

    protected static ?string $pluralModelLabel = 'Empresas';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    public static function form(Schema $schema): Schema
    {
        return IssuerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IssuersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIssuers::route('/'),
            'create' => CreateIssuer::route('/create'),
            'edit' => EditIssuer::route('/{record}/edit'),
        ];
    }
}
