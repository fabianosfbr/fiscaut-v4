<?php

namespace App\Filament\Condominio\Resources\ManutencaoRecorrencias;

use App\Filament\Condominio\Resources\ManutencaoRecorrencias\Pages\CreateManutencaoRecorrencia;
use App\Filament\Condominio\Resources\ManutencaoRecorrencias\Pages\EditManutencaoRecorrencia;
use App\Filament\Condominio\Resources\ManutencaoRecorrencias\Pages\ListManutencaoRecorrencias;
use App\Filament\Condominio\Resources\ManutencaoRecorrencias\Schemas\ManutencaoRecorrenciaForm;
use App\Filament\Condominio\Resources\ManutencaoRecorrencias\Tables\ManutencaoRecorrenciasTable;
use App\Models\ManutencaoRecorrencia;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ManutencaoRecorrenciaResource extends Resource
{
    protected static ?string $model = ManutencaoRecorrencia::class;

    protected static ?string $navigationLabel = 'Recorrências';

    protected static ?string $modelLabel = 'Recorrência';

    protected static ?string $pluralModelLabel = 'Recorrências';

    protected static ?string $slug = 'recorrencias';

    protected static string|UnitEnum|null $navigationGroup = 'Controles';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ManutencaoRecorrenciaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManutencaoRecorrenciasTable::configure($table);
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
            'index' => ListManutencaoRecorrencias::route('/'),
            'create' => CreateManutencaoRecorrencia::route('/create'),
            'edit' => EditManutencaoRecorrencia::route('/{record}/edit'),
        ];
    }
}
