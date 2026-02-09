<?php

namespace App\Filament\Resources\Layouts\Pages;

use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Layouts\LayoutResource;

class CreateLayout extends CreateRecord
{
    protected static string $resource = LayoutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['issuer_id'] = Auth::user()->currentIssuer->id;

        return $data;
    }


}
