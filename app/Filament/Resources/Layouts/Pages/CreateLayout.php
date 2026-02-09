<?php

namespace App\Filament\Resources\Layouts\Pages;

use App\Filament\Resources\Layouts\LayoutResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLayout extends CreateRecord
{
    protected static string $resource = LayoutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['issuer_id'] = Auth::user()->currentIssuer->id;

        return $data;
    }
}
