<?php

namespace App\Filament\Resources\Issuers\Pages;

use App\Enums\IssuerTypeEnum;
use App\Filament\Resources\Issuers\IssuerResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

class EditIssuer extends EditRecord
{
    protected static string $resource = IssuerResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Remover campos sensíveis que não devem aparecer no formulário de edição
        unset($data['path_certificado']);
        unset($data['certificado_content']);
        unset($data['senha_certificado']);

        return $data;
    }

    /**
     * Mutate form data before saving the record.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {       
        // Criptografar apenas a senha (certificado_content já vem criptografado do CertificateService)
        if (! empty($data['senha_certificado']) && $data['certificado_verificado'] === true) {
            $data['senha_certificado'] = Crypt::encrypt($data['senha_certificado']);
        } else {

            unset($data['senha_certificado']);
            unset($data['certificado_content']);
        }

        // Remover campos temporários que não devem ser salvos na tabela
        unset($data['certificado_verificado']);
        unset($data['data_inicio_certificado']);
        
        $data['cnpj'] = sanitize($data['cnpj']);
        $data['data_abertura'] = $this->normalizeDateToDatabase($data['data_abertura'] ?? null);
        $data['data_situacao_cadastral'] = $this->normalizeDateToDatabase($data['data_situacao_cadastral'] ?? null);
        $data['contract_start_date'] = $this->normalizeDateToDatabase($data['contract_start_date'] ?? null);

        if (! in_array($data['issuer_type'] ?? null, [IssuerTypeEnum::CONDOMINIO->value, IssuerTypeEnum::ASSOCIACAO->value], true)) {
            $data['contract_number'] = null;
            $data['contract_start_date'] = null;
        }

        if (($data['issuer_type'] ?? null) !== IssuerTypeEnum::CONDOMINIO->value) {
            $data['condominium_type'] = null;
            $data['units_count'] = null;
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        // Redirecionar para a listagem de empresas
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Salvar Alterações')
                ->color('primary'),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->requiresConfirmation()
            ->modalHeading('Confirmar Alterações')
            ->modalDescription('Deseja realmente salvar as alterações feitas nesta empresa?')
            ->modalSubmitActionLabel('Sim, Salvar')
            ->modalCancelActionLabel('Cancelar');
    }

    private function normalizeDateToDatabase(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value) === 1) {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
