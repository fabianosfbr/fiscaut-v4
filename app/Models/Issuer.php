<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issuer extends Model
{
    protected $guarded = ['id'];

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
}
