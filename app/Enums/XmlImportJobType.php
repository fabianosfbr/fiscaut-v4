<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum XmlImportJobType: string implements HasColor, HasLabel
{
    case USER = 'user';
    case SYSTEM = 'system';
    case SEFAZ_NFE = 'sefaz_nfe';
    case SEFAZ_NFSE = 'sefaz_nfse';
    case SEFAZ_CTE = 'sefaz_cte';
    case SIEG = 'sieg';

    /**
     * Get all available types as an array
     */
    public static function toArray(): array
    {
        return [
            self::USER->value,
            self::SYSTEM->value,
            self::SEFAZ_NFE->value,
            self::SEFAZ_NFSE->value,
            self::SEFAZ_CTE->value,
            self::SIEG->value,

        ];
    }

    /**
     * Check if a given value is valid
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::toArray());
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::USER => 'Usuário',
            self::SYSTEM => 'Sistema',
            self::SEFAZ_NFE => 'SEFA NFE',
            self::SEFAZ_NFSE => 'SEFAZ NFSE',
            self::SEFAZ_CTE => 'SEFAZ CTE',
            self::SIEG => 'SIEG',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::USER => 'info',
            self::SYSTEM => 'success',
            self::SEFAZ_NFE => 'success',
            self::SEFAZ_NFSE => 'success',
            self::SEFAZ_CTE => 'success',
            self::SIEG => 'success',
        };
    }
}
