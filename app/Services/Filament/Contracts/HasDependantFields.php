<?php

namespace App\Services\Filament\Contracts;

use Filament\Forms\Components\Field;

interface HasDependantFields
{
    public function getDependantFields(): array;

    public function hasDependantFields(): bool;

    public function getDependantFieldsContainer(): ?Field;
}
