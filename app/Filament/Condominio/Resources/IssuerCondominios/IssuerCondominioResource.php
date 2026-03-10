<?php

namespace App\Filament\Condominio\Resources\IssuerCondominios;

use App\Filament\Condominio\Resources\IssuerCondominios\Pages\CreateIssuerCondominio;
use App\Filament\Condominio\Resources\IssuerCondominios\Pages\EditIssuerCondominio;
use App\Filament\Condominio\Resources\IssuerCondominios\Pages\ListIssuerCondominios;
use App\Filament\Resources\Issuers\Schemas\IssuerForm;
use App\Filament\Resources\Issuers\Tables\IssuersTable;
use App\Models\Issuer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IssuerCondominioResource extends Resource
{
    protected static ?string $model = Issuer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $slug = 'issuers';

    protected static bool $shouldRegisterNavigation = false;

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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIssuerCondominios::route('/'),
            'create' => CreateIssuerCondominio::route('/create'),
            'edit' => EditIssuerCondominio::route('/{record}/edit'),
        ];
    }
}
