<?php

namespace App\Filament\Condominio\Pages;

use App\Models\SuperLogicaFornecedor;
use App\Models\SuperLogicaPlanoDeConta;
use App\Services\SuperlogicaConnectionService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class ListarContaPagar extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.condominio.pages.listar-conta-pagar';

    protected static string|UnitEnum|null $navigationGroup = 'Cobranças';

    protected static ?string $title = 'Contas a Pagar';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = true;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cadastrarDespesa')
                ->label('Nova Despesa')
                ->icon(Heroicon::PlusCircle)
                ->form($this->getDespesaFormSchema())
                ->action(fn (array $data) => $this->handleCadastrarDespesa($data))
                ->modalWidth(Width::FiveExtraLarge)
                ->modalHeading('Cadastrar Nova Despesa'),
        ];
    }

    protected function getDespesaFormSchema(): array
    {
        return [
            Grid::make()
                ->columns(6)
                ->schema([
                    Select::make('fornecedor_id')
                        ->label('Fornecedor')
                        ->options(function () {
                            return SuperLogicaFornecedor::all()->mapWithKeys(function ($fornecedor) {
                                return [$fornecedor->id_contato_con => $fornecedor->st_nome_con.' '.$fornecedor->st_cpf_con];
                            });
                        })
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('favorecido_id', $state))
                        ->columnSpan(4)
                        ->required(),
                    Select::make('id_contabanco_cb')
                        ->label('Conta Bancária')
                        ->options(fn () => $this->getContasBancariasOptions())
                        ->searchable()
                        ->columnSpan(2)
                        ->required(),
                    Select::make('favorecido_id')
                        ->label('Favorecido')
                        ->live()
                        ->options(function () {
                            return SuperLogicaFornecedor::all()->mapWithKeys(function ($fornecedor) {
                                return [$fornecedor->id_contato_con => $fornecedor->st_nome_con.' '.$fornecedor->st_cpf_con];
                            });
                        })
                        ->searchable()
                        ->columnSpan(4)
                        ->required(),
                    DatePicker::make('data_vencimento')
                        ->label('Vencimento')
                        ->columnSpan(1)
                        ->required(),
                    Select::make('forma_pagamento')
                        ->label('Pagamento')
                        ->columnSpan(1)
                        ->options($this->getFormaPagamentoMap())
                        ->required(),
                    Select::make('dados_pagamentos')
                        ->label('Dados de Pagamento')
                        ->options(function (callable $get, callable $set) {
                            $fornecedorId = $get('fornecedor_id');
                            if (! $fornecedorId) {
                                return [];
                            }
                            $dadosPagamentos = $this->getDadosPagamentosOptions($fornecedorId);

                            return $dadosPagamentos;
                        })
                        ->live()
                        ->searchable()
                        ->columnSpan(2)
                        ->required(),
                    TextInput::make('st_codigobarras_pdes')
                        ->label('Linha digitável')
                         ->columnSpan(4)
                         ->required()
                        ->maxLength(200),
                    Fieldset::make('Documento')
                        ->columnSpanFull()
                        ->schema([
                            Grid::make()
                                ->columns(5)
                                ->columnSpanFull()
                                ->schema([
                                    DatePicker::make('data_documento')
                                        ->label('Data do Documento'),
                                    Select::make('tipo_documento')
                                        ->label('Tipo de Documento')
                                        ->options($this->getTipoDocumentoMap()),
                                    TextInput::make('numero_documento')
                                        ->label('Número do Documento')
                                        ->maxLength(50),
                                    TextInput::make('serie_nota')
                                        ->label('Série da Nota')
                                        ->maxLength(20),
                                    TextInput::make('valor_documento')
                                        ->label('Valor documento')
                                        ->columnSpan(1)
                                        ->required()
                                        ->minValue(0.01),
                                ]),
                        ]),
                    Fieldset::make('Apropriação')
                        ->columnSpanFull()
                        ->schema([
                            Grid::make()
                                ->columns(6)
                                ->columnSpanFull()
                                ->schema([
                                    Select::make('st_conta_cont')
                                        ->label('Conta / Categoria')
                                        ->options(function () {
                                            return SuperLogicaPlanoDeConta::where('id_condominio', currentIssuer()->superlogica_condominio_id)
                                                ->get()
                                                ->mapWithKeys(function ($conta) {
                                                    return [$conta->st_conta_cont => $conta->st_conta_cont.' - '.$conta->st_descricao_cont];
                                                });
                                        })
                                        ->columnSpan(4)
                                        ->searchable()
                                        ->required(),
                                    TextInput::make('st_complemento_apro')
                                        ->label('Complemento')
                                        ->columnSpan(1)
                                        ->maxLength(255),
                                    TextInput::make('vl_valor_pdes')
                                        ->label('Valor')
                                        ->columnSpan(1)
                                        ->required()
                                        ->minValue(0.01),
                                ]),
                        ]),
                    // Fieldset::make('Retenção')
                    //     ->columnSpanFull()
                    //     ->schema([
                    //         Grid::make()
                    //             ->columns(6)
                    //             ->columnSpanFull()
                    //             ->schema([
                    //                 Select::make('id_rv2_cod_imp')
                    //                     ->label('Imposto')
                    //                     ->options(fn() => $this->getImpostosOptions())
                    //                     ->columnSpan(4)
                    //                     ->searchable(),
                    //                 DatePicker::make('dt_vencimento_pdes')
                    //                     ->label('Vencimento')
                    //                     ->columnSpan(1),
                    //                 Select::make('fl_reterimposto_des')
                    //                     ->label('Reter Imposto')
                    //                     ->options([
                    //                         '0' => 'Não',
                    //                         '1' => 'Sim',
                    //                     ])
                    //                     ->default('0')
                    //                     ->columnSpan(1),
                    //             ]),
                    //     ]),
                    // Select::make('recorrente')
                    //     ->label('Recorrência')
                    //     ->options($this->getFormaRecorrenciaMap())
                    //     ->default('-1')
                    //     ->columnSpan(1),
                    Fieldset::make('Arquivos')
                        ->columnSpanFull()
                        ->schema([
                            FileUpload::make('arquivos')
                                ->label('Anexar arquivos')
                                ->multiple()
                                ->required()
                                ->directory('documentos-superlogica')
                                ->preserveFilenames()
                                ->maxSize(51200)
                                ->columnSpanFull(),
                        ]),
                ]),
        ];
    }

    protected function getFornecedoresOptions(): array
    {
        $allFornecedores = collect();
        $pagina = 1;

        $issuer = currentIssuer();
        $service = new SuperlogicaConnectionService($issuer->tenant);

        while (true) {
            try {
                $fornecedores = $service->despesa()->listarFavorecido([
                    'idCondominio' => $issuer->superlogica_condominio_id,
                    'itensPorPagina' => 50,
                    'pagina' => $pagina,
                ]);
            } catch (\Throwable $e) {
                break;
            }

            $fornecedores = collect($fornecedores);

            if ($fornecedores->isEmpty()) {
                break;
            }

            $allFornecedores = $allFornecedores->merge($fornecedores);
            $pagina++;

            if ($pagina > 100) {
                break;
            }
        }

        return collect($allFornecedores)->mapWithKeys(fn ($item) => [
            $item['id_contato_con'] ?? '' => $item['st_nome_con'] ?? '',
        ])->filter()->all();
    }

    protected function getContasBancariasOptions(): array
    {
        $issuer = currentIssuer();
        $service = new SuperlogicaConnectionService($issuer->tenant);

        $contasBancarias = $service->condominio()->contaBancaria()->listar([
            'idCondominio' => $issuer->superlogica_condominio_id,
            'exibirContasAtivas' => 1,
        ]);

        if (! is_array($contasBancarias) || empty($contasBancarias)) {
            return [];
        }

        return collect($contasBancarias)->mapWithKeys(fn ($item) => [
            $item['id_contabanco_cb'] ?? '' => $item['st_descricao_cb'] ?? '',
        ])->filter()->all();
    }

    protected function getImpostosOptions(): array
    {
        $issuer = currentIssuer();
        $service = new SuperlogicaConnectionService($issuer->tenant);

        $impostos = $service->despesa()->listarImposto();

        if (! is_array($impostos) || empty($impostos)) {
            return [];
        }

        return collect($impostos)->mapWithKeys(fn ($item) => [
            $item['id_rv2_cod_imp'] ?? '' => $item['st_rv2_nome_imp'] ?? '',
        ])->filter()->all();
    }

    protected function getDadosPagamentosOptions($idFavorecido): array
    {
        $issuer = currentIssuer();
        $service = new SuperlogicaConnectionService($issuer->tenant);

        $dadosPagamentos = $service->despesa()->listarDadosPagamentoFavorecido([
            'idFornecedor' => $idFavorecido,
        ]);

        if (! is_array($dadosPagamentos) || empty($dadosPagamentos)) {
            return [];
        }

        return collect($dadosPagamentos)->mapWithKeys(fn ($item) => [
            $item['id_favorecido_fav'] ?? '' => $item['st_nomerecebedor'] ?? '',
        ])->filter()->all();
    }

    protected function handleCadastrarDespesa(array $data): void
    {
        $issuer = currentIssuer();
        $service = new SuperlogicaConnectionService($issuer->tenant);

        $arquivosProcessados = $this->processarArquivos($data['arquivos'] ?? [], $service, $issuer);

        $payload = $this->mutateDespesaData($data, $issuer);
        

        foreach ($arquivosProcessados as $indice => $idArquivoArq) {
            $payload["ARQUIVOS[{$indice}][ID_ARQUIVO_ARQ]"] = $idArquivoArq;
        }

        $response = $service->despesa()->cadastrar($payload);
        

        if (isset($response[0]['status']) && $response[0]['status'] == '200') {
            Notification::make()
                ->title('Sucesso!')
                ->body('Despesa cadastrada com sucesso.')
                ->success()
                ->send();
        } else {

            
            Notification::make()
                ->title('Erro!')
                ->body($response[0]['msg'] ?? 'Ocorreu um erro ao cadastrar a despesa.')
                ->danger()
                ->send();
        }
    }

    protected function processarArquivos(array $arquivos, SuperlogicaConnectionService $service, $issuer): array
    {
        if (empty($arquivos)) {
            return [];
        }

        $idsArquivos = [];

        foreach ($arquivos as $arquivo) {
            $fileContent = Storage::disk('local')->get($arquivo);
            $file = [
                'nm_arquivo' => basename($arquivo),
                'conteudo' => $fileContent,
            ];
            $payload = [
                'ID_RESPONSAVEL_ARQ' => 983,
                'FL_TIPO_ARQ' => '9',
            ];

            $response = $service->arquivo()->cadastrar($file, $payload);

            $idArquivoArq = $this->extrairIdArquivo($response);

            if ($idArquivoArq === null) {
                throw new Halt("Falha ao cadastrar o arquivo '{$originalName}': resposta inválida da API.");
            }

            $idsArquivos[] = $idArquivoArq;

            Storage::disk('local')->delete($arquivo);
        }

        return $idsArquivos;
    }

    protected function resolveTemporaryFile(mixed $arquivo): TemporaryUploadedFile
    {
        if ($arquivo instanceof TemporaryUploadedFile) {
            return $arquivo;
        }

        if (is_string($arquivo)) {
            $path = $arquivo;

            if (str($path)->startsWith('livewire-file:')) {
                $path = (string) str($path)->after('livewire-file:');
            }

            return TemporaryUploadedFile::createFromLivewire($path);
        }

        throw new Halt('Formato de arquivo inválido.');
    }

    protected function resolveFilePath(mixed $arquivo): string
    {
        return $this->resolveTemporaryFile($arquivo)->getRealPath();
    }

    protected function resolveFileName(mixed $arquivo): string
    {
        return $this->resolveTemporaryFile($arquivo)->getClientOriginalName();
    }

    protected function extrairIdArquivo(array $response): ?string
    {

        if (is_array($response) && $response[0]['status'] == '200' && isset($response[0]['data'])) {
            return $response[0]['data']['id_arquivo_arq'];
        }

        return null;
    }

    protected function mutateDespesaData(array $data, $issuer): array
    {
        $fornecedor = SuperLogicaFornecedor::where('id_contato_con', $data['fornecedor_id'])->first();
        $stNomeCon = $fornecedor->st_nome_con ?? '';

        $contas = SuperLogicaPlanoDeConta::where('id_condominio', $issuer->superlogica_condominio_id)
            ->where('st_conta_cont', $data['st_conta_cont'])
            ->first();
        $stDescricaoCont = $contas->st_descricao_cont ?? '';

        $valor = (float) str_replace([','], ['.'], (string) ($data['valor_documento'] ?? 0));

        $params = [
            'ID_CONDOMINIO_COND' => $issuer->superlogica_condominio_id,
            'ST_NOME_CON' => $stNomeCon,
            'ID_CONTATO_CON' => $data['fornecedor_id'],
            'DT_VENCIMENTOPRIMEIRAPARCELA' => Carbon::parse($data['data_vencimento'])->format('m/d/Y'),
            'ID_FORMA_PAG' => $data['forma_pagamento'],
            'NM_PARCELAS' => 1,
            //'NM_PRIMEIRAPARCELA' => 1,            
            'APROPRIACAO[0][ST_CONTA_CONT]' => $data['st_conta_cont'],
            'APROPRIACAO[0][ST_DESCRICAO_CONT]' => $stDescricaoCont,
            'APROPRIACAO[0][ST_COMPLEMENTO_APRO]' => $data['st_complemento_apro'] ?? null,
            'APROPRIACAO[0][VL_VALOR_PDES]' => $valor,
            'DESPESA_PARCELA[0][DT_VENCIMENTO_PDES]' => Carbon::parse($data['data_vencimento'])->format('m/d/Y'),
            'DESPESA_PARCELA[0][VL_VALOR_PDES]' => $valor,
            'DESPESA_PARCELA[0][ST_CODIGOBARRAS_PDES]' => str_replace(['-', ''], '', $data['st_codigobarras_pdes'] ?? null),
            'DESPESA_PARCELA[0][ST_NOMERECEBEDOR_FAV]' => $stNomeCon,
            'DESPESA_PARCELA[0][ID_FAVORECIDO_CON]' => $data['fornecedor_id'],
            'DESPESA_PARCELA[0][DADOS_PAGAMENTOS]' => $data['dados_pagamentos'] ?? null,
            'ID_CONTABANCO_CB' => $data['id_contabanco_cb'] ?? null,
            'FL_ACAO_IMPRESSAO' => 1,
            'FL_RECORRENTEMANUAL_DES' => 1,
            'FL_RECORRENTE_DES' => $data['recorrente'] ?? '-1',
            'DT_DESPESA_DES' => isset($data['data_documento']) ? Carbon::parse($data['data_documento'])->format('m/d/Y') : null,
            'ID_TIPO_DOC' => $data['tipo_documento'] ?? null,
        ];        

        return $params;
    }

    protected function getTipoDocumentoMap(): array
    {
        return [
            '1' => 'Nota Fiscal',
            '2' => 'Imposto',
            '3' => 'Fatura',
            '4' => 'Recibo',
            '5' => 'Cupom Fiscal',
            '6' => 'Outros',
            '7' => 'Folha de pagamento',
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->searchDebounce('750ms')
            ->records(function (?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int|string $recordsPerPage): LengthAwarePaginator {
                return $this->apiData($search, $sortColumn, $sortDirection, $page, $recordsPerPage);
            })
            ->columns([
                TextColumn::make('id_despesa_des')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('dt_vencimento_pdes')
                    ->label('Vencimento')
                    ->state(fn (array $record): string => $this->formatDate(data_get($record, 'dt_vencimento_pdes')))
                    ->sortable(),
                TextColumn::make('st_descricao_cont')
                    ->label('Descrição')
                    ->state(function (array $record): string {
                        return data_get($record, 'apropriacao.0.st_descricao_cont', '').' '.data_get($record, 'apropriacao.0.st_complemento_apro', '');
                    })
                    ->formatStateUsing(function ($record, $state) {
                        $recorrencia = $this->mapFormaRecorrencia(data_get($record, 'fl_recorrente_des'));

                        return new HtmlString($state.' <span style="color: #3d3de6ff;"> ('.$recorrencia.')</span>');
                    })
                    ->description(function (array $record) {
                        $favorecido = data_get($record, 'apropriacao.0.st_complemento_apro', '');
                        if (empty($favorecido)) {
                            $favorecido = $record['st_nomerecebedor_fav'] ?? $record['st_fantasia_con'];
                        }

                        return new HtmlString('<span style="color: #b3b3b6ff;">Despesa </span>'.$record['id_parcela_pdes'].'<span style="color: #b3b3b6ff;"> para o favorecido </span>'.$favorecido);
                    })
                    ->sortable(),
                TextColumn::make('forma_pagamento_text')
                    ->label('Forma de Pagamento')
                    ->state(fn (array $record): string => $this->mapFormaPagamento(data_get($record, 'id_forma_pag')))
                    ->badge()
                    ->sortable(),
                TextColumn::make('st_descricao_cb')
                    ->label('Conta Bancária')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                TextColumn::make('vl_valor_pdes')
                    ->label('Valor')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->prefix('R$ ')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('verArquivos')
                    ->label('Arquivos')
                    ->icon(Heroicon::PaperClip)
                    ->visible(fn (array $record): bool => ! empty(data_get($record, 'arquivos')))
                    ->modalHeading('Arquivos da Cobrança')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(function (array $record): View {
                        $arquivos = data_get($record, 'arquivos', []);

                        return view('filament.condominio.pages.partials.arquivos-cobranca', ['arquivos' => $arquivos]);
                    }),
                Action::make('removerDespesa')
                    ->label('Remover')
                    ->icon(Heroicon::Trash)
                    ->requiresConfirmation()
                    ->action(function (array $record): void {
                        $issuer = currentIssuer();
                        $service = new SuperlogicaConnectionService($issuer->tenant);

                        $response = $service
                            ->despesa()
                            ->remover([
                                'ID_PARCELA_PDES' => $record['id_parcela_pdes'],
                            ]);

                        if (isset($response[0]['status']) && $response[0]['status'] == '200') {
                            Notification::make()
                                ->title('Sucesso!')
                                ->body($response[0]['msg'] ?? 'Despesa cadastrada com sucesso.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Erro!')
                                ->body($response[0]['msg'] ?? 'Ocorreu um erro ao cadastrar a despesa.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->filters([
                Filter::make('data_despesa')
                    ->label('Data de Vencimento')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('despesa_de')
                            ->label('Data Inicial')
                            ->columnSpan(1),
                        DatePicker::make('despesa_ate')
                            ->label('Data Final')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['despesa_de'] ?? null) {
                            $indicators[] = Indicator::make('Data a partir de '.Carbon::parse($data['despesa_de'])->format('d/m/Y'))
                                ->removeField('despesa_de');
                        }
                        if ($data['despesa_ate'] ?? null) {
                            $indicators[] = Indicator::make('Data até '.Carbon::parse($data['despesa_ate'])->format('d/m/Y'))
                                ->removeField('despesa_ate');
                        }

                        return $indicators;
                    }),
                SelectFilter::make('id_forma_pag')
                    ->label('Forma de Pagamento')
                    ->options($this->getFormaPagamentoOptions())
                    ->indicateUsing(function (array $data) {
                        $indicators = [];
                        if ($data['id_forma_pag'] ?? null) {
                            $indicators[] = Indicator::make('Forma de Pagamento: '.$this->mapFormaPagamento($data['id_forma_pag']))
                                ->removeField('id_forma_pag');
                        }

                        return $indicators;
                    }),
                Filter::make('st_nomerecebedor_fav')
                    ->label('Favorecido')
                    ->schema([
                        TextInput::make('favorecido')
                            ->label('Nome do Favorecido'),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['favorecido'] ?? null) {
                            $query->where('st_nomerecebedor_fav', 'like', '%'.$data['favorecido'].'%');
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data) {
                        $indicators = [];
                        if ($data['favorecido'] ?? null) {
                            $indicators[] = Indicator::make('Favorecido: '.$data['favorecido'])
                                ->removeField('favorecido');
                        }

                        return $indicators;
                    }),
                Filter::make('st_descricao_cont')
                    ->label('Descrição')
                    ->schema([
                        TextInput::make('descricao')
                            ->label('Descrição'),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['descricao'] ?? null) {
                            $query->where('st_descricao_cont', 'like', '%'.$data['descricao'].'%');
                        }

                        return $query;
                    })
                    ->indicateUsing(function (array $data) {
                        $indicators = [];
                        if ($data['descricao'] ?? null) {
                            $indicators[] = Indicator::make('Descrição: '.$data['descricao'])
                                ->removeField('descricao');
                        }

                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordUrl(null);
    }

    protected function apiData(?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int|string $recordsPerPage): LengthAwarePaginator
    {
        $records = $this->fetchContasPagar();

        $filters = $this->tableFilters ?? [];
        $records = $this->applyFilters($records, $filters);
        $records = $this->applySearch($records, $search);

        if ($sortColumn) {
            $records = $records->sortBy(function ($record) use ($sortColumn) {
                return match ($sortColumn) {
                    'vl_valor_pdes' => (float) data_get($record, 'vl_valor_pdes', 0),
                    'dt_vencimento_pdes' => $this->parseDateToTimestamp(data_get($record, 'dt_vencimento_pdes')),
                    'forma_pagamento_text' => $this->mapFormaPagamento(data_get($record, 'id_forma_pag')),
                    'st_descricao_cont' => $this->getDescricaoApropriacao($record),
                    default => data_get($record, $sortColumn) ?? '',
                };
            }, SORT_REGULAR, $sortDirection === 'desc');
        } else {
            $records = $records->sortBy(function ($record) {
                return $this->parseDateToTimestamp(data_get($record, 'dt_vencimento_pdes'));
            });
        }

        $total = $records->count();
        $records = $records->forPage($page, $recordsPerPage)->values();

        return new LengthAwarePaginator(
            $records,
            total: $total,
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    protected function fetchContasPagar(): Collection
    {
        $issuer = currentIssuer();

        $service = new SuperlogicaConnectionService($issuer->tenant);

        $despesas = $service
            ->despesa()
            ->listarDespesa([
                'idCondominio' => $issuer->superlogica_condominio_id,
                'dtInicio' => '01/01/2026',
                'comStatus' => 'pendentes',
            ]);

        return collect($despesas);
    }

    protected function applyFilters(Collection $records, array $filters): Collection
    {
        $despesaDe = data_get($filters, 'data_despesa.despesa_de');
        $despesaAte = data_get($filters, 'data_despesa.despesa_ate');
        $formaPagamento = data_get($filters, 'id_forma_pag.value');
        $favorecido = data_get($filters, 'st_nomerecebedor_fav.favorecido');
        $descricao = data_get($filters, 'st_descricao_cont.descricao');

        if (! empty($formaPagamento)) {
            $records = $records->filter(function ($record) use ($formaPagamento) {
                $idFormaPag = data_get($record, 'id_forma_pag');
                $idFormaPagStr = is_array($idFormaPag) ? (string) array_first($idFormaPag) : (string) $idFormaPag;

                return $idFormaPagStr === (string) $formaPagamento;
            });
        }

        if ($favorecido) {
            $favorecido = (string) Str::of($favorecido)->trim()->lower();
            $records = $records->filter(function ($record) use ($favorecido) {
                return Str::of((string) (data_get($record, 'st_nomerecebedor_fav') ?? ''))->lower()->contains($favorecido);
            });
        }

        if ($descricao) {
            $descricao = (string) Str::of($descricao)->trim()->lower();
            $records = $records->filter(function ($record) use ($descricao) {
                $apropriacaoDesc = $this->getDescricaoApropriacao($record);

                return Str::of((string) $apropriacaoDesc)->lower()->contains($descricao);
            });
        }

        if (! $despesaDe && ! $despesaAte) {
            return $records;
        }

        return $records->filter(function ($record) use ($despesaDe, $despesaAte) {
            try {
                $dtVencimento = Carbon::parse(data_get($record, 'dt_vencimento_pdes'))->startOfDay();

                if ($despesaDe && $dtVencimento->lt(Carbon::parse($despesaDe)->startOfDay())) {
                    return false;
                }
                if ($despesaAte && $dtVencimento->gt(Carbon::parse($despesaAte)->startOfDay())) {
                    return false;
                }

                return true;
            } catch (\Exception $e) {
                return true;
            }
        });
    }

    protected function applySearch(Collection $records, ?string $search): Collection
    {
        if (! filled($search)) {
            return $records;
        }

        $search = (string) Str::of($search)->trim()->lower();

        return $records->filter(function (array $record) use ($search): bool {
            return Str::of((string) (data_get($record, 'id_despesa_des') ?? ''))->lower()->contains($search) ||
                Str::of((string) (data_get($record, 'st_descricao_cb') ?? ''))->lower()->contains($search) ||
                Str::of((string) ($this->getDescricaoApropriacao($record) ?? ''))->lower()->contains($search) ||
                Str::of((string) (data_get($record, 'st_nomerecebedor_fav') ?? ''))->lower()->contains($search) ||
                Str::of((string) $this->mapFormaPagamento(data_get($record, 'id_forma_pag')))->lower()->contains($search) ||
                Str::of((string) (data_get($record, 'vl_valor_pdes') ?? ''))->lower()->contains($search);
        });
    }

    protected function getDescricaoApropriacao(array $record): string
    {
        $apropriacao = data_get($record, 'apropriacao');

        if (! is_array($apropriacao) || empty($apropriacao)) {
            return '-';
        }

        $primeira = array_values($apropriacao)[0] ?? null;

        if (! $primeira) {
            return '-';
        }

        return data_get($primeira, 'st_descricao_cont', '-');
    }

    protected function mapFormaPagamento(mixed $id): string
    {
        $map = $this->getFormaPagamentoMap();

        return $map[(string) ($id ?? '')] ?? 'Indefinido';
    }

    protected function mapFormaRecorrencia(mixed $id): string
    {
        $map = $this->getFormaRecorrenciaMap();

        return $map[(string) ($id ?? '')] ?? 'Indefinido';
    }

    protected function getFormaPagamentoOptions(): array
    {
        return array_filter($this->getFormaPagamentoMap(), function ($key) {
            return $key !== '';
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function getFormaPagamentoMap(): array
    {
        return [
            '' => 'Indefinido',
            '0' => 'Boleto',
            '1' => 'Cheque',
            '2' => 'Dinheiro',
            '3' => 'Cartão de Crédito',
            '4' => 'Cartão de Débito',
            '7' => 'Débito Automático',
            '8' => 'Trans. Bancária',
            '9' => 'Doc/Ted',
            '10' => 'Outros',
            '11' => 'Tributo sem código de barras',
            '12' => 'Pix',
            '13' => 'DCTFWeb',
            '14' => 'Pix Copia e Cola',
        ];
    }

    protected function getFormaRecorrenciaMap(): array
    {
        return [
            '-1' => 'Auto',
            '0' => 'Ordinária',
            '1' => 'Recorrente fixa',
            '2' => 'Recorrente variável',
        ];
    }

    protected function formatDate(?string $date): string
    {
        if (empty($date)) {
            return '-';
        }

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Exception $e) {
            return $date;
        }
    }

    protected function parseDateToTimestamp(?string $date): int
    {
        if (empty($date)) {
            return 0;
        }

        try {
            return Carbon::parse($date)->timestamp;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
