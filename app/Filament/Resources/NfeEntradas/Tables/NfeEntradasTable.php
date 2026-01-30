<?php

namespace App\Filament\Resources\NfeEntradas\Tables;

use App\Models\Tag;
use Filament\Tables\Table;
use App\Enums\StatusNfeEnum;
use Filament\Actions\Action;
use App\Models\GeneralSetting;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Bus;
use App\Models\NotaFiscalEletronica;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\Alignment;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use App\Enums\StatusManifestacaoNfeEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use App\Filament\Actions\DownloadXmlAction;
use Filament\Forms\Components\CheckboxList;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Actions\ManifestarNfeAction;
use App\Filament\Actions\DownloadPdfNfeAction;
use App\Services\Tagging\TagSuggestionService;
use App\Filament\Actions\SugerirEtiquetaAction;
use App\Filament\Tables\Columns\TagBadgesColumn;
use App\Filament\Tables\Columns\ViewChaveColumn;
use App\Filament\Actions\ToggleEscrituracaoAction;
use App\Filament\Actions\ClassificarDocumentoAction;
use App\Jobs\BulkAction\DownloadXmlNfeBulkActionJob;
use App\Filament\Actions\RemoverClassificaoNfeAction;
use App\Filament\Actions\DownloadXmlPdfNfeEmLoteAction;
use Filament\QueryBuilder\Constraints\NumberConstraint;

class NfeEntradasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('data_emissao', 'desc')
            ->paginated([10, 25, 50, 100])
            ->recordUrl(null)
            ->columns([
                TextColumn::make('nNF')
                    ->label('Nº')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('emitente_razao_social')
                    ->label('Empresa')
                    ->limit(30)
                    ->searchable(['emitente_razao_social', 'emitente_cnpj'])
                    ->size('sm')
                    ->description(function (NotaFiscalEletronica $record) {

                        return $record->emitente_cnpj;
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

                TextColumn::make('data_entrada')
                    ->label('Entrada')
                    ->toggleable()
                    ->date('d/m/Y'),

                IconColumn::make('processed')
                    ->label('Apurada')
                    ->boolean()
                    ->alignment(Alignment::Center)
                    ->toggleable(),

                TextColumn::make('vNfe')
                    ->label('Valor Total')
                    ->sortable()
                    ->money('BRL'),

                TagBadgesColumn::make('tagged')
                    ->label('Etiqueta')
                    ->alignCenter()
                    ->emptyText('')
                    ->showTagCode(function () {
                        $issuerId = Auth::user()->currentIssuer->id;
                        return GeneralSetting::getValue(
                            name: 'configuracoes_gerais',
                            key: 'isNfeMostrarCodigoEtiqueta',
                            default: false,
                            issuerId: $issuerId
                        );
                    })
                    ->toggleable(),


                TextColumn::make('status_nota')
                    ->label('Status')
                    ->toggleable()
                    ->badge(),

                TextColumn::make('status_manifestacao')
                    ->label('Manifestação')
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

                SelectFilter::make('status_manifestacao')
                    ->label('Status do Manifesto')
                    ->options([
                        StatusManifestacaoNfeEnum::CONFIRMACAO_OPERACAO->value => StatusManifestacaoNfeEnum::CONFIRMACAO_OPERACAO->getLabel(),
                        StatusManifestacaoNfeEnum::CIENCIA_OPERACAO->value => StatusManifestacaoNfeEnum::CIENCIA_OPERACAO->getLabel(),
                        StatusManifestacaoNfeEnum::DESCONHECIMENTO_OPERACAO->value => StatusManifestacaoNfeEnum::DESCONHECIMENTO_OPERACAO->getLabel(),
                        StatusManifestacaoNfeEnum::OPERACAO_NAO_REALIZADA->value => StatusManifestacaoNfeEnum::OPERACAO_NAO_REALIZADA->getLabel(),
                    ])
                    ->multiple(),

                SelectFilter::make('etiquetas')
                    ->label('Etiquetas')
                    ->options([
                        'sem_etiqueta' => 'Sem Etiqueta',
                        'com_etiqueta' => 'Com Etiqueta',
                        'uma_etiqueta' => 'Apenas Uma Etiqueta',
                        'multiplas_etiquetas' => 'Múltiplas Etiquetas',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'sem_etiqueta' => $query->doesntHave('tagged'),
                            'com_etiqueta' => $query->has('tagged'),
                            'uma_etiqueta' => $query->has('tagged', '=', 1),
                            'multiplas_etiquetas' => $query->has('tagged', '>', 1),
                            default => $query,
                        };
                    }),

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

                Filter::make('etiquetas_especificas')
                    ->label('Etiquetas Específicas')
                    ->columnSpanFull()
                    ->schema([
                        CheckboxList::make('etiquetas')
                            ->label('Etiquetas Específicas')
                            ->options(function () {
                                return Tag::getTagsUsedInNfe();
                            })
                            ->columns(4)
                            ->searchable()
                            ->helperText('Selecione as etiquetas específicas para filtrar as notas fiscais'),

                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['etiquetas'])) {
                            return $query;
                        }

                        // Extrai os IDs das etiquetas selecionadas
                        $tagIds = collect($data['etiquetas'])
                            ->map(function ($value) {
                                return explode(' - ', $value)[0];
                            })
                            ->toArray();

                        $query->whereHas('tagged', function ($query) use ($tagIds) {
                            $query->whereIn('tag_id', $tagIds);
                        });

                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['etiquetas'])) {
                            return null;
                        }

                        $etiquetas = Tag::whereIn('id', $data['etiquetas'])
                            ->get()
                            ->keyBy('id')
                            ->map(fn($tag) => $tag->code . ' - ' . $tag->name)
                            ->toArray();

                        return 'Etiquetas: ' . implode(', ', $etiquetas);
                    }),


            ])
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                ActionGroup::make([
                    SugerirEtiquetaAction::make(),
                    ViewAction::make(),
                    ManifestarNfeAction::make(),
                    ToggleEscrituracaoAction::make(),
                    ClassificarDocumentoAction::make(),
                    RemoverClassificaoNfeAction::make(),
                    DownloadXmlAction::make(),
                    DownloadPdfNfeAction::make(),

                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    DownloadXmlPdfNfeEmLoteAction::make(),
                ]),
            ]);
    }
}
