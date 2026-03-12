<?php

namespace App\Filament\Condominio\Resources\IssuerGroupControls;

use App\Filament\Condominio\Resources\IssuerGroupControls\Pages\CreateIssuerGroupControl;
use App\Filament\Condominio\Resources\IssuerGroupControls\Pages\EditIssuerGroupControl;
use App\Filament\Condominio\Resources\IssuerGroupControls\Pages\ListIssuerGroupControls;
use App\Filament\Condominio\Resources\IssuerGroupControls\RelationManagers\FieldsRelationManager;
use App\Filament\Condominio\Resources\IssuerGroupControls\Schemas\IssuerGroupControlForm;
use App\Filament\Condominio\Resources\IssuerGroupControls\Tables\IssuerGroupControlsTable;
use App\Models\IssuerGroupControl;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IssuerGroupControlResource extends Resource
{
    protected static ?string $model = IssuerGroupControl::class;

    protected static ?string $modelLabel = 'Grupo de Controle';

    protected static ?string $pluralModelLabel = 'Grupos de Controles';

    protected static string|UnitEnum|null $navigationGroup = 'Controles';

    protected static bool $hasTitleCaseModelLabel = false;

    public static function form(Schema $schema): Schema
    {
        return IssuerGroupControlForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IssuerGroupControlsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            FieldsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIssuerGroupControls::route('/'),
            'create' => CreateIssuerGroupControl::route('/create'),
            'edit' => EditIssuerGroupControl::route('/{record}/edit'),
        ];
    }
}
