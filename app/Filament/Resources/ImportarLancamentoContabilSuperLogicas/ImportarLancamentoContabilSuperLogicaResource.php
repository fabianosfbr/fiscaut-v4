<?php

namespace App\Filament\Resources\ImportarLancamentoContabilSuperLogicas;

use App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\Pages\CreateImportarLancamentoContabilSuperLogica;
use App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\Pages\EditImportarLancamentoContabilSuperLogica;
use App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\Pages\ListImportarLancamentoContabilSuperLogicas;
use App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\Schemas\ImportarLancamentoContabilSuperLogicaForm;
use App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\Tables\ImportarLancamentoContabilSuperLogicasTable;
use App\Models\ImportarLancamentoContabil;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ImportarLancamentoContabilSuperLogicaResource extends Resource
{
    protected static ?string $model = ImportarLancamentoContabil::class;

    protected static ?string $modelLabel = 'Lanç. Contábil Super Lógica';

    protected static ?string $pluralModelLabel = 'Lanç. Contábeis Super Lógica';

    protected static ?string $slug = 'importar-lancamento-contabil-super-logica';

    protected static string|UnitEnum|null $navigationGroup = 'Contabilidade';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ImportarLancamentoContabilSuperLogicaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImportarLancamentoContabilSuperLogicasTable::configure($table);
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
            'index' => ListImportarLancamentoContabilSuperLogicas::route('/'),
            'create' => CreateImportarLancamentoContabilSuperLogica::route('/create'),
            'edit' => EditImportarLancamentoContabilSuperLogica::route('/{record}/edit'),
        ];
    }
}
