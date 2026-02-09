<?php

namespace App\Filament\Resources\PlanoDeContas\Pages;

use App\Filament\Resources\PlanoDeContas\PlanoDeContaResource;
use Filament\Resources\Pages\ListRecords;

class ListPlanoDeContas extends ListRecords
{
    protected static string $resource = PlanoDeContaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeading(): string
    {
        return 'Plano de Contas';
    }
}
