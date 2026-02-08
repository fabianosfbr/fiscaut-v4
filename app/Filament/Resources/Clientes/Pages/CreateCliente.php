<?php

namespace App\Filament\Resources\Clientes\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Clientes\ClienteResource;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {      
        $data['issuer_id'] = Auth::user()->currentIssuer->id;

        return $data;
    }
}
