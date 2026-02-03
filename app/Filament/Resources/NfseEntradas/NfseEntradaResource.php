<?php

namespace App\Filament\Resources\NfseEntradas;

use App\Filament\Resources\NfseEntradas\Pages\ListNfseEntradas;
use App\Filament\Resources\NfseEntradas\Pages\ViewNfseEntrada;
use App\Filament\Resources\NfseEntradas\Schemas\NfseEntradaForm;
use App\Filament\Resources\NfseEntradas\Schemas\NfseEntradaInfolist;
use App\Filament\Resources\NfseEntradas\Tables\NfseEntradasTable;
use App\Models\NotaFiscalServico;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class NfseEntradaResource extends Resource
{
    protected static ?string $model = NotaFiscalServico::class;

    protected static ?string $modelLabel = 'NFse Entrada';

    protected static ?string $pluralLabel = 'NFses Entrada';

    protected static ?string $navigationLabel = 'NFse Entrada';

    protected static ?string $slug = 'nfse-entrada';

    protected static string|UnitEnum|null $navigationGroup = 'NFSe';

    public static function form(Schema $schema): Schema
    {
        return NfseEntradaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NfseEntradaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NfseEntradasTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['tagged.tag', 'apurada']);
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
            'index' => ListNfseEntradas::route('/'),
            'view' => ViewNfseEntrada::route('/{record}'),

        ];
    }
}
