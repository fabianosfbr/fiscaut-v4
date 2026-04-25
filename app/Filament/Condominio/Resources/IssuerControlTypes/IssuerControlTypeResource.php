<?php

namespace App\Filament\Condominio\Resources\IssuerControlTypes;

use App\Filament\Condominio\Resources\IssuerControlTypes\Pages\CreateIssuerControlType;
use App\Filament\Condominio\Resources\IssuerControlTypes\Pages\EditIssuerControlType;
use App\Filament\Condominio\Resources\IssuerControlTypes\Pages\ListIssuerControlTypes;
use App\Filament\Condominio\Resources\IssuerControlTypes\Schemas\IssuerControlTypeForm;
use App\Filament\Condominio\Resources\IssuerControlTypes\Tables\IssuerControlTypesTable;
use App\Models\IssuerControlType;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class IssuerControlTypeResource extends Resource
{
    protected static ?string $model = IssuerControlType::class;

    protected static ?string $navigationLabel = 'Tipos de Controle';

    protected static ?string $modelLabel = 'Tipo de Controle';

    protected static ?string $pluralModelLabel = 'Tipos de Controle';

    protected static string|UnitEnum|null $navigationGroup = 'Controles';

    protected static ?string $slug = 'tipos-de-controle';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return IssuerControlTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IssuerControlTypesTable::configure($table);
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
            'index' => ListIssuerControlTypes::route('/'),
            'create' => CreateIssuerControlType::route('/create'),
            'edit' => EditIssuerControlType::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('super-admin');
    }
}
