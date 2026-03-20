<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ManutencaoStatusEnum: string implements HasLabel
{
    case PROGRAMADA = 'programada';
    case EM_ANDAMENTO = 'em_andamento';
    case CONCLUIDA = 'concluida';
    case CANCELADA = 'cancelada';
    case ATRASADA = 'atrasada';

    public function getLabel(): string
    {
        return match ($this) {
            self::PROGRAMADA => 'Programada',
            self::EM_ANDAMENTO => 'Em Andamento',
            self::CONCLUIDA => 'Concluída',
            self::CANCELADA => 'Cancelada',
            self::ATRASADA => 'Atrasada',
        };
    }

    public static function toArray(): array
    {
        $statuses = [];

        foreach (self::cases() as $status) {
            $statuses[$status->value] = $status->getLabel();
        }

        return $statuses;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PROGRAMADA => 'info',
            self::EM_ANDAMENTO => 'warning',
            self::CONCLUIDA => 'success',
            self::CANCELADA => 'gray',
            self::ATRASADA => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PROGRAMADA => 'heroicon-o-calendar',
            self::EM_ANDAMENTO => 'heroicon-o-play',
            self::CONCLUIDA => 'heroicon-o-check-circle',
            self::CANCELADA => 'heroicon-o-x-circle',
            self::ATRASADA => 'heroicon-o-exclamation-triangle',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PROGRAMADA, self::EM_ANDAMENTO]);
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::CONCLUIDA, self::CANCELADA]);
    }
}
