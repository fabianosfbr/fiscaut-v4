<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AtaStatusEnum: string implements HasLabel
{
    case NOT_STARTED = 'nao_iniciada';
    case DRAFT = 'rascunho';
    case REVIEW = 'em_revisao';
    case PENDING_APPROVAL = 'em_aprovacao';
    case APPROVED = 'aprovada';
    case REJECTED = 'rejeitada';
    case SIGNED = 'assinada';
    case EM_LAVRATURA = 'em_lavratura';
    case REGISTERED = 'registrada';
    case PUBLISHED = 'publicada';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NOT_STARTED => 'Não Iniciada',
            self::DRAFT => 'Rascunho',
            self::REVIEW => 'Em Revisão',
            self::PENDING_APPROVAL => 'Em Aprovação',
            self::APPROVED => 'Aprovada',
            self::REJECTED => 'Rejeitada',
            self::SIGNED => 'Assinada',
            self::EM_LAVRATURA => 'Em Lavratura',
            self::REGISTERED => 'Registrada',
            self::PUBLISHED => 'Publicada',
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
