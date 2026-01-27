<?php

namespace App\Filament\Resources\CodigosServicos;

use App\Filament\Resources\CodigosServicos\Pages\CreateCodigoServico;
use App\Filament\Resources\CodigosServicos\Pages\EditCodigoServico;
use App\Filament\Resources\CodigosServicos\Pages\ListCodigosServicos;
use App\Filament\Resources\CodigosServicos\Schemas\CodigoServicoForm;
use App\Filament\Resources\CodigosServicos\Tables\CodigosServicosTable;
use App\Models\CodigoServico;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CodigoServicoResource extends Resource
{
    protected static ?string $model = CodigoServico::class;

    protected static ?string $modelLabel = 'Código de Serviço';

    protected static ?string $pluralModelLabel = 'Códigos de Serviço';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|UnitEnum|null $navigationGroup = 'Administração';

    public static function form(Schema $schema): Schema
    {
        return CodigoServicoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CodigosServicosTable::configure($table);
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
            'index' => ListCodigosServicos::route('/'),
            'create' => CreateCodigoServico::route('/create'),
            'edit' => EditCodigoServico::route('/{record}/edit'),
        ];
    }
}
