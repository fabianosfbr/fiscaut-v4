<?php

namespace App\Models;

use App\Enums\CondominiumTypeEnum;
use App\Enums\IssuerTypeEnum;
use App\Observers\IssuerSuperLogicaCondominio;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([IssuerSuperLogicaCondominio::class])]
class Issuer extends Model
{
    protected $with = ['municipio'];

    protected $guarded = ['id', 'superlogica_condominio_id'];

    protected $casts = [
        'side_activities' => 'array',
        'main_activity' => 'array',
        'tagsCreditoIcms' => 'array',
        'is_enabled' => 'boolean',
        'isNfeClassificarNaEntrada' => 'boolean',
        'isNfeManifestarAutomatica' => 'boolean',
        'isNfeClassificarSomenteManifestacao' => 'boolean',
        'isNfeMostrarEtiquetaComNomeAbreviado' => 'boolean',
        'isNfeTomaCreditoIcms' => 'boolean',
        'sync_sieg' => 'boolean',
        'sync_unecont' => 'boolean',
        'nfe_servico' => 'boolean',
        'cte_servico' => 'boolean',
        'cfe_servico' => 'boolean',
        'nfse_servico' => 'boolean',
        'contribuinte_icms' => 'boolean',
        'sync_sefaz' => 'boolean',
        'validade_certificado' => 'datetime',
        'unecont_registered_at' => 'datetime',
        'unecont_unregistered_at' => 'datetime',
        'data_abertura' => 'date',
        'data_situacao_cadastral' => 'date',
        'contract_start_date' => 'date',
        'units_count' => 'integer',
        'residential_count' => 'integer',
        'commercial_count' => 'integer',
        'units_data' => 'array',
        'atividade' => 'array',
        'issuer_type' => IssuerTypeEnum::class,
        'condominium_type' => CondominiumTypeEnum::class,
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_issuers_permissions', 'issuer_id', 'user_id')
            ->using(IssuerUserPermission::class)
            ->withPivot(['expires_at', 'active'])
            ->withTimestamps();
    }

    public function categoryTags()
    {
        return $this->hasMany(CategoryTag::class);
    }

    public function contacts()
    {
        return $this->hasMany(IssuerContact::class);
    }

    public function areaResponsibles()
    {
        return $this->hasMany(IssuerAreaResponsible::class);
    }

    public function groupControls()
    {
        return $this->hasMany(IssuerGroupControl::class);
    }

    public function controls()
    {
        return $this->hasMany(IssuerControl::class);
    }

    public function municipio()
    {
        return $this->hasOne(Municipio::class, 'id', 'cod_municipio_ibge');
    }

    public function units()
    {
        return $this->hasMany(IssuerUnit::class);
    }

    public function unitFracoes()
    {
        return $this->hasMany(IssuerUnitFracao::class);
    }
}
