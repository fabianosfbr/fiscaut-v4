<?php

namespace App\Filament\Resources\LogSefazCteContents;

use App\Filament\Resources\LogSefazCteContents\Pages\ListLogSefazCteContents;
use App\Filament\Resources\LogSefazCteContents\Schemas\LogSefazCteContentForm;
use App\Filament\Resources\LogSefazCteContents\Tables\LogSefazCteContentsTable;
use App\Filament\Resources\LogSefazCteContents\Widgets\MinMaxNsuCteOverview;
use App\Models\LogSefazCteContent;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LogSefazCteContentResource extends Resource
{
    protected static ?string $model = LogSefazCteContent::class;

    protected static ?string $modelLabel = 'Log NSU - CTe';

    protected static ?string $navigationLabel = 'Log NSU - CTe';

    protected static ?string $pluralModelLabel = 'Logs de NSU - CTe';

    protected static string|UnitEnum|null $navigationGroup = 'Administração';

    public static function form(Schema $schema): Schema
    {
        return LogSefazCteContentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LogSefazCteContentsTable::configure($table);
    }

    public static function getWidgets(): array
    {
        return [
            MinMaxNsuCteOverview::class,
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
            'index' => ListLogSefazCteContents::route('/'),
        ];
    }
}
