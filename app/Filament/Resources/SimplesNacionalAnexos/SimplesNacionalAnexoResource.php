<?php

namespace App\Filament\Resources\SimplesNacionalAnexos;

use App\Filament\Resources\SimplesNacionalAnexos\Pages\CreateSimplesNacionalAnexo;
use App\Filament\Resources\SimplesNacionalAnexos\Pages\EditSimplesNacionalAnexo;
use App\Filament\Resources\SimplesNacionalAnexos\Pages\ListSimplesNacionalAnexos;
use App\Filament\Resources\SimplesNacionalAnexos\Pages\ViewSimplesNacionalAnexo;
use App\Filament\Resources\SimplesNacionalAnexos\RelationManagers\AliquotasRelationManager;
use App\Filament\Resources\SimplesNacionalAnexos\RelationManagers\CnaesRelationManager;
use App\Filament\Resources\SimplesNacionalAnexos\Schemas\SimplesNacionalAnexoForm;
use App\Filament\Resources\SimplesNacionalAnexos\Tables\SimplesNacionalAnexosTable;
use App\Models\SimplesNacionalAnexo;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SimplesNacionalAnexoResource extends Resource
{
    protected static ?string $model = SimplesNacionalAnexo::class;

    protected static ?string $modelLabel = 'Anexo Simples Nacional';

    protected static ?string $pluralModelLabel = 'Anexos Simples Nacional';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|UnitEnum|null $navigationGroup = 'Administração';

    public static function form(Schema $schema): Schema
    {
        return SimplesNacionalAnexoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SimplesNacionalAnexosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AliquotasRelationManager::class,
            CnaesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSimplesNacionalAnexos::route('/'),
            'create' => CreateSimplesNacionalAnexo::route('/create'),
            'view' => ViewSimplesNacionalAnexo::route('/{record}'),
            'edit' => EditSimplesNacionalAnexo::route('/{record}/edit'),
        ];
    }
}
