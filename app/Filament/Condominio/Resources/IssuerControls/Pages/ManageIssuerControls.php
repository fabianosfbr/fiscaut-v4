<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Pages;

use App\Filament\Condominio\Resources\IssuerControls\IssuerControlResource;
use App\Models\IssuerGroupControl;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManageIssuerControls extends Page
{
    protected static string $resource = IssuerControlResource::class;

    protected static ?string $title = 'Controles';

    protected string $view = 'filament.condominio.resources.issuer-controls.pages.manage-issuer-controls';

    public function form(Schema $schema): Schema
    {
        $issuerId = currentIssuer()->id;

        $groups = IssuerGroupControl::query()
            ->where('issuer_id', $issuerId)
            ->orderBy('order')
            ->get();

        if ($groups->isEmpty()) {
            return $schema
                ->components([
                    Section::make('Sem grupos')
                        ->schema([
                            Placeholder::make('Nenhum grupo foi encontrado'),
                        ]),
                ])
                ->statePath('data');
        }

        $sections = [];
        foreach ($groups as $group) {
            $sections[] = Section::make($group->name)
                ->description($group->description ?: null)
                ->collapsible()
                ->collapsed()
                ->schema([
                    Livewire::make('issuer-group-contol-form', [
                        'groupId' => $group->id,
                    ]),
                ])
                ->columns(1);
        }

        return $schema
            ->components($sections);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('index')
                ->label('Voltar para a lista')
                ->url(route('filament.condominio.resources.issuer-controls.index')),

        ];
    }
}
