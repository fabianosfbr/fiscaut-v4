<?php

namespace App\Models;

use App\Enums\StatusNfeEnum;
use App\Models\Traits\HasTags;
use Illuminate\Support\Facades\Auth;
use App\Enums\StatusManifestoNfeEnum;
use App\Services\Xml\XmlReaderService;
use Illuminate\Database\Eloquent\Model;

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
        'difal' => 'array',
        'cobranca' => 'array',
        'parcela' => 'array',
        'cfops' => 'array',
        'processed' => 'boolean',
    ];

    public function nfeReferenciada()
    {
        return $this->hasMany(NfeReferenciada::class, 'nfe_id', 'id');
    }

    public function apuracoes()
    {
        return $this->hasMany(NfeApurada::class, 'nfe_id');
    }

    public function isApuradaParaEmpresa(Issuer $issuer): bool
    {
        $apuracao = $this->apuracoes()
            ->where('issuer_id', $issuer->id)
            ->latest('id')
            ->first();

        if ($apuracao !== null) {
            return (bool) $apuracao->status;
        }

        return (bool) ($this->processed ?? false);
    }

    public function toggleApuracao(Issuer $issuer): bool
    {
        $apuracao = $this->apuracoes()
            ->where('issuer_id', $issuer->id)
            ->latest('id')
            ->first();

        if ($apuracao === null) {
            $this->apuracoes()->create([
                'issuer_id' => $issuer->id,
                'status' => true,
            ]);

            $this->updateQuietly([
                'processed' => true,
            ]);

            return true;
        }

        $newStatus = ! $apuracao->status;

        $apuracao->update([
            'status' => $newStatus,
        ]);

        $this->updateQuietly([
            'processed' => $newStatus,
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

        $data = (new XmlReaderService())->read($xml);
        $det = $data['nfeProc']['NFe']['infNFe']['det'] ?? null;

        $detList = $this->normalizeList($det);
        if ($detList === []) {
            return [];
        }

        return array_values(array_map(function ($detItem) {
            if (!is_array($detItem)) {
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
                    'vBC' => searchValueInArray($imposto, 'vBC'),
                    'pICMS' => searchValueInArray($imposto, 'pICMS'),
                    'vICMS' => searchValueInArray($imposto, 'vICMS'),
                    'vIPI' => searchValueInArray($imposto, 'vIPI'),
                    'vPIS' => searchValueInArray($imposto, 'vPIS'),
                    'vCOFINS' => searchValueInArray($imposto, 'vCOFINS'),
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

        $data = (new XmlReaderService())->read($xml);

        $ender = $data['nfeProc']['NFe']['infNFe']['dest']['enderDest'] ?? [];
        $logradouro = $ender['xLgr'] ?? null;
        $numero = $ender['nro'] ?? null;
        $complemento = $ender['xCpl'] ?? null;
        $bairro = $ender['xBairro'] ?? null;
        $municipio = $ender['xMun'] ?? null;
        $uf = $ender['UF'] ?? null;
        $cep = $ender['CEP'] ?? null;

        $endereco = $this->buildEnderecoCompletoFromFields($logradouro, $numero, $complemento, $bairro);

        $cidadeUf = trim(($municipio ?? '') . ($uf ? '/' . $uf : ''));
        if ($cidadeUf !== '') {
            $endereco = trim($endereco);
            $endereco .= ($endereco !== '' ? ' - ' : '') . $cidadeUf;
        }

        $cepFormatado = $this->formatCep($cep);
        if ($cepFormatado !== '') {
            $endereco = trim($endereco);
            $endereco .= ($endereco !== '' ? ' - ' : '') . 'CEP: ' . $cepFormatado;
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

        $data = (new XmlReaderService())->read($xml);

        $ender = $data['nfeProc']['NFe']['infNFe']['emit']['enderEmit'] ?? [];
        $logradouro = $ender['xLgr'] ?? null;
        $numero = $ender['nro'] ?? null;
        $complemento = $ender['xCpl'] ?? null;
        $bairro = $ender['xBairro'] ?? null;
        $municipio = $ender['xMun'] ?? null;
        $uf = $ender['UF'] ?? null;
        $cep = $ender['CEP'] ?? null;

        $endereco = $this->buildEnderecoCompletoFromFields($logradouro, $numero, $complemento, $bairro);

        $cidadeUf = trim(($municipio ?? '') . ($uf ? '/' . $uf : ''));
        if ($cidadeUf !== '') {
            $endereco = trim($endereco);
            $endereco .= ($endereco !== '' ? ' - ' : '') . $cidadeUf;
        }

        $cepFormatado = $this->formatCep($cep);
        if ($cepFormatado !== '') {
            $endereco = trim($endereco);
            $endereco .= ($endereco !== '' ? ' - ' : '') . 'CEP: ' . $cepFormatado;
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
            $endereco .= ($endereco !== '' ? ', ' : '') . $numero;
        }

        $complemento = trim((string) $complemento);
        if ($complemento !== '') {
            $endereco .= ($endereco !== '' ? ' - ' : '') . $complemento;
        }

        $bairro = trim((string) $bairro);
        if ($bairro !== '') {
            $endereco .= ($endereco !== '' ? ' - ' : '') . $bairro;
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

        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }

    private function extrairXmlComoString(): ?string
    {
        $raw = $this->xml ?? null;
        if (!is_string($raw) || $raw === '') {
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

        if (!is_array($value)) {
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
}
