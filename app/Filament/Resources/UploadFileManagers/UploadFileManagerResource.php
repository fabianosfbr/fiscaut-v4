<?php

namespace App\Filament\Resources\UploadFileManagers;

use App\Filament\Resources\UploadFileManagers\Pages\CreateUploadFileManager;
use App\Filament\Resources\UploadFileManagers\Pages\EditUploadFileManager;
use App\Filament\Resources\UploadFileManagers\Pages\ListUploadFileManagers;
use App\Filament\Resources\UploadFileManagers\Schemas\UploadFileManagerForm;
use App\Filament\Resources\UploadFileManagers\Tables\UploadFileManagersTable;
use App\Models\UploadFile;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class UploadFileManagerResource extends Resource
{
    protected static ?string $model = UploadFile::class;

    protected static ?string $modelLabel = 'Documento';

    protected static string|UnitEnum|null $navigationGroup = 'Demais docs. fiscais';

    public static function form(Schema $schema): Schema
    {
        return UploadFileManagerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UploadFileManagersTable::configure($table);
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
            'index' => ListUploadFileManagers::route('/'),
            'create' => CreateUploadFileManager::route('/create'),
            'edit' => EditUploadFileManager::route('/{record}/edit'),
        ];
    }
}
