<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimplesNacionalCalculation extends Model
{
    use HasFactory;

    protected $table = 'simples_nacional_calculations';

    protected $fillable = [
        'issuer_id',
        'periodo_apuracao',
        'faturamento_12_meses',
        'faturamento_periodo',
        'anexo',
        'faixa_receita',
        'aliquota_efetiva',
        'valor_das',
        'detalhamento_impostos',
        'status',
        // Campos do Fator R
        'folha_salarios_12_meses',
        'fator_r',
        'sujeito_fator_r',
        'anexo_fator_r',
        'detalhamento_folha',
    ];

    protected $casts = [
        'periodo_apuracao' => 'date',
        'faturamento_12_meses' => 'decimal:2',
        'faturamento_periodo' => 'decimal:2',
        'aliquota_efetiva' => 'decimal:4',
        'valor_das' => 'decimal:2',
        'detalhamento_impostos' => 'array',
        // Casts para campos do Fator R
        'folha_salarios_12_meses' => 'decimal:2',
        'fator_r' => 'decimal:4',
        'sujeito_fator_r' => 'boolean',
        'detalhamento_folha' => 'array',
    ];

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(Issuer::class);
    }

    public function anexoModel(): BelongsTo
    {
        return $this->belongsTo(SimplesNacionalAnexo::class, 'anexo', 'anexo');
    }

    public function scopeByIssuer($query, int $issuerId)
    {
        return $query->where('issuer_id', $issuerId);
    }

    public function scopeByPeriodo($query, $periodo)
    {
        return $query->where('periodo_apuracao', $periodo);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCalculados($query)
    {
        return $query->where('status', 'calculado');
    }

    public function scopePagos($query)
    {
        return $query->where('status', 'pago');
    }

    public function getValorIrpjAttribute(): float
    {
        return $this->detalhamento_impostos['irpj'] ?? 0;
    }

    public function getValorCsllAttribute(): float
    {
        return $this->detalhamento_impostos['csll'] ?? 0;
    }

    public function getValorCofinsAttribute(): float
    {
        return $this->detalhamento_impostos['cofins'] ?? 0;
    }

    public function getValorPisAttribute(): float
    {
        return $this->detalhamento_impostos['pis'] ?? 0;
    }

    public function getValorCppAttribute(): float
    {
        return $this->detalhamento_impostos['cpp'] ?? 0;
    }

    public function getValorIcmsAttribute(): float
    {
        return $this->detalhamento_impostos['icms'] ?? 0;
    }

    public function getValorIssAttribute(): float
    {
        return $this->detalhamento_impostos['iss'] ?? 0;
    }

    // Métodos auxiliares para Fator R
    public function isSujeitoFatorR(): bool
    {
        return $this->sujeito_fator_r ?? false;
    }

    public function getFatorRPercentual(): float
    {
        return $this->fator_r ? ($this->fator_r * 100) : 0;
    }

    public function getAnexoFinalAttribute(): string
    {
        return $this->isSujeitoFatorR() ? ($this->anexo_fator_r ?? $this->anexo) : $this->anexo;
    }

    public function scopeSujeitoFatorR($query)
    {
        return $query->where('sujeito_fator_r', true);
    }

    public function scopeByAnexoFatorR($query, string $anexo)
    {
        return $query->where('anexo_fator_r', $anexo);
    }
}
