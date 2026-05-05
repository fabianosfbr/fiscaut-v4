<?php

namespace App\Filament\Resources\NfceSaidas;

use App\Filament\Resources\NfceSaidas\Pages\CreateNfceSaida;
use App\Filament\Resources\NfceSaidas\Pages\EditNfceSaida;
use App\Filament\Resources\NfceSaidas\Pages\ListNfceSaidas;
use App\Filament\Resources\NfceSaidas\Pages\ViewNfceSaida;
use App\Filament\Resources\NfceSaidas\Schemas\NfceSaidaForm;
use App\Filament\Resources\NfceSaidas\Schemas\NfceSaidaInfolist;
use App\Filament\Resources\NfceSaidas\Tables\NfceSaidasTable;
use App\Models\NotaFiscalConsumidor;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class NfceSaidaResource extends Resource
{
    protected static ?string $model = NotaFiscalConsumidor::class;

    protected static ?string $modelLabel = 'NFCe Saída';

    protected static ?string $pluralLabel = 'NFCes Saída';

    protected static ?string $navigationLabel = 'NFCe Saída';

    protected static ?string $slug = 'nfces-saida';

    protected static string|UnitEnum|null $navigationGroup = 'NFCe';

    public static function form(Schema $schema): Schema
    {
        return NfceSaidaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NfceSaidaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NfceSaidasTable::configure($table);
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
            'index' => ListNfceSaidas::route('/'),
            // 'create' => CreateNfceSaida::route('/create'),
            // 'view' => ViewNfceSaida::route('/{record}'),
            // 'edit' => EditNfceSaida::route('/{record}/edit'),
        ];
    }
}
