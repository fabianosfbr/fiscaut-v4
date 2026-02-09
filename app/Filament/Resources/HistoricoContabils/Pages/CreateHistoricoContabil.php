<?php

namespace App\Filament\Resources\HistoricoContabils\Pages;

use App\Filament\Resources\HistoricoContabils\HistoricoContabilResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateHistoricoContabil extends CreateRecord
{
    protected static string $resource = HistoricoContabilResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['issuer_id'] = Auth::user()->currentIssuer->id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }
}
