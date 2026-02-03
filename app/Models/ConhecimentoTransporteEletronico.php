<?php

namespace App\Models;

use App\Enums\StatusCteEnum;
use App\Models\Traits\HasTags;
use App\Services\Xml\XmlReaderService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class ConhecimentoTransporteEletronico extends Model
{
    use HasTags;

    protected $table = 'ctes';

    protected $guarded = ['id'];

    protected $casts = [
        'data_emissao' => 'date',
        'status_cte' => StatusCteEnum::class,
        'nfe_chave' => 'array',
        'metadata' => 'array',
        // 'tpCTe' => TipoCteEnum::class,
    ];

    private bool $cteXmlDataLoaded = false;

    private ?array $cteXmlData = null;

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }

    public function getCfopAttribute(): ?int
    {
        $ide = $this->cteIde();

        $cfop = is_array($ide) ? ($ide['CFOP'] ?? null) : null;

        if (! is_numeric($cfop)) {
            return null;
        }

        return (int) $cfop;
    }

    public function getSerieAttribute(): ?string
    {
        $ide = $this->cteIde();

        $serie = is_array($ide) ? ($ide['serie'] ?? null) : null;

        return $this->stringOrNull($serie);
    }

    public function getTipoTomadorAttribute(): ?string
    {
        $toma = $this->tomaCodigo();

        return match ($toma) {
            0 => 'Remetente',
            1 => 'Expedidor',
            2 => 'Recebedor',
            3 => 'Destinatário',
            4 => 'Outros',
            default => null,
        };
    }

    public function getTomadorRazaoSocialAttribute(?string $value): ?string
    {
        if ($value !== null && trim($value) !== '') {
            return $value;
        }

        $tomador = $this->tomadorNode();

        return $this->stringOrNull(is_array($tomador) ? ($tomador['xNome'] ?? null) : null);
    }

    public function getTomadorCnpjAttribute(?string $value): ?string
    {
        if ($value !== null && trim($value) !== '') {
            return $value;
        }

        $tomador = $this->tomadorNode();

        return $this->stringOrNull($this->getDocumentoPessoa(is_array($tomador) ? $tomador : null));
    }

    public function getEmitenteLogradouroAttribute(): ?string
    {
        return $this->formatEndereco($this->cteEmitente(), 'enderEmit');
    }

    public function getEmitenteMunicipioAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteEmitente(), 'enderEmit', 'xMun'));
    }

    public function getEmitenteUfAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteEmitente(), 'enderEmit', 'UF'));
    }

    public function getEmitenteCepAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteEmitente(), 'enderEmit', 'CEP'));
    }

    public function getRemetenteTelefoneAttribute(): ?string
    {
        $rem = $this->cteRemetente();
        if (! is_array($rem)) {
            return null;
        }

        return $this->firstNonEmptyString(
            $rem['fone'] ?? null,
            is_array($rem['enderReme'] ?? null) ? ($rem['enderReme']['fone'] ?? null) : null,
        );
    }

    public function getRemetenteLogradouroAttribute(): ?string
    {
        return $this->formatEndereco($this->cteRemetente(), 'enderReme');
    }

    public function getRemetenteMunicipioAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteRemetente(), 'enderReme', 'xMun'));
    }

    public function getRemetenteUfAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteRemetente(), 'enderReme', 'UF'));
    }

    public function getRemetenteCepAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteRemetente(), 'enderReme', 'CEP'));
    }

    public function getDestinatarioTelefoneAttribute(): ?string
    {
        $dest = $this->cteDestinatario();
        if (! is_array($dest)) {
            return null;
        }

        return $this->firstNonEmptyString(
            $dest['fone'] ?? null,
            is_array($dest['enderDest'] ?? null) ? ($dest['enderDest']['fone'] ?? null) : null,
        );
    }

    public function getDestinatarioLogradouroAttribute(): ?string
    {
        return $this->formatEndereco($this->cteDestinatario(), 'enderDest');
    }

    public function getDestinatarioMunicipioAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteDestinatario(), 'enderDest', 'xMun'));
    }

    public function getDestinatarioUfAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteDestinatario(), 'enderDest', 'UF'));
    }

    public function getDestinatarioCepAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteDestinatario(), 'enderDest', 'CEP'));
    }

    public function getExpedidorNomeAttribute(): ?string
    {
        return $this->stringOrNull($this->cteExpedidorValue('xNome'));
    }

    public function getExpedidorCnpjAttribute(): ?string
    {
        return $this->stringOrNull($this->getDocumentoPessoa($this->cteExpedidor()));
    }

    public function getExpedidorIeAttribute(): ?string
    {
        return $this->stringOrNull($this->cteExpedidorValue('IE'));
    }

    public function getExpedidorXFantAttribute(): ?string
    {
        return $this->stringOrNull($this->cteExpedidorValue('xFant'));
    }

    public function getExpedidorTelefoneAttribute(): ?string
    {
        $exped = $this->cteExpedidor();

        return $this->firstNonEmptyString(
            is_array($exped) ? ($exped['fone'] ?? null) : null,
            is_array($exped) && is_array($exped['enderExped'] ?? null) ? ($exped['enderExped']['fone'] ?? null) : null,
        );
    }

    public function getExpedidorLogradouroAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteExpedidor(), 'enderExped', 'xLgr'));
    }

    public function getExpedidorNumeroAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteExpedidor(), 'enderExped', 'nro'));
    }

    public function getExpedidorComplementoAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteExpedidor(), 'enderExped', 'xCpl'));
    }

    public function getExpedidorBairroAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteExpedidor(), 'enderExped', 'xBairro'));
    }

    public function getExpedidorMunicipioAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteExpedidor(), 'enderExped', 'xMun'));
    }

    public function getExpedidorUfAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteExpedidor(), 'enderExped', 'UF'));
    }

    public function getExpedidorCepAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteExpedidor(), 'enderExped', 'CEP'));
    }

    public function getRecebedorNomeAttribute(): ?string
    {
        return $this->stringOrNull($this->cteRecebedorValue('xNome'));
    }

    public function getRecebedorCnpjAttribute(): ?string
    {
        return $this->stringOrNull($this->getDocumentoPessoa($this->cteRecebedor()));
    }

    public function getRecebedorIeAttribute(): ?string
    {
        return $this->stringOrNull($this->cteRecebedorValue('IE'));
    }

    public function getRecebedorXFantAttribute(): ?string
    {
        return $this->stringOrNull($this->cteRecebedorValue('xFant'));
    }

    public function getRecebedorTelefoneAttribute(): ?string
    {
        $receb = $this->cteRecebedor();

        return $this->firstNonEmptyString(
            is_array($receb) ? ($receb['fone'] ?? null) : null,
            is_array($receb) && is_array($receb['enderReceb'] ?? null) ? ($receb['enderReceb']['fone'] ?? null) : null,
        );
    }

    public function getRecebedorLogradouroAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteRecebedor(), 'enderReceb', 'xLgr'));
    }

    public function getRecebedorNumeroAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteRecebedor(), 'enderReceb', 'nro'));
    }

    public function getRecebedorComplementoAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteRecebedor(), 'enderReceb', 'xCpl'));
    }

    public function getRecebedorBairroAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteRecebedor(), 'enderReceb', 'xBairro'));
    }

    public function getRecebedorMunicipioAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteRecebedor(), 'enderReceb', 'xMun'));
    }

    public function getRecebedorUfAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteRecebedor(), 'enderReceb', 'UF'));
    }

    public function getRecebedorCepAttribute(): ?string
    {
        return $this->stringOrNull($this->cteEnderecoValue($this->cteRecebedor(), 'enderReceb', 'CEP'));
    }

    public function getValorServicoAttribute(): ?float
    {
        $vPrest = $this->cteVPrest();
        $value = is_array($vPrest) ? ($vPrest['vTPrest'] ?? null) : null;

        return $this->numericOrNull($value);
    }

    public function getValorReceberAttribute(): ?float
    {
        $vPrest = $this->cteVPrest();
        $value = is_array($vPrest) ? ($vPrest['vRec'] ?? null) : null;

        return $this->numericOrNull($value);
    }

    public function getBaseCalculoIcmsAttribute(): ?float
    {
        $icms = $this->cteIcmsGroup();
        $value = is_array($icms) ? ($icms['vBC'] ?? null) : null;

        return $this->numericOrNull($value);
    }

    public function getAliquotaIcmsAttribute(): ?float
    {
        $icms = $this->cteIcmsGroup();
        $value = is_array($icms) ? ($icms['pICMS'] ?? null) : null;

        return $this->numericOrNull($value);
    }

    public function getValorIcmsAttribute(): ?float
    {
        $icms = $this->cteIcmsGroup();
        $value = is_array($icms) ? ($icms['vICMS'] ?? null) : null;

        return $this->numericOrNull($value);
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

    private function cteData(): ?array
    {
        if ($this->cteXmlDataLoaded) {
            return $this->cteXmlData;
        }

        $this->cteXmlDataLoaded = true;

        $xml = $this->extrairXmlComoString();
        if ($xml === null) {
            $this->cteXmlData = null;

            return null;
        }

        try {
            $this->cteXmlData = (new XmlReaderService)->read($xml);
        } catch (Throwable) {
            $this->cteXmlData = null;
        }

        return $this->cteXmlData;
    }

    private function cteInfCte(): ?array
    {
        $data = $this->cteData();
        if (! is_array($data)) {
            return null;
        }

        $infCte = $this->getNested($data, ['cteProc', 'CTe', 'infCte']);
        if (! is_array($infCte)) {
            $infCte = $this->getNested($data, ['CTe', 'infCte']);
        }

        return is_array($infCte) ? $infCte : null;
    }

    private function cteIde(): ?array
    {
        $infCte = $this->cteInfCte();

        return is_array($infCte) ? ($infCte['ide'] ?? null) : null;
    }

    private function cteEmitente(): ?array
    {
        $infCte = $this->cteInfCte();

        return is_array($infCte) ? ($infCte['emit'] ?? null) : null;
    }

    private function cteRemetente(): ?array
    {
        $infCte = $this->cteInfCte();

        return is_array($infCte) ? ($infCte['rem'] ?? null) : null;
    }

    private function cteDestinatario(): ?array
    {
        $infCte = $this->cteInfCte();

        return is_array($infCte) ? ($infCte['dest'] ?? null) : null;
    }

    private function cteExpedidor(): ?array
    {
        $infCte = $this->cteInfCte();

        return is_array($infCte) ? ($infCte['exped'] ?? null) : null;
    }

    private function cteRecebedor(): ?array
    {
        $infCte = $this->cteInfCte();

        return is_array($infCte) ? ($infCte['receb'] ?? null) : null;
    }

    private function cteVPrest(): ?array
    {
        $infCte = $this->cteInfCte();

        return is_array($infCte) ? ($infCte['vPrest'] ?? null) : null;
    }

    private function cteIcmsGroup(): ?array
    {
        $infCte = $this->cteInfCte();
        if (! is_array($infCte)) {
            return null;
        }

        $icms = $this->getNested($infCte, ['imp', 'ICMS']);
        if (! is_array($icms)) {
            return null;
        }

        foreach ($icms as $value) {
            if (is_array($value)) {
                return $value;
            }
        }

        return null;
    }

    private function tomaCodigo(): ?int
    {
        $ide = $this->cteIde();
        if (! is_array($ide)) {
            return null;
        }

        $toma = $this->getNested($ide, ['toma3', 'toma']) ?? $this->getNested($ide, ['toma4', 'toma']);

        if (! is_numeric($toma)) {
            return null;
        }

        return (int) $toma;
    }

    private function tomadorNode(): ?array
    {
        $infCte = $this->cteInfCte();
        if (! is_array($infCte)) {
            return null;
        }

        $toma = $this->tomaCodigo();

        return match ($toma) {
            0 => is_array($infCte['rem'] ?? null) ? $infCte['rem'] : null,
            1 => is_array($infCte['exped'] ?? null) ? $infCte['exped'] : null,
            2 => is_array($infCte['receb'] ?? null) ? $infCte['receb'] : null,
            3 => is_array($infCte['dest'] ?? null) ? $infCte['dest'] : null,
            4 => is_array($this->getNested($infCte, ['ide', 'toma4'])) ? $this->getNested($infCte, ['ide', 'toma4']) : null,
            default => null,
        };
    }

    private function cteEnderecoValue(?array $node, string $enderKey, string $field): mixed
    {
        if (! is_array($node)) {
            return null;
        }

        $ender = $node[$enderKey] ?? null;

        return is_array($ender) ? ($ender[$field] ?? null) : null;
    }

    private function formatEndereco(?array $node, string $enderKey): ?string
    {
        if (! is_array($node)) {
            return null;
        }

        $ender = $node[$enderKey] ?? null;
        if (! is_array($ender)) {
            return null;
        }

        $logradouro = $this->stringOrNull($ender['xLgr'] ?? null);
        $numero = $this->stringOrNull($ender['nro'] ?? null);
        $complemento = $this->stringOrNull($ender['xCpl'] ?? null);
        $bairro = $this->stringOrNull($ender['xBairro'] ?? null);

        if ($logradouro === null && $numero === null && $bairro === null && $complemento === null) {
            return null;
        }

        $texto = trim(($logradouro ?? '').($numero !== null ? ', '.$numero : ''));

        if ($complemento !== null) {
            $texto .= ', '.$complemento;
        }

        if ($bairro !== null) {
            $texto .= ', '.$bairro;
        }

        return trim($texto) === '' ? null : $texto;
    }

    private function cteExpedidorValue(string $key): mixed
    {
        $exped = $this->cteExpedidor();

        return is_array($exped) ? ($exped[$key] ?? null) : null;
    }

    private function cteRecebedorValue(string $key): mixed
    {
        $receb = $this->cteRecebedor();

        return is_array($receb) ? ($receb[$key] ?? null) : null;
    }

    private function getDocumentoPessoa(?array $node): ?string
    {
        if (! is_array($node)) {
            return null;
        }

        return $this->stringOrNull($node['CNPJ'] ?? $node['CPF'] ?? null);
    }

    private function getNested(array $data, array $path): mixed
    {
        $current = $data;

        foreach ($path as $key) {
            if (! is_array($current) || ! array_key_exists($key, $current)) {
                return null;
            }

            $current = $current[$key];
        }

        return $current;
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

    public function scopeWhereNfeChave($query, string $chave)
    {
        $chave = trim($chave);
        $driver = $query->getConnection()->getDriverName();
        $table = $query->getModel()->getTable();

        if ($driver === 'mysql') {
            return $query->whereRaw(
                "(JSON_SEARCH({$table}.nfe_chave, 'one', ?) is not null or JSON_SEARCH({$table}.nfe_chave, 'one', ?, null, '$[*].chave') is not null)",
                [$chave, $chave],
            );
        }

        if ($driver === 'sqlite') {
            return $query->whereRaw(
                "exists (select 1 from json_each({$table}.nfe_chave) where json_each.value = ? or json_extract(json_each.value, '$.chave') = ?)",
                [$chave, $chave],
            );
        }

        return $query->where(function ($query) use ($chave) {
            $query
                ->whereJsonContains('nfe_chave->*.chave', $chave)
                ->orWhereJsonContains('nfe_chave', $chave);
        });
    }

    public function scopeTomadasEntrada($query, $issuer)
    {

        $query->join('nfes', function ($join) use ($issuer) {
            $join->on('ctes.nfe_chave', '=', 'nfes.chave');
            $join->on('nfes.destinatario_cnpj', '=', DB::raw("'".$issuer."'"));
        })
            ->select('ctes.*', 'nfes.chave as chave_nfe');

        return $query;
    }

    public function scopeTomadasSaida($query, $issuer)
    {

        $query->join('nfes', function ($join) use ($issuer) {
            $join->on('ctes.nfe_chave', '=', 'nfes.chave');
            $join->on('nfes.emitente_cnpj', '=', DB::raw("'".$issuer."'"));
        })
            ->select('ctes.*', 'nfes.chave as chave_nfe');

        return $query;
    }

    public function apurada()
    {
        return $this->hasOne(CteApurada::class, 'cte_id')->where('issuer_id', Auth::user()->currentIssuer->id);
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

    public function retag(string $tag)
    {
        $this->untag();
        $this->tag($tag, $this->vCTe);
    }
}
