<?php

namespace App\Filament\Resources\ParametroSuperLogicas\Pages;

use App\Filament\Resources\ParametroSuperLogicas\ParametroSuperLogicaResource;
use App\Models\PlanoDeConta;
use Filament\Resources\Pages\CreateRecord;

class CreateParametroSuperLogica extends CreateRecord
{
    protected static string $resource = ParametroSuperLogicaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $issuerId = currentIssuer()->id;
        $data['issuer_id'] = $issuerId;
        $data['conta_credito'] = PlanoDeConta::where('issuer_id', $issuerId)
            ->where('codigo', $data['conta_credito'])
            ->first()->id;
        $data['conta_debito'] = PlanoDeConta::where('issuer_id', $issuerId)
            ->where('codigo', $data['conta_debito'])
            ->first()->id;

        return $data;
    }

    protected function afterCreate(): void
    {
        redirect(request()->header('Referer'));
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }

    public function getHeading(): string
    {
        return 'Criar Parâmetro Super Lógica';
    }
}
