<?php

namespace App\Enums;

use App\Filament\Condominio\Pages\Kanban\Concerns\IsKanbanStatus;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TaskStatusEnum: string implements HasLabel, HasColor
{
    use IsKanbanStatus;

    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public function getLabel(): string
    {
        return match ($this) {
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::DONE => 'Done',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TODO => 'gray',
            self::IN_PROGRESS => 'blue',
            self::DONE => 'green',
        };
    }
}
