<?php

namespace App\Services\Filament\Contracts;

interface HasAcceptedFileTypes
{
    public function getAcceptedFileTypes(): ?array;
}
