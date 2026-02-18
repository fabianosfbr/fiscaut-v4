<?php

namespace App\Models;

use App\Enums\StatusManifestoNfeEnum;
use App\Enums\StatusNfeEnum;
use App\Models\Traits\HasTags;
use App\Services\Xml\XmlReaderService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class NotaFiscalEletronica extends Model
{
    use HasTags;

    protected $table = 'nfes';

    protected $guarded = ['id'];

    protected $casts = [
        'data_emissao' => 'datetime',
        'data_entrada' => 'datetime',
        'status_nota' => StatusNfeEnum::class,
        'status_manifestacao' => StatusManifestoNfeEnum::class,
        'carta_correcao' => 'array',
        'nfe_chave' => 'array',
        'difal' => 'array',
        'cobranca' => 'array',
        'parcela' => 'array',
        'cfops' => 'array',
    ];

    public function nfeReferenciada()
    {
        return $this->hasMany(NfeReferenciada::class, 'nfe_id', 'id');
    }

    public function apurada()
    {
        return $this->hasOne(NfeApurada::class, 'nfe_id')->where('issuer_id', Auth::user()->currentIssuer->id);
    }

    public function isApuradaParaEmpresa(Issuer $issuer): bool
    {
        $apuracao = $this->apurada()
            ->where('issuer_id', $issuer->id)
            ->latest('id')
            ->first();

        if ($apuracao !== null) {
            return (bool) $apuracao->status;
        }

        return (bool) false;
    }

    public function toggleApuracao(Issuer $issuer): bool
    {
        $apuracao = $this->apurada()
            ->where('issuer_id', $issuer->id)
            ->latest('id')
            ->first();

        if ($apuracao === null) {
            $this->apurada()->create([
                'issuer_id' => $issuer->id,
                'status' => true,
            ]);

            return true;
        }

        $newStatus = ! $apuracao->status;

        $apuracao->update([
            'status' => $newStatus,
        ]);

        return $newStatus;
    }

    public function scopeEntradasTerceiros($query, $issuer = null)
    {
        $issuer = $issuer ?? Auth::user()->currentIssuer;

        return $query->where('destinatario_cnpj', $issuer->cnpj)
            ->where('emitente_cnpj', '<>', $issuer->cnpj)
            ->where('tpNf', 1);
    }

    public function scopeEntradasProprias($query, $issuer = null)
    {
        $issuer = $issuer ?? Auth::user()->currentIssuer;

        return $query->where('emitente_cnpj', $issuer->cnpj)
            ->where('tpNf', '0');
    }

    public function scopeEntradasPropriasTerceiros($query, $issuer = null)
    {
        $issuer = $issuer ?? Auth::user()->currentIssuer;

        return $query->where('destinatario_cnpj', $issuer->cnpj)
            ->where('emitente_cnpj', '<>', $issuer->cnpj)
            ->where('tpNf', '0');
    }

    public function getProdutosAttribute(): array
    {
        $xml = $this->extrairXmlComoString();
        if ($xml === null) {
            return [];
        }

        $data = (new XmlReaderService)->read($xml);
        $det = $data['nfeProc']['NFe']['infNFe']['det'] ?? null;

        $detList = $this->normalizeList($det);
        if ($detList === []) {
            return [];
        }

        return array_values(array_map(function ($detItem) {
            if (! is_array($detItem)) {
                return [];
            }

            $prod = $detItem['prod'] ?? [];
            $imposto = $detItem['imposto'] ?? [];

            return [
                'nItem' => $detItem['@attributes']['nItem'] ?? null,
                'cProd' => $prod['cProd'] ?? null,
                'xProd' => $prod['xProd'] ?? null,
                'NCM' => $prod['NCM'] ?? null,
                'CFOP' => $prod['CFOP'] ?? null,
                'uCom' => $prod['uCom'] ?? null,
                'qCom' => $prod['qCom'] ?? null,
                'vUnCom' => $prod['vUnCom'] ?? null,
                'vProd' => $prod['vProd'] ?? null,
                'vDesc' => $prod['vDesc'] ?? null,
                'vFrete' => $prod['vFrete'] ?? null,
                'vSeg' => $prod['vSeg'] ?? null,
                'cEAN' => $prod['cEAN'] ?? null,
                'cEANTrib' => $prod['cEANTrib'] ?? null,
                'impostos' => [
                    'vBC' => searchValueInArray($imposto, 'vBC') ?? 0.0,
                    'pICMS' => searchValueInArray($imposto, 'pICMS') ?? 0.0,
                    'vICMS' => searchValueInArray($imposto, 'vICMS') ?? 0.0,
                    'pST' => searchValueInArray($imposto, 'pST') ?? 0.0,
                    'vBCSTRet' => searchValueInArray($imposto, 'vBCSTRet') ?? 0.0,
                    'vICMSSubstituto' => searchValueInArray($imposto, 'vICMSSubstituto') ?? 0.0,                    
                    'vICMSSTRet' => searchValueInArray($imposto, 'vICMSSTRet') ?? 0.0,
                    'vIPI' => searchValueInArray($imposto, 'vIPI') ?? 0.0,
                    'vPIS' => searchValueInArray($imposto, 'vPIS') ?? 0.0,
                    'pPIS' => searchValueInArray($imposto, 'pPIS') ?? 0.0,
                    'vCOFINS' => searchValueInArray($imposto, 'vCOFINS') ?? 0.0,
                    'pCOFINS' => searchValueInArray($imposto, 'pCOFINS') ?? 0.0,
                ],
            ];
        }, $detList));
    }

    public function getEnderecoDestinatarioCompletoAttribute(): string
    {
        $xml = $this->extrairXmlComoString();
        if ($xml === null) {
            return '';
        }

        $data = (new XmlReaderService)->read($xml);

        $ender = $data['nfeProc']['NFe']['infNFe']['dest']['enderDest'] ?? [];
        $logradouro = $ender['xLgr'] ?? null;
        $numero = $ender['nro'] ?? null;
        $complemento = $ender['xCpl'] ?? null;
        $bairro = $ender['xBairro'] ?? null;
        $municipio = $ender['xMun'] ?? null;
        $uf = $ender['UF'] ?? null;
        $cep = $ender['CEP'] ?? null;

        $endereco = $this->buildEnderecoCompletoFromFields($logradouro, $numero, $complemento, $bairro);

        $cidadeUf = trim(($municipio ?? '').($uf ? '/'.$uf : ''));
        if ($cidadeUf !== '') {
            $endereco = trim($endereco);
            $endereco .= ($endereco !== '' ? ' - ' : '').$cidadeUf;
        }

        $cepFormatado = $this->formatCep($cep);
        if ($cepFormatado !== '') {
            $endereco = trim($endereco);
            $endereco .= ($endereco !== '' ? ' - ' : '').'CEP: '.$cepFormatado;
        }

        return trim($endereco);

        return $endereco;
    }

    public function getEnderecoEmitenteCompletoAttribute(): string
    {
        $xml = $this->extrairXmlComoString();
        if ($xml === null) {
            return '';
        }

        $data = (new XmlReaderService)->read($xml);

        $ender = $data['nfeProc']['NFe']['infNFe']['emit']['enderEmit'] ?? [];
        $logradouro = $ender['xLgr'] ?? null;
        $numero = $ender['nro'] ?? null;
        $complemento = $ender['xCpl'] ?? null;
        $bairro = $ender['xBairro'] ?? null;
        $municipio = $ender['xMun'] ?? null;
        $uf = $ender['UF'] ?? null;
        $cep = $ender['CEP'] ?? null;

        $endereco = $this->buildEnderecoCompletoFromFields($logradouro, $numero, $complemento, $bairro);

        $cidadeUf = trim(($municipio ?? '').($uf ? '/'.$uf : ''));
        if ($cidadeUf !== '') {
            $endereco = trim($endereco);
            $endereco .= ($endereco !== '' ? ' - ' : '').$cidadeUf;
        }

        $cepFormatado = $this->formatCep($cep);
        if ($cepFormatado !== '') {
            $endereco = trim($endereco);
            $endereco .= ($endereco !== '' ? ' - ' : '').'CEP: '.$cepFormatado;
        }

        return trim($endereco);

        return $endereco;
    }

    private function buildEnderecoCompletoFromFields(
        ?string $logradouro,
        ?string $numero,
        ?string $complemento,
        ?string $bairro,
    ): string {
        $endereco = trim((string) $logradouro);

        $numero = trim((string) $numero);
        if ($numero !== '') {
            $endereco .= ($endereco !== '' ? ', ' : '').$numero;
        }

        $complemento = trim((string) $complemento);
        if ($complemento !== '') {
            $endereco .= ($endereco !== '' ? ' - ' : '').$complemento;
        }

        $bairro = trim((string) $bairro);
        if ($bairro !== '') {
            $endereco .= ($endereco !== '' ? ' - ' : '').$bairro;
        }

        return trim($endereco);
    }

    private function formatCep(?string $cep): string
    {
        $cep = preg_replace('/\D+/', '', (string) $cep) ?? '';
        if ($cep === '') {
            return '';
        }

        if (strlen($cep) !== 8) {
            return $cep;
        }

        return substr($cep, 0, 5).'-'.substr($cep, 5, 3);
    }

    private function extrairXmlComoString(): ?string
    {
        $raw = $this->xml ?? null;
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $uncompressed = @gzuncompress($raw);
        if (is_string($uncompressed) && $uncompressed !== '') {
            return $uncompressed;
        }

        return $raw;
    }

    private function normalizeList($value): array
    {
        if ($value === null) {
            return [];
        }

        if (! is_array($value)) {
            return [$value];
        }

        if ($value === []) {
            return [];
        }

        $keys = array_keys($value);
        $isList = $keys === range(0, count($keys) - 1);

        if ($isList) {
            return $value;
        }

        return [$value];
    }

    public function retag(string $tag)
    {
        $this->untag();
        $this->tag($tag, $this->vNfe);
    }

    public function calcularDifalProdutos(): array
    {
        $resultado = [];
        $produtos = $this->produtos ?? [];

        $totalDifal = (float) ($this->vICMSUFDest ?? 0.0);

        if ($totalDifal > 0.0) {
            $bases = [];
            $sumBases = 0.0;
            foreach ($produtos as $idx => $item) {
                $base = null;
                if (is_array($item) && isset($item['vProd'])) {
                    $base = (float) str_replace(',', '.', (string) $item['vProd']);
                }
                if ((! is_numeric($base) || $base <= 0) && is_array($item) && isset($item['vBC'])) {
                    $base = (float) str_replace(',', '.', (string) $item['vBC']);
                }
                $base = is_numeric($base) ? (float) $base : 0.0;
                $bases[$idx] = $base;
                $sumBases += max(0.0, $base);
            }

            $origemUF = strtoupper((string) ($this->enderEmit_UF ?? ''));
            $destinoUF = strtoupper((string) ($this->enderDest_UF ?? ''));

            $aliqInter = ($this->ufExisteNoConfig($origemUF) && $this->ufExisteNoConfig($destinoUF))
                ? (float) $this->normalizarAliquota($this->getAliquotaInterestadual($origemUF, $destinoUF))
                : 0.0;

            $provisoes = [];
            $fracs = [];
            $somaProvisoria = 0.0;

            foreach ($produtos as $idx => $item) {
                $peso = $sumBases > 0 ? max(0.0, $bases[$idx]) / $sumBases : 0.0;
                $valor = round($totalDifal * $peso, 2);
                $provisoes[$idx] = $valor;
                $somaProvisoria += $valor;
                $fracs[$idx] = $sumBases > 0 ? (($totalDifal * max(0.0, $bases[$idx]) / $sumBases) - $valor) : 0.0;
            }

            $diferenca = round($totalDifal - $somaProvisoria, 2);
            if ($diferenca !== 0.0) {
                $ordem = array_keys($fracs);
                usort($ordem, function ($a, $b) use ($fracs) {
                    if ($fracs[$a] === $fracs[$b]) {
                        return 0;
                    }

                    return ($fracs[$a] > $fracs[$b]) ? -1 : 1;
                });
                $passo = $diferenca > 0 ? 0.01 : -0.01;
                $ajuste = abs((int) round($diferenca / 0.01));
                for ($i = 0; $i < $ajuste; $i++) {
                    $idxAjuste = $ordem[$i % max(1, count($ordem))];
                    $provisoes[$idxAjuste] = round($provisoes[$idxAjuste] + $passo, 2);
                }
            }

            foreach ($produtos as $idx => $item) {
                $cfop = is_array($item) ? ($item['CFOP'] ?? null) : null;
                $base = (float) $bases[$idx];
                $difalItem = (float) ($provisoes[$idx] ?? 0.0);
                $delta = ($base > 0) ? (($difalItem * 100.0) / $base) : 0.0;
                $aliqInternaDestino = (float) max(0.0, $aliqInter + $delta);
                $percentualItem = ($sumBases > 0) ? round(max(0.0, $base) * 100.0 / $sumBases, 6) : 0.0;

                $resultado[] = [
                    'item' => is_array($item) && isset($item['item']) ? (int) $item['item'] : ($idx + 1),
                    'codigo' => $item['cProd'] ?? '',
                    'produto' => $item['xProd'] ?? '',
                    'cfop' => $cfop,
                    'valor_produto' => $item['vProd'] ?? 0.0,
                    'base_calculo' => round((float) $base, 2),
                    'aliquota_interna_destino' => $aliqInternaDestino,
                    'aliquota_interestadual' => (float) $aliqInter,
                    'difal' => round($difalItem, 2),
                    'percentual_item' => $percentualItem,
                    'fonte_total_difal' => round($totalDifal, 2),
                    'metodo_distribuicao' => 'proporcional_valor_produto_maior_resto',
                ];
            }
        }

        return $resultado;
    }

    private function ufExisteNoConfig(string $uf): bool
    {
        $map = Config::get('aliquotas_icms.valor_icms');
        $ufs = $map['UF'] ?? [];

        return in_array($uf, $ufs, true);
    }

    /**
     * Converte alíquota (string como '07', '12', '17.5') para float percentual.
     */
    private function normalizarAliquota($valor): float
    {
        $v = is_string($valor) ? str_replace(',', '.', $valor) : $valor;

        return (float) $v;
    }

    /**
     * Obtém a alíquota interestadual entre UF de origem e UF de destino a partir da matriz do config.
     */
    private function getAliquotaInterestadual(string $origemUF, string $destinoUF): ?float
    {
        $map = Config::get('aliquotas_icms.valor_icms');
        $ufs = $map['UF'] ?? [];
        $colIndex = array_search($destinoUF, $ufs, true);
        if ($colIndex === false || ! isset($map[$origemUF])) {
            return null;
        }
        $row = $map[$origemUF];
        if (! isset($row[$colIndex])) {
            return null;
        }

        return $this->normalizarAliquota($row[$colIndex]);
    }
}
