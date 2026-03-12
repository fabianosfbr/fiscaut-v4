<?php

namespace App\Services\Filament\Contracts;

use Closure;

interface CanDehydrateState
{
    public function dehydrateStateUsing(): ?Closure;
}
