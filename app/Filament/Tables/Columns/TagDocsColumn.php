<?php

namespace App\Filament\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;

class TagDocsColumn extends Column
{
    protected string $view = 'filament.tables.columns.tag-docs-column';

    protected bool $mostrarCodigo = false;

    public function showTagCode(bool|Closure $mostrarCodigo): static
    {
        if ($mostrarCodigo instanceof Closure) {
            $this->mostrarCodigo = $mostrarCodigo();
        } else {
            $this->mostrarCodigo = $mostrarCodigo;
        }

        return $this;
    }

    public function getShowTagCode(): bool
    {
        return $this->mostrarCodigo;
    }
}
