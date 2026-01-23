<?php

namespace App\Filament\Resources\Acumuladores;

use App\Filament\Resources\Acumuladores\Pages\CreateAcumuladores;
use App\Filament\Resources\Acumuladores\Pages\EditAcumuladores;
use App\Filament\Resources\Acumuladores\Pages\ListAcumuladores;
use App\Filament\Resources\Acumuladores\Schemas\AcumuladoresForm;
use App\Filament\Resources\Acumuladores\Tables\AcumuladoresTable;
use App\Models\Acumulador;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class AcumuladoresResource extends Resource
{
    protected static ?string $model = Acumulador::class;

    protected static ?string $modelLabel = 'Acumulador';

    protected static ?string $pluralModelLabel = 'Acumuladores';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    public static function form(Schema $schema): Schema
    {
        return AcumuladoresForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AcumuladoresTable::configure($table);
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
            'index' => ListAcumuladores::route('/'),
            'create' => CreateAcumuladores::route('/create'),
            'edit' => EditAcumuladores::route('/{record}/edit'),
        ];
    }
}
