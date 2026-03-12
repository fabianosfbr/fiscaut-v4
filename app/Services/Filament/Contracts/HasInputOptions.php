<?php

namespace App\Services\Filament\Contracts;

interface HasInputOptions
{
    public function getInputMask(): ?string;

    public function getInputPlaceholder(): ?string;
}
