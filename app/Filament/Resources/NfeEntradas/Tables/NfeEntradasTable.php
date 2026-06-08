<?php

namespace App\Filament\Resources\NfeEntradas\Tables;

use App\Enums\StatusManifestacaoNfeEnum;
use App\Enums\StatusManifestoNfeEnum;
use App\Enums\StatusNfeEnum;
use App\Filament\Actions\ClassificarDocumentoAction;
use App\Filament\Actions\ClassificarDocumentoEmLoteAction;
use App\Filament\Actions\ClassificarDocumentoMaisAplicadaEmLoteAction;
use App\Filament\Actions\DownloadPdfNfeAction;
use App\Filament\Actions\DownloadXmlAction;
use App\Filament\Actions\DownloadXmlPdfNfeEmLoteAction;
use App\Filament\Actions\GerarTxtIntegracaoDominioSistema;
use App\Filament\Actions\ManifestarNfeAction;
use App\Filament\Actions\ManifestarNfeEmLoteAction;
use App\Filament\Actions\RemoverClassificaoAction;
use App\Filament\Actions\SugerirEtiquetaAction;
use App\Filament\Actions\ToggleEscrituacaoEmLoteAction;
use App\Filament\Actions\ToggleEscrituracaoAction;
use App\Filament\Forms\Components\CheckboxListTag;
use App\Filament\Tables\Columns\TagBadgesColumn;
use App\Filament\Tables\Columns\ViewChaveColumn;
use App\Models\GeneralSetting;
use App\Models\LogSefazManifestoEvent;
use App\Models\NotaFiscalEletronica;
use App\Models\Tag;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\QueryBuilder\Constraints\NumberConstraint;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use NFePHP\NFe\Common\Standardize;

class NfeEntradasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('data_emissao', 'desc')
            ->paginated([10, 25, 50, 100])
            ->recordUrl(null)
            ->searchDebounce('750ms')
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

                IconColumn::make('apurada.status')
                    ->label('Apurada')
                    ->boolean()
                    ->default(false)
                    ->alignment(Alignment::Center)
                    ->toggleable(),

                TextColumn::make('vNfe')
                    ->label('Valor Total')
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('vNfe', str_replace(',', '.', $search));
                    })
                    ->money('BRL'),

                TagBadgesColumn::make('tagged')
                    ->label('Etiqueta')
                    ->alignCenter()
                    ->emptyText('')
                    ->showTagCode(function () {
                        $issuerId = currentIssuer()->id;

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
                    ->icon(function (NotaFiscalEletronica $record) {
                        if ($record->status_manifestacao === StatusManifestoNfeEnum::DESCONHECIDA || $record->status_manifestacao === StatusManifestoNfeEnum::NAOREALIZADA) {
                            return 'heroicon-o-printer';
                        }

                        return null;
                    })
                    ->action(function (NotaFiscalEletronica $record) {
                        $event = LogSefazManifestoEvent::query()
                            ->where('chave', $record->chave)
                            ->where('type', 'nfe')
                            ->latest('id')
                            ->first();

                        if (! $event || empty($event->xml)) {
                            Notification::make()
                                ->title('Nenhum evento de manifesto encontrado para esta NF-e')
                                ->warning()
                                ->send();

                            return;
                        }

                        try {
                            $xmlString = $event->xml;

                            // Tenta usar o Standardize da NFePHP primeiro (funciona em SefazNfeDownloadService)
                            $st = new Standardize;
                            $std = $st->toStd($xmlString);
                        } catch (\Exception $e) {
                            try {
                                // Fallback: limpa possíveis problemas de encoding e tenta SimpleXML
                                libxml_use_internal_errors(true);

                                // Tenta extrair a tag principal do XML
                                $xmlString = preg_replace('/^[^<]*/', '', $xmlString);
                                $xmlString = preg_replace('/[^>]*$/', '', $xmlString);

                                $std = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING);

                                if ($std === false) {
                                    $errors = libxml_get_errors();
                                    libxml_clear_errors();
                                    $errorMsg = ! empty($errors) ? $errors[0]->message : 'Erro desconhecido ao processar XML';
                                    throw new \Exception($errorMsg);
                                }
                            } catch (\Exception $e2) {
                                Notification::make()
                                    ->title('Erro ao processar o XML do evento')
                                    ->body($e2->getMessage())
                                    ->danger()
                                    ->send();

                                return;
                            }
                        }

                        $creditos = config('admin.footer_credits_danfe', '');

                        $pdf = DomPdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
                            ->loadView('pdf.evento-manifesto-nfe', [
                                'event' => $event,
                                'xml' => $std,
                                'creditos' => $creditos,
                            ]);

                        $filename = "evento-manifesto-{$record->chave}.pdf";

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename);
                    })
                    ->iconPosition(IconPosition::After)
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
                                static fn (string $value): string => trim($value),
                                preg_split('/[,\s;]+/', $input, -1, PREG_SPLIT_NO_EMPTY) ?: []
                            ),
                            static fn (string $value): bool => $value !== ''
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

                        return $data['value']
                            ? $query->whereHas('apurada', fn (Builder $query): Builder => $query->where('status', true))
                            : $query->where(function (Builder $query): Builder {
                                return $query
                                    ->whereDoesntHave('apurada')
                                    ->orWhereHas('apurada', fn (Builder $query): Builder => $query->where('status', false));
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
                        CheckboxListTag::make('etiquetas')
                            ->label('Etiquetas Específicas')
                            ->options(function () {
                                return Tag::tagsUsedInNfeGroupedByCategory();
                            })
                            ->columns(2)
                            ->searchable()
                            ->helperText('Selecione as etiquetas específicas para filtrar os documentos fiscais'),

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
                            ->map(fn ($tag) => $tag->code.' - '.$tag->name)
                            ->toArray();

                        return 'Etiquetas: '.implode(', ', $etiquetas);
                    }),

            ])
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                ActionGroup::make([
                    SugerirEtiquetaAction::make(),
                    ViewAction::make()
                        ->label('Detalhes'),
                    ManifestarNfeAction::make(),
                    ToggleEscrituracaoAction::make(),
                    ClassificarDocumentoAction::make(),
                    RemoverClassificaoAction::make(),
                    DownloadXmlAction::make(),
                    DownloadPdfNfeAction::make(),
                    

                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ToggleEscrituacaoEmLoteAction::make(),
                    ManifestarNfeEmLoteAction::make(),
                    DownloadXmlPdfNfeEmLoteAction::make(),
                    ClassificarDocumentoEmLoteAction::make()
                        ->after(function () {
                            Cache::forget('tags_used_in_nfe_'.currentIssuer()->id);

                            Notification::make()
                                ->title('Etiquetas aplicadas com sucesso')
                                ->success()
                                ->send();
                        }),
                    ClassificarDocumentoMaisAplicadaEmLoteAction::make(),
                    GerarTxtIntegracaoDominioSistema::make(),
                    // BulkAction::make('remove')
                    //     ->label('Excluir')
                    //     ->icon('heroicon-o-trash')
                    //     ->requiresConfirmation()
                    //     ->action(function (Collection $records, array $data){
                    //         $records->each(function (NotaFiscalEletronica $record) {
                    //             $record->delete();
                    //         });
                    //     }),
                ]),
            ]);
    }
}
