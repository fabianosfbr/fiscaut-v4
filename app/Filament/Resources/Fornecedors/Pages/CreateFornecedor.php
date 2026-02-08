<?php

namespace App\Filament\Resources\Fornecedors\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Fornecedors\FornecedorResource;

class CreateFornecedor extends CreateRecord
{
    protected static string $resource = FornecedorResource::class;

        protected function mutateFormDataBeforeCreate(array $data): array
    {      
        $data['issuer_id'] = Auth::user()->currentIssuer->id;

        return $data;
    }
}
