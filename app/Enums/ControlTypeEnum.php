<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ControlTypeEnum: string implements HasLabel
{
    case SEGURO = 'seguro';
    case AVCB = 'avcb';
    case ESTANQUEIDADE = 'estanqueidade';
    case SPDA = 'spda';
    case LAUDO_ELETRICO = 'laudo_eletrico';
    case PISCINA = 'piscina';
    case BRIGADA_INCENDIO = 'brigada_incendio';
    case PLANO_CONTINGENCIA = 'plano_contingencia';
    case MANUTENCAO_PROGRAMADA = 'manutencao_programada';
    case OBRIGACAO_ACESSORIA = 'obrigacao_acessoria';

    public function getLabel(): string
    {
        return match ($this) {
            self::SEGURO => 'Seguro',
            self::AVCB => 'AVCB',
            self::ESTANQUEIDADE => 'Estanqueidade',
            self::SPDA => 'SPDA',
            self::LAUDO_ELETRICO => 'Laudo Elétrico',
            self::PISCINA => 'Piscina',
            self::BRIGADA_INCENDIO => 'Brigada de Incêndio',
            self::PLANO_CONTINGENCIA => 'Plano de Contingência',
            self::MANUTENCAO_PROGRAMADA => 'Manutenção Programada',
            self::OBRIGACAO_ACESSORIA => 'Obrigação Acessoria',
        };
    }

    public static function toArray()
    {
        $statuses = [];

        foreach (self::cases() as $status) {
            $statuses[$status->value] = $status->getLabel();
        }

        return $statuses;
    }
}
