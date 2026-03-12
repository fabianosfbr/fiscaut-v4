<?php

namespace App\Services\Filament\Contracts;

use Closure;

interface CanHandleFieldState
{
    public function afterStateUpdated(): ?Closure;
}
