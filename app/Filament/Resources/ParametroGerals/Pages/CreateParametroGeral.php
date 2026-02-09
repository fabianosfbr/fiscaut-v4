<?php

namespace App\Filament\Resources\ParametroGerals\Pages;

use App\Filament\Resources\ParametroGerals\ParametroGeralResource;
use App\Models\HistoricoContabil;
use App\Models\PlanoDeConta;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateParametroGeral extends CreateRecord
{
    protected static string $resource = ParametroGeralResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $issuerId = Auth::user()->currentIssuer->id;
        $conta_contabil = PlanoDeConta::where('codigo', $data['conta_contabil'])->where('issuer_id', $issuerId)->first();

        $historico = HistoricoContabil::where('codigo', $data['codigo_historico'])->where('issuer_id', $issuerId)->first();

        $data['conta_contabil'] = $conta_contabil->id;

        $descricao_conta_contabil = [
            'codigo' => $conta_contabil->codigo,
            'descricao' => $conta_contabil->nome,
        ];

        $descricao_historico = [
            'id' => $historico->id,
            'descricao' => $historico->descricao,
        ];

        $data['descricao_conta_contabil'] = $descricao_conta_contabil;
        $data['descricao_historico'] = $descricao_historico;
        $data['codigo'] = $data['params'];
        $data['descricao'] = $data['params'];
        $data['tipo'] = 'debito';

        $data['issuer_id'] = $issuerId;

        return $data;
    }

    protected function getRedirectUrl(): string
    {

        return $this->getResource()::getUrl('index');
    }

    public function getHeading(): string
    {
        return 'Criar Parâmetro Geral';
    }
}
