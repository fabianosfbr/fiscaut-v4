<?php

namespace App\Filament\Resources\Layouts\RelationManagers;

use App\Filament\Resources\Layouts\Schemas\LayoutRuleSchema;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LayoutRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'layoutRules';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Regras de Exportação';

    public function form(Schema $schema): Schema
    {
        return LayoutRuleSchema::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->heading('Regras de Exportação')
            ->description('Regras que definem como os dados serão transformados durante a exportação')
            ->modelLabel('Regra')
            ->pluralModelLabel('Regras')
            ->emptyStateHeading('Nenhuma regra cadastrada')
            ->emptyStateDescription('Quando você cadastrar uma regra, ela aparecerá aqui')
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rule_type')
                    ->label('Tipo de Regra')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'Desconhecido')
                    ->color(fn (string $state): string => match ((string) $state) {
                        'data_da_operacao' => 'warning',
                        'operacao_de_debito' => 'danger',
                        'operacao_de_credito' => 'success',
                        'historico_contabil' => 'info',
                        'valor_da_operacao' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('data_source_type')
                    ->label('Tipo de Fonte')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'Desconhecido')
                    ->color(fn (string $state): string => match ((string) $state) {
                        'column' => 'gray',
                        'constant' => 'primary',
                        'query' => 'warning',
                        'parametros_gerais' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('position')
                    ->label('Posição')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_sanitize')
                    ->label('Sanitiza')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_condition')
                    ->label('Tem Condição')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rule_type')
                    ->label('Tipo de Regra')
                    ->options(\App\Enums\TipoRegraExportacaoEnum::class),

                Tables\Filters\SelectFilter::make('data_source_type')
                    ->label('Tipo de Fonte')
                    ->options(\App\Enums\TipoFonteDeDadosEnum::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Adicionar Nova Regra'),
            ])
            ->defaultSort('position', 'asc');
    }
}
