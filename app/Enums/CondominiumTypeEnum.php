<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CondominiumTypeEnum: string implements HasLabel
{
    case RESIDENCIAL_HORIZONTAL = 'residencial_horizontal';
    case APARTAMENTO_VERTICAL = 'apartamento_vertical';
    case COMERCIAL = 'comercial';
    case MISTO = 'misto';
    case GALPOES = 'galpoes';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::RESIDENCIAL_HORIZONTAL => 'Residencial Horizontal',
            self::APARTAMENTO_VERTICAL => 'Apartamento Vertical',
            self::COMERCIAL => 'Comercial',
            self::MISTO => 'Misto',
            self::GALPOES => 'Galpões',
        };
    }
}
