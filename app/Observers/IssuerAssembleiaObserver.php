<?php

namespace App\Observers;

use App\Enums\AssembleiaStatusEnum;
use App\Enums\AtaStatusEnum;
use App\Enums\DeliberacaoStatusEnum;
use App\Models\IssuerAssembleia;
use App\Models\IssuerAssembleiaEventLog;
use Illuminate\Support\Facades\Auth;

class IssuerAssembleiaObserver
{
    public function created(IssuerAssembleia $issuerAssembleia): void
    {
        $userId = Auth::check() ? Auth::id() : 1;

        $statusLabel = $issuerAssembleia->assembleia_status instanceof AssembleiaStatusEnum
            ? $issuerAssembleia->assembleia_status->getLabel()
            : ($issuerAssembleia->assembleia_status ?? 'rascunho');

        IssuerAssembleiaEventLog::registrarCriacao(
            $issuerAssembleia->id,
            $userId,
            "Assembleia criada com status: {$statusLabel}"
        );
    }

    public function updated(IssuerAssembleia $issuerAssembleia): void
    {
        $auditFields = [
            'assembleia_status',
            'ata_status',
            'deliberacao_status',
            'data_realizacao',
            'type',
            'data_limite_edital',
            'data_limite_ago',
            'mandato_fim',
            'mandato_conselho_fim',
            'mandato_banco_fim',
            'vigencia_date',
        ];

        foreach ($auditFields as $field) {
            if ($issuerAssembleia->isDirty($field)) {
                $this->createAuditRecord($issuerAssembleia, $field);
            }
        }
    }

    private function createAuditRecord(IssuerAssembleia $issuerAssembleia, string $field): void
    {
        $oldValue = $issuerAssembleia->getOriginal($field);
        $newValue = $issuerAssembleia->$field;

        $userId = Auth::check() ? Auth::id() : 3;

        $observation = $this->generateObservation($field, $oldValue, $newValue);

        IssuerAssembleiaEventLog::registrarAlteracaoCampo(
            $issuerAssembleia->id,
            $userId,
            $field,
            $this->formatValue($oldValue),
            $this->formatValue($newValue),
            $observation
        );
    }

    private function generateObservation(string $field, $oldValue, $newValue): string
    {
        $oldLabel = $this->getFieldLabel($oldValue, $field);
        $newLabel = $this->getFieldLabel($newValue, $field);

        $fieldName = $this->getFieldName($field);

        return "Alteração de {$fieldName}: {$oldLabel} → {$newLabel}";
    }

    private function getFieldLabel($value, string $field): string
    {
        if (is_null($value)) {
            return 'não definido';
        }

        if ($value instanceof \BackedEnum) {
            return $value->getLabel();
        }

        $enumClass = match ($field) {
            'assembleia_status' => AssembleiaStatusEnum::class,
            'ata_status' => AtaStatusEnum::class,
            'deliberacao_status' => DeliberacaoStatusEnum::class,
            default => null,
        };

        if ($enumClass && is_string($value)) {
            try {
                return $enumClass::from($value)->getLabel();
            } catch (\ValueError) {
                return $value;
            }
        }

        if ($value instanceof \DateTime || is_string($value)) {
            try {
                return \Carbon\Carbon::parse($value)->format('d/m/Y');
            } catch (\Exception) {
                return (string) $value;
            }
        }

        if (is_object($value)) {
            if ($value instanceof \BackedEnum) {
                return $value->getLabel();
            }

            return 'objeto';
        }

        return (string) $value;
    }

    private function getFieldName(string $field): string
    {
        return match ($field) {
            'assembleia_status' => 'Status da Assembleia',
            'ata_status' => 'Status da Ata',
            'deliberacao_status' => 'Status da Deliberação',
            'data_realizacao' => 'Data de Realização',
            'type' => 'Tipo',
            'data_limite_edital' => 'Data Limite Edital',
            'data_limite_ago' => 'Data Limite AGO',
            'mandato_fim' => 'Fim do Mandato',
            'mandato_conselho_fim' => 'Fim do Mandato Conselho',
            'mandato_banco_fim' => 'Fim do Mandato Banco',
            'vigencia_date' => 'Data de Vigência',
            default => $field,
        };
    }

    private function formatValue($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \DateTime || is_string($value)) {
            try {
                return \Carbon\Carbon::parse($value)->format('d/m/Y');
            } catch (\Exception) {
                return (string) $value;
            }
        }

        if (is_object($value)) {
            return 'objeto';
        }

        return (string) $value;
    }

    public function deleted(IssuerAssembleia $issuerAssembleia): void
    {
        //
    }

    public function restored(IssuerAssembleia $issuerAssembleia): void
    {
        //
    }

    public function forceDeleted(IssuerAssembleia $issuerAssembleia): void
    {
        //
    }
}
