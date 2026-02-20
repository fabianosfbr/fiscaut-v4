<?php

namespace App\Filament\Resources\ParametroSuperLogicas;

use App\Filament\Resources\ParametroSuperLogicas\Pages\CreateParametroSuperLogica;
use App\Filament\Resources\ParametroSuperLogicas\Pages\EditParametroSuperLogica;
use App\Filament\Resources\ParametroSuperLogicas\Pages\ListParametroSuperLogicas;
use App\Filament\Resources\ParametroSuperLogicas\Schemas\ParametroSuperLogicaForm;
use App\Filament\Resources\ParametroSuperLogicas\Tables\ParametroSuperLogicasTable;
use App\Models\ParametroSuperLogica;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ParametroSuperLogicaResource extends Resource
{
    protected static ?string $model = ParametroSuperLogica::class;

    protected static ?string $navigationLabel = 'Parâmetros Super Lógica';

    protected static string|UnitEnum|null $navigationGroup = 'Contabilidade';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return ParametroSuperLogicaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParametroSuperLogicasTable::configure($table);
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
            'index' => ListParametroSuperLogicas::route('/'),
            'create' => CreateParametroSuperLogica::route('/create'),
            'edit' => EditParametroSuperLogica::route('/{record}/edit'),
        ];
    }
}
