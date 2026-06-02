<?php

namespace App\Integrations\DominioSistemas\Services;

use App\Models\EntradaCfopEquivalente;
use App\Models\EntradaAcumuladorEquivalente;
use App\Models\EntradasImpostosEquivalente;
use App\Models\Issuer;
use Illuminate\Support\Facades\Cache;

/**
 * Resolve CFOP de entrada e acumulador para uma NF com base nas tags
 * e nas tabelas de equivalência do banco.
 *
 * Equivalente ao Python: resolver_cfop() + CFOP_DIRETO + tábea_etiquetas
 */
class ResolvedorCfopService
{
    private int $issuerId;
    private int $tenantId;
    private string $ufEmpresa;
    private string $cnpjEmpresa;

    /** @var array cache de equivalências carregadas */
    private array $cfopCache = [];
    private array $acumuladorCache = [];
    private array $impostosCache = [];

    public function __construct(Issuer $issuer)
    {
        $this->issuerId = $issuer->id;
        $this->tenantId = $issuer->tenant_id;
        $this->ufEmpresa = 'SP';
        $this->cnpjEmpresa = $issuer->cnpj;
    }

    /**
     * Resolve o CFOP de entrada para uma combinação de tag + cfop_saida + UF
     * 
     * 1. Tenta match direto em EntradaCfopEquivalente (grupo + cfop_saida)
     * 2. Fallback para regra de família (compra, imobilizado, uso_consumo, etc.)
     */
    public function resolverCfop(
        int $tagId,
        string $cfopSaida,
        string $ufEmitente,
        int $tpNf,
        string $emitenteCnpj,
    ): string {
        $cfopSaida = str_pad((string) $cfopSaida, 4, '0', STR_PAD_LEFT);
        $tipo = $this->determinarTipoNota($tpNf, $emitenteCnpj);

        // 1. Buscar equivalências para esta tag
        $grupos = $this->carregarGruposCfop();
        $dentro = strtoupper($ufEmitente) === $this->ufEmpresa;
        $st = $this->temSt($cfopSaida);

        // Procura nas equivalências por tipo
        foreach ($grupos as $grupo) {
            $tagsNoGrupo = is_array($grupo['tags']) ? $grupo['tags'] : json_decode($grupo['tags'] ?? '[]', true);
            if (!in_array($tagId, $tagsNoGrupo)) {
                continue;
            }

            $cfops = $grupo['cfopsEquivalentes'] ?? [];
            foreach ($cfops as $cfop) {
                // Verifica se o tipo corresponde
                if ((string) ($cfop['tipo'] ?? '') !== (string) $tipo) {
                    continue;
                }

                // Verifica cfop_entrada
                $cfopEntrada = $cfop['cfop_entrada'] ?? '';
                if (empty($cfopEntrada)) {
                    continue;
                }

                // Verifica se tem restrição de UF diferente
                $ufDiferente = $cfop['uf_diferente'] ?? null;
                if ($ufDiferente !== null && $ufDiferente !== '') {
                    $ufsPermitidas = array_map('trim', explode(',', $ufDiferente));
                    if (in_array($this->ufEmpresa, $ufsPermitidas)) {
                        continue; // UF diferente, não aplica
                    }
                }

                return str_pad($cfopEntrada, 4, '0', STR_PAD_LEFT);
            }
        }

        // 2. Fallback: regras genéricas por família (compra, imobilizado, etc.)
        return $this->resolverCfopFallback($tagId, $cfopSaida, $dentro, $st);
    }

    /**
     * Obtém o acumulador para uma tag + CFOP
     */
    public function resolverAcumulador(int $tagId, string $cfopEntrada): int
    {
        $acumuladores = $this->carregarAcumuladores();
        $cfopEntrada = str_pad((string) $cfopEntrada, 4, '0', STR_PAD_LEFT);

        // Procura por tag_id
        foreach ($acumuladores as $ac) {
            if ((int) ($ac['etiqueta_entrada'] ?? 0) !== $tagId) {
                continue;
            }

            // Verifica se tem restrição de CFOPs
            $cfopsPermitidos = is_array($ac['cfops'] ?? null) ? $ac['cfops'] : 
                (json_decode($ac['cfops'] ?? '[]', true) ?: []);

            if (!empty($cfopsPermitidos)) {
                // Se tem CFOPs definidos, só aplica se o CFOP estiver na lista
                if (in_array($cfopEntrada, $cfopsPermitidos)) {
                    $valores = is_array($ac['valores'] ?? null) ? $ac['valores'] :
                        (json_decode($ac['valores'] ?? '[]', true) ?: []);
                    return isset($valores[0]) ? (int) $valores[0] : 8000;
                }
            } else {
                // Sem restrição de CFOP, aplica sempre
                $valores = is_array($ac['valores'] ?? null) ? $ac['valores'] :
                    (json_decode($ac['valores'] ?? '[]', true) ?: []);
                return isset($valores[0]) ? (int) $valores[0] : 8000;
            }
        }

        return 8000; // fallback
    }

