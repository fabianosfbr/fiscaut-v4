<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuerAssembleiaEventLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'dados_alterados' => 'array',
    ];

    public function assembleia(): BelongsTo
    {
        return $this->belongsTo(IssuerAssembleia::class, 'issuer_assembleia_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePorAcao($query, string $acao)
    {
        return $query->where('acao', $acao);
    }

    public function scopePorUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecentes($query, int $dias = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($dias));
    }

    public function scopeOrdenadoPorData($query, string $direcao = 'desc')
    {
        return $query->orderBy('created_at', $direcao);
    }

    public static function criarHistorico(
        int $assembleiaId,
        int $userId,
        string $acao,
        ?string $campoAlterado = null,
        ?string $valorAnterior = null,
        ?string $valorNovo = null,
        ?string $observacao = null,
        ?array $dadosAlterados = null
    ): self {
        return self::create([
            'issuer_assembleia_id' => $assembleiaId,
            'user_id' => $userId,
            'acao' => $acao,
            'campo_alterado' => $campoAlterado,
            'valor_anterior' => $valorAnterior,
            'valor_novo' => $valorNovo,
            'observacao' => $observacao,
            'dados_alterados' => $dadosAlterados,
            'created_at' => now(),
        ]);
    }

    public static function registrarCriacao(int $assembleiaId, int $usuarioId, ?string $observacao = null): self
    {
        return self::criarHistorico(
            $assembleiaId,
            $usuarioId,
            'criacao',
            null,
            null,
            'rascunho',
            $observacao ?? 'Assembleia criada'
        );
    }

    public static function registrarAlteracaoCampo(
        int $assembleiaId,
        int $usuarioId,
        string $campoAlterado,
        ?string $valorAnterior,
        ?string $valorNovo,
        ?string $observacao = null
    ): self {
        return self::criarHistorico(
            $assembleiaId,
            $usuarioId,
            'alteracao_campo',
            $campoAlterado,
            $valorAnterior,
            $valorNovo,
            $observacao,
            [
                'campo' => $campoAlterado,
                'valor_anterior' => $valorAnterior,
                'valor_novo' => $valorNovo,
            ]
        );
    }

    public static function registrarAlteracaoStatus(
        int $assembleiaId,
        int $usuarioId,
        string $tipoStatus,
        ?string $statusAnterior,
        ?string $statusNovo,
        ?string $observacao = null
    ): self {
        return self::criarHistorico(
            $assembleiaId,
            $usuarioId,
            'alteracao_status',
            $tipoStatus,
            $statusAnterior,
            $statusNovo,
            $observacao,
            [
                'tipo_status' => $tipoStatus,
                'status_anterior' => $statusAnterior,
                'status_novo' => $statusNovo,
            ]
        );
    }

    public function getDescricaoAcaoAttribute(): string
    {
        return match ($this->acao) {
            'criacao' => 'Criou a assembleia',
            'alteracao_campo' => "Alterou o campo {$this->campo_alterado}",
            'alteracao_status' => 'Alterou status',
            default => 'Realizou uma ação',
        };
    }

    public function getIconeAcaoAttribute(): string
    {
        return match ($this->acao) {
            'criacao' => 'heroicon-o-plus-circle',
            'alteracao_campo' => 'heroicon-o-pencil',
            'alteracao_status' => 'heroicon-o-arrow-path',
            default => 'heroicon-o-information-circle',
        };
    }

    public function getCorAcaoAttribute(): string
    {
        return match ($this->acao) {
            'criacao' => 'success',
            'alteracao_campo' => 'primary',
            'alteracao_status' => 'warning',
            default => 'gray',
        };
    }
}
