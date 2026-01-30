<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ScheduleStatusEnum: string implements HasIcon, HasColor, HasLabel
{

    case Active = 'active';
    case Inactive = 'inactive';
    case Trashed = 'trashed';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'warning',
            self::Trashed => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Active => 'heroicon-o-check-circle',
            self::Inactive => 'heroicon-o-document',
            self::Trashed => 'heroicon-o-x-circle',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Active => 'Ativo',
            self::Inactive => 'Inativo',
            self::Trashed => 'Excluído',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Active->value,
            self::Inactive->value,
            self::Trashed->value,
        ];
    }
}