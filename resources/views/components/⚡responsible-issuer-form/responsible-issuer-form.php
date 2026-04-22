<?php

use App\Enums\AreaAtendimentoEnum;
use App\Models\Issuer;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?int $issuerId = null;

    public ?Issuer $issuer = null;

    public function mount(?int $issuerId = null)
    {
        $this->issuerId = $issuerId;
        $this->issuer = Issuer::find($this->issuerId);
    }

    public function table(Table $table): Table
    {
        $issuer = $this->issuer ?? Issuer::find($this->issuerId);

        return $table
            ->relationship(fn () => $issuer->areaResponsibles())
            ->headerActions([
                Action::make('add')
                    ->label('Adicionar Novo')
                    ->schema($this->getSchemaForm())
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->action(function (array $data) {
                        $data['tenant_id'] = $this->issuer->tenant_id;
                        $data['issuer_id'] = $this->issuer->id;
                        $this->issuer->areaResponsibles()->create($data);
                    }),
            ])
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('area')
                    ->label('Área de Atendimento')
                    ->badge()
                    ->formatStateUsing(fn ($state): ?string => AreaAtendimentoEnum::tryFrom($state)?->getLabel() ?? $state)
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->modalWidth('md')
                    ->schema([
                        Select::make('area')
                            ->label('Área de Atendimento')
                            ->multiple()
                            ->options(AreaAtendimentoEnum::class)
                            ->required(),
                    ])
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->action(function (array $data, $record) {

                        $record->update($data);
                    }),
                DeleteAction::make(),
            ]);
    }

    private function getSchemaForm(): array
    {
        return [
            Section::make()
                ->schema([
                    Select::make('user_id')
                        ->label('Usuário')
                        ->relationship('user', 'name', function ($query) {
                            return $query->where('tenant_id', $this->issuer->tenant_id);
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('area')
                        ->label('Área de Atendimento')
                        ->multiple()
                        ->options(AreaAtendimentoEnum::class)
                        ->required(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
};
