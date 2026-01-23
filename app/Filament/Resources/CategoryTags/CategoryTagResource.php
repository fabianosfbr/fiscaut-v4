<?php

namespace App\Filament\Resources\CategoryTags;

use App\Filament\Resources\CategoryTags\Pages\CreateCategoryTag;
use App\Filament\Resources\CategoryTags\Pages\EditCategoryTag;
use App\Filament\Resources\CategoryTags\Pages\ListCategoryTags;
use App\Filament\Resources\CategoryTags\Schemas\CategoryTagForm;
use App\Filament\Resources\CategoryTags\Tables\CategoryTagsTable;
use App\Models\CategoryTag;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class CategoryTagResource extends Resource
{
    protected static ?string $model = CategoryTag::class;

    protected static ?string $modelLabel = 'Etiqueta';

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    public static function form(Schema $schema): Schema
    {
        return CategoryTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoryTagsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TagsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategoryTags::route('/'),
            'create' => CreateCategoryTag::route('/create'),
            'edit' => EditCategoryTag::route('/{record}/edit'),
        ];
    }
}
