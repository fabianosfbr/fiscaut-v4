<?php

namespace App\Filament\Condominio\Resources\TipoManutencaos;

use App\Filament\Condominio\Resources\TipoManutencaos\Pages\CreateTipoManutencao;
use App\Filament\Condominio\Resources\TipoManutencaos\Pages\EditTipoManutencao;
use App\Filament\Condominio\Resources\TipoManutencaos\Pages\ListTipoManutencaos;
use App\Filament\Condominio\Resources\TipoManutencaos\Schemas\TipoManutencaoForm;
use App\Filament\Condominio\Resources\TipoManutencaos\Tables\TipoManutencaosTable;
use App\Models\TipoManutencao;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TipoManutencaoResource extends Resource
{
    protected static ?string $model = TipoManutencao::class;

    protected static ?string $navigationLabel = 'Tipos de Controle';

    protected static ?string $modelLabel = 'Tipo de Controle';

    protected static ?string $pluralModelLabel = 'Tipos de Controle';

    protected static string|UnitEnum|null $navigationGroup = 'Controles';

    protected static ?string $slug = 'tipos-de-controle';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return TipoManutencaoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TipoManutencaosTable::configure($table);
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
            'index' => ListTipoManutencaos::route('/'),
            'create' => CreateTipoManutencao::route('/create'),
            'edit' => EditTipoManutencao::route('/{record}/edit'),
        ];
    }
}
