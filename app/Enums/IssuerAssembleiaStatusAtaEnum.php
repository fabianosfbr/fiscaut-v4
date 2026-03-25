<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IssuerAssembleiaStatusAtaEnum: string implements HasLabel
{
    case EM_LAVRATURA = 'em_lavratura';
    case GERADA = 'gerada';
    case REGISTRADA = 'registrada';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EM_LAVRATURA => 'Em lavratura',
            self::GERADA => 'Gerada',
            self::REGISTRADA => 'Registrada',
        };
    }
}
