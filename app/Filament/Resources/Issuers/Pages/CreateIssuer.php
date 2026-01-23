<?php

namespace App\Filament\Resources\Issuers\Pages;

use App\Filament\Resources\Issuers\IssuerResource;
use App\Models\Issuer;
use App\Models\IssuerUserPermission;
use App\Models\User;
use App\Services\CnpjJaService;
use Exception;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class CreateIssuer extends CreateRecord
{
    protected static string $resource = IssuerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        // Adicionar tenant_id do usuário logado
        $data['tenant_id'] = $user->tenant_id;

        // Adicionar valores padrão para campos opcionais
        $data['is_enabled'] = true;
        $data['ambiente'] = 2; // Ambiente de homologação por padrão

        // Criptografar apenas a senha (certificado_content já vem criptografado do CertificateService)
        if (! empty($data['senha_certificado'])) {
            $data['senha_certificado'] = Crypt::encrypt($data['senha_certificado']);
        }

        // Remover campos temporários que não devem ser salvos na tabela
        unset($data['certificado_verificado']);
        unset($data['data_inicio_certificado']);

        $data['cnpj'] = sanitize($data['cnpj']);

        return $data;
    }

    /**
     * Handle the record creation process.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();

            $cnpjDetails = [];
            try {
                $cnpjDetails = CnpjJaService::getCnpjDetails($data['cnpj']);
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
                $data['telefone'] = $cnpjDetails['phones'][0]['area'].$cnpjDetails['phones'][0]['number'] ?? null;
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

            // 1. Criar a empresa
            $issuer = Issuer::create($data);

            // 2. Criar permissão para o usuário logado acessar esta empresa
            IssuerUserPermission::create([
                'user_id' => $user->id,
                'issuer_id' => $issuer->id,
                'active' => true,
                'expires_at' => null, // Sem data de expiração
            ]);

            // 3. Definir esta empresa como empresa atual do usuário
            User::where('id', $user->id)->update([
                'issuer_id' => $issuer->id,
                'issuer_cnpj' => $issuer->cnpj,
            ]);

            return $issuer;
        });
    }
}
