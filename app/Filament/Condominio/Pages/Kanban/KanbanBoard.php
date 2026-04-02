<?php

namespace App\Filament\Condominio\Pages\Kanban;

use App\Enums\TaskStatusCaseEnum;
use App\Filament\Condominio\Pages\Kanban\Concerns\HasKanbanCaseChange;
use App\Models\Task;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class KanbanBoard extends Page
{
    use HasKanbanCaseChange;

    protected string $view = 'filament.condominio.pages.kanban.kanban-board';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public bool $disableEditModal = false;

    protected static string $model;

    protected static string $statusEnum;

    protected static string $recordStatusAttribute = 'status';

    protected string $editModalTitle = 'Editar Tarefa';

    protected bool $editModalSlideOver = false;

    protected string $editModalWidth = '2xl';

    protected string $editModalSaveButtonLabel = 'Salvar';

    protected string $editModalCancelButtonLabel = 'Cancelar';

    public null|int|string $editModalRecordId = null;


    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->getEditModalFormSchema($this->editModalRecordId))
            ->statePath('data');
    }

    public function save(): void
    {

        $this->editRecord($this->editModalRecordId, $this->form->getState());

        $this->editModalRecordId = null;

        $this->dispatch('close-modal', id: 'kanban--edit-record-modal');
    }

    public function recordClicked(int|string $recordId): void
    {
        $this->editModalRecordId = $recordId;

        $this->form->fill($this->getEditModalRecordData($recordId));

        $this->dispatch('open-modal', id: 'kanban--edit-record-modal');
    }

    protected function statuses(): Collection
    {
        return static::$statusEnum::statuses();
    }

    protected function getEditModalFormSchema(null | int | string $recordId): array
    {
        return [
            TextEntry::make('title')
                ->label('É necessário sobrescrever o método getEditModalFormSchema para editar os campos')
                ->disabled()
        ];
    }



    protected function getViewData(): array
    {
        $records = $this->records();
        $statuses = $this->statuses()
            ->map(function ($status) use ($records) {
                $status['records'] = $this->filterRecordsByStatus($records, $status);

                return $status;
            });

        return [
            'statuses' => $statuses,
        ];
    }

    protected function records(): Collection
    {
        return $this->getEloquentQuery()
            ->when(method_exists(static::$model, 'scopeOrdered'), fn($query) => $query->ordered())
            ->get();
    }

    protected function getEloquentQuery(): Builder
    {
        return static::$model::query();
    }

    protected function filterRecordsByStatus(Collection $records, array $status): array
    {
        $statusIsCastToEnum = $records->first()?->getAttribute(static::$recordStatusAttribute) instanceof UnitEnum;

        $filter = $statusIsCastToEnum
            ? static::$statusEnum::from($status['id'])
            : $status['id'];

        return $records->where(static::$recordStatusAttribute, $filter)->all();
    }

    protected function getEditModalRecordData(int|string $recordId): array
    {
        return $this->getEloquentQuery()->find($recordId)->toArray();
    }

    protected function editRecord(int|string $recordId, array $data): void
    {
        $this->getEloquentQuery()->find($recordId)->update($data);
    }


    protected function getEditModalTitle(): string
    {
        return $this->editModalTitle;
    }

    protected function getEditModalSlideOver(): bool
    {
        return $this->editModalSlideOver;
    }

    protected function getEditModalWidth(): string
    {
        return $this->editModalWidth;
    }

    protected function getEditModalSaveButtonLabel(): string
    {
        return $this->editModalSaveButtonLabel;
    }

    protected function getEditModalCancelButtonLabel(): string
    {
        return $this->editModalCancelButtonLabel;
    }
}
