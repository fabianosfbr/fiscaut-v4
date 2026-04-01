<?php

namespace App\Filament\Condominio\Pages\Kanban\Concerns;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Collection;

trait IsKanbanStatus
{
    public static function statuses(): Collection
    {
        return collect(static::kanbanCases())
            ->map(function (self $item) {
                return [
                    'id' => $item->getId(),
                    'title' => $item->getTitle(),
                    'icon' => $item->getStatusIcon(),
                    'color' => $item->getStatusColor(),
                ];
            });
    }

    public static function kanbanCases(): array
    {
        return static::cases();
    }

    public function getId(): string
    {
        return $this->value;
    }

    public function getTitle(): string
    {
        return $this instanceof HasLabel
            ? (string) $this->getLabel()
            : $this->value;
    }

    public function getStatusIcon(): ?string
    {
        if (! ($this instanceof HasIcon)) {
            return null;
        }

        $icon = $this->getIcon();

        return is_string($icon) ? $icon : null;
    }

    public function getStatusColor(): string|array|null
    {
        if (! ($this instanceof HasColor)) {
            return null;
        }

        return $this->getColor();
    }
}
