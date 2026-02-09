<?php

namespace App\Filament\Resources\ParametroGerals;

use App\Filament\Resources\ParametroGerals\Pages\CreateParametroGeral;
use App\Filament\Resources\ParametroGerals\Pages\EditParametroGeral;
use App\Filament\Resources\ParametroGerals\Pages\ListParametroGerals;
use App\Filament\Resources\ParametroGerals\Schemas\ParametroGeralForm;
use App\Filament\Resources\ParametroGerals\Tables\ParametroGeralsTable;
use App\Models\ParametroGeral;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ParametroGeralResource extends Resource
{
    protected static ?string $model = ParametroGeral::class;

      protected static ?string $navigationLabel = 'Parâmetros Gerais';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    public static function form(Schema $schema): Schema
    {
        return ParametroGeralForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParametroGeralsTable::configure($table);
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
            'index' => ListParametroGerals::route('/'),
            'create' => CreateParametroGeral::route('/create'),
            'edit' => EditParametroGeral::route('/{record}/edit'),
        ];
    }
}
