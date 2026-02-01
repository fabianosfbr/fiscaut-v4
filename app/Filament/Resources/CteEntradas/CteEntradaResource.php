<?php

namespace App\Filament\Resources\CteEntradas;

use App\Filament\Resources\CteEntradas\Pages\ListCteEntradas;
use App\Filament\Resources\CteEntradas\Pages\ViewCteEntrada;
use App\Filament\Resources\CteEntradas\Schemas\CteEntradaForm;
use App\Filament\Resources\CteEntradas\Schemas\CteEntradaInfolist;
use App\Filament\Resources\CteEntradas\Tables\CteEntradasTable;
use App\Models\ConhecimentoTransporteEletronico;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CteEntradaResource extends Resource
{
    protected static ?string $model = ConhecimentoTransporteEletronico::class;

    protected static ?string $modelLabel = 'CTe Entrada';

    protected static ?string $pluralLabel = 'CTes Entradas';

    protected static ?string $navigationLabel = 'CTe Entrada';

    protected static ?string $slug = 'ctes-entrada';

    protected static string|UnitEnum|null $navigationGroup = 'CTe';

    public static function form(Schema $schema): Schema
    {
        return CteEntradaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CteEntradaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CteEntradasTable::configure($table);
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
            'index' => ListCteEntradas::route('/'),
            'view' => ViewCteEntrada::route('/{record}'),
        ];
    }
}
