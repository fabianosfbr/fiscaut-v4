<?php

namespace App\Filament\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;

class TagBadgesColumn extends Column
{
    protected string $view = 'filament.tables.columns.tag-badges-column';

    protected bool | Closure $showTagCode = false;

    protected int | Closure $maxVisible = 2;

    protected string | Closure $emptyText = '—';

    public function showTagCode(bool | Closure $showTagCode): static
    {
        $this->showTagCode = $showTagCode;

        return $this;
    }

    public function maxVisible(int | Closure $maxVisible): static
    {
        $this->maxVisible = $maxVisible;

        return $this;
    }

    public function emptyText(string | Closure $emptyText): static
    {
        $this->emptyText = $emptyText;

        return $this;
    }

    public function getShowTagCode(): bool
    {
        return (bool) $this->evaluate($this->showTagCode);
    }

    public function getMaxVisible(): int
    {
        return (int) $this->evaluate($this->maxVisible);
    }

    public function getEmptyText(): string
    {
        return (string) $this->evaluate($this->emptyText);
    }
}

