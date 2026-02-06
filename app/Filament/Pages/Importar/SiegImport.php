<?php

namespace App\Filament\Pages\Importar;

use UnitEnum;
use App\Models\User;
use App\Models\Issuer;
use Filament\Pages\Page;
use App\Models\XmlImportJob;
use Filament\Schemas\Schema;
use App\Jobs\Sieg\SiegConnect;
use App\Enums\XmlImportJobType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class SiegImport extends Page
{
    protected string $view = 'filament.pages.importar.sieg-import';

    protected static ?string $navigationLabel = 'Importar SIEG';

    protected static ?string $title = 'Importar SIEG';

    // Constantes para tipos de documentos
    const XML_TYPE_NFE = 1;

    const XML_TYPE_CTE = 2;

    const XML_TYPE_NFSE = 3;

    const XML_TYPE_NFCE = 4;

    const XML_TYPE_CFE = 5;

    protected static string|UnitEnum|null $navigationGroup = 'Ferramentas';

    public ?array $data = [];

    public $tipoDocumento = 1; // Padrão: NFe

    public $tipoCnpj = 'emitente';

    public $cnpj = '';

    public $skip = 0;

    public $take = 50;

    public $apiKey = '';

    public $apiUrl = '';

    public bool $isLoading = false;

    public array $resultados = [];

    public bool $temMaisResultados = false;

    public int $totalDocumentos = 0; // Contador para o total de documentos importados

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Importação de documentos fiscais')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('dataInicial')
                                    ->label('Data inicial')
                                    ->displayFormat('d/m/Y')
                                    ->default(now())
                                    ->required(),
                                DatePicker::make('dataFinal')
                                    ->label('Data final')
                                    ->maxDate(now())
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('tipoDocumento')
                                    ->label('Tipo de documento')
                                    ->options(self::getTiposDocumento())
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        if (filled($state)) {
                                            $this->tipoDocumento = $state;

                                            if ($this->tipoDocumento == self::XML_TYPE_CTE) {
                                                $set('tipoCnpj', 'tomador');
                                                $this->tipoCnpj = 'tomador';
                                            } else {
                                                $set('tipoCnpj', 'emitente');
                                                $this->tipoCnpj = 'emitente';
                                            }
                                        }
                                    }),
                                Select::make('tipoCnpj')
                                    ->label('Tipo de CNPJ')
                                    ->options(function (Get $get) {
                                        $tipoDoc = $get('tipoDocumento');

                                        if ($tipoDoc == self::XML_TYPE_CTE) {
                                            return [
                                                'tomador' => 'CNPJ do Tomador',
                                                'remetente' => 'CNPJ do Remetente',
                                                'emitente' => 'CNPJ do Emitente',
                                                'destinatario' => 'CNPJ do Destinatário',
                                            ];
                                        }

                                        return [
                                            'emitente' => 'CNPJ do Emitente',
                                            'destinatario' => 'CNPJ do Destinatário',
                                        ];
                                    })
                                    ->default('emitente')
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        if (filled($state)) {
                                            $this->tipoCnpj = $state;
                                        }
                                    })
                                    ->required(),
                            ]),

                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->isLoading = true;
        $data = $this->form->getState();

        try {
            // Validar se o emissor atual está configurado
            if (! Auth::user()->currentIssuer) {
                Notification::make()
                    ->title('Erro')
                    ->body('Empresa atual não configurada. Por favor, selecione uma empresa.')
                    ->danger()
                    ->send();

                return;
            }

            $user = Auth::user();
            $importJob = $this->createImportJob($user->currentIssuer, $user);
            // Dispatch o job para processar a conexão com a API SIEG de forma assíncrona
            SiegConnect::dispatch(
                (int) $data['tipoDocumento'],
                $this->tipoCnpj,
                $data['dataInicial'],
                $data['dataFinal'],            
                $user->currentIssuer->id,
                $importJob->id,
            );

            // Exibe uma notificação informando que o processo foi iniciado
            Notification::make()
                ->title('Processamento iniciado')
                ->body('A importação dos documentos foi iniciada e será processada em segundo plano. Você receberá notificações sobre o progresso.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro')
                ->body('Ocorreu um erro ao iniciar o processamento: ' . $e->getMessage())
                ->danger()
                ->send();

            Log::error('Erro ao iniciar importação SIEG: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    private function createImportJob(Issuer $issuer, User $user): XmlImportJob
    {
        return  XmlImportJob::createQuietly([
            'user_id' => $user->id,
            'tenant_id' => $issuer->tenant_id,
            'issuer_id' => $issuer->id,
            'owner_type' => $user::class,
            'owner_id' => $user->id,
            'import_type' => XmlImportJobType::SYSTEM,
            'status' => XmlImportJob::STATUS_PENDING,
            'processed_files' => 0,
            'imported_files' => 0,
            'error_files' => 0,
            'total_files' => 0,
            'errors' => [],
        ]);
    }

    /**
     * Carrega mais resultados da API
     */
    public function carregarMais(): void
    {
        $this->save();
    }

    public static function getTiposDocumento(): array
    {
        return [
            self::XML_TYPE_NFE => 'NFe',
            self::XML_TYPE_CTE => 'CT-e',
            self::XML_TYPE_NFSE => 'NFSe',
            self::XML_TYPE_NFCE => 'NFCe',
            self::XML_TYPE_CFE => 'CF-e',
        ];
    }
}
