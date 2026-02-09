<?php

namespace App\Filament\Resources\HistoricoContabils;

use App\Filament\Resources\HistoricoContabils\Pages\CreateHistoricoContabil;
use App\Filament\Resources\HistoricoContabils\Pages\EditHistoricoContabil;
use App\Filament\Resources\HistoricoContabils\Pages\ListHistoricoContabils;
use App\Filament\Resources\HistoricoContabils\Schemas\HistoricoContabilForm;
use App\Filament\Resources\HistoricoContabils\Tables\HistoricoContabilsTable;
use App\Models\HistoricoContabil;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class HistoricoContabilResource extends Resource
{
    protected static ?string $model = HistoricoContabil::class;

    protected static ?string $modelLabel = 'Histórico Contábil';

    protected static ?string $pluralModelLabel = 'Históricos Contábeis';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    public static function form(Schema $schema): Schema
    {
        return HistoricoContabilForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HistoricoContabilsTable::configure($table);
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
            'index' => ListHistoricoContabils::route('/'),
            'create' => CreateHistoricoContabil::route('/create'),
            'edit' => EditHistoricoContabil::route('/{record}/edit'),
        ];
    }
}
