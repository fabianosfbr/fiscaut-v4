<?php

namespace App\Filament\Resources\PlanoDeContas;

use App\Filament\Resources\PlanoDeContas\Pages\CreatePlanoDeConta;
use App\Filament\Resources\PlanoDeContas\Pages\EditPlanoDeConta;
use App\Filament\Resources\PlanoDeContas\Pages\ListPlanoDeContas;
use App\Filament\Resources\PlanoDeContas\Schemas\PlanoDeContaForm;
use App\Filament\Resources\PlanoDeContas\Tables\PlanoDeContasTable;
use App\Models\PlanoDeConta;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PlanoDeContaResource extends Resource
{
    protected static ?string $model = PlanoDeConta::class;

    protected static ?string $navigationLabel = 'Plano de Contas';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    public static function form(Schema $schema): Schema
    {
        return PlanoDeContaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlanoDeContasTable::configure($table);
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
            'index' => ListPlanoDeContas::route('/'),
            'create' => CreatePlanoDeConta::route('/create'),
            'edit' => EditPlanoDeConta::route('/{record}/edit'),
        ];
    }
}
