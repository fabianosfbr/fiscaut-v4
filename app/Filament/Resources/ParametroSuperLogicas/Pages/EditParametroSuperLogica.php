<?php

namespace App\Filament\Resources\ParametroSuperLogicas\Pages;

use App\Filament\Resources\ParametroSuperLogicas\ParametroSuperLogicaResource;
use App\Models\PlanoDeConta;
use Filament\Resources\Pages\EditRecord;

class EditParametroSuperLogica extends EditRecord
{
    protected static string $resource = ParametroSuperLogicaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function mutateFormDataBeforeFill(array $data): array
    {

        $data['conta_credito'] = PlanoDeConta::find($data['conta_credito'])?->codigo;
        $data['conta_debito'] = PlanoDeConta::find($data['conta_debito'])?->codigo;

        return $data;
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }

    public function getHeading(): string
    {
        return 'Editar Parâmetro Super Lógica';
    }
}
