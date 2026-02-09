<?php

namespace App\Filament\Resources\Layouts\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\Layouts\LayoutResource;

class EditLayout extends EditRecord
{
    protected static string $resource = LayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string | Htmlable
    {
        return 'Gerenciar Leiaute';
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-m-cog';
    }
}
