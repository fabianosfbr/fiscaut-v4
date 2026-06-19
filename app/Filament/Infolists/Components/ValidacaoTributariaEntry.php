<?php

namespace App\Filament\Infolists\Components;

use App\Models\NfeValidacaoTributaria;
use Filament\Infolists\Components\Entry;
use Filament\Support\Concerns\EvaluatesClosures;

class ValidacaoTributariaEntry extends Entry
{
    use EvaluatesClosures;

    protected string $view = 'filament.infolists.components.validacao-tributaria-entry';

    protected string|Closure|null $nfeId = null;

    public function nfeId(string|Closure|null $nfeId): static
    {
        $this->nfeId = $nfeId;

        return $this;
    }

    public function getNfeId(): ?string
    {
        return $this->evaluate($this->nfeId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getValidacoes(): array
    {
        $nfeId = $this->getNfeId();
        if ($nfeId === null) {
            return [];
        }

        return NfeValidacaoTributaria::porNfe((int) $nfeId)
            ->where('issuer_id', currentIssuer()->id)
            ->orderBy('severidade', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}
