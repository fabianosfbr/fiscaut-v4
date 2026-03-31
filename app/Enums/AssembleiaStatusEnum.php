<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssembleiaStatusEnum: string implements HasLabel
{
    case DRAFT = 'rascunho';
    case SCHEDULED = 'agendada';
    case CALLED = 'convocada';
    case IN_PROGRESS = 'em_andamento';
    case SUSPENDED = 'suspensa';
    case FINISHED = 'encerrada';
    case CANCELED = 'cancelada';
    case POSTPONED = 'adiada';
    case NO_QUORUM = 'sem_quorum';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DRAFT => 'Rascunho',
            self::SCHEDULED => 'Agendada',
            self::CALLED => 'Convocada',
            self::IN_PROGRESS => 'Em Andamento',
            self::SUSPENDED => 'Suspensa',
            self::FINISHED => 'Encerrada',
            self::CANCELED => 'Cancelada',
            self::POSTPONED => 'Adiada',
            self::NO_QUORUM => 'Sem Quorum',
        };
    }

    public static function toArray(): array
    {
        $statuses = [];
        foreach (self::cases() as $status) {
            $statuses[$status->value] = $status->getLabel();
        }

        return $statuses;
    }
}
