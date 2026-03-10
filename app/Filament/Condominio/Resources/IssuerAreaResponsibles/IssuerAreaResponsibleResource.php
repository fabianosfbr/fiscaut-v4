<?php

namespace App\Filament\Condominio\Resources\IssuerAreaResponsibles;

use App\Filament\Condominio\Resources\IssuerAreaResponsibles\Pages\CreateIssuerAreaResponsible;
use App\Filament\Condominio\Resources\IssuerAreaResponsibles\Pages\EditIssuerAreaResponsible;
use App\Filament\Condominio\Resources\IssuerAreaResponsibles\Pages\ListIssuerAreaResponsibles;
use App\Filament\Condominio\Resources\IssuerAreaResponsibles\Schemas\IssuerAreaResponsibleForm;
use App\Filament\Condominio\Resources\IssuerAreaResponsibles\Tables\IssuerAreaResponsiblesTable;
use App\Models\IssuerAreaResponsible;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class IssuerAreaResponsibleResource extends Resource
{
    protected static ?string $model = IssuerAreaResponsible::class;

    protected static ?string $modelLabel = 'Responsável por Área';

    protected static ?string $pluralModelLabel = 'Responsáveis por Área';

    protected static string|UnitEnum|null $navigationGroup = 'Responsáveis';

    public static function form(Schema $schema): Schema
    {
        return IssuerAreaResponsibleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IssuerAreaResponsiblesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('tenant_id', currentIssuer()->tenant_id);
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
            'index' => ListIssuerAreaResponsibles::route('/'),
            'create' => CreateIssuerAreaResponsible::route('/create'),
            'edit' => EditIssuerAreaResponsible::route('/{record}/edit'),
        ];
    }
}
