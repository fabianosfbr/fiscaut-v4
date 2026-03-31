<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IssuerControlFrequencyEnum: string implements HasLabel
{
    case DIARIA = 'diaria';
    case SEMANAL = 'semanal';
    case QUINZENAL = 'quinzenal';
    case MENSAL = 'mensal';
    case BIMESTRAL = 'bimestral';
    case TRIMESTRAL = 'trimestral';
    case SEMESTRAL = 'semestral';
    case ANUAL = 'anual';

    public function getLabel(): string
    {
        return match ($this) {
            self::DIARIA => 'Diária',
            self::SEMANAL => 'Semanal',
            self::QUINZENAL => 'Quinzenal',
            self::MENSAL => 'Mensal',
            self::BIMESTRAL => 'Bimestral',
            self::TRIMESTRAL => 'Trimestral',
            self::SEMESTRAL => 'Semestral',
            self::ANUAL => 'Anual',
        };
    }

    public static function toArray(): array
    {
        $frequencies = [];

        foreach (self::cases() as $frequency) {
            $frequencies[$frequency->value] = $frequency->getLabel();
        }

        return $frequencies;
    }

    public function getDays(): int
    {
        return match ($this) {
            self::DIARIA => 1,
            self::SEMANAL => 7,
            self::QUINZENAL => 15,
            self::MENSAL => 30,
            self::BIMESTRAL => 60,
            self::TRIMESTRAL => 90,
            self::SEMESTRAL => 180,
            self::ANUAL => 365,
        };
    }

    public function getMonths(): int
    {
        return match ($this) {
            self::DIARIA => 0,
            self::SEMANAL => 0,
            self::QUINZENAL => 0,
            self::MENSAL => 1,
            self::BIMESTRAL => 2,
            self::TRIMESTRAL => 3,
            self::SEMESTRAL => 6,
            self::ANUAL => 12,
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::DIARIA => 'heroicon-o-sun',
            self::SEMANAL => 'heroicon-o-calendar-days',
            self::QUINZENAL => 'heroicon-o-calendar-days',
            self::MENSAL => 'heroicon-o-calendar',
            self::BIMESTRAL => 'heroicon-o-calendar',
            self::TRIMESTRAL => 'heroicon-o-calendar',
            self::SEMESTRAL => 'heroicon-o-calendar',
            self::ANUAL => 'heroicon-o-calendar',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DIARIA => 'danger',
            self::SEMANAL => 'warning',
            self::QUINZENAL => 'warning',
            self::MENSAL => 'info',
            self::BIMESTRAL => 'info',
            self::TRIMESTRAL => 'success',
            self::SEMESTRAL => 'success',
            self::ANUAL => 'gray',
        };
    }
}
