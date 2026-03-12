<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Pages;

use App\Filament\Condominio\Resources\IssuerControls\IssuerControlResource;
use App\Models\IssuerGroupControl;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditIssuerControlGroup extends Page
{
    protected static string $resource = IssuerControlResource::class;

    protected string $view = 'filament.condominio.resources.issuer-controls.pages.edit-issuer-control-group';

    public int $groupId;

    public ?IssuerGroupControl $group = null;

    public function mount(int $groupId): void
    {
        $this->groupId = $groupId;

        $this->group = IssuerGroupControl::query()
            ->where('issuer_id', currentIssuer()->id)
            ->where('id', $groupId)
            ->first();

        if ($this->group) {
            static::$title = 'Editar Grupo: '.$this->group->name;
        }
    }

    public function form(Schema $schema): Schema
    {
        if (! $this->group) {
            return $schema->components([
                Section::make('Grupo não encontrado')
                    ->schema([
                        TextEntry::make('O grupo informado não existe para este issuer.'),
                    ]),
            ]);
        }

        return $schema->components([
            Section::make($this->group->name)
                ->description($this->group->description ?: null)
                ->schema([
                    Livewire::make('issuer-group-contol-form', [
                        'groupId' => $this->group->id,
                    ]),
                ])
                ->columns(1),
        ]);

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
