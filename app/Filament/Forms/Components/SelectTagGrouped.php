<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Illuminate\Database\Eloquent\Collection;

class SelectTagGrouped extends Field
{
    protected string $view = 'filament.forms.components.select-tag-grouped';

    protected Collection|Closure $options;

    protected bool $multiple = false;

    protected ?string $placeholder = null;

    protected ?string $searchPlaceholder = null;

    protected bool $searchable = true;

    protected bool $clearable = true;

    public function options(Collection|Closure $options): static
    {
        if (is_callable($options)) {
            $val = call_user_func($options);

            $this->options = $val;
        } else {
            $this->options = $options;
        }

        return $this;
    }

    public function multiple(bool $condition = true): static
    {
        $this->multiple = $condition;

        return $this;
    }

    public function placeholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function searchPlaceholder(?string $searchPlaceholder): static
    {
        $this->searchPlaceholder = $searchPlaceholder;

        return $this;
    }

    public function searchable(bool $condition = true): static
    {
        $this->searchable = $condition;

        return $this;
    }

    public function clearable(bool $condition = true): static
    {
        $this->clearable = $condition;

        return $this;
    }

    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getSearchPlaceholder(): ?string
    {
        return $this->searchPlaceholder ?? 'Buscar etiquetas...';
    }

    public function getSearchable(): bool
    {
        return $this->searchable;
    }

    public function getClearable(): bool
    {
        return $this->clearable;
    }

    public function getOptions(): Collection
    {
        return $this->options;
    }

    /**
     * Processa as categorias e tags para o formato esperado pelo TomSelect
     */
    public function getProcessedOptions(): array
    {
        $categories = $this->getOptions();

        // Verificar se há categorias
        if (! $categories || $categories->isEmpty()) {
            return [];
        }

        $processedOptions = [];

        foreach ($categories as $category) {
            $tags = [];

            // Verificar se a categoria tem tags
            if ($category->tags && $category->tags->count() > 0) {
                foreach ($category->tags as $tag) {
                    $tags[] = [
                        'id' => (string) $tag->id,
                        'code' => $tag->code,
                        'name' => $tag->name,
                        'display' => $tag->code.' - '.$tag->name,
                        'color' => $tag->color ?? $category->color ?? '#3b82f6',
                        'category_id' => $category->id,
                        'category_name' => $category->name,
                    ];
                }
            }

            // Só adicionar categoria se tiver tags
            if (! empty($tags)) {
                $processedOptions[] = [
                    'text' => $category->name,
                    'children' => $tags,
                ];
            }
        }

        // dd($processedOptions);

        return $processedOptions;
    }
}
