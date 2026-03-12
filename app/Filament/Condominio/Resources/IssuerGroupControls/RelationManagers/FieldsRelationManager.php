<?php

namespace App\Filament\Condominio\Resources\IssuerGroupControls\RelationManagers;

use App\Enums\FieldAttributesEnum;
use App\Enums\FieldTypesEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn as RepeaterTableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';

    protected static ?string $recordTitleAttribute = 'label';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('Chave')
                    ->helperText('Identificador único do campo para o Issuer.')
                    ->required()
                    ->maxLength(255),

                TextInput::make('label')
                    ->label('Rótulo')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->label('Tipo')
                    ->options(FieldTypesEnum::toArray())
                    ->required()
                    ->reactive(),

                Select::make('attribute')
                    ->label('Atributo')
                    ->options(fn($get) => FieldTypesEnum::tryFrom($get('type'))?->attributes() ?? [])
                    ->reactive()
                    ->searchable(),

                Toggle::make('required')
                    ->label('Obrigatório')
                    ->default(false),

                TextInput::make('order')
                    ->label('Ordem')
                    ->numeric()
                    ->default(0),

                TagsInput::make('accepted_types')
                    ->label('Tipos de arquivo aceitos (MIME)')
                    ->placeholder('application/pdf')
                    ->visible(fn($get) => $get('attribute') === FieldAttributesEnum::File->value)
                    ->columnSpanFull(),

                Repeater::make('options')
                    ->label('Opções')
                    ->table([
                        RepeaterTableColumn::make('label'),
                        RepeaterTableColumn::make('value'),
                    ])
                    ->schema([
                        TextInput::make('label')
                            ->label('Rótulo')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('value')
                            ->label('Valor')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->afterStateHydrated(function (Repeater $component, $state): void {
                        if (!is_array($state) || empty($state)) {
                            return;
                        }

                        if (Arr::isAssoc($state)) {
                            $component->state(
                                collect($state)
                                    ->map(fn($label, $value) => [
                                        'label' => $label,
                                        'value' => $value,
                                    ])
                                    ->values()
                                    ->all()
                            );
                        }
                    })
                    ->dehydrateStateUsing(function ($state): array {
                        if (!is_array($state)) {
                            return [];
                        }

                        return collect($state)
                            ->filter(fn($row) => is_array($row) && filled($row['value'] ?? null))
                            ->mapWithKeys(fn($row) => [$row['value'] => $row['label'] ?? $row['value']])
                            ->toArray();
                    })
                    ->visible(fn($get) => $get('type') === FieldTypesEnum::Select->value)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Descrição')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->heading('Campos do Grupo')
            ->description('Configure os campos que serão renderizados neste grupo de controle.')
            ->modelLabel('Campo')
            ->pluralModelLabel('Campos')
            ->emptyStateHeading('Nenhum campo cadastrado')
            ->emptyStateDescription('Crie um campo para começar a montar o formulário.')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('order')
                    ->label('Ordem')
                    ->sortable(),

                TextColumn::make('label')
                    ->label('Rótulo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('key')
                    ->label('Chave')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('attribute')
                    ->label('Atributo')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                IconColumn::make('required')
                    ->label('Obrigatório')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(FieldTypesEnum::toArray()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Adicionar Campo')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['issuer_id'] = $this->getOwnerRecord()->issuer_id;

                        return $data;
                    }),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }
}
