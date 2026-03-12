<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Pages;

use App\Filament\Condominio\Resources\IssuerControls\IssuerControlResource;
use App\Models\IssuerControl;
use App\Models\IssuerControlField;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditIssuerControl extends EditRecord
{
    protected static string $resource = IssuerControlResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $issuerId = currentIssuer()->id;

        $controls = IssuerControl::query()
            ->where('issuer_id', $issuerId)
            ->get(['issuer_control_field_id', 'value'])
            ->keyBy('issuer_control_field_id');

        $fields = IssuerControlField::query()
            ->where('issuer_id', $issuerId)
            ->orderBy('order')
            ->get(['id', 'key']);

        $state = [];
        foreach ($fields as $field) {
            $state[$field->key] = $controls[$field->id]->value ?? null;
        }

        return $state;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $issuerId = currentIssuer()->id;

        $fields = IssuerControlField::query()
            ->where('issuer_id', $issuerId)
            ->orderBy('order')
            ->get(['id', 'key']);

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

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
