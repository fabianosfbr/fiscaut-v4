<?php

namespace App\Models;

use App\Models\Traits\HasTags;
use App\Services\Xml\XmlReaderService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Throwable;

class NotaFiscalServico extends Model
{
    use HasTags;

    protected $table = 'nfses';

    protected $guarded = ['id'];

    protected $casts = [
        'data_emissao' => 'datetime',
        'import_data' => 'array',
        'cancelada' => 'boolean',
    ];

    private bool $nfseXmlDataLoaded = false;

    private ?array $nfseXmlData = null;

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }

    public function scopeSearch($query, $term)
    {
        $term = "%$term%";
        $query->where(function ($query) use ($term) {
            $query->where('tomador_servico', 'like', $term)
                ->orWhere('tomador_cnpj', 'like', $term)
                ->orWhere('prestador_servico', 'like', $term)
                ->orWhere('prestador_cnpj', 'like', $term)
                ->orWhere('prestador_im', 'like', $term)
                ->orWhere('data_emissao', 'like', $term)
                ->orWhere('valor_servico', 'like', $term)
                ->orWhere('codigo_verificacao', 'like', $term)
                ->orWhere('numero', 'like', $term);
        });
    }

    public function retag(string $tag)
    {
        $this->untag();
        $this->tag($tag, $this->valor_servico);
    }

    public function apurada()
    {
        return $this->hasOne(NfseApurada::class, 'nfse_id')->where('issuer_id', Auth::user()->currentIssuer->id);
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

    public function getXmlExtraidoAttribute(): ?string
    {
        return $this->extrairXmlComoString();
    }

    public function getNfseCodigoServicoExtraidoAttribute(): ?string
    {
        $data = $this->nfseRoot();
        if (! is_array($data)) {
            return null;
        }

        return searchValueInArray($data, 'cNBS');
    }

    public function getNfseDescricaoServicoExtraidaAttribute(): ?string
    {
        $data = $this->nfseRoot();

        return $this->firstNonEmptyString(
            $this->descricao_servico ?? null,
            is_array($data) ? searchValueInArray($data, 'xDescServ') : null,
            is_array($data) ? searchValueInArray($data, 'xTribNac') : null,
            is_array($data) ? searchValueInArray($data, 'cTribNac') : null,
            is_array($data) ? searchValueInArray($data, 'cNBS') : null,
        );
    }

    public function getNfseDiscriminacaoExtraidaAttribute(): ?string
    {
        $data = $this->nfseRoot();

        return $this->firstNonEmptyString(
            $this->discriminacao ?? null,
            is_array($data) ? searchValueInArray($data, 'xInfComp') : null,
            is_array($data) ? searchValueInArray($data, 'infCpl') : null,
            is_array($data) ? searchValueInArray($data, 'xInfCpl') : null,
            is_array($data) ? searchValueInArray($data, 'discriminacao') : null,
        );
    }

    public function getNfseValorIssExtraidoAttribute(): ?float
    {
        $data = $this->nfseRoot();
        if (! is_array($data)) {
            return null;
        }

        return $this->numericOrNull(
            $this->firstNonEmptyString(
                searchValueInArray($data, 'vISSQN'),
                searchValueInArray($data, 'vISS'),
            ),
        );
    }

    public function getNfseBaseCalculoIssExtraidaAttribute(): ?float
    {
        $data = $this->nfseRoot();
        if (! is_array($data)) {
            return null;
        }

        return $this->numericOrNull(searchValueInArray($data, 'vBC'));
    }

    public function getNfseAliquotaIssExtraidaAttribute(): ?float
    {
        $data = $this->nfseRoot();
        if (! is_array($data)) {
            return null;
        }

        return $this->numericOrNull(
            $this->firstNonEmptyString(
                searchValueInArray($data, 'pAliqAplic'),
                searchValueInArray($data, 'pAliq'),
            ),
        );
    }

    public function getNfseIssRetidoExtraidoAttribute(): ?float
    {
        $data = $this->nfseRoot();
        if (! is_array($data)) {
            return null;
        }

        return $this->numericOrNull(
            $this->firstNonEmptyString(
                searchValueInArray($data, 'vISSRet'),
                searchValueInArray($data, 'vISSRetido'),
            ),
        );
    }

    public function getNfseValoresXmlAttribute(): ?string
    {
        $data = $this->nfseRoot();
        if (! is_array($data)) {
            return null;
        }

        $valores = data_get($data, 'infNFSe.valores');
        if (! is_array($valores) || $valores === []) {
            return null;
        }

        $json = json_encode($valores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return is_string($json) && $json !== '' ? $json : null;
    }

    public function getNfseXmlJsonAttribute(): ?string
    {
        $data = $this->nfseData();
        if (! is_array($data)) {
            return null;
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return is_string($json) && $json !== '' ? $json : null;
    }

    private function nfseNumericGet(string ...$paths): ?float
    {
        $value = $this->nfseGet(...$paths);

        return $this->numericOrNull($value);
    }

    private function nfseGet(string ...$paths): mixed
    {
        $data = $this->nfseRoot();
        if (! is_array($data)) {
            return null;
        }

        foreach ($paths as $path) {
            $value = data_get($data, $path);
            $string = $this->stringOrNull($value);
            if ($string !== null) {
                return $string;
            }
        }

        return null;
    }

    private function nfseData(): ?array
    {
        if ($this->nfseXmlDataLoaded) {
            return $this->nfseXmlData;
        }

        $this->nfseXmlDataLoaded = true;

        $xml = $this->extrairXmlComoString();
        if ($xml === null) {
            $this->nfseXmlData = null;

            return null;
        }

        try {
            $this->nfseXmlData = (new XmlReaderService)->read($xml);
        } catch (Throwable) {
            $this->nfseXmlData = null;
        }

        return $this->nfseXmlData;
    }

    private function nfseRoot(): ?array
    {
        $data = $this->nfseData();
        if (! is_array($data) || $data === []) {
            return null;
        }

        $rootKey = array_key_first($data);
        if (! is_string($rootKey) || $rootKey === '') {
            return null;
        }

        $root = $data[$rootKey] ?? null;

        return is_array($root) ? $root : null;
    }

    private function extrairXmlComoString(): ?string
    {
        $raw = $this->xml ?? null;
        if (is_string($raw) && $raw !== '') {
            $uncompressed = @gzuncompress($raw);
            if (is_string($uncompressed) && $uncompressed !== '') {
                return $uncompressed;
            }

            $decoded = @gzdecode($raw);
            if (is_string($decoded) && $decoded !== '') {
                return $decoded;
            }

            return $raw;
        }

        $raw = $this->xml_content ?? null;
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $uncompressed = @gzuncompress($raw);
        if (is_string($uncompressed) && $uncompressed !== '') {
            return $uncompressed;
        }

        $decoded = @gzdecode($raw);
        if (is_string($decoded) && $decoded !== '') {
            return $decoded;
        }

        return $raw;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function numericOrNull(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function firstNonEmptyString(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            $string = $this->stringOrNull($value);
            if ($string !== null) {
                return $string;
            }
        }

        return null;
    }
}
