<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\CheckboxList;
use Illuminate\Support\Collection;

class CheckboxListTag extends CheckboxList
{
    protected string $view = 'filament.forms.components.checkbox-list-tag';

    public function getNormalizedOptions(): array
    {
        $options = $this->getOptions();

        if ($options instanceof Collection) {
            $options = $options->all();
        }

        if (! is_array($options) || $options === []) {
            return [];
        }

        if ($this->hasGroupedOptions($options)) {
            return $this->normalizeGroupedOptions($options);
        }

        return [
            [
                'key' => '__ungrouped__',
                'label' => null,
                'children' => $this->normalizeFlatOptions($options),
            ],
        ];
    }

    protected function hasGroupedOptions(array $options): bool
    {
        $firstOption = collect($options)->first();

        return (is_array($firstOption) && array_key_exists('tags', $firstOption))
            || (is_object($firstOption) && isset($firstOption->tags));
    }

    protected function normalizeGroupedOptions(array $options): array
    {
        return collect($options)
            ->map(function ($group, int $index): array {
                $groupId = (string) data_get($group, 'id', $index);
                $groupLabel = (string) data_get($group, 'name', 'Sem categoria');
                $tags = data_get($group, 'tags', []);

                if ($tags instanceof Collection) {
                    $tags = $tags->all();
                }

                return [
                    'key' => $groupId,
                    'label' => $groupLabel,
                    'children' => $this->normalizeTagOptions(is_array($tags) ? $tags : []),
                ];
            })
            ->filter(fn (array $group): bool => count($group['children']) > 0)
            ->values()
            ->all();
    }

    protected function normalizeFlatOptions(array $options): array
    {
        return collect($options)
            ->map(function ($label, $value): array {
                return [
                    'value' => (string) $value,
                    'label' => (string) $label,
                ];
            })
            ->values()
            ->all();
    }

    protected function normalizeTagOptions(array $tags): array
    {
        return collect($tags)
            ->map(function ($tag, int $index): array {
                $value = (string) data_get($tag, 'id', $index);
                $label = data_get($tag, 'namecode');

                if (! filled($label)) {
                    $label = collect([
                        data_get($tag, 'code'),
                        data_get($tag, 'name'),
                    ])->filter()->implode(' - ');
                }

                if (! filled($label)) {
                    $label = (string) data_get($tag, 'name', $value);
                }

                return [
                    'value' => $value,
                    'label' => (string) $label,
                ];
            })
            ->values()
            ->all();
    }
}
