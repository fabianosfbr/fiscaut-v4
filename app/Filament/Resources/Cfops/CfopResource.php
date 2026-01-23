<?php

namespace App\Filament\Resources\Cfops;

use App\Filament\Resources\Cfops\Pages\CreateCfop;
use App\Filament\Resources\Cfops\Pages\EditCfop;
use App\Filament\Resources\Cfops\Pages\ListCfops;
use App\Filament\Resources\Cfops\Schemas\CfopForm;
use App\Filament\Resources\Cfops\Tables\CfopsTable;
use App\Models\Cfop;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CfopResource extends Resource
{
    protected static ?string $model = Cfop::class;

    protected static ?string $modelLabel = 'CFOP';

    protected static ?string $pluralModelLabel = 'CFOPs';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    public static function form(Schema $schema): Schema
    {
        return CfopForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CfopsTable::configure($table);
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
            'index' => ListCfops::route('/'),
            'create' => CreateCfop::route('/create'),
            'edit' => EditCfop::route('/{record}/edit'),
        ];
    }
}
