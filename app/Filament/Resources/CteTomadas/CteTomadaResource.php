<?php

namespace App\Filament\Resources\CteTomadas;



use UnitEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\ConhecimentoTransporteEletronico;
use App\Filament\Resources\CteTomadas\Pages\ViewCteTomada;
use App\Filament\Resources\CteTomadas\Pages\ListCteTomadas;
use App\Filament\Resources\CteTomadas\Schemas\CteTomadaForm;
use App\Filament\Resources\CteTomadas\Tables\CteTomadasTable;
use App\Filament\Resources\CteTomadas\Schemas\CteTomadaInfolist;

class CteTomadaResource extends Resource
{
    protected static ?string $model = ConhecimentoTransporteEletronico::class;

    protected static ?string $modelLabel = 'CTe Tomada';

    protected static ?string $pluralLabel = 'CTes Tomadas';

    protected static ?string $navigationLabel = 'CTe Tomadas';

    protected static ?string $slug = 'ctes-tomadas';

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
