<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Pages;

use App\Filament\Condominio\Resources\IssuerControls\IssuerControlResource;
use App\Models\IssuerControl;
use App\Models\IssuerControlField;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateIssuerControl extends CreateRecord
{
    protected static string $resource = IssuerControlResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $issuerId = currentIssuer()->id;
        $fields = IssuerControlField::query()
            ->where('issuer_id', $issuerId)
            ->orderBy('order')
            ->get(['id', 'key']);

        $firstField = $fields->first();
        if (! $firstField) {
            throw new \RuntimeException('Nenhum campo de controle foi configurado para este issuer.');
        }

        $firstControl = IssuerControl::updateOrCreate(
            [
                'issuer_id' => $issuerId,
                'issuer_control_field_id' => $firstField->id,
            ],
            [
                'value' => $data[$firstField->key] ?? null,
            ]
        );

        foreach ($fields->skip(1) as $field) {
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

        return $firstControl;
    }
}
