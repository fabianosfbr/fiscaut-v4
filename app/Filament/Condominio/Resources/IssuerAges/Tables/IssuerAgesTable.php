<?php

namespace App\Filament\Condominio\Resources\IssuerAges\Tables;

use App\Enums\IssuerAgeTypeEnum;
use App\Filament\Condominio\Resources\IssuerAges\IssuerAgeResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class IssuerAgesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('issuer_id', currentIssuer()->id);
            })
            ->recordUrl(null)
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (IssuerAgeTypeEnum $state): string => match ($state) {
                        IssuerAgeTypeEnum::AGO => 'success',
                        IssuerAgeTypeEnum::AGE => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('document_path')
                    ->label('Documento')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getListLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return basename($state);
                    })
                    ->formatStateUsing(fn ($state) => basename($state)),

                // AGE Only
                TextColumn::make('vigencia_date')
                    ->label('Vigência')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'age')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('prazo_tecnico')
                    ->label('Prazo Técnico')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'age')
                    ->sortable(),

                // AGO Only
                TextColumn::make('data_limite_ago')
                    ->label('Data Limite')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('prazo_tecnico_edital')
                    ->label('Prazo Técnico (Edital)')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('mandato_fim')
                    ->label('Fim Mandato (Síndico)')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),

                TextColumn::make('prazo_tecnico_mandato')
                    ->label('Prazo Técnico (Mandato)')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('mandato_conselho_fim')
                    ->label('Fim Mandato (Conselho)')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('prazo_tecnico_mandato_conselho')
                    ->label('Prazo Técnico (Conselho)')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('mandato_banco_fim')
                    ->label('Fim Mandato (Banco)')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('prazo_tecnico_mandato_banco')
                    ->label('Prazo Técnico (Banco)')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                // Boleto fields (AGO)
                TextColumn::make('boleto_dia_vencimento')
                    ->label('Dia Vencimento')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),

                TextColumn::make('boleto_tipo_prazo')
                    ->label('Tipo Prazo')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'uteis' => 'Dias Úteis',
                        'corridos' => 'Dias Corridos',
                        default => (string) $state,
                    }),

                TextColumn::make('boleto_gerado_por')
                    ->label('Gerado Por')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'administradora' => 'Administradora',
                        'garantidora' => 'Garantidora',
                        default => (string) $state,
                    }),

                TextColumn::make('boleto_forma_rateio')
                    ->label('Forma Rateio')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'ideal' => 'Rateio Ideal',
                        'unidade' => 'Unidade',
                        'm2' => 'Por m²',
                        default => (string) $state,
                    }),

                // Isenção/Remuneração (AGO)
                TextColumn::make('tem_isencao_remuneracao')
                    ->label('Tipo')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state): string => $state ? 'Isenção' : 'Remuneração')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'info'),

                TextColumn::make('quem_recebe_isencao')
                    ->label('Quem Recebe')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),

                TextColumn::make('valor_isencao_remuneracao')
                    ->label('Valor')
                    ->visible(fn ($livewire) => $livewire->activeTab === 'ago')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->money('BRL'),

                // Common
                TextColumn::make('data_limite_edital')
                    ->label('Data Limite Edital')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Detalhes')
                        ->color('gray'),
                    EditAction::make()
                        ->mutateFormDataUsing(function (array $data): array {
                            return IssuerAgeResource::cleanData($data);
                        })
                        ->color('gray'),
                    DeleteAction::make()
                        ->color('gray'),
                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
