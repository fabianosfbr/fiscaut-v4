<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ManutencaoTipoEnum: string implements HasLabel
{
    case PREVENTIVA = 'preventiva';
    case CORRETIVA = 'corretiva';

    public function getLabel(): string
    {
        return match ($this) {
            self::PREVENTIVA => 'Preventiva',
            self::CORRETIVA => 'Corretiva',
        };
    }

    public static function toArray(): array
    {
        $types = [];

        foreach (self::cases() as $type) {
            $types[$type->value] = $type->getLabel();
        }

        return $types;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PREVENTIVA => 'success',
            self::CORRETIVA => 'warning',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PREVENTIVA => 'heroicon-o-shield-check',
            self::CORRETIVA => 'heroicon-o-wrench-screwdriver',
        };
    }
}
