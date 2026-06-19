<?php

namespace App\Services\ValidacaoTributaria;

use App\Models\Issuer;
use App\Models\NfeValidacaoTributaria;
use App\Models\NotaFiscalEletronica;
use App\Services\ValidacaoTributaria\Contracts\RegraValidacaoInterface;
use App\Services\ValidacaoTributaria\DTO\ResultadoValidacao;
use App\Services\ValidacaoTributaria\Regras\ValidaCalculoCofins;
use App\Services\ValidacaoTributaria\Regras\ValidaCalculoIcms;
use App\Services\ValidacaoTributaria\Regras\ValidaCalculoIpi;
use App\Services\ValidacaoTributaria\Regras\ValidaCalculoPis;
use App\Services\ValidacaoTributaria\Regras\ValidaCstVsRegime;
use App\Services\ValidacaoTributaria\Regras\ValidaDifal;
use App\Services\ValidacaoTributaria\Regras\ValidaTotaisVsProdutos;

class ValidacaoTributariaService
{
    private const TOLERANCIA_PADRAO = 0.01;

    /** @var array<string, RegraValidacaoInterface> */
    private array $regras = [];

    public function __construct()
    {
        $tolerancia = (float) config('validacao_tributaria.tolerancia', self::TOLERANCIA_PADRAO);

        $this->regras = [
            'cst_vs_regime' => new ValidaCstVsRegime,
            'calculo_icms' => new ValidaCalculoIcms(tolerancia: $tolerancia),
            'calculo_ipi' => new ValidaCalculoIpi(tolerancia: $tolerancia),
            'calculo_pis' => new ValidaCalculoPis(tolerancia: $tolerancia),
            'calculo_cofins' => new ValidaCalculoCofins(tolerancia: $tolerancia),
            'difal' => new ValidaDifal,
            'totais_vs_produtos' => new ValidaTotaisVsProdutos(tolerancia: $tolerancia),
        ];
    }

    public function getTolerancia(): float
    {
        return (float) config('validacao_tributaria.tolerancia', self::TOLERANCIA_PADRAO);
    }

    /**
     * Valida uma NF-e completa.
     *
     * @return ResultadoValidacao[]
     */
    public function validar(NotaFiscalEletronica $nfe, ?Issuer $issuer = null): array
    {
        $issuer = $issuer ?? currentIssuer();
        $resultados = [];

        $produtos = $nfe->produtos;
        $nota = $this->extrairDadosNota($nfe);

        foreach ($this->getRegrasAtivas($issuer) as $nome => $regra) {
            $resultados = array_merge($resultados, $regra->validar($produtos, $nota, $issuer));
        }

        return $resultados;
    }

    /**
     * Valida e persiste os resultados no banco.
     *
     * @return int Quantidade de validações pendentes geradas
     */
    public function validarEPersistir(NotaFiscalEletronica $nfe, ?Issuer $issuer = null): int
    {
        $issuer = $issuer ?? currentIssuer();
        $resultados = $this->validar($nfe, $issuer);

        $this->limparValidacoesAnteriores($nfe, $issuer);

        $dados = [];
        foreach ($resultados as $r) {
            $dados[] = [
                'nfe_id' => $nfe->id,
                'tenant_id' => $issuer->tenant_id,
                'issuer_id' => $issuer->id,
                'regra' => $r->regra,
                'tipo_imposto' => $r->tipoImposto,
                'n_item' => $r->nItem,
                'severidade' => $r->severidade->value,
                'mensagem' => $r->mensagem,
                'valor_esperado' => $r->valorEsperado,
                'valor_encontrado' => $r->valorEncontrado,
                'status' => 'pendente',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($dados !== []) {
            NfeValidacaoTributaria::insert($dados);
        }

        return count($dados);
    }

    private function limparValidacoesAnteriores(NotaFiscalEletronica $nfe, Issuer $issuer): void
    {
        NfeValidacaoTributaria::porNfe($nfe->id)
            ->where('issuer_id', $issuer->id)
            ->where('status', 'pendente')
            ->delete();
    }

    /**
     * @return array<string, RegraValidacaoInterface>
     */
    private function getRegrasAtivas(Issuer $issuer): array
    {
        $regrasConfig = config('validacao_tributaria.regras', []);
        $porIssuer = config('validacao_tributaria.por_issuer', []);

        $regrasIssuer = $porIssuer[$issuer->id] ?? [];

        $ativas = [];
        foreach ($this->regras as $nome => $regra) {
            $config = $regrasConfig[$nome] ?? [];
            $padraoAtivo = $config['ativo'] ?? true;
            $ativo = $regrasIssuer[$nome] ?? $padraoAtivo;

            if ($ativo) {
                $ativas[$nome] = $regra;
            }
        }

        return $ativas;
    }

    /**
     * @return array<string, mixed>
     */
    private function extrairDadosNota(NotaFiscalEletronica $nfe): array
    {
        return [
            'id' => $nfe->id,
            'chave' => $nfe->chave,
            'nNF' => $nfe->nNF,
            'serie' => $nfe->serie,
            'vNfe' => (float) ($nfe->vNfe ?? 0),
            'vProd' => (float) ($nfe->vProd ?? 0),
            'vBC' => (float) ($nfe->vBC ?? 0),
            'vICMS' => (float) ($nfe->vICMS ?? 0),
            'vBCST' => (float) ($nfe->vBCST ?? 0),
            'vST' => (float) ($nfe->vST ?? 0),
            'vIPI' => (float) ($nfe->vIPI ?? 0),
            'vPIS' => (float) ($nfe->vPIS ?? 0),
            'vCOFINS' => (float) ($nfe->vCOFINS ?? 0),
            'vICMSUFDest' => (float) ($nfe->vICMSUFDest ?? 0),
            'vFrete' => (float) ($nfe->vFrete ?? 0),
            'vSeg' => (float) ($nfe->vSeg ?? 0),
            'vDesc' => (float) ($nfe->vDesc ?? 0),
            'vOutro' => (float) ($nfe->vOutro ?? 0),
            'vFCP' => (float) ($nfe->vFCP ?? 0),
            'vTotTrib' => (float) ($nfe->vTotTrib ?? 0),
            'emitente_cnpj' => $nfe->emitente_cnpj,
            'emitente_uf' => $nfe->enderEmit_UF ?? '',
            'destinatario_uf' => $nfe->enderDest_UF ?? '',
            'destinatario_cnpj' => $nfe->destinatario_cnpj,
            'tpNf' => $nfe->tpNf,
            'modFrete' => $nfe->modFrete,
            'data_emissao' => $nfe->data_emissao,
        ];
    }
}
