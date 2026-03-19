<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManutencaoHistorico extends Model
{
    protected $table = 'manutencao_historicos';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'dados_alterados' => 'array',
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'manutencao_id',
        'usuario_id',
        'acao',
        'status_anterior',
        'status_novo',
        'observacao',
        'dados_alterados',
        'created_at',
    ];

    public function manutencao(): BelongsTo
    {
        return $this->belongsTo(Manutencao::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePorAcao($query, string $acao)
    {
        return $query->where('acao', $acao);
    }

    public function scopePorUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeRecentes($query, int $dias = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($dias));
    }

    public function scopeOrdenadoPorData($query, string $direcao = 'desc')
    {
        return $query->orderBy('created_at', $direcao);
    }

    // Métodos estáticos para criar históricos
    public static function criarHistorico(
        int $manutencaoId,
        int $usuarioId,
        string $acao,
        ?string $statusAnterior = null,
        ?string $statusNovo = null,
        ?string $observacao = null,
        ?array $dadosAlterados = null
    ): self {
        return self::create([
            'manutencao_id' => $manutencaoId,
            'usuario_id' => $usuarioId,
            'acao' => $acao,
            'status_anterior' => $statusAnterior,
            'status_novo' => $statusNovo,
            'observacao' => $observacao,
            'dados_alterados' => $dadosAlterados,
            'created_at' => now(),
        ]);
    }

    public static function registrarCriacao(int $manutencaoId, int $usuarioId, ?string $observacao = null): self
    {
        return self::criarHistorico(
            $manutencaoId,
            $usuarioId,
            'criacao',
            null,
            'programada',
            $observacao ?? 'Manutenção criada'
        );
    }

    public static function registrarInicio(int $manutencaoId, int $usuarioId, ?string $observacao = null): self
    {
        return self::criarHistorico(
            $manutencaoId,
            $usuarioId,
            'inicio',
            'programada',
            'em_andamento',
            $observacao ?? 'Manutenção iniciada'
        );
    }

    public static function registrarConclusao(int $manutencaoId, int $usuarioId, ?string $observacao = null): self
    {
        return self::criarHistorico(
            $manutencaoId,
            $usuarioId,
            'conclusao',
            'em_andamento',
            'concluida',
            $observacao ?? 'Manutenção concluída'
        );
    }

    public static function registrarCancelamento(int $manutencaoId, int $usuarioId, string $motivo): self
    {
        return self::criarHistorico(
            $manutencaoId,
            $usuarioId,
            'cancelamento',
            null,
            'cancelada',
            "Manutenção cancelada. Motivo: {$motivo}"
        );
    }

    public static function registrarReagendamento(
        int $manutencaoId,
        int $usuarioId,
        string $dataAnterior,
        string $dataNova,
        ?string $motivo = null
    ): self {
        $observacao = "Manutenção reagendada de {$dataAnterior} para {$dataNova}";
        if ($motivo) {
            $observacao .= ". Motivo: {$motivo}";
        }

        return self::criarHistorico(
            $manutencaoId,
            $usuarioId,
            'reagendamento',
            null,
            null,
            $observacao,
            [
                'data_anterior' => $dataAnterior,
                'data_nova' => $dataNova,
                'motivo' => $motivo,
            ]
        );
    }

    public static function registrarComentario(int $manutencaoId, int $usuarioId, string $comentario): self
    {
        return self::criarHistorico(
            $manutencaoId,
            $usuarioId,
            'comentario',
            null,
            null,
            $comentario
        );
    }

    public static function registrarAlteracao(
        int $manutencaoId,
        int $usuarioId,
        array $dadosAlterados,
        ?string $observacao = null
    ): self {
        return self::criarHistorico(
            $manutencaoId,
            $usuarioId,
            'alteracao',
            null,
            null,
            $observacao ?? 'Dados da manutenção alterados',
            $dadosAlterados
        );
    }

    // Métodos auxiliares
    public function getDescricaoAcaoAttribute(): string
    {
        return match ($this->acao) {
            'criacao' => 'Criou a manutenção',
            'inicio' => 'Iniciou a manutenção',
            'conclusao' => 'Concluiu a manutenção',
            'cancelamento' => 'Cancelou a manutenção',
            'reagendamento' => 'Reagendou a manutenção',
            'comentario' => 'Adicionou um comentário',
            'alteracao' => 'Alterou dados da manutenção',
            default => 'Realizou uma ação',
        };
    }

    public function getIconeAcaoAttribute(): string
    {
        return match ($this->acao) {
            'criacao' => 'heroicon-o-plus-circle',
            'inicio' => 'heroicon-o-play',
            'conclusao' => 'heroicon-o-check-circle',
            'cancelamento' => 'heroicon-o-x-circle',
            'reagendamento' => 'heroicon-o-calendar',
            'comentario' => 'heroicon-o-chat-bubble-left',
            'alteracao' => 'heroicon-o-pencil',
            default => 'heroicon-o-information-circle',
        };
    }

    public function getCorAcaoAttribute(): string
    {
        return match ($this->acao) {
            'criacao' => 'success',
            'inicio' => 'warning',
            'conclusao' => 'success',
            'cancelamento' => 'danger',
            'reagendamento' => 'info',
            'comentario' => 'gray',
            'alteracao' => 'primary',
            default => 'gray',
        };
    }
}
