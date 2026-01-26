<?php

namespace App\Filament\Resources\SimplesNacionalAliquotas;

use App\Filament\Resources\SimplesNacionalAliquotas\Pages\CreateSimplesNacionalAliquota;
use App\Filament\Resources\SimplesNacionalAliquotas\Pages\EditSimplesNacionalAliquota;
use App\Filament\Resources\SimplesNacionalAliquotas\Pages\ListSimplesNacionalAliquotas;
use App\Filament\Resources\SimplesNacionalAliquotas\Schemas\SimplesNacionalAliquotaForm;
use App\Filament\Resources\SimplesNacionalAliquotas\Tables\SimplesNacionalAliquotasTable;
use App\Models\SimplesNacionalAliquota;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class SimplesNacionalAliquotaResource extends Resource
{
    protected static ?string $model = SimplesNacionalAliquota::class;

    protected static ?string $modelLabel = 'Alíquota Simples Nacional';

    protected static ?string $pluralModelLabel = 'Alíquotas Simples Nacional';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    public static function form(Schema $schema): Schema
    {
        return SimplesNacionalAliquotaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SimplesNacionalAliquotasTable::configure($table);
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
            'index' => ListSimplesNacionalAliquotas::route('/'),
            'create' => CreateSimplesNacionalAliquota::route('/create'),
            'edit' => EditSimplesNacionalAliquota::route('/{record}/edit'),
        ];
    }
}
