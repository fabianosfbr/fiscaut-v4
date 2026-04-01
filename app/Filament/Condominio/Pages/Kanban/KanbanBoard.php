<?php

namespace App\Filament\Condominio\Pages\Kanban;

use App\Filament\Condominio\Pages\Kanban\Concerns\HasStatusChange;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class KanbanBoard extends Page
{
    use HasStatusChange;

    protected string $view = 'filament.condominio.pages.kanban.kanban-board';

    protected static bool $shouldRegisterNavigation = false;

    public bool $disableEditModal = false;

    public ?array $editModalFormState = [];

    public null|int|string $editModalRecordId = null;

    protected string $editModalTitle = 'Edit Record';

    protected bool $editModalSlideOver = false;

    protected string $editModalWidth = '2xl';

    protected string $editModalSaveButtonLabel = 'Save';

    protected string $editModalCancelButtonLabel = 'Cancel';

    // Page Attributes 

    protected static string $model;

    protected static string $statusEnum;

    protected static string $recordTitleAttribute = 'title';

    protected static string $recordStatusAttribute = 'status';


    protected static string $headerView = 'filament.condominio.pages.kanban.kanban-header';

    protected static string $recordView = 'filament.condominio.pages.kanban.kanban-record';

    protected static string $statusView = 'filament.condominio.pages.kanban.kanban-status';

    protected static string $scriptsView = 'filament.condominio.pages.kanban.kanban-scripts';


    public function recordClicked(int|string $recordId, array $data): void
    {
        $this->editModalRecordId = $recordId;

        /**
         * todo - the following line is a hacky fix
         * figure why sometimes form schema is created before this
         * method when a RichText is present in the form schema
         **/
        $this->form($this->form);
        $this->form->fill($this->getEditModalRecordData($recordId, $data));

        $this->dispatch('open-modal', id: 'kanban--edit-record-modal');
    }

    public function editModalFormSubmitted(): void
    {
        $this->editRecord($this->editModalRecordId, $this->form->getState(), $this->editModalFormState);

        $this->editModalRecordId = null;
        $this->form->fill();

        $this->dispatch('close-modal', id: 'kanban--edit-record-modal');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->getEditModalFormSchema($this->editModalRecordId))
            ->statePath('editModalFormState')
            ->model($this->editModalRecordId ? static::$model::find($this->editModalRecordId) : static::$model);
    }

    protected function getEditModalFormSchema(null|int|string $recordId): array
    {
        return [
            TextInput::make(static::$recordTitleAttribute),
        ];
    }

    protected function getEditModalRecordData(int|string $recordId, array $data): array
    {
        return $this->getEloquentQuery()->find($recordId)->toArray();
    }

    protected function editRecord(int|string $recordId, array $data, array $state): void
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


    // Page functions

    protected function statuses(): Collection
    {
        return static::$statusEnum::statuses();
    }

    protected function records(): Collection
    {
        return $this->getEloquentQuery()
            ->when(method_exists(static::$model, 'scopeOrdered'), fn($query) => $query->ordered())
            ->get();
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

    protected function filterRecordsByStatus(Collection $records, array $status): array
    {
        $statusIsCastToEnum = $records->first()?->getAttribute(static::$recordStatusAttribute) instanceof UnitEnum;

        $filter = $statusIsCastToEnum
            ? static::$statusEnum::from($status['id'])
            : $status['id'];

        return $records->where(static::$recordStatusAttribute, $filter)->all();
    }

    protected function getEloquentQuery(): Builder
    {
        return static::$model::query();
    }
}
