<?php

namespace App\Filament\Resources\Fornecedors\Pages;

use App\Filament\Resources\Fornecedors\FornecedorResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateFornecedor extends CreateRecord
{
    protected static string $resource = FornecedorResource::class;

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
