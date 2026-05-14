<?php

namespace App\Filament\Condominio\Resources\SuperLogicaUnidades;

use App\Filament\Condominio\Resources\SuperLogicaUnidades\Pages\CreateSuperLogicaUnidade;
use App\Filament\Condominio\Resources\SuperLogicaUnidades\Pages\EditSuperLogicaUnidade;
use App\Filament\Condominio\Resources\SuperLogicaUnidades\Pages\ListSuperLogicaUnidades;
use App\Filament\Condominio\Resources\SuperLogicaUnidades\Pages\ViewSuperLogicaUnidade;
use App\Filament\Condominio\Resources\SuperLogicaUnidades\Schemas\SuperLogicaUnidadeForm;
use App\Filament\Condominio\Resources\SuperLogicaUnidades\Schemas\SuperLogicaUnidadeInfolist;
use App\Filament\Condominio\Resources\SuperLogicaUnidades\Tables\SuperLogicaUnidadesTable;
use App\Models\SuperLogicaUnidade;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SuperLogicaUnidadeResource extends Resource
{
    protected static ?string $model = SuperLogicaUnidade::class;

    protected static ?string $modelLabel = 'Unidade';

    protected static ?string $pluralModelLabel = 'Unidades';

    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';

    public static function form(Schema $schema): Schema
    {
        return SuperLogicaUnidadeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SuperLogicaUnidadeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuperLogicaUnidadesTable::configure($table);
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
            'index' => ListSuperLogicaUnidades::route('/'),
            'create' => CreateSuperLogicaUnidade::route('/create'),
            'edit' => EditSuperLogicaUnidade::route('/{record}/edit'),
            'view' => ViewSuperLogicaUnidade::route('/{record}'),
        ];
    }
}
