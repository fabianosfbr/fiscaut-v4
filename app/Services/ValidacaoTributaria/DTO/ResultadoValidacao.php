<?php

namespace App\Services\ValidacaoTributaria\DTO;

use App\Enums\SeveridadeValidacaoEnum;

class ResultadoValidacao
{
    public function __construct(
        public readonly string $regra,
        public readonly ?string $tipoImposto,
        public readonly ?int $nItem,
        public readonly SeveridadeValidacaoEnum $severidade,
        public readonly string $mensagem,
        public readonly ?string $valorEsperado = null,
        public readonly ?string $valorEncontrado = null,
    ) {}

    public function toArray(): array
    {
        return [
            'regra' => $this->regra,
            'tipo_imposto' => $this->tipoImposto,
            'n_item' => $this->nItem,
            'severidade' => $this->severidade->value,
            'mensagem' => $this->mensagem,
            'valor_esperado' => $this->valorEsperado,
            'valor_encontrado' => $this->valorEncontrado,
        ];
    }
}
