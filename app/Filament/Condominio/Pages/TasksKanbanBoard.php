<?php

namespace App\Filament\Condominio\Pages;

use App\Enums\TaskStatusCaseEnum;
use App\Filament\Condominio\Pages\Kanban\KanbanBoard;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Arr;
use Relaticle\Comments\Filament\Actions\CommentsAction;
use Relaticle\Comments\Filament\Infolists\Components\CommentsEntry;
use Illuminate\Support\Collection;

class TasksKanbanBoard extends KanbanBoard
{

    protected static bool $shouldRegisterNavigation = true;

    protected static string $model = Task::class;

    protected static string $statusEnum = TaskStatusCaseEnum::class;

    protected static string $recordStatusAttribute = 'status';

    protected string $editModalTitle = 'Editar Tarefa';

    protected bool $editModalSlideOver = true;

    protected string $editModalWidth = '2xl';

    protected string $editModalSaveButtonLabel = 'Salvar';

    protected string $editModalCancelButtonLabel = 'Cancelar';


    protected function getHeaderActions(): array
    {
        return [
            Action::make('add-task')
                ->label('Adicionar Tarefa')
                ->icon('heroicon-o-plus')
                ->schema([
                    TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->columnSpanFull(),
                    Textarea::make('description')
                        ->label('Descrição')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),
                    DateTimePicker::make('init_at')
                        ->label('Data Início')
                        ->columnSpan(1),
                    DateTimePicker::make('due_date')
                        ->label('Data de Entrega')
                        ->columnSpan(1),
                    Select::make('assignee_ids')
                        ->label('Responsáveis')
                        ->multiple()
                        ->options($this->getAssignableUsersOptions())
                        ->columnSpanFull(),

                ])
                ->action(function (array $data) {
                    $data['project'] = 'Condominio';
                    $data['status'] = 'todo';
                    $data['order_column'] = 1;
                    $task = Task::create($data);
                    $task->assignees()->sync($data['assignee_ids']);
                }),

        ];
    }

    protected function getEditModalFormSchema(null|int|string $recordId): array
    {
        $schema = [
            TextInput::make('title')
                ->label('Título')
                ->required()
                ->columnSpanFull(),
            Textarea::make('description')
                ->label('Descrição')
                ->rows(4)
                ->columnSpanFull(),
            DateTimePicker::make('init_at')
                ->label('Data Início')
                ->columnSpan(1),
            DateTimePicker::make('due_date')
                ->label('Data de Entrega')
                ->columnSpan(1),
            Select::make('assignee_ids')
                ->label('Responsáveis')
                ->multiple()
                ->options($this->getAssignableUsersOptions())
                ->columnSpanFull(),

        ];

        if (filled($recordId)) {
            $schema[] = Actions::make([
                // CommentsAction::make()
                //     ->label('Comentários')
                //     ->icon('heroicon-m-chat-bubble-left-right')
                //     ->modalWidth('2xl')
                //     ->record(fn(): ?Task => $this->getEloquentQuery()->find($recordId)),
            ])

                ->alignment(Alignment::End)
                ->columnSpanFull();

            $schema[] = CommentsEntry::make('comments')
                ->hiddenLabel()
                ->model(fn(): ?Task => $this->getEloquentQuery()->find($recordId))
                ->columnSpanFull();
        }

        return [
            Grid::make(2)->schema($schema),
        ];
    }

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

    protected function editRecord(int|string $recordId, array $data): void
    {
        $assigneeIds = collect(Arr::pull($data, 'assignee_ids', Arr::get($data, 'assignee_ids', [])))
            ->filter()
            ->map(fn($id): int => (int) $id)
            ->values()
            ->all();

        $task = $this->getEloquentQuery()->findOrFail($recordId);

        $task->update($data);
        $task->assignees()->sync($assigneeIds);
    }

    protected function records(): Collection
    {
        return Task::ordered()->get();
    }

    protected function getEditModalRecordData(int|string $recordId): array
    {
        $task = $this->getEloquentQuery()->findOrFail($recordId);

        return [
            ...$task->toArray(),
            'assignee_ids' => $task->assignees
                ->pluck('id')
                ->map(fn($id): string => (string) $id)
                ->all(),
        ];
    }
}
