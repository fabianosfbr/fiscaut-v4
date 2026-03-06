<?php

declare(strict_types=1);

namespace App\Neuron\Tools;

use App\Models\NotaFiscalEletronica;
use App\Models\Tagged;
use Illuminate\Database\Eloquent\Builder;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;

final class ConsultaNfeEntradaTool extends Tool
{
    public function __construct()
    {
        parent::__construct(
            name: 'consulta_nfe_entrada',
            description: 'Consulta NF-es de entrada (terceiros, própria, própria de terceiros) do issuer atual.',
        );
    }

    protected function properties(): array
    {
        return [
            ToolProperty::make('tipo_entrada', PropertyType::STRING, 'Filtro: terceiros | propria | propria_terceiros.', false, ['terceiros', 'propria', 'propria_terceiros']),
            ToolProperty::make('chave', PropertyType::STRING, 'Chave de acesso da NF-e (44 dígitos).', false),
            ToolProperty::make('nNF', PropertyType::INTEGER, 'Número da nota (nNF).', false),
            ToolProperty::make('serie', PropertyType::STRING, 'Série da nota.', false),
            ToolProperty::make('emitente_cnpj', PropertyType::STRING, 'CNPJ/CPF do emitente (somente dígitos).', false),
            ToolProperty::make('destinatario_cnpj', PropertyType::STRING, 'CNPJ/CPF do destinatário (somente dígitos).', false),
            ToolProperty::make('data_emissao_inicio', PropertyType::STRING, 'Data de emissão inicial (YYYY-MM-DD).', false),
            ToolProperty::make('data_emissao_fim', PropertyType::STRING, 'Data de emissão final (YYYY-MM-DD).', false),
            ToolProperty::make('data_entrada_inicio', PropertyType::STRING, 'Data de entrada inicial (YYYY-MM-DD).', false),
            ToolProperty::make('data_entrada_fim', PropertyType::STRING, 'Data de entrada final (YYYY-MM-DD).', false),
            ToolProperty::make('incluir_itens', PropertyType::BOOLEAN, 'Quando true, inclui itens apenas em modo detalhe.', false),
            ToolProperty::make('limit', PropertyType::INTEGER, 'Máximo de resultados (1 a 50).', false),
        ];
    }

    public function __invoke(
        ?string $tipo_entrada,
        ?string $chave,
        ?int $nNF,
        ?string $serie,
        ?string $emitente_cnpj,
        ?string $destinatario_cnpj,
        ?string $data_emissao_inicio,
        ?string $data_emissao_fim,
        ?string $data_entrada_inicio,
        ?string $data_entrada_fim,
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
        $emitente_cnpj = $this->onlyDigits($emitente_cnpj);
        $destinatario_cnpj = $this->onlyDigits($destinatario_cnpj);
        $tipo_entrada = $this->normalizeTipoEntrada($tipo_entrada);

        $query = NotaFiscalEletronica::query()
            ->where(function (Builder $q) use ($issuer, $tipo_entrada): void {
                if ($tipo_entrada === 'terceiros') {
                    $this->applyEntradaTerceiros($q, $issuer->cnpj);
                    return;
                }
                if ($tipo_entrada === 'propria') {
                    $this->applyEntradaPropria($q, $issuer->cnpj);
                    return;
                }
                if ($tipo_entrada === 'propria_terceiros') {
                    $this->applyEntradaPropriaTerceiros($q, $issuer->cnpj);
                    return;
                }

                $q->where(function (Builder $q) use ($issuer): void {
                    $this->applyEntradaTerceiros($q, $issuer->cnpj);
                })->orWhere(function (Builder $q) use ($issuer): void {
                    $this->applyEntradaPropria($q, $issuer->cnpj);
                })->orWhere(function (Builder $q) use ($issuer): void {
                    $this->applyEntradaPropriaTerceiros($q, $issuer->cnpj);
                });
            });

        if ($chave !== null) {
            $query->where('chave', $chave);
        }
        if ($nNF !== null) {
            $query->where('nNF', $nNF);
        }
        if ($serie !== null) {
            $query->where('serie', $serie);
        }
        if ($emitente_cnpj !== null) {
            $query->where('emitente_cnpj', $emitente_cnpj);
        }
        if ($destinatario_cnpj !== null) {
            $query->where('destinatario_cnpj', $destinatario_cnpj);
        }

        $this->applyDateRange($query, 'data_emissao', $data_emissao_inicio, $data_emissao_fim);
        $this->applyDateRange($query, 'data_entrada', $data_entrada_inicio, $data_entrada_fim);

        $records = $query
            ->with(['tagged.tag.category'])
            ->orderByDesc('data_emissao')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $includeItems = (bool) ($incluir_itens ?? false);
        $isDetail = $includeItems && $this->shouldIncludeItems($records->count(), $limit, $chave);

        return [
            'count' => $records->count(),
            'items' => $records->map(function (NotaFiscalEletronica $record) use ($issuer, $isDetail): array {
                return $this->mapNfe($record, $issuer->cnpj, $isDetail);
            })->values()->all(),
            'warnings' => $isDetail ? [] : ($includeItems ? ['Itens não incluídos: refine a consulta (ex.: chave ou limit=1).'] : []),
        ];
    }

