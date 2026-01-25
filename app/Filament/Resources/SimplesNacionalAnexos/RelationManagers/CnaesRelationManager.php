<?php

namespace App\Filament\Resources\SimplesNacionalAnexos\RelationManagers;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Resources\RelationManagers\RelationManager;

class CnaesRelationManager extends RelationManager
{
    protected static string $relationship = 'cnaes';

    protected static ?string $recordTitleAttribute = 'codigo_cnae';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
               TextInput::make('codigo_cnae')
                            ->label('Código CNAE')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->helperText('Código CNAE conforme tabela oficial')
                            ->placeholder('Ex: 4711-3/01')
                            ->rules([
                                'required',
                                'string',
                                'max:20',
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        // Validação do formato CNAE (XXXX-X/XX)
                                        if (!preg_match('/^\d{4}-\d{1}\/\d{2}$/', $value)) {
                                            $fail('O código CNAE deve seguir o formato XXXX-X/XX (ex: 4711-3/01).');
                                        }
                                    };
                                },
                            ]),
                Textarea::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(500)
                            ->rows(3)
                            ->helperText('Descrição da atividade econômica')
                            ->columnSpanFull(),
                Toggle::make('ativo')
                            ->label('Ativo')
                            ->default(true)
                            ->helperText('Define se este CNAE está ativo para uso no sistema')
                            ->inline(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo_cnae')
                    ->label('Código CNAE')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Código CNAE copiado!')
                    ->copyMessageDuration(1500),

                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(60)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 60) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable()
                    ->wrap(),
                IconColumn::make('ativo')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('ativo')
                    ->label('Status')
                    ->placeholder('Todos os status')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Novo CNAE'),
                DeleteAction::make()
                    ->label('Excluir'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),
                DeleteAction::make()
                    ->label('Excluir'),
            ]);
    }
}
