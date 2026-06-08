<?php

namespace App\Filament\Resources\NcmRestricoes;

use App\Filament\Resources\NcmRestricoes\Pages\CreateNcmRestricao;
use App\Filament\Resources\NcmRestricoes\Pages\EditNcmRestricao;
use App\Filament\Resources\NcmRestricoes\Pages\ListNcmRestricoes;
use App\Filament\Resources\NcmRestricoes\Schemas\NcmRestricaoForm;
use App\Filament\Resources\NcmRestricoes\Tables\NcmRestricoesTable;
use App\Models\NcmRestricao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NcmRestricaoResource extends Resource
{
    protected static ?string $model = NcmRestricao::class;

    protected static ?string $modelLabel = 'Restrição NCM';

    protected static ?string $pluralModelLabel = 'Restrições NCM';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';

    protected static ?string $slug = 'ncm-restricoes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentCheck;

    public static function form(Schema $schema): Schema
    {
        return NcmRestricaoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NcmRestricoesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNcmRestricoes::route('/'),
            'create' => CreateNcmRestricao::route('/create'),
            'edit' => EditNcmRestricao::route('/{record}/edit'),
        ];
    }
}
