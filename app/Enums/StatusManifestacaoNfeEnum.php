<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusManifestacaoNfeEnum: string implements HasColor, HasLabel
{
    case CONFIRMACAO_OPERACAO = '210200';
    case CIENCIA_OPERACAO = '0';
    case DESCONHECIMENTO_OPERACAO = '210220';
    case OPERACAO_NAO_REALIZADA = '210240';

    public function getLabel(): ?string
    {
        return match ($this) {

            self::CONFIRMACAO_OPERACAO => 'Confirmada',
            self::CIENCIA_OPERACAO => 'Ciente',
            self::DESCONHECIMENTO_OPERACAO => 'Desconhecida',
            self::OPERACAO_NAO_REALIZADA => 'Não Realizada',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CONFIRMACAO_OPERACAO => 'success',
            self::CIENCIA_OPERACAO => 'info',
            self::DESCONHECIMENTO_OPERACAO => 'danger',
            self::OPERACAO_NAO_REALIZADA => 'danger',
        };
    }

    /**
     * Obtém o tipo de evento SEFAZ correspondente
     */
    public function getTipoEvento(): ?int
    {
        return match ($this) {
            self::CONFIRMACAO_OPERACAO => 210200,
            self::CIENCIA_OPERACAO => 210210,
            self::DESCONHECIMENTO_OPERACAO => 210220,
            self::OPERACAO_NAO_REALIZADA => 210240,
            default => null,
        };
    }

    /**
     * Cria uma instância do enum a partir do tipo de evento SEFAZ
     */
    public static function fromTipoEvento(int $tipoEvento): ?self
    {
        return match ($tipoEvento) {
            210200 => self::CONFIRMACAO_OPERACAO,
            210210 => self::CIENCIA_OPERACAO,
            210220 => self::DESCONHECIMENTO_OPERACAO,
            210240 => self::OPERACAO_NAO_REALIZADA,
            default => null,
        };
    }

    /**
     * Verifica se o tipo de manifestação requer justificativa
     */
    public function requerJustificativa(): bool
    {
        return in_array($this, [self::DESCONHECIMENTO_OPERACAO, self::OPERACAO_NAO_REALIZADA]);
    }
}
