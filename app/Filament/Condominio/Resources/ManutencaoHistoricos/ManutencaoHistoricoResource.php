<?php

namespace App\Filament\Condominio\Resources\ManutencaoHistoricos;

use App\Filament\Condominio\Resources\ManutencaoHistoricos\Pages\ListManutencaoHistoricos;
use App\Filament\Condominio\Resources\ManutencaoHistoricos\Tables\ManutencaoHistoricosTable;
use App\Models\ManutencaoHistorico;
use UnitEnum;
use Filament\Resources\Resource;

use Filament\Tables\Table;

class ManutencaoHistoricoResource extends Resource
{
    protected static ?string $model = ManutencaoHistorico::class;

    protected static ?string $navigationLabel = 'Histórico de Controles';

    protected static ?string $modelLabel = 'Histórico de Controle';

    protected static ?string $pluralModelLabel = 'Histórico de Controles';

    protected static ?string $slug = 'historico';

    protected static string|UnitEnum|null $navigationGroup = 'Controles';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return ManutencaoHistoricosTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false; // Histórico é apenas para leitura
    }

    public static function canEdit($record): bool
    {
        return false; // Histórico é apenas para leitura
    }

    public static function canDelete($record): bool
    {
        return false; // Histórico é apenas para leitura
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
            'index' => ListManutencaoHistoricos::route('/'),
        ];
    }
}