    private function mapNfe(NotaFiscalEletronica $record, string $issuerCnpj, bool $includeItems): array
    {
        $tipoEntrada = $this->inferTipoEntrada($record, $issuerCnpj);

        $data = [
            'id' => $record->getKey(),
            'tipo_entrada' => $tipoEntrada,
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
            'etiquetas' => $this->mapEtiquetas($record),
        ];

        if ($includeItems) {
            $data['produtos'] = $record->produtos ?? [];
            $data['parcelas'] = $record->parcelas ?? [];
        }

        return $data;
    }

    private function mapEtiquetas(NotaFiscalEletronica $record): array
    {
        return $record->tagged->map(function (Tagged $tagged): array {
            $tag = $tagged->tag;
            $category = $tag?->category;
            $nomeEtiqueta = $tagged->tag_name ?? $tag?->name;
            $codigoEtiqueta = $tag?->code;

            return [
                'tag_id' => $tagged->tag_id,
                'codigo' => $codigoEtiqueta,
                'nome' => $nomeEtiqueta,
                'nome_com_codigo' => $this->composeNomeComCodigo($codigoEtiqueta, $nomeEtiqueta),
                'slug' => $tagged->tag_slug ?? $tag?->slug,
                'valor_aplicado' => is_numeric($tagged->value) ? (float) $tagged->value : null,
                'produtos' => is_array($tagged->product) ? array_values($tagged->product) : [],
                'categoria' => [
                    'id' => $category?->id,
                    'nome' => $category?->name,
                    'grupo' => $category?->grupo,
                    'is_devolucao' => $category?->is_devolucao,
                ],
            ];
        })->values()->all();
    }

    private function composeNomeComCodigo(?string $codigo, ?string $nome): ?string
    {
        $codigo = $this->normalizeString($codigo);
        $nome = $this->normalizeString($nome);

        if ($codigo === null && $nome === null) {
            return null;
        }

        if ($codigo === null) {
            return $nome;
        }

        if ($nome === null) {
            return $codigo;
        }

        return "{$codigo} - {$nome}";
    }

    private function normalizeTipoEntrada(?string $value): ?string
    {
        $value = $this->normalizeString($value);
        if ($value === null) {
            return null;
        }

        return match ($value) {
            'terceiros', 'propria', 'propria_terceiros' => $value,
            default => null,
        };
    }

    private function inferTipoEntrada(NotaFiscalEletronica $record, string $issuerCnpj): ?string
    {
        $tp = (string) ($record->tpNf ?? '');
        $emit = (string) ($record->emitente_cnpj ?? '');
        $dest = (string) ($record->destinatario_cnpj ?? '');

        if ($dest === $issuerCnpj && $emit !== $issuerCnpj && $tp === '1') {
            return 'terceiros';
        }
        if ($emit === $issuerCnpj && $tp === '0') {
            return 'propria';
        }
        if ($dest === $issuerCnpj && $emit !== $issuerCnpj && $tp === '0') {
            return 'propria_terceiros';
        }

        return null;
    }

    private function applyEntradaTerceiros(Builder $query, string $issuerCnpj): void
    {
        $query
            ->where('destinatario_cnpj', $issuerCnpj)
            ->where('emitente_cnpj', '<>', $issuerCnpj)
            ->whereIn('tpNf', [1, '1']);
    }

    private function applyEntradaPropria(Builder $query, string $issuerCnpj): void
    {
        $query
            ->where('emitente_cnpj', $issuerCnpj)
            ->whereIn('tpNf', [0, '0']);
    }

    private function applyEntradaPropriaTerceiros(Builder $query, string $issuerCnpj): void
    {
        $query
            ->where('destinatario_cnpj', $issuerCnpj)
            ->where('emitente_cnpj', '<>', $issuerCnpj)
            ->whereIn('tpNf', [0, '0']);
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
