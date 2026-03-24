<?php

namespace App\Filament\Condominio\Resources\IssuerControls;

use App\Filament\Condominio\Resources\IssuerControls\Pages\CreateIssuerControl;
use App\Filament\Condominio\Resources\IssuerControls\Pages\EditIssuerControl;
use App\Filament\Condominio\Resources\IssuerControls\Pages\ListIssuerControls;
use App\Filament\Condominio\Resources\IssuerControls\Pages\ViewIssuerControl;
use App\Filament\Condominio\Resources\IssuerControls\Schemas\IssuerControlForm;
use App\Filament\Condominio\Resources\IssuerControls\Schemas\IssuerControlInfoList;
use App\Filament\Condominio\Resources\IssuerControls\Tables\IssuerControlsTable;
use App\Models\IssuerControl;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class IssuerControlResource extends Resource
{
    protected static ?string $model = IssuerControl::class;

    protected static ?string $modelLabel = 'Controle';

    protected static ?string $pluralModelLabel = 'Controles';

    protected static string|UnitEnum|null $navigationGroup = 'Controles';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return IssuerControlForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IssuerControlsTable::configure($table);
    }


    public static function infolist(Schema $schema): Schema
    {
        return IssuerControlInfoList::configure($schema);
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
            'index' => ListIssuerControls::route('/'),
            'create' => CreateIssuerControl::route('/create'),
            'edit' => EditIssuerControl::route('/{record}/edit'),
            'view' => ViewIssuerControl::route('/{record}'),
        ];
    }
}
