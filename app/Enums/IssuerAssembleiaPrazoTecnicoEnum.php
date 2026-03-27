<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IssuerAssembleiaPrazoTecnicoEnum: string implements HasLabel
{
    case ANTES_DO_PRAZO = 'antes_do_prazo';
    case PRIMEIRO = 'primeiro';
    case SEGUNDO = 'segundo';
    case TERCEIRO = 'terceiro';
    case QUARTO = 'quarto';
    case ATRASADO = 'atrasado';

    public function getLabel(): string
    {
        return match ($this) {
            self::ANTES_DO_PRAZO => 'Antes do Prazo Técnico',
            self::PRIMEIRO => '1º Prazo Técnico',
            self::SEGUNDO => '2º Prazo Técnico',
            self::TERCEIRO => '3º Prazo Técnico',
            self::QUARTO => '4º Prazo Técnico',
            self::ATRASADO => 'Atrasado',
        };
    }

    public function getToolTip(): string
    {
        return match ($this) {
            self::ANTES_DO_PRAZO => 'O prazo técnico ainda não começou',
            self::PRIMEIRO => 'Começou o prazo técnico',
            self::SEGUNDO => 'Estamos no prazo técnico',
            self::TERCEIRO => 'Estamos na data limite do edital considerando chegando a data limite do edital',
            self::QUARTO => 'Ainda não chegou a data limite do edital',
            self::ATRASADO => 'Ultrapassou a data limite do edital',
        };
    }

    public function getColorHex(): string
    {
        return match ($this) {
            self::PRIMEIRO => '#7bd88f',
            self::SEGUNDO => '#28a745',
            self::TERCEIRO => '#f0ad4e',
            self::QUARTO => '#fd7e14',
            self::ATRASADO => '#dc3545',
            self::ANTES_DO_PRAZO => '#6c757d',
        };
    }

    public function getFilamentColor(): string
    {
        return match ($this) {
            self::PRIMEIRO, self::SEGUNDO => 'success',
            self::TERCEIRO, self::QUARTO => 'warning',
            self::ATRASADO => 'danger',
            self::ANTES_DO_PRAZO => 'gray',
        };
    }

    public static function fromIndex(int $index): self
    {
        return match (true) {
            $index <= 1 => self::PRIMEIRO,
            $index === 2 => self::SEGUNDO,
            $index === 3 => self::TERCEIRO,
            default => self::QUARTO,
        };
    }
}
