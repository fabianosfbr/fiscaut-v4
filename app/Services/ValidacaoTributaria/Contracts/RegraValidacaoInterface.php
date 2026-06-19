<?php

namespace App\Services\ValidacaoTributaria\Contracts;

use App\Models\Issuer;
use App\Services\ValidacaoTributaria\DTO\ResultadoValidacao;

interface RegraValidacaoInterface
{
    /**
     * @param  array<int, array<string, mixed>>  $produtos  Lista de produtos da NF-e
     * @param  array<string, mixed>  $nota  Dados do cabeçalho da NF-e
     * @return ResultadoValidacao[]
     */
    public function validar(array $produtos, array $nota, Issuer $issuer): array;

    public function nome(): string;

    public function descricao(): string;
}
