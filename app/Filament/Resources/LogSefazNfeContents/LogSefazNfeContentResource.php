<?php

namespace App\Filament\Resources\LogSefazNfeContents;

use App\Filament\Resources\LogSefazNfeContents\Pages\ListLogSefazNfeContents;
use App\Filament\Resources\LogSefazNfeContents\Schemas\LogSefazNfeContentForm;
use App\Filament\Resources\LogSefazNfeContents\Tables\LogSefazNfeContentsTable;
use App\Filament\Resources\LogSefazNfeContents\Widgets\MinMaxNsuOverview;
use App\Models\LogSefazNfeContent;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LogSefazNfeContentResource extends Resource
{
    protected static ?string $model = LogSefazNfeContent::class;

    protected static ?string $modelLabel = 'Log NSU - NFe';

    protected static ?string $navigationLabel = 'Log NSU - NFe';

    protected static ?string $pluralModelLabel = 'Logs de NSU - NFe';

    protected static string|UnitEnum|null $navigationGroup = 'Administração';

    public static function form(Schema $schema): Schema
    {
        return LogSefazNfeContentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LogSefazNfeContentsTable::configure($table);
    }

    public static function getWidgets(): array
    {
        return [
            MinMaxNsuOverview::class,
        ];
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
            'index' => ListLogSefazNfeContents::route('/'),
        ];
    }
}
