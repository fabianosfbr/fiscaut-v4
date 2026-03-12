<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Schemas;

use App\Models\IssuerControlField;
use App\Services\Filament\FormBuilderRender;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;

class IssuerControlForm
{
    public static function configure(Schema $schema): Schema
    {
        $components = FormBuilderRender::make()
            ->form(IssuerControlField::class)
            ->modifyQueryUsing(function ($query) {
                $issuerId = currentIssuer()->id;

                return $query
                    ->where('issuer_control_fields.issuer_id', $issuerId)
                    ->leftJoin('issuer_group_controls', 'issuer_group_controls.id', '=', 'issuer_control_fields.issuer_group_control_id')
                    ->orderBy('issuer_group_controls.order')
                    ->orderBy('issuer_control_fields.order')
                    ->select('issuer_control_fields.*');
            })
            ->group('groupControl.name')
            ->container(Section::class)
            ->requiredCondition(fn ($field) => $field->getRequired())
            ->render();

        $components = $components instanceof Component ? [$components] : $components;

        return $schema
            ->components([
                ...$components,
            ]);
    }
}
