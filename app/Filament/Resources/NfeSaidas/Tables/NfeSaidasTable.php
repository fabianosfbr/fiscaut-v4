<?php

namespace App\Filament\Resources\NfeSaidas\Tables;

use Filament\Tables\Table;
use App\Enums\StatusNfeEnum;
use App\Models\GeneralSetting;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Filters\Filter;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Cache;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use App\Filament\Actions\DownloadXmlAction;
use App\Filament\Actions\DownloadPdfNfeAction;
use App\Filament\Tables\Columns\TagBadgesColumn;
use App\Filament\Tables\Columns\ViewChaveColumn;
use App\Filament\Actions\ToggleEscrituracaoAction;
use App\Filament\Actions\DownloadXmlPdfNfeEmLoteAction;
use App\Filament\Actions\ToggleEscrituacaoEmLoteAction;
use App\Filament\Actions\ClassificarDocumentoEmLoteAction;
use Filament\Tables\Filters\QueryBuilder\Constraints\NumberConstraint;

class NfeSaidasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $issuer = Auth::user()->currentIssuer;
                return $query->where('emitente_cnpj', $issuer->cnpj);
            })
            ->recordUrl(null)
            ->columns([
                TextColumn::make('nNF')
                    ->label('Nº')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('destinatario_razao_social')
                    ->label('Empresa')
                    ->limit(30)
                    ->searchable(['destinatario_razao_social', 'destinatario_cnpj'])
                    ->size('sm')
                    ->description(function (NotaFiscalEletronica $record) {

                        return $record->destinatario_cnpj;
                    })
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getListLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),

                TextColumn::make('cfops')
                    ->label('CFOP')
                    ->alignCenter(),

                TextColumn::make('data_emissao')
                    ->label('Emissão')
                    ->date('d/m/Y')
                    ->toggleable(),

                IconColumn::make('processed')
                    ->label('Apurada')
                    ->boolean()
                    ->alignment(Alignment::Center)
                    ->toggleable(),

                TextColumn::make('vNfe')
                    ->label('Valor Total')
                    ->sortable()
                    ->money('BRL'),


                TextColumn::make('status_nota')
                    ->label('Status')
                    ->toggleable()
                    ->badge(),

                ViewChaveColumn::make('chave')
                    ->label('Chave')
                    ->searchable(),
            ])
            ->filters([
                QueryBuilder::make()
                    ->constraints([
                        NumberConstraint::make('vICMS')->label('ICMS'),
                        NumberConstraint::make('vPIS')->label('PIS'),
                        NumberConstraint::make('vCOFINS')->label('COFINS'),
                        NumberConstraint::make('vIPI')->label('IPI'),
                        NumberConstraint::make('vSeg')->label('Seguro'),
                        NumberConstraint::make('vFrete')->label('Frete'),
                        NumberConstraint::make('vST')->label('ST'),
                        NumberConstraint::make('vICMSUFDest')->label('DIFAL'),
                        NumberConstraint::make('vDesc')->label('Desconto'),
                    ]),

                Filter::make('data_emissao')
                    ->label('Data de Emissão')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('data_emissao_inicio')
                            ->label('Data Emissão Início')
                            ->columnSpan(1),
                        DatePicker::make('data_emissao_fim')
                            ->label('Data Emissão Final')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['data_emissao_inicio']) && empty($data['data_emissao_fim'])) {
                            return null;
                        }

                        $inicio = $data['data_emissao_inicio'] ? date('d/m/Y', strtotime($data['data_emissao_inicio'])) : '...';
                        $fim = $data['data_emissao_fim'] ? date('d/m/Y', strtotime($data['data_emissao_fim'])) : '...';

                        return "Emissão: {$inicio} até {$fim}";
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['data_emissao_inicio'])) {
                            $query->whereDate('data_emissao', '>=', $data['data_emissao_inicio']);
                        }
                        if (! empty($data['data_emissao_fim'])) {
                            $query->whereDate('data_emissao', '<=', $data['data_emissao_fim']);
                        }

                        return $query;
                    }),

                Filter::make('data_entrada')
                    ->label('Data de Entrada')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('data_entrada_inicio')
                            ->label('Data Entrada Início')
                            ->columnSpan(1),
                        DatePicker::make('data_entrada_fim')
                            ->label('Data Entrada Final')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['data_entrada_inicio']) && empty($data['data_entrada_fim'])) {
                            return null;
                        }

                        $inicio = $data['data_entrada_inicio'] ? date('d/m/Y', strtotime($data['data_entrada_inicio'])) : '...';
                        $fim = $data['data_entrada_fim'] ? date('d/m/Y', strtotime($data['data_entrada_fim'])) : '...';

                        return "Entrada: {$inicio} até {$fim}";
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['data_entrada_inicio'])) {
                            $query->whereDate('data_entrada', '>=', $data['data_entrada_inicio']);
                        }
                        if (! empty($data['data_entrada_fim'])) {
                            $query->whereDate('data_entrada', '<=', $data['data_entrada_fim']);
                        }

                        return $query;
                    }),

                SelectFilter::make('status_nota')
                    ->label('Status da Nota Fiscal')
                    ->options([
                        StatusNfeEnum::ATIVA->value => StatusNfeEnum::ATIVA->getLabel(),
                        StatusNfeEnum::CANCELADA->value => StatusNfeEnum::CANCELADA->getLabel(),
                        StatusNfeEnum::DENEGADA->value => StatusNfeEnum::DENEGADA->getLabel(),
                        StatusNfeEnum::AUTORIZADA_FORA_PRAZO->value => StatusNfeEnum::AUTORIZADA_FORA_PRAZO->getLabel(),
                    ])
                    ->multiple(),

                Filter::make('cfop')
                    ->schema([
                        TextInput::make('cfop')
                            ->label('CFOP')
                            ->placeholder('Ex: 5102, 6108'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $input = (string) ($data['cfop'] ?? '');

                        $cfops = array_values(array_filter(
                            array_map(
                                static fn(string $value): string => trim($value),
                                preg_split('/[,\s;]+/', $input, -1, PREG_SPLIT_NO_EMPTY) ?: []
                            ),
                            static fn(string $value): bool => $value !== ''
                        ));

                        if ($cfops === []) {
                            return $query;
                        }

                        return $query->where(function (Builder $query) use ($cfops): Builder {
                            foreach ($cfops as $cfop) {
                                $query->orWhereJsonContains('cfops', $cfop);
                            }

                            return $query;
                        });
                    }),

                TernaryFilter::make('escriturada')
                    ->label('Escriturada')
                    ->columnSpan(1)
                    ->placeholder('Todos')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === null) {
                            return $query;
                        }

                        $issuer = Auth::user()->currentIssuer;

                        return $data['value']
                            ? $query->whereHas('apuracoes', function ($query) use ($issuer) {
                                $query->where('issuer_id', $issuer->id);
                            })
                            : $query->whereDoesntHave('apuracoes', function ($query) use ($issuer) {
                                $query->where('issuer_id', $issuer->id);
                            });
                    }),

                TernaryFilter::make('difal')
                    ->label('Com DIFAL')
                    ->columnSpan(1)
                    ->placeholder('Todos')
                    ->trueLabel('Sim')
                    ->falseLabel('Não')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === null) {
                            return $query;
                        }
                        return $data['value']
                            ? $query->where('vICMSUFDest', '>', 0)
                            : $query->where('valor_difal', '=', 0);
                    }),
            ])
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Detalhes'),
                    DownloadXmlAction::make(),
                    DownloadPdfNfeAction::make(),
                    ToggleEscrituracaoAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ToggleEscrituacaoEmLoteAction::make(),
                    DownloadXmlPdfNfeEmLoteAction::make(),
                    ClassificarDocumentoEmLoteAction::make()
                        ->after(function () {
                            Cache::forget('tags_used_in_nfe_' . Auth::user()->currentIssuer->id);

                            Notification::make()
                                ->title('Etiquetas aplicadas com sucesso')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
