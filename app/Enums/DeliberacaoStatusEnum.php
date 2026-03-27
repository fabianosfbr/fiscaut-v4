<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DeliberacaoStatusEnum: string implements HasLabel
{
    case PENDING = 'pendente';
    case IN_VOTING = 'em_votacao';
    case APPROVED = 'aprovada';
    case REJECTED = 'rejeitada';
    case TIED = 'empatada';
    case CANCELED = 'cancelada';
    case SUSPENDED = 'suspensa';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::IN_VOTING => 'Em Votação',
            self::APPROVED => 'Aprovada',
            self::REJECTED => 'Rejeitada',
            self::TIED => 'Empatada',
            self::CANCELED => 'Cancelada',
            self::SUSPENDED => 'Suspensa',
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
