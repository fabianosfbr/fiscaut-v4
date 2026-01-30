<?php

namespace App\Filament\Resources\NfeSaidas;

use UnitEnum;
use App\Models\NfeSaida;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use App\Models\NotaFiscalEletronica;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\NfeSaidas\Pages\EditNfeSaida;
use App\Filament\Resources\NfeSaidas\Pages\ViewNfeSaida;
use App\Filament\Resources\NfeSaidas\Pages\ListNfeSaidas;
use App\Filament\Resources\NfeSaidas\Pages\CreateNfeSaida;
use App\Filament\Resources\NfeSaidas\Schemas\NfeSaidaForm;
use App\Filament\Resources\NfeSaidas\Tables\NfeSaidasTable;
use App\Filament\Resources\NfeSaidas\Schemas\NfeSaidaInfolist;

class NfeSaidaResource extends Resource
{
    protected static ?string $model = NotaFiscalEletronica::class;

    protected static ?string $modelLabel = 'NFe Saída';

    protected static ?string $pluralLabel = 'NFes Saída';

    protected static ?string $navigationLabel = 'NFe Saída';

    protected static ?string $slug = 'nfes-saida';

    protected static string|UnitEnum|null $navigationGroup = 'NFe';

    public static function form(Schema $schema): Schema
    {
        return NfeSaidaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NfeSaidaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NfeSaidasTable::configure($table);
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
            'index' => ListNfeSaidas::route('/'),
            'create' => CreateNfeSaida::route('/create'),
            'view' => ViewNfeSaida::route('/{record}'),
            'edit' => EditNfeSaida::route('/{record}/edit'),
        ];
    }
}
