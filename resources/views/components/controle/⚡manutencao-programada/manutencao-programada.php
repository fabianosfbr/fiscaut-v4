<?php

use App\Enums\ControlTypeEnum;
use App\Models\IssuerControl as IssuerControlModel;
use App\Models\TipoManutencao;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => IssuerControlModel::query()
                ->where('issuer_id', currentIssuer()->id)
                ->where('control_type', ControlTypeEnum::MANUTENCAO_PROGRAMADA))
            ->defaultSort('created_at', 'desc')
            ->heading('Manutenções Programadas cadastradas')
            ->emptyStateHeading('Nenhuma manutenção programada cadastrada')
            ->searchDebounce(750)
            ->columns([
                TextColumn::make('value.tipo_manutencao')
                    ->label('Tipo de Manutenção')
                    ->badge(),
                TextColumn::make('value.data_realizacao')
                    ->label('Data de Realização'),
                TextColumn::make('value.data_vencimento')
                    ->label('Valido até'),
                TextColumn::make('value.document_path')
                    ->label('Documento')
                    ->formatStateUsing(fn ($state) => 'Visualizar Documento')
                    ->url(function ($record) {

                        if (! isset($record->value['document_path'])) {
                            return null;
                        }

                        return route('issuer-rag.document.show', $record);
                    }, true)
                    ->icon('heroicon-m-document-arrow-down')
                    ->color('primary'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('add')
                    ->label('Adicionar')
                    ->modalSubmitActionLabel('Salvar')
                    ->modalCancelActionLabel('Cancelar')
                    ->schema(self::getFormSchema())
                    ->action(function ($data) {
                        self::updateOrCreate($data);
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Editar')
                    ->icon('heroicon-m-pencil-square')
                    ->modalSubmitActionLabel('Salvar')
                    ->modalCancelActionLabel('Cancelar')
                    ->schema(self::getFormSchema())
                    ->fillForm(function (IssuerControlModel $record) {
                        return [
                            'id' => $record?->id,
                            'tipo_manutencao' => $record?->value['tipo_manutencao'] ?? null,
                            'data_realizacao' => $record?->value['data_realizacao'] ?? null,
                            'data_vencimento' => $record?->value['data_vencimento'] ?? null,
                            'document_path' => $record?->value['document_path'] ?? null,
                        ];
                    })
                    ->action(function ($data, IssuerControlModel $record) {
                        self::updateOrCreate($data);
                    }),

                DeleteAction::make()
                    ->modalHeading('Excluir seguro')
                    ->modalDescription('Tem certeza que deseja excluir este documento? Esta ação não pode ser desfeita.')
                    ->modalSubmitActionLabel('Sim, excluir')
                    ->modalCancelActionLabel('Cancelar')
                    ->before(function ($record) {
                        if (isset($record->value['document_path']) && Storage::disk('local')->exists($record->value['document_path'])) {
                            Storage::disk('local')->delete($record->value['document_path']);

                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function updateOrCreate($data)
    {

        IssuerControlModel::updateOrCreate(
            [
                'id' => $data['id'] ?? null,
            ],
            [
                'issuer_id' => currentIssuer()->id,
                'control_type' => ControlTypeEnum::MANUTENCAO_PROGRAMADA,
                'value' => [
                    'tipo_manutencao' => $data['tipo_manutencao'],
                    'data_realizacao' => $data['data_realizacao'],
                    'data_vencimento' => $data['data_vencimento'],
                    'document_path' => $data['document_path'] ?? null,
                ],
            ]
        );
    }

    public static function getFormSchema()
    {
        return [
            Hidden::make('id'),
            Select::make('tipo_manutencao')
                ->label('Tipo de Manutenção')
                ->required()
                ->options(function () {
                    return TipoManutencao::where('tenant_id', currentIssuer()->tenant_id)
                        ->pluck('nome', 'nome');
                })
                ->afterLabel(
                    Action::make('generate')
                        ->modalHeading('Adicionar Tipo de Manutenção')
                        ->modalDescription('Cadastre um novo tipo de manutenção para poder selecioná-lo no formulário.')
                        ->modalWidth('sm')
                        ->label('Adicionar Tipo de Manutenção')
                        ->color('success')
                        ->schema([
                            TextInput::make('nome')
                                ->label('Nome do Tipo de Manutenção')
                                ->required(),
                        ])
                        ->action(function ($data) {
                            $existing = TipoManutencao::where('tenant_id', currentIssuer()->tenant_id)
                                ->where('nome', $data['nome'])
                                ->first();

                            if ($existing) {
                                Notification::make()
                                    ->title('Tipo de Manutenção já existe')
                                    ->body('O tipo de manutenção informado já está cadastrado.')
                                    ->danger()
                                    ->send();
                            } else {
                                TipoManutencao::create([
                                    'tenant_id' => currentIssuer()->tenant_id,
                                    'nome' => $data['nome'],
                                ]);
                            }
                        })
                ),
            TextInput::make('data_realizacao')
                ->label('Data de Realização')
                ->mask('99/99/9999')
                ->placeholder('DD/MM/AAAA')
                ->required(),
            TextInput::make('data_vencimento')
                ->label('Valido até')
                ->mask('99/99/9999')
                ->placeholder('DD/MM/AAAA')
                ->required(),

            FileUpload::make('document_path')
                ->label('Documento')
                ->disk('local')
                ->directory(function ($get) {
                    $issuer = currentIssuer();
                    if (! $issuer) {
                        return null;
                    }

                    return 'rag/'.$issuer->tenant_id.'/'.sanitize($issuer->cnpj).'/documents';
                })
                ->visibility('private')
                ->acceptedFileTypes([
                    'application/pdf',
                ])
                ->storeFileNamesIn('original_name')
                ->preserveFilenames()
                ->helperText('Formatos permitidos: PDF (20MB max)')
                ->columnSpanFull(),
        ];
    }
};
