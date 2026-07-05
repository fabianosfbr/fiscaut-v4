<?php

namespace App\Filament\Exports;

use App\Models\Nfe;
use App\Models\NotaFiscalEletronica;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;

class NfeExporter extends Exporter
{
    protected static ?string $model = NotaFiscalEletronica::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('nNF')
                ->label('Nº NF'),
            ExportColumn::make('chave')
                ->label('Chave'),
            ExportColumn::make('emitente_razao_social')
                ->label('Emitente'),
            ExportColumn::make('emitente_cnpj')
                ->label('Emitente CNPJ'),
            ExportColumn::make('destinatario_razao_social')
                ->label('Destinatário'),
            ExportColumn::make('destinatario_cnpj')
                ->label('Destinatário CNPJ'),
            ExportColumn::make('cfops')
                ->label('CFOP'),
            ExportColumn::make('vNfe')
                ->label('Valor')
                ->formatStateUsing(fn(string $state): string => number_format($state, 2, ',', '.')),
            ExportColumn::make('data_emissao')
                ->label('Data Emissão')
                ->formatStateUsing(fn(string $state): string => filled($state) ? date_format(date_create($state), 'd/m/Y') : ''),
            ExportColumn::make('data_entrada')
                ->label('Data Entrada')
                ->formatStateUsing(function ($state) {
                    $result = '';
                    if (filled($state)) {
                        $result = date_format(date_create($state), 'd/m/Y');
                    }

                    return $result;
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'As notas ficais foram exportadas e ' . number_format($export->successful_rows) . ' ' . str('linha')->plural($export->successful_rows) . ' foram exportadas com sucesso.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFormats(): array
    {
        return [
            ExportFormat::Xlsx,
        ];
    }

    public function getFileName(Export $export): string
    {
        return "notas-fiscais-eletronica-{$export->getKey()}";
    }
}
