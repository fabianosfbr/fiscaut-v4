<?php

namespace App\Services\Filament\Contracts;

interface HasFileUploadOptions
{
    public function getFileDirectory(): string|\Closure|null;

    public function getFileMaxSize(): ?int;

    public function shouldPreserveFilenames(): bool;

    public function getFileDisk(): ?string;
}
