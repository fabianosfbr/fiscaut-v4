<?php

namespace App\Filament\Condominio\Resources\IssuerControlRecorrencies;

use App\Filament\Condominio\Resources\IssuerControlRecorrencies\Pages\CreateIssuerControlRecorrency;
use App\Filament\Condominio\Resources\IssuerControlRecorrencies\Pages\EditIssuerControlRecorrency;
use App\Filament\Condominio\Resources\IssuerControlRecorrencies\Pages\ListIssuerControlRecorrencies;
use App\Filament\Condominio\Resources\IssuerControlRecorrencies\Schemas\IssuerControlRecorrencyForm;
use App\Filament\Condominio\Resources\IssuerControlRecorrencies\Tables\IssuerControlRecorrenciesTable;
use App\Models\IssuerControlRecorrency;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IssuerControlRecorrencyResource extends Resource
{
    protected static ?string $model = IssuerControlRecorrency::class;

    protected static ?string $navigationLabel = 'Recorrências';

    protected static ?string $modelLabel = 'Recorrência';

    protected static ?string $pluralModelLabel = 'Recorrências';

    protected static ?string $slug = 'recorrencias';

    protected static string|UnitEnum|null $navigationGroup = 'Controles';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return IssuerControlRecorrencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IssuerControlRecorrenciesTable::configure($table);
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
            'index' => ListIssuerControlRecorrencies::route('/'),
            'create' => CreateIssuerControlRecorrency::route('/create'),
            'edit' => EditIssuerControlRecorrency::route('/{record}/edit'),
        ];
    }
}
