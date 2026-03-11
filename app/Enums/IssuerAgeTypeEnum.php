<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IssuerAgeTypeEnum: string implements HasLabel
{
    case AGE = 'AGE';
    case AGO = 'AGO';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AGE => 'Assembleia Geral Extraordinária',
            self::AGO => 'Assembleia Geral Ordinária',
        };
    }
}
