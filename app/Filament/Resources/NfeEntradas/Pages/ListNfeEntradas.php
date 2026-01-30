<?php

namespace App\Filament\Resources\NfeEntradas\Pages;

use App\Filament\Resources\NfeEntradas\NfeEntradaResource;
use App\Models\NotaFiscalEletronica;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;

class ListNfeEntradas extends ListRecords
{
    protected static string $resource = NfeEntradaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function getHeading(): string
    {
        return 'Notas Fiscais Eletrônicas';
    }

    public function getTabs(): array
    {

        return [
            'propria' => Tab::make()
                ->label('Entrada de Terceiros')
                ->query(function () {
                    return NotaFiscalEletronica::query()->entradasTerceiros();
                }),
            'terceiro' => Tab::make()
                ->label('Entrada Própria')
                ->query(function () {
                    return NotaFiscalEletronica::query()->entradasProprias();
                }),
            'propria_terceiro' => Tab::make()
                ->label('Entrada Própria de Terceiros')
                ->query(function () {
                    return NotaFiscalEletronica::query()->entradasPropriasTerceiros();
                }),

        ];
    }

    public function applySuggestedTag(int $recordId, int $tagId): void
    {
        $record = NotaFiscalEletronica::find($recordId);

        $record->retag($tagId);

        Notification::make()
            ->success()
            ->title('Etiqueta aplicada')
            ->body('A etiqueta foi aplicada ao documento.')
            ->send();

        $this->flushCachedTableRecords();
    }
}
