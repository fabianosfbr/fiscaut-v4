<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IssuerDocumentTypeEnum: string implements HasColor, HasLabel
{
    case REGULAMENTO_INTERNO = 'regulamento_interno';
    case CONVENCAO = 'convencao';
    case ESTATUTO = 'estatuto';
    case CODIGO_OBRAS = 'codigo_obras';
    case PLANTA_ELETRICA = 'planta_eletrica';
    case PLANTA_HIDRAULICA = 'planta_hidraulica';
    case PLANTA_ARQUITETONICA = 'planta_arquitetonica';
    case PLANTA_ESTRUTURAL = 'planta_estrutural';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::REGULAMENTO_INTERNO => 'Regulamento Interno',
            self::CONVENCAO => 'Convenção',
            self::ESTATUTO => 'Estatuto',
            self::CODIGO_OBRAS => 'Código de Obras',
            self::PLANTA_ELETRICA => 'Planta Elétrica',
            self::PLANTA_HIDRAULICA => 'Planta Hidráulica',
            self::PLANTA_ARQUITETONICA => 'Planta Arquitetônica',
            self::PLANTA_ESTRUTURAL => 'Planta Estrutural',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::REGULAMENTO_INTERNO => 'gray',
            self::CONVENCAO => 'blue',
            self::ESTATUTO => 'green',
            self::CODIGO_OBRAS => 'yellow',
            self::PLANTA_ELETRICA => 'red',
            self::PLANTA_HIDRAULICA => 'blue',
            self::PLANTA_ARQUITETONICA => 'green',
            self::PLANTA_ESTRUTURAL => 'yellow',
        };
    }

    public static function toArray(): array
    {
        $values = [];
        foreach (self::cases() as $status) {
            $values[$status->value] = $status->getLabel();
        }

        return $values;
    }

    public static function getDocumentTypes(): array
    {
        return self::toArray();
    }
}
