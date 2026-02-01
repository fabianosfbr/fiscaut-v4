<?php

namespace App\Filament\Resources\CteSaidas;

use App\Filament\Resources\CteSaidas\Pages\CreateCteSaida;
use App\Filament\Resources\CteSaidas\Pages\EditCteSaida;
use App\Filament\Resources\CteSaidas\Pages\ListCteSaidas;
use App\Filament\Resources\CteSaidas\Pages\ViewCteSaida;
use App\Filament\Resources\CteSaidas\Schemas\CteSaidaForm;
use App\Filament\Resources\CteSaidas\Schemas\CteSaidaInfolist;
use App\Filament\Resources\CteSaidas\Tables\CteSaidasTable;
use App\Models\ConhecimentoTransporteEletronico;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CteSaidaResource extends Resource
{
    protected static ?string $model = ConhecimentoTransporteEletronico::class;

    protected static ?string $modelLabel = 'CTe Saída';

    protected static ?string $pluralLabel = 'CTes Saída';

    protected static ?string $navigationLabel = 'CTe Saída';

    protected static ?string $slug = 'ctes-saida';

    protected static string|UnitEnum|null $navigationGroup = 'CTe';

    public static function form(Schema $schema): Schema
    {
        return CteSaidaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CteSaidaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CteSaidasTable::configure($table);
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
            'index' => ListCteSaidas::route('/'),
            'create' => CreateCteSaida::route('/create'),
            'view' => ViewCteSaida::route('/{record}'),
            'edit' => EditCteSaida::route('/{record}/edit'),
        ];
    }
}
