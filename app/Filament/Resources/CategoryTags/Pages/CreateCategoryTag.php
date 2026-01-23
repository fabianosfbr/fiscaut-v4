<?php

namespace App\Filament\Resources\CategoryTags\Pages;

use App\Filament\Resources\CategoryTags\CategoryTagResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCategoryTag extends CreateRecord
{
    protected static string $resource = CategoryTagResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Preenche campos de controle automaticamente
        $user = Auth::user();

        if ($user && $user->tenant_id && $user->currentIssuer) {
            $data['tenant_id'] = $user->tenant_id;
            $data['issuer_id'] = $user->currentIssuer->id;
        } else {
            throw new \Exception('Usuário não autenticado ou empresa não selecionada');
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Categoria de etiqueta criada com sucesso!';
    }
}
