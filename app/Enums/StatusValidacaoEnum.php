<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusValidacaoEnum: string implements HasColor, HasLabel
{
    case PENDENTE = 'pendente';
    case CONFIRMADO = 'confirmado';
    case IGNORADO = 'ignorado';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDENTE => 'Pendente',
            self::CONFIRMADO => 'Confirmado',
            self::IGNORADO => 'Ignorado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDENTE => 'warning',
            self::CONFIRMADO => 'success',
            self::IGNORADO => 'gray',
        };
    }
}
