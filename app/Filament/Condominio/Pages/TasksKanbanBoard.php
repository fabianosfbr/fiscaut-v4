<?php

namespace App\Filament\Condominio\Pages;

use App\Enums\TaskStatusEnum;
use App\Filament\Condominio\Pages\Kanban\KanbanBoard;
use App\Models\Task;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class TasksKanbanBoard extends KanbanBoard
{
    protected static string $model = Task::class;

    protected static bool $shouldRegisterNavigation = true;

    protected bool $editModalSlideOver = true;

    protected string $editModalTitle = 'Editar Tarefa';

    protected string $editModalSaveButtonLabel = 'Salvar';

    protected string $editModalCancelButtonLabel = 'Fechar';

    protected static string $statusEnum = TaskStatusEnum::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected function getEditModalFormSchema(int|string|null $recordId): array
    {
        return [
            TextInput::make('title')
                ->label('Título')
                ->required(),
            Textarea::make('description')
                ->label('Descrição')
                ->rows(4),
            DateTimePicker::make('due_date')
                ->label('Data de Entrega'),
            Select::make('status')
                ->multiple()
                ->options([
                    'draft' => 'Draft',
                    'reviewing' => 'Reviewing',
                    'published' => 'Published',
                ])
        ];
    }

    // protected function getEditModalRecordData(int|string $recordId): array
    // {
    //     $task = $this->getEloquentQuery()->findOrFail($recordId);

    //     return [
    //         ...$task->toArray(),
    //         'assignee_ids' => $task->assignees
    //             ->pluck('id')
    //             ->map(fn ($id): string => (string) $id)
    //             ->all(),
    //     ];
    // }

    // protected function editRecord(int|string $recordId, array $data, array $state): void
    // {
    //     dd($state);
    //     $assigneeIds = collect(Arr::pull($data, 'assignee_ids', Arr::get($state, 'assignee_ids', [])))
    //         ->filter()
    //         ->map(fn ($id): int => (int) $id)
    //         ->values()
    //         ->all();

    //     $task = $this->getEloquentQuery()->findOrFail($recordId);

    //     $task->update($data);
    //     $task->assignees()->sync($assigneeIds);
    // }

    // protected function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->with(['assignees' => fn ($query) => $query->orderBy('name')]);
    // }

    protected function getAssignableUsersOptions(): array
    {
        $issuerUsers = currentIssuer()?->users()
            ->orderBy('name')
            ->pluck('name', 'users.id')
            ->mapWithKeys(fn($name, $id): array => [(string) $id => $name])
            ->all();

        if (filled($issuerUsers)) {
            return $issuerUsers;
        }

        return [];
    }
}
