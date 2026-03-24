<?php

namespace App\Observers;

use App\Enums\ManutencaoStatusEnum;
use App\Enums\ManutencaoPrioridadeEnum;
use App\Models\Manutencao;
use App\Models\ManutencaoHistorico;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ManutencaoObserver
{
    /**
     * Handle the Manutencao "updated" event.
     */
    public function updated(Manutencao $manutencao): void
    {
        // Campos que devem gerar histórico de alterações
        $auditFields = [
            'custo_real',
            'usuario_responsavel',
            'data_programada',
            'data_execucao',
            'data_conclusao',
            'prioridade',
            'status'
        ];

        // Verificar cada campo auditável para alterações
        foreach ($auditFields as $field) {
            if ($manutencao->isDirty($field)) {
                $this->createAuditRecord($manutencao, $field);
            }
        }
    }

    /**
     * Cria um registro de auditoria para o campo alterado
     */
    private function createAuditRecord(Manutencao $manutencao, string $field): void
    {
        $oldValue = $manutencao->getOriginal($field);
        $newValue = $manutencao->$field;

        // Gerar observação personalizada para cada tipo de alteração
        $observation = $this->generateObservation($field, $oldValue, $newValue);

        // Criar registro no histórico
        $userId = Auth::check() ? Auth::id() : 3; // 3 = usuário sistema

        $historico = new ManutencaoHistorico();
        $historico->manutencao_id = $manutencao->id;
        $historico->usuario_id = $userId;
        $historico->acao = 'alteracao_campo';
        $historico->status_anterior = $field === 'status' ? $oldValue : null;
        $historico->status_novo = $field === 'status' ? $newValue : null;
        $historico->observacao = $observation;
        $historico->dados_alterados = json_encode([
            'campo' => $field,
            'valor_anterior' => $oldValue,
            'valor_novo' => $newValue
        ]);
        $historico->created_at = now();
        $historico->save();
    }

    /**
     * Gera observação personalizada para cada tipo de alteração
     */
    private function generateObservation(string $field, $oldValue, $newValue): string
    {
        switch ($field) {
            case 'status':
                $oldLabel = $this->getStatusLabel($oldValue);
                $newLabel = $this->getStatusLabel($newValue);
                return "Alteração de status de '{$oldLabel}' para '{$newLabel}'";

            case 'custo_real':
                $oldFormatted = $this->formatCurrency($oldValue);
                $newFormatted = $this->formatCurrency($newValue);
                return "Alteração do custo real de {$oldFormatted} para {$newFormatted}";

            case 'data_programada':
            case 'data_execucao':
            case 'data_conclusao':
                $fieldName = $this->getFormattedFieldName($field);
                $oldFormatted = $this->formatDate($oldValue);
                $newFormatted = $this->formatDate($newValue);
                return "Alteração da data de {$fieldName} de {$oldFormatted} para {$newFormatted}";

            case 'usuario_responsavel':
                $oldResponsavel = $oldValue ?: 'não definido';
                $newResponsavel = $newValue ?: 'não definido';
                return "Alteração do responsável de '{$oldResponsavel}' para '{$newResponsavel}'";

            case 'prioridade':
                $oldLabel = $this->getPrioridadeLabel($oldValue);
                $newLabel = $this->getPrioridadeLabel($newValue);
                return "Alteração da prioridade de '{$oldLabel}' para '{$newLabel}'";

            default:
                return "Alteração do campo '{$field}'";
        }
    }

    /**
     * Formata o rótulo do status
     */
    private function getStatusLabel($status): string
    {
        if (is_null($status)) {
            return 'não definido';
        }

        // Se for um enum, usar o método getLabel()
        if ($status instanceof ManutencaoStatusEnum) {
            return $status->getLabel();
        }

        // Se for string, converter para enum e usar getLabel()
        if (is_string($status)) {
            try {
                return ManutencaoStatusEnum::from($status)->getLabel();
            } catch (\ValueError) {
                return $status;
            }
        }

        return $status;
    }

    /**
     * Formata o rótulo da prioridade
     */
    private function getPrioridadeLabel($prioridade): string
    {
        if (is_null($prioridade)) {
            return 'não definido';
        }

        // Se for um enum, usar o método getLabel()
        if ($prioridade instanceof ManutencaoPrioridadeEnum) {
            return $prioridade->getLabel();
        }

        // Se for string, converter para enum e usar getLabel()
        if (is_string($prioridade)) {
            try {
                return ManutencaoPrioridadeEnum::from($prioridade)->getLabel();
            } catch (\ValueError) {
                return $prioridade;
            }
        }

        return $prioridade;
    }

    /**
     * Formata valor monetário
     */
    private function formatCurrency(?float $value): string
    {
        if (is_null($value) || $value == 0) {
            return 'não definido';
        }

        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    /**
     * Formata data
     */
    private function formatDate(?string $date): string
    {
        if (is_null($date) || empty($date)) {
            return 'não definida';
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            return $date;
        }
    }

    /**
     * Formata nome do campo de data
     */
    private function getFormattedFieldName(string $field): string
    {
        return match ($field) {
            'data_programada' => 'programação',
            'data_execucao' => 'execução',
            'data_conclusao' => 'conclusão',
            default => str_replace(['data_', '_'], ['', ' '], $field)
        };
    }
}
