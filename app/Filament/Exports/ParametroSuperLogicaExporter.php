<?php

namespace App\Filament\Exports;

use App\Models\ParametroSuperLogica;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ParametroSuperLogicaExporter extends Exporter
{
    protected static ?string $model = ParametroSuperLogica::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('params')
                ->label('Parâmetros'),
            ExportColumn::make('contaCredito.codigo')
                ->label('Conta Crédito'),
            ExportColumn::make('contaDebito.codigo')
                ->label('Conta Débito'),
            ExportColumn::make('codigo_historico')
                ->label('Código Histórico'),
            ExportColumn::make('check_value')
                ->label('Checagem de Valor')
                ->state(function (ParametroSuperLogica $record): string {
                    return $record->check_value ? 'Sim' : 'Não';
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your parametro super logica export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
