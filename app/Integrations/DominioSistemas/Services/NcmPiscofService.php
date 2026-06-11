<?php

namespace App\Integrations\DominioSistemas\Services;

use App\Models\NcmRestricao;
use Illuminate\Support\Facades\Cache;

/**
 * Verificador de crédito PIS/COFINS por NCM.
 * Implementa as 3 camadas de validação:
 *   Camada 1: etiqueta permite crédito? (cred_piscof)
 *   Camada 2: NCM tem restrição? (tabela_ncm + exclusões)
 *   Camada 3: aplica crédito com CST/aliq corretos
 *
 * Equivalente ao Python: ncm_piscof/verificador.py + ncm_piscof/tabela_ncm.py
 */
class NcmPiscofService
{
    // CST resultante por tipo de restrição
    private const CST_POR_TIPO = [
        'NORMAL' => '50',   // crédito pleno
        'ALIQUOTA_ZERO' => '73',   // direito mas aliq zero
        'MONOFASICO' => '73',   // recolhido na cadeia
        'SUSPENSAO' => '73',   // suspensão
        'ISENCAO' => '73',   // isenção
        'SEM_CREDITO' => '70',   // etiqueta não permite
    ];

    /** @var array|null Cache das regras carregadas */
    private static ?array $regrasCache = null;

    /**
     * Verifica se o NCM tem restrição de crédito PIS/COFINS
     *
     * @param  string  $ncm  Código NCM de 8 dígitos
     * @param  bool  $credPiscof  Flag da etiqueta (Camada 1)
     * @return array{cst: string, tipo: string, aplica: bool, grupo: ?string, fundamento: string, obs: string}
     */
    public function verificar(string $ncm, bool $credPiscof): array
    {
        // --- Camada 1: etiqueta ---
        if (! $credPiscof) {
            return [
                'cst' => '70',
                'tipo' => 'SEM_CREDITO',
                'aplica' => false,
                'grupo' => null,
                'fundamento' => '',
                'obs' => 'Etiqueta/operacao nao permite credito de PIS/COFINS',
            ];
        }

        // --- Camada 2: NCM ---
        $restricao = $this->buscarRestricaoNcm($ncm);
        if ($restricao !== null) {
            $tipo = $restricao['tipo'];

            return [
                'cst' => self::CST_POR_TIPO[$tipo] ?? '73',
                'tipo' => $tipo,
                'aplica' => false, // CST 73: tem direito mas não aplica crédito
                'grupo' => $restricao['grupo'],
                'fundamento' => $restricao['fundamento'] ?? '',
                'obs' => $restricao['obs'] ?? '',
            ];
        }

        // --- Camada 3: sem restrição — crédito normal ---
        return [
            'cst' => '50',
            'tipo' => 'NORMAL',
            'aplica' => true,
            'grupo' => null,
            'fundamento' => '',
            'obs' => '',
        ];
    }

    /**
     * Busca uma restrição de NCM na tabela
     */
    private function buscarRestricaoNcm(string $ncm): ?array
    {
        $ncmNorm = $this->normalizarNcm($ncm);
        $regras = $this->carregarRegras();

        foreach ($regras as $regra) {
            if ($this->matchRegra($ncmNorm, $regra)) {
                return $regra;
            }
        }

        return null;
    }

    /**
     * Verifica se um NCM normalizado corresponde a uma regra
     */
    private function matchRegra(string $ncmNorm, array $regra): bool
    {
        // Verificar exclusões primeiro
        $excluir = $regra['excluir_ncm'] ?? [];
        foreach ($excluir as $exc) {
            if ($ncmNorm === $this->normalizarNcm($exc)) {
                return false;
            }
        }

        $tipo = $regra['tipo_match'];
        $valores = $regra['valor_match'] ?? [];

        if (empty($valores)) {
            // ZFM, drawback — match por operação, não por NCM
            return false;
        }

        switch ($tipo) {
            case 'exato':
                $normalizados = array_map(fn ($v) => $this->normalizarNcm($v), $valores);

                return in_array($ncmNorm, $normalizados, true);

            case 'prefixo':
            case 'capitulo':
                foreach ($valores as $v) {
                    $prefixo = $this->normalizarPrefixo($v);
                    if (str_starts_with($ncmNorm, $prefixo)) {
                        return true;
                    }
                }

                return false;

            case 'faixa_prefixo':
                foreach ($valores as $item) {
                    if (is_array($item) && count($item) === 2) {
                        $ini = $this->normalizarPrefixo($item[0]);
                        $fim = $this->normalizarPrefixo($item[1]);
                        $ncm4 = mb_substr($ncmNorm, 0, 4);
                        if ($ini <= $ncm4 && $ncm4 <= $fim) {
                            return true;
                        }
                    } else {
                        $prefixo = $this->normalizarPrefixo(is_string($item) ? $item : '');
                        if (str_starts_with($ncmNorm, $prefixo)) {
                            return true;
                        }
                    }
                }

                return false;

            default:
                return false;
        }
    }

    /**
     * Carrega as regras do banco com cache
     */
    private function carregarRegras(): array
    {
        if (self::$regrasCache === null) {
            self::$regrasCache = Cache::remember('ncm_restricoes_all', now()->addDay(), function () {
                return NcmRestricao::all()->toArray();
            });
        }

        return self::$regrasCache;
    }

    private function normalizarNcm(string $ncm): string
    {
        return str_pad(preg_replace('/\D/', '', $ncm) ?? '', 8, '0', STR_PAD_LEFT);
    }

    private function normalizarPrefixo(string $valor): string
    {
        return preg_replace('/\D/', '', $valor) ?? '';
    }
}
