<?php

namespace App\Filament\Resources\Issuers\Pages;

use App\Filament\Resources\Issuers\IssuerResource;
use App\Services\CnpjJaService;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
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

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $cnpjDetails = [];
        try {
            $cnpjDetails = CnpjJaService::getCnpjDetails($record->cnpj);
        } catch (Exception $e) {
            Notification::make()
                ->title('Erro')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        if (! empty($cnpjDetails)) {
            $data['data_abertura'] = $cnpjDetails['founded'] ?? null;
            $data['email'] = $cnpjDetails['emails'][0]['address'] ?? null;
            $data['telefone'] = $cnpjDetails['phones'][0]['area'] . $cnpjDetails['phones'][0]['number'] ?? null;
            $data['logradouro'] = $cnpjDetails['address']['street'] ?? null;
            $data['numero'] = $cnpjDetails['address']['number'] ?? null;
            $data['complemento'] = $cnpjDetails['address']['details'] ?? null;
            $data['bairro'] = $cnpjDetails['address']['district'] ?? null;
            $data['cidade'] = $cnpjDetails['address']['city'] ?? null;
            $data['uf'] = $cnpjDetails['address']['state'] ?? null;
            $data['cep'] = $cnpjDetails['address']['zip'] ?? null;

            $data['situacao_cadastral'] = $cnpjDetails['status']['text'] ?? null;
            $data['data_situacao_cadastral'] = $cnpjDetails['statusDate'] ?? null;

            $data['main_activity'] = $cnpjDetails['mainActivity'] ?? null;
            $data['side_activities'] = $cnpjDetails['sideActivities'] ?? null;
        }

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
}
