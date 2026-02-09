<?php

namespace App\Filament\Resources\HistoricoContabils\Pages;

use App\Filament\Resources\HistoricoContabils\HistoricoContabilResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHistoricoContabil extends EditRecord
{
    protected static string $resource = HistoricoContabilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }
}