    /**
     * Verifica se ICMS deve ser zerado para uma tag
     */
    public function isZeraIcms(int $tagId): bool
    {
        $impostos = $this->carregarImpostos();
        foreach ($impostos as $imp) {
            if ((int) ($imp['tag_id'] ?? 0) === $tagId) {
                return (bool) ($imp['status_icms'] ?? false);
            }
        }
        return false; // padrão: não zera
    }

    /**
     * Verifica se IPI deve ser zerado para uma tag
     */
    public function isZeraIpi(int $tagId): bool
    {
        $impostos = $this->carregarImpostos();
        foreach ($impostos as $imp) {
            if ((int) ($imp['tag_id'] ?? 0) === $tagId) {
                return (bool) ($imp['status_ipi'] ?? false);
            }
        }
        return false; // padrão: não zera
    }

    private function determinarTipoNota(int $tpNf, string $emitenteCnpj): int
    {
        // tipo=1: entrada de terceiros (empresa é destinatário, tpNf=1)
        if ($tpNf === 1) {
            return 1;
        }
        // tipo=0: entrada própria (empresa é emitente OU destinatário + emitente != empresa + tpNf=0)
        return 0;
    }

    private function temSt(string $cfopSaida): bool
    {
        // CFOPs que indicam Substituição Tributária
        $cfopsComSt = [
            '5401', '5402', '5403', '5405', '5407',
            '6401', '6402', '6403', '6405', '6407',
        ];
        return in_array($cfopSaida, $cfopsComSt);
    }

    private function carregarGruposCfop(): array
    {
        $key = "resolvedor_cfop_grupos_{$this->issuerId}_{$this->tenantId}";
        if (!isset($this->cfopCache[$key])) {
            $this->cfopCache[$key] = Cache::remember($key, now()->addDay(), function () {
                return \App\Models\GrupoEntradaCfopEquivalente::where('issuer_id', $this->issuerId)
                    ->where('tenant_id', $this->tenantId)
                    ->with('cfopsEquivalentes')
                    ->get()
                    ->toArray();
            });
        }
        return $this->cfopCache[$key];
    }

    private function carregarAcumuladores(): array
    {
        $key = "resolvedor_acumulador_{$this->issuerId}_{$this->tenantId}";
        if (!isset($this->acumuladorCache[$key])) {
            $this->acumuladorCache[$key] = Cache::remember($key, now()->addDay(), function () {
                return \App\Models\EntradaAcumuladorEquivalente::where('issuer_id', $this->issuerId)
                    ->where('tenant_id', $this->tenantId)
                    ->get()
                    ->toArray();
            });
        }
        return $this->acumuladorCache[$key];
    }

    private function carregarImpostos(): array
    {
        $key = "resolvedor_impostos_{$this->issuerId}_{$this->tenantId}";
        if (!isset($this->impostosCache[$key])) {
            $this->impostosCache[$key] = Cache::remember($key, now()->addDay(), function () {
                return \App\Models\EntradasImpostosEquivalente::where('issuer_id', $this->issuerId)
                    ->where('tenant_id', $this->tenantId)
                    ->get()
                    ->toArray();
            });
        }
        return $this->impostosCache[$key];
    }

    /**
     * Fallback: quando não encontra equivalência no banco, usa regras genéricas
     * baseadas na família da tag (derivada do nome/tipo da tag)
     */
    private function resolverCfopFallback(int $tagId, string $cfopSaida, bool $dentro, bool $st): string
    {
        // CFOPs diretos (industrializacao, retorno, etc.)
        $cfopDireto = [
            '5124' => '1124', '6124' => '2124',
            '5125' => '1125', '6125' => '2125',
            '5901' => '1901', '6901' => '2901',
            '5902' => '1902', '6902' => '2902',
            '5903' => '1903', '6903' => '2903',
            '5201' => '1201', '5202' => '1202',
            '6201' => '2201', '6202' => '2202',
            '5949' => '1949', '6949' => '2949',
            '5908' => '1908', '6908' => '2908',
            '5911' => '1911', '6911' => '2911',
            '5915' => '1915', '6915' => '2915',
            '5656' => '1653',
            '3101' => '3101', '3102' => '3102',
        ];

        if (isset($cfopDireto[$cfopSaida])) {
            return $cfopDireto[$cfopSaida];
        }

        // Regra genérica baseada no CFOP de saída
        // Compra: 1xxx/2xxx
        $prefixo = substr($cfopSaida, 0, 1);
        if ($prefixo === '1' || $prefixo === '2') {
            // Operação dentro/fora do estado
            if ($dentro) {
                return $st ? '1401' : '1101';
            } else {
                return $st ? '2401' : '2101';
            }
        }

        // CFOP 6xxx (interestadual) -> 2xxx
        if ($prefixo === '6') {
            $sufixo = substr($cfopSaida, 1);
            return '2' . $sufixo;
        }

        return '1101'; // fallback final
    }

    public function getCnpjEmpresa(): string
    {
        return $this->cnpjEmpresa;
    }

    public function getUfEmpresa(): string
    {
        return $this->ufEmpresa;
    }
}