<?php

namespace App\Filament\Resources\CodigosServicos\Pages;

use App\Filament\Resources\CodigosServicos\CodigoServicoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCodigoServico extends CreateRecord
{
    protected static string $resource = CodigoServicoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
