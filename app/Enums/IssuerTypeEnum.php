<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IssuerTypeEnum: string implements HasLabel
{
    case PADRAO = 'padrao';
    case CONDOMINIO = 'condominio';
    case ASSOCIACAO = 'associacao';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PADRAO => 'Padrão',
            self::CONDOMINIO => 'Condomínio',
            self::ASSOCIACAO => 'Associação',
        };
    }

    public static function toArray()
    {
        $statuses = [];

        foreach (self::cases() as $status) {
            $statuses[$status->value] = $status->getLabel();
        }

        return $statuses;
    }
}
