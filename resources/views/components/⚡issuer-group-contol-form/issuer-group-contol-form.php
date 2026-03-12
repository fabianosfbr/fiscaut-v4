<?php

use App\Models\IssuerControl;
use App\Models\IssuerControlField;
use App\Models\IssuerGroupControl;
use App\Services\Filament\FormBuilderRender;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Component;

new class extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public int $groupId;

    public ?array $data = [];

    public function mount(int $groupId): void
    {
        $this->groupId = $groupId;

        $issuerId = currentIssuer()->id;

        $controls = IssuerControl::query()
            ->where('issuer_id', $issuerId)
            ->whereHas('field', fn ($query) => $query->where('issuer_group_control_id', $groupId))
            ->get(['issuer_control_field_id', 'value'])
            ->keyBy('issuer_control_field_id');

        $fields = IssuerControlField::query()
            ->where('issuer_id', $issuerId)
            ->where('issuer_group_control_id', $groupId)
            ->orderBy('order')
            ->get(['id', 'key']);

        $state = [];
        foreach ($fields as $field) {
            $state[$field->key] = $controls[$field->id]->value ?? null;
        }

        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        $issuerId = currentIssuer()->id;

        $fields = FormBuilderRender::make()
            ->form(IssuerControlField::class)
            ->modifyQueryUsing(function ($query) use ($issuerId) {
                return $query
                    ->where('issuer_control_fields.issuer_id', $issuerId)
                    ->where('issuer_control_fields.issuer_group_control_id', $this->groupId)
                    ->orderBy('issuer_control_fields.order');
            })
            ->requiredCondition(fn ($field) => $field->getRequired())
            ->render();

        return $schema
            ->components($fields)
            ->statePath('data');
    }

    public function save(): void
    {
        $issuerId = currentIssuer()->id;

        $fields = IssuerControlField::query()
            ->where('issuer_id', $issuerId)
            ->where('issuer_group_control_id', $this->groupId)
            ->orderBy('order')
            ->get(['id', 'key']);

        $data = $this->form->getState();

        foreach ($fields as $field) {
            if (! array_key_exists($field->key, $data)) {
                continue;
            }

            IssuerControl::updateOrCreate(
                [
                    'issuer_id' => $issuerId,
                    'issuer_control_field_id' => $field->id,
                ],
                [
                    'value' => $data[$field->key],
                ]
            );
        }

        $group = IssuerGroupControl::find($this->groupId);

        Notification::make()
            ->title('Grupo salvo com sucesso')
            ->body($group ? $group->name : null)
            ->success()
            ->send();
    }
};
