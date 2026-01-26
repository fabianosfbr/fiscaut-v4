<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusNfeEnum: int implements HasColor, HasLabel
{
    case ATIVA = 100;
    case CANCELADA = 101;
    case DENEGADA = 302;
    case AUTORIZADA_FORA_PRAZO = 150;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ATIVA => 'Ativa',
            self::CANCELADA => 'Cancelada',
            self::DENEGADA => 'Denegada',
            self::AUTORIZADA_FORA_PRAZO => 'Autorizada Fora de Prazo',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ATIVA => 'success',
            self::CANCELADA => 'danger',
            self::DENEGADA => 'danger',
            self::AUTORIZADA_FORA_PRAZO => 'warning',
        };
    }
}
