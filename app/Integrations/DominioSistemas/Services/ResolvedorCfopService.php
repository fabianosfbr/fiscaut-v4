<?php

namespace App\Integrations\DominioSistemas\Services;

use App\Models\EntradaAcumuladorEquivalente;
use App\Models\EntradasImpostosEquivalente;
use App\Models\GrupoEntradaCfopEquivalente;
use App\Models\Issuer;
use Illuminate\Support\Facades\Cache;

/**
 * Resolve CFOP de entrada e acumulador para uma NF com base nas tags
 * e nas tabelas de equivalência do banco.
 *
 * Equivalente ao Python: resolver_cfop() + CFOP_DIRETO + tabela_etiquetas
 */
class ResolvedorCfopService
{
    private int $issuerId;

    private int $tenantId;

    private string $ufEmpresa;

    private string $cnpjEmpresa;

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

    public function resolverCfop(int $tagId, string $cfopSaida, string $ufEmitente, int $tpNf, string $emitenteCnpj): string
    {
        $cfopSaida = str_pad((string) $cfopSaida, 4, '0', STR_PAD_LEFT);
        $tipo = $this->determinarTipoNota($tpNf, $emitenteCnpj);
        $grupos = $this->carregarGruposCfop();
        $dentro = strtoupper($ufEmitente) === $this->ufEmpresa;
        $st = $this->temSt($cfopSaida);

        foreach ($grupos as $grupo) {
            $tagsNoGrupo = is_array($grupo['tags']) ? $grupo['tags'] : json_decode($grupo['tags'] ?? '[]', true);
            if (! in_array($tagId, $tagsNoGrupo)) {
                continue;
            }

            $cfops = $grupo['cfopsEquivalentes'] ?? [];
            foreach ($cfops as $cfop) {
                if ((string) ($cfop['tipo'] ?? '') !== (string) $tipo) {
                    continue;
                }
                $cfopEntrada = $cfop['cfop_entrada'] ?? '';
                if (empty($cfopEntrada)) {
                    continue;
                }
                $ufDiferente = $cfop['uf_diferente'] ?? null;
                if ($ufDiferente !== null && $ufDiferente !== '') {
                    $ufsPermitidas = array_map('trim', explode(',', $ufDiferente));
                    if (in_array($this->ufEmpresa, $ufsPermitidas)) {
                        continue;
                    }
                }

                return str_pad($cfopEntrada, 4, '0', STR_PAD_LEFT);
            }
        }

        return $this->resolverCfopFallback($tagId, $cfopSaida, $dentro, $st);
    }

    public function resolverAcumulador(int $tagId, string $cfopEntrada): int
    {
        $acumuladores = $this->carregarAcumuladores();
        $cfopEntrada = str_pad((string) $cfopEntrada, 4, '0', STR_PAD_LEFT);

        foreach ($acumuladores as $ac) {
            if ((int) ($ac['etiqueta_entrada'] ?? 0) !== $tagId) {
                continue;
            }
            $cfopsPermitidos = is_array($ac['cfops'] ?? null) ? $ac['cfops'] :
                (json_decode($ac['cfops'] ?? '[]', true) ?: []);
            if (! empty($cfopsPermitidos)) {
                if (in_array($cfopEntrada, $cfopsPermitidos)) {
                    $valores = is_array($ac['valores'] ?? null) ? $ac['valores'] :
                        (json_decode($ac['valores'] ?? '[]', true) ?: []);

                    return isset($valores[0]) ? (int) $valores[0] : 8000;
                }
            } else {
                $valores = is_array($ac['valores'] ?? null) ? $ac['valores'] :
                    (json_decode($ac['valores'] ?? '[]', true) ?: []);

                return isset($valores[0]) ? (int) $valores[0] : 8000;
            }
        }

        return 8000;
    }

    public function isZeraIcms(int $tagId): bool
    {
        foreach ($this->carregarImpostos() as $imp) {
            if ((int) ($imp['tag_id'] ?? 0) === $tagId) {
                return (bool) ($imp['status_icms'] ?? false);
            }
        }

        return false;
    }

    public function isZeraIpi(int $tagId): bool
    {
        foreach ($this->carregarImpostos() as $imp) {
            if ((int) ($imp['tag_id'] ?? 0) === $tagId) {
                return (bool) ($imp['status_ipi'] ?? false);
            }
        }

        return false;
    }

    public function getBaseCredito(int $tagId): string
    {
        foreach ($this->carregarImpostos() as $imp) {
            if ((int) ($imp['tag_id'] ?? 0) === $tagId) {
                return (string) ($imp['base_credito'] ?? '');
            }
        }

        return '';
    }

    private function determinarTipoNota(int $tpNf, string $emitenteCnpj): int
    {
        if ($tpNf === 1) {
            return 1;
        }

        return 0;
    }

    private function temSt(string $cfopSaida): bool
    {
        return in_array($cfopSaida, [
            '5401', '5402', '5403', '5405', '5407',
            '6401', '6402', '6403', '6405', '6407',
        ]);
    }

    private function carregarGruposCfop(): array
    {
        $key = "resolvedor_cfop_grupos_{$this->issuerId}_{$this->tenantId}";
        if (! isset($this->cfopCache[$key])) {
            $this->cfopCache[$key] = Cache::remember($key, now()->addDay(), function () {
                return GrupoEntradaCfopEquivalente::where('issuer_id', $this->issuerId)
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
        if (! isset($this->acumuladorCache[$key])) {
            $this->acumuladorCache[$key] = Cache::remember($key, now()->addDay(), function () {
                return EntradaAcumuladorEquivalente::where('issuer_id', $this->issuerId)
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
        if (! isset($this->impostosCache[$key])) {
            $this->impostosCache[$key] = Cache::remember($key, now()->addDay(), function () {
                return EntradasImpostosEquivalente::where('issuer_id', $this->issuerId)
                    ->where('tenant_id', $this->tenantId)
                    ->get()
                    ->toArray();
            });
        }

        return $this->impostosCache[$key];
    }

    private function resolverCfopFallback(int $tagId, string $cfopSaida, bool $dentro, bool $st): string
    {
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

        $prefixo = substr($cfopSaida, 0, 1);
        if ($prefixo === '1' || $prefixo === '2') {
            return $dentro ? ($st ? '1401' : '1101') : ($st ? '2401' : '2101');
        }

        if ($prefixo === '6') {
            return '2'.substr($cfopSaida, 1);
        }

        return '1101';
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
