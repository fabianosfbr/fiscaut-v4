<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum XmlImportJobType: string implements HasColor, HasLabel
{
    case USER = 'user';
    case SYSTEM = 'system';

    /**
     * Get all available types as an array
     */
    public static function toArray(): array
    {
        return [
            self::USER->value,
            self::SYSTEM->value,
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
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::USER => 'info',
            self::SYSTEM => 'success',
        };
    }
}
