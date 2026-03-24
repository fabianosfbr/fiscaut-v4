<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IssuerControlPriorityEnum: string implements HasLabel
{
    case BAIXA = 'baixa';
    case MEDIA = 'media';
    case ALTA = 'alta';
    case CRITICA = 'critica';

    public function getLabel(): string
    {
        return match ($this) {
            self::BAIXA => 'Baixa',
            self::MEDIA => 'Média',
            self::ALTA => 'Alta',
            self::CRITICA => 'Crítica',
        };
    }

    public static function toArray(): array
    {
        $priorities = [];

        foreach (self::cases() as $priority) {
            $priorities[$priority->value] = $priority->getLabel();
        }

        return $priorities;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::BAIXA => 'gray',
            self::MEDIA => 'info',
            self::ALTA => 'warning',
            self::CRITICA => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::BAIXA => 'heroicon-o-arrow-down',
            self::MEDIA => 'heroicon-o-minus',
            self::ALTA => 'heroicon-o-arrow-up',
            self::CRITICA => 'heroicon-o-exclamation-triangle',
        };
    }

    public function getOrder(): int
    {
        return match ($this) {
            self::BAIXA => 1,
            self::MEDIA => 2,
            self::ALTA => 3,
            self::CRITICA => 4,
        };
    }

    public function isUrgent(): bool
    {
        return in_array($this, [self::ALTA, self::CRITICA]);
    }
}
