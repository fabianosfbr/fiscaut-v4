<?php

namespace App\Filament\Resources\NfeEntradas;

use App\Filament\Resources\NfeEntradas\Pages\CreateNfeEntrada;
use App\Filament\Resources\NfeEntradas\Pages\EditNfeEntrada;
use App\Filament\Resources\NfeEntradas\Pages\ListNfeEntradas;
use App\Filament\Resources\NfeEntradas\Pages\ViewNfeEntrada;
use App\Filament\Resources\NfeEntradas\Schemas\NfeEntradaForm;
use App\Filament\Resources\NfeEntradas\Schemas\NfeEntradaInfolist;
use App\Filament\Resources\NfeEntradas\Tables\NfeEntradasTable;
use App\Models\NotaFiscalEletronica;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NfeEntradaResource extends Resource
{
    protected static ?string $model = NotaFiscalEletronica::class;

    protected static ?string $modelLabel = 'NFe Entrada';

    protected static ?string $pluralLabel = 'NFes Entrada';

    protected static ?string $navigationLabel = 'NFe Entrada';

    protected static ?string $slug = 'nfes-entrada';

    protected static string|UnitEnum|null $navigationGroup = 'NFe';

    public static function form(Schema $schema): Schema
    {
        return NfeEntradaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NfeEntradaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NfeEntradasTable::configure($table);
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
            'index' => ListNfeEntradas::route('/'),
            'create' => CreateNfeEntrada::route('/create'),
            'view' => ViewNfeEntrada::route('/{record}'),
            'edit' => EditNfeEntrada::route('/{record}/edit'),
        ];
    }
}
