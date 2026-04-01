<?php

namespace App\Enums;

use App\Filament\Condominio\Pages\Kanban\Concerns\IsKanbanStatus;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TaskStatusEnum: string implements HasLabel, HasColor, HasIcon
{
    use IsKanbanStatus;

    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public function getLabel(): string
    {
        return match ($this) {
            self::TODO => 'A Fazer',
            self::IN_PROGRESS => 'Em Andamento',
            self::DONE => 'Concluído',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TODO => 'gray',
            self::IN_PROGRESS => 'gray',
            self::DONE => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::TODO => 'heroicon-o-clipboard-document-list',
            self::IN_PROGRESS => 'heroicon-o-arrow-path',
            self::DONE => 'heroicon-o-check-circle',
        };
    }
}
