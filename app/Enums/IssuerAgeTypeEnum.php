<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IssuerAgeTypeEnum: string implements HasLabel
{
    case AGO = 'AGO';
    case AGE = 'AGE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AGO => 'Assembleia Geral Ordinária',
            self::AGE => 'Assembleia Geral Extraordinária',
        };
    }
}
