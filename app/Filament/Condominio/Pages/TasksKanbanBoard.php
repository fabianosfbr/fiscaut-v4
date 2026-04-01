<?php

namespace App\Filament\Condominio\Pages;

use App\Enums\TaskStatusEnum;
use App\Filament\Condominio\Pages\Kanban\KanbanBoard;
use App\Models\Task;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

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
            TextInput::make('title')->label('Título'),
            Textarea::make('description')->label('Descrição'),            
            DateTimePicker::make('due_date')->label('Data de Entrega'),
        ];
    }


}
