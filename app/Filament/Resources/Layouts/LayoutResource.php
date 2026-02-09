<?php

namespace App\Filament\Resources\Layouts;

use App\Filament\Resources\Layouts\Pages\CreateLayout;
use App\Filament\Resources\Layouts\Pages\EditLayout;
use App\Filament\Resources\Layouts\Pages\ListLayouts;
use App\Filament\Resources\Layouts\Schemas\LayoutForm;
use App\Filament\Resources\Layouts\Tables\LayoutsTable;
use App\Models\Layout;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LayoutResource extends Resource
{
    protected static ?string $model = Layout::class;

    protected static ?string $navigationLabel = 'Leiautes de Planilhas';

    protected static string|UnitEnum|null $navigationGroup = 'Contabilidade';

    public static function form(Schema $schema): Schema
    {
        return LayoutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LayoutsTable::configure($table);
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
            'index' => ListLayouts::route('/'),
            'create' => CreateLayout::route('/create'),
            'edit' => EditLayout::route('/{record}/edit'),
        ];
    }
}
