<?php

namespace App\Filament\Resources\Cnaes;

use App\Filament\Resources\Cnaes\Pages\CreateCnae;
use App\Filament\Resources\Cnaes\Pages\EditCnae;
use App\Filament\Resources\Cnaes\Pages\ListCnaes;
use App\Filament\Resources\Cnaes\Schemas\CnaeForm;
use App\Filament\Resources\Cnaes\Tables\CnaesTable;
use App\Models\Cnae;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CnaeResource extends Resource
{
    protected static ?string $model = Cnae::class;

    protected static ?string $modelLabel = 'CNAE';

    protected static ?string $pluralModelLabel = 'CNAEs';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    public static function form(Schema $schema): Schema
    {
        return CnaeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CnaesTable::configure($table);
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
            'index' => ListCnaes::route('/'),
            'create' => CreateCnae::route('/create'),
            'edit' => EditCnae::route('/{record}/edit'),
        ];
    }
}
