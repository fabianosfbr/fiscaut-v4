<?php

namespace App\Filament\Resources\Issuers\Pages;

use App\Filament\Resources\Issuers\IssuerResource;
use App\Models\Issuer;
use App\Models\IssuerUserPermission;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
        $data['data_abertura'] = $this->normalizeDateToDatabase($data['data_abertura'] ?? null);
        $data['data_situacao_cadastral'] = $this->normalizeDateToDatabase($data['data_situacao_cadastral'] ?? null);
        $data['contract_start_date'] = $this->normalizeDateToDatabase($data['contract_start_date'] ?? null);

        return $data;
    }

    /**
     * Handle the record creation process.
     */
    protected function handleRecordCreation(array $data): Model
    {
        
        return DB::transaction(function () use ($data) {
            $user = Auth::user();

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
