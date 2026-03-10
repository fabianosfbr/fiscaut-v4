<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AreaAtendimentoEnum: string implements HasLabel
{
    case CONSULTOR = 'consultor';
    case CONTAS_A_PAGAR = 'contas_a_pagar';
    case FECHAMENTO = 'fechamento';
    case DEPARTAMENTO_PESSOAL = 'departamento_pessoal';
    case GERENTE = 'gerente';
    case FINANCEIRO = 'financeiro';
    case COBRANCA = 'cobranca';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CONSULTOR => 'Consultor',
            self::CONTAS_A_PAGAR => 'Contas a Pagar',
            self::FECHAMENTO => 'Fechamento',
            self::DEPARTAMENTO_PESSOAL => 'Departamento Pessoal',
            self::GERENTE => 'Gerente',
            self::FINANCEIRO => 'Financeiro',
            self::COBRANCA => 'Cobrança',
        };
    }

    public static function toArray(): array
    {
        $areas = [];

        foreach (self::cases() as $area) {
            $areas[$area->value] = $area->getLabel();
        }

        return $areas;
    }
}
