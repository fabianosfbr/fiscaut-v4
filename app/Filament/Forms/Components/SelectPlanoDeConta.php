<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class SelectPlanoDeConta extends Field
{
    protected string $view = 'filament.forms.components.select-plano-de-conta';

    protected string $apiEndpoint = '';

    public function apiEndpoint(string $endpoint): static
    {
        $this->apiEndpoint = $endpoint;

        return $this;
    }

    public function getApiEndpoint(): string
    {
        return $this->apiEndpoint = config('app.url').'/filament/remote-select/search';
    }
}
