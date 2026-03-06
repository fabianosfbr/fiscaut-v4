<?php

declare(strict_types=1);

namespace App\Neuron\Tools;

use App\Models\NotaFiscalEletronica;
use Illuminate\Database\Eloquent\Builder;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

final class ConsultaNfeSaidaTool extends Tool
{
    public function __construct()
    {
        parent::__construct(
            name: 'consulta_nfe_saida',
            description: 'Consulta NF-es de saída (emitidas pelo issuer atual).',
        );
    }

    protected function properties(): array
    {
        return [
            ToolProperty::make('chave', PropertyType::STRING, 'Chave de acesso da NF-e (44 dígitos).', false),
            ToolProperty::make('nNF', PropertyType::INTEGER, 'Número da nota (nNF).', false),
            ToolProperty::make('serie', PropertyType::STRING, 'Série da nota.', false),
            ToolProperty::make('destinatario_cnpj', PropertyType::STRING, 'CNPJ/CPF do destinatário (somente dígitos).', false),
            ToolProperty::make('data_emissao_inicio', PropertyType::STRING, 'Data de emissão inicial (YYYY-MM-DD).', false),
            ToolProperty::make('data_emissao_fim', PropertyType::STRING, 'Data de emissão final (YYYY-MM-DD).', false),
            ToolProperty::make('incluir_itens', PropertyType::BOOLEAN, 'Quando true, inclui itens apenas em modo detalhe.', false),
            ToolProperty::make('limit', PropertyType::INTEGER, 'Máximo de resultados (1 a 50).', false),
        ];
    }

    public function __invoke(
        ?string $chave,
        ?int $nNF,
        ?string $serie,
        ?string $destinatario_cnpj,
        ?string $data_emissao_inicio,
        ?string $data_emissao_fim,
        ?bool $incluir_itens,
        ?int $limit,
    ): array {
        $issuer = currentIssuer();
        if ($issuer === null) {
            return [
                'count' => 0,
                'items' => [],
                'warnings' => ['Issuer não identificado no contexto atual.'],
            ];
        }

        $limit = max(1, min(50, $limit ?? 10));

        $chave = $this->normalizeString($chave);
        $serie = $this->normalizeString($serie);
        $destinatario_cnpj = $this->onlyDigits($destinatario_cnpj);

        $query = NotaFiscalEletronica::query()
            ->where('emitente_cnpj', $issuer->cnpj);

        if ($chave !== null) {
            $query->where('chave', $chave);
        }
        if ($nNF !== null) {
            $query->where('nNF', $nNF);
        }
        if ($serie !== null) {
            $query->where('serie', $serie);
        }
        if ($destinatario_cnpj !== null) {
            $query->where('destinatario_cnpj', $destinatario_cnpj);
        }

        $this->applyDateRange($query, 'data_emissao', $data_emissao_inicio, $data_emissao_fim);

        $records = $query
            ->orderByDesc('data_emissao')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $includeItems = (bool) ($incluir_itens ?? false);
        $isDetail = $includeItems && $this->shouldIncludeItems($records->count(), $limit, $chave);

        return [
            'count' => $records->count(),
            'items' => $records->map(function (NotaFiscalEletronica $record) use ($isDetail): array {
                return $this->mapNfe($record, $isDetail);
            })->values()->all(),
            'warnings' => $isDetail ? [] : ($includeItems ? ['Itens não incluídos: refine a consulta (ex.: chave ou limit=1).'] : []),
        ];
    }

    private function mapNfe(NotaFiscalEletronica $record, bool $includeItems): array
    {
        $data = [
            'id' => $record->getKey(),
            'tipo_documento' => 'saida',
            'chave' => $record->chave,
            'nNF' => $record->nNF,
            'serie' => $record->serie,
            'data_emissao' => $record->data_emissao?->toDateTimeString(),
            'data_entrada' => $record->data_entrada?->toDateTimeString(),
            'emitente' => [
                'cnpj' => $record->emitente_cnpj,
                'razao_social' => $record->emitente_razao_social,
            ],
            'destinatario' => [
                'cnpj' => $record->destinatario_cnpj,
                'razao_social' => $record->destinatario_razao_social,
            ],
            'status_nota' => [
                'value' => $record->status_nota?->value,
                'label' => $record->status_nota?->getLabel(),
            ],
            'vNfe' => $record->vNfe,
            'cfops' => is_array($record->cfops) ? array_values($record->cfops) : [],
            'produtos_count' => $record->num_produtos ?? null,
            'difal_total' => (float) ($record->vICMSUFDest ?? 0.0),
        ];

        if ($includeItems) {
            $data['produtos'] = $record->produtos ?? [];
            $data['parcelas'] = $record->parcelas ?? [];
        }

        return $data;
    }

    private function applyDateRange(Builder $query, string $column, ?string $start, ?string $end): void
    {
        $start = $this->normalizeString($start);
        $end = $this->normalizeString($end);

        if ($start !== null) {
            $query->whereDate($column, '>=', $start);
        }
        if ($end !== null) {
            $query->whereDate($column, '<=', $end);
        }
    }

    private function shouldIncludeItems(int $count, int $limit, ?string $chave): bool
    {
        if ($count <= 0) {
            return false;
        }

        if ($count === 1 && $chave !== null) {
            return true;
        }

        return $limit === 1 && $count === 1;
    }

    private function onlyDigits(?string $value): ?string
    {
        $value = $this->normalizeString($value);
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits !== '' ? $digits : null;
    }

    private function normalizeString(?string $value): ?string
    {
        $value = $value !== null ? trim($value) : null;

        return ($value === null || $value === '') ? null : $value;
    }
}

