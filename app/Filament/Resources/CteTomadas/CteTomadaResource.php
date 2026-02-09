<?php

namespace App\Filament\Resources\CteTomadas;

use App\Filament\Resources\CteTomadas\Pages\ListCteTomadas;
use App\Filament\Resources\CteTomadas\Pages\ViewCteTomada;
use App\Filament\Resources\CteTomadas\Schemas\CteTomadaForm;
use App\Filament\Resources\CteTomadas\Schemas\CteTomadaInfolist;
use App\Filament\Resources\CteTomadas\Tables\CteTomadasTable;
use App\Models\ConhecimentoTransporteEletronico;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CteTomadaResource extends Resource
{
    protected static ?string $model = ConhecimentoTransporteEletronico::class;

    protected static ?string $modelLabel = 'CTe Tomada';

    protected static ?string $pluralLabel = 'CTes Tomadas';

    protected static ?string $navigationLabel = 'CTe Tomada';

    protected static ?string $slug = 'ctes-tomada';

    protected static string|UnitEnum|null $navigationGroup = 'CTe';

    public static function form(Schema $schema): Schema
    {
        return CteTomadaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CteTomadaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CteTomadasTable::configure($table);
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
            'index' => ListCteTomadas::route('/'),
            'view' => ViewCteTomada::route('/{record}'),

        ];
    }
}
