<?php


use App\Models\EntradasImpostosEquivalente;

use Livewire\Component;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;

use Illuminate\Support\Facades\Storage;

use App\Models\IssuerControl as IssuerControlModel;
use App\Enums\ControlTypeEnum;

new class extends Component implements HasActions, HasSchemas, HasTable {
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;


    public function table(Table $table): Table
    {
        return $table
            ->query(fn(): Builder => IssuerControlModel::query()
                ->where('issuer_id', currentIssuer()->id)
                ->where('control_type', ControlTypeEnum::OBRIGACAO_ACESSORIA))
            ->defaultSort('created_at', 'desc')
            ->heading('Obrigações Acessórias cadastradas')
            ->emptyStateHeading('Nenhuma obrigação acessoria cadastrada')
            ->searchDebounce(750)
            ->columns([
                TextColumn::make('value.nome_obrigacao')
                    ->label('Nome da Obrigação Acessória'),
                TextColumn::make('value.data_emissao')
                    ->label('Data de Emissão'),
                TextColumn::make('value.data_vencimento')
                    ->label('Valido até'),
                TextColumn::make('value.document_path')
                    ->label('Documento')
                    ->formatStateUsing(fn($state) => 'Visualizar Documento')
                    ->url(function ($record) {

                        if (!isset($record->value['document_path'])) {
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
                    })
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
                            'nome_responsavel_tecnico' => $record?->value['nome_responsavel_tecnico'] ?? null,
                            'data_emissao' => $record?->value['data_emissao'] ?? null,
                            'data_vencimento' => $record?->value['data_vencimento'] ?? null,
                            'document_path' => $record?->value['document_path'] ?? null,
                        ];
                    })
                    ->action(function ($data, IssuerControlModel $record) {
                        self::updateOrCreate($data);
                    }),

                DeleteAction::make()
                    ->modalHeading('Excluir seguro')
                    ->modalSubheading('Tem certeza que deseja excluir este documento? Esta ação não pode ser desfeita.')
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
                'control_type' => ControlTypeEnum::OBRIGACAO_ACESSORIA,
                'value' => [
                    'nome_obrigacao' => $data['nome_obrigacao'],
                    'data_emissao' => $data['data_emissao'],
                    'data_vencimento' => $data['data_vencimento'],
                    'document_path' => $data['document_path'],
                ],

            ]
        );
    }

    public static function getFormSchema()
    {
        return [
            Hidden::make('id'),
            TextInput::make('nome_obrigacao')
                ->label('Nome da Obrigação Acessória')
                ->required(),
            TextInput::make('data_emissao')
                ->label('Data de Emissão')
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
                ->required()
                ->disk('local')
                ->directory(function ($get) {
                    $issuer = currentIssuer();
                    if (!$issuer) {
                        return null;
                    }

                    return 'rag/' . $issuer->tenant_id . '/' . sanitize($issuer->cnpj) . '/documents';
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