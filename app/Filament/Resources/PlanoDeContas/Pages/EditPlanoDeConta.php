<?php

namespace App\Filament\Resources\PlanoDeContas\Pages;

use App\Filament\Resources\PlanoDeContas\PlanoDeContaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlanoDeConta extends EditRecord
{
    protected static string $resource = PlanoDeContaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
