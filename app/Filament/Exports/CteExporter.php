<?php

namespace App\Filament\Exports;

use App\Models\ConhecimentoTransporteEletronico;
use App\Models\Cte;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CteExporter extends Exporter
{
    protected static ?string $model = ConhecimentoTransporteEletronico::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('nCTe')
                ->label('Nº CT'),
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
            ExportColumn::make('tomador_razao_social')
                ->label('Tomador'),
            ExportColumn::make('tomador_cnpj')
                ->label('Tomador CNPJ'),
            ExportColumn::make('vCTe')
                ->label('Valor')
                ->formatStateUsing(fn(string $state): string => number_format($state, 2, ',', '.')),
            ExportColumn::make('data_emissao')
                ->label('Data Emissão')
                ->formatStateUsing(fn(string $state): string => date_format(date_create($state), 'd/m/Y')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Os ctes foram exportados e ' . number_format($export->successful_rows) . ' ' . str('linha')->plural($export->successful_rows) . ' foram exportadas com sucesso.';

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
        return "conhecimentos-transporte-eletronico-{$export->getKey()}";
    }
}
