<?php

namespace App\Filament\Condominio\Resources\Manutencaos;

use App\Filament\Condominio\Resources\Manutencaos\Pages\CreateManutencao;
use App\Filament\Condominio\Resources\Manutencaos\Pages\EditManutencao;
use App\Filament\Condominio\Resources\Manutencaos\Pages\ListManutencaos;
use App\Filament\Condominio\Resources\Manutencaos\Pages\ViewManutencao;
use App\Filament\Condominio\Resources\Manutencaos\Schemas\ManutencaoForm;
use App\Filament\Condominio\Resources\Manutencaos\Schemas\ManutencaoInfolist;
use App\Filament\Condominio\Resources\Manutencaos\Tables\ManutencaosTable;
use App\Models\Manutencao;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ManutencaoResource extends Resource
{
    protected static ?string $model = Manutencao::class;

    protected static ?string $modelLabel = 'Controle';

    protected static ?string $pluralModelLabel = 'Controles';

    protected static string|UnitEnum|null $navigationGroup = 'Controles';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ManutencaoForm::configure($schema);
    }


    public static function infolist(Schema $schema): Schema
    {
        return ManutencaoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManutencaosTable::configure($table);
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
            'index' => ListManutencaos::route('/'),
            'create' => CreateManutencao::route('/create'),
            'edit' => EditManutencao::route('/{record}/edit'),
            'view' => ViewManutencao::route('/{record}'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
