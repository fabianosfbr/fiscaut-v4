<?php

namespace App\Models;

use App\Enums\SeveridadeValidacaoEnum;
use App\Enums\StatusValidacaoEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NfeValidacaoTributaria extends Model
{
    use SoftDeletes;

    protected $table = 'nfe_validacoes';

    protected $guarded = ['id'];

    protected $casts = [
        'severidade' => SeveridadeValidacaoEnum::class,
        'status' => StatusValidacaoEnum::class,
        'resolved_at' => 'datetime',
    ];

    public function nfe()
    {
        return $this->belongsTo(NotaFiscalEletronica::class, 'nfe_id');
    }

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', StatusValidacaoEnum::PENDENTE);
    }

    public function scopePorRegra($query, string $regra)
    {
        return $query->where('regra', $regra);
    }

    public function scopePorNfe($query, int $nfeId)
    {
        return $query->where('nfe_id', $nfeId);
    }
}
