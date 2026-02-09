<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;



enum TipoFonteDeDadosEnum: string implements HasLabel
{

    case COLUNA = 'column';
    case CONSTANTE = 'constant';
    case CONSULTA = 'query';
    case PARAMETROS_GERAIS = 'parametros_gerais';

    public function getLabel(): string
    {
        return match ($this) {
            self::COLUNA => 'Coluna Excel',
            self::CONSTANTE => 'Valor Fixo',
            self::CONSULTA => 'Consulta ao Banco de Dados',
            self::PARAMETROS_GERAIS => 'Parâmetros Gerais',
            default => 'Tipo de Fonte de Dados não encontrado'
        };
    }
}
