<?php

namespace App\Filament\Resources\Layouts\RelationManagers;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LayoutColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\Layouts\Schemas\LayoutColumnSchema;
use Filament\Forms;
use Filament\Schemas\Schema;

class LayoutColumnsRelationManager extends RelationManager
{
    protected static string $relationship = 'layoutColumns';

    protected static ?string $recordTitleAttribute = 'excel_column_name';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('excel_column_name')
                    ->label('Nome da Coluna no Excel')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('target_column_name')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('data_type')
                    ->label('Tipo de Dado')
                    ->live()
                    ->options([
                        'text' => 'Texto',
                        'number' => 'Número',
                        'date' => 'Data',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('format')
                    ->label('Formato')
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_sanitize')
                    ->label('Limpar conteúdo')
                    ->default(false)
                    ->columnSpanFull(),
                // ...
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('excel_column_name')
            ->heading('Estrutura da Planilha')
            ->description('Estrutura de colunas que serão importados do arquivo Excel')
            ->modelLabel('Coluna')
            ->pluralModelLabel('Colunas')
            ->emptyStateHeading('Nenhuma coluna cadastrada')
            ->emptyStateDescription('Quando cadastrar uma coluna ela aparecerá aqui')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('excel_column_name')
                    ->label('Nome da Coluna no Excel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('target_column_name')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('data_type')
                    ->label('Tipo de Dado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'text' => 'gray',
                        'number' => 'primary',
                        'date' => 'warning',
                        'boolean' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('format')
                    ->label('Formato')
                    ->placeholder('Nenhum'),

                IconColumn::make('is_required')
                    ->label('Obrigatório')
                    ->boolean(),

                IconColumn::make('is_sanitize')
                    ->label('Sanitizado')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('data_type')
                    ->label('Tipo de Dado')
                    ->options([
                        'text' => 'Texto',
                        'number' => 'Número',
                        'date' => 'Data',
                        'boolean' => 'Booleano',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Adicionar Novo'),


            ])
            ->defaultSort('excel_column_name', 'asc');
    }
}
