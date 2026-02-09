<?php

namespace App\Filament\Resources\ImportarLancamentoContabilGerals;

use App\Filament\Resources\ImportarLancamentoContabilGerals\Pages\CreateImportarLancamentoContabilGeral;
use App\Filament\Resources\ImportarLancamentoContabilGerals\Pages\EditImportarLancamentoContabilGeral;
use App\Filament\Resources\ImportarLancamentoContabilGerals\Pages\ListImportarLancamentoContabilGerals;
use App\Filament\Resources\ImportarLancamentoContabilGerals\Schemas\ImportarLancamentoContabilGeralForm;
use App\Filament\Resources\ImportarLancamentoContabilGerals\Tables\ImportarLancamentoContabilGeralsTable;
use App\Models\ImportarLancamentoContabil;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ImportarLancamentoContabilGeralResource extends Resource
{
    protected static ?string $model = ImportarLancamentoContabil::class;

    protected static ?string $modelLabel = 'Lanç. Contábil';

    protected static ?string $pluralModelLabel = 'Lanç Contábeis';

    protected static ?string $slug = 'importar-lancamento-contabil';

    protected static string|UnitEnum|null $navigationGroup = 'Contabilidade';


    public static function form(Schema $schema): Schema
    {
        return ImportarLancamentoContabilGeralForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImportarLancamentoContabilGeralsTable::configure($table);
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
            'index' => ListImportarLancamentoContabilGerals::route('/'),
            'create' => CreateImportarLancamentoContabilGeral::route('/create'),
            'edit' => EditImportarLancamentoContabilGeral::route('/{record}/edit'),
        ];
    }
}
