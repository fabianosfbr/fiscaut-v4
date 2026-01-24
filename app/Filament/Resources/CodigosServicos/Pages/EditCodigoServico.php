<?php

namespace App\Filament\Resources\CodigosServicos\Pages;

use App\Filament\Resources\CodigosServicos\CodigoServicoResource;
use Filament\Resources\Pages\EditRecord;

class EditCodigoServico extends EditRecord
{
    protected static string $resource = CodigoServicoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
