<?php

namespace App\Filament\Resources\LogSefazNfeContents;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\LogSefazNfeContent;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\LogSefazNfeContents\Widgets\MinMaxNsuOverview;
use App\Filament\Resources\LogSefazNfeContents\Pages\EditLogSefazNfeContent;
use App\Filament\Resources\LogSefazNfeContents\Pages\ListLogSefazNfeContents;
use App\Filament\Resources\LogSefazNfeContents\Pages\CreateLogSefazNfeContent;
use App\Filament\Resources\LogSefazNfeContents\Schemas\LogSefazNfeContentForm;
use App\Filament\Resources\LogSefazNfeContents\Tables\LogSefazNfeContentsTable;

class LogSefazNfeContentResource extends Resource
{
    protected static ?string $model = LogSefazNfeContent::class;

    protected static ?string $modelLabel = 'Log NSU - NFe';
    
    protected static ?string $navigationLabel = 'Log NSU - NFe';

    protected static ?string $pluralModelLabel = 'Logs de NSU - NFe';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

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
