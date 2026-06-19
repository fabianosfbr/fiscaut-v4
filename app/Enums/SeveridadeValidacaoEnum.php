<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SeveridadeValidacaoEnum: string implements HasColor, HasLabel
{
    case INFO = 'info';
    case AVISO = 'aviso';
    case ERRO = 'erro';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INFO => 'Informativo',
            self::AVISO => 'Aviso',
            self::ERRO => 'Erro',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INFO => 'info',
            self::AVISO => 'warning',
            self::ERRO => 'danger',
        };
    }
}
