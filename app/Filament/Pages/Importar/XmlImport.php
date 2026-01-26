<?php

namespace App\Filament\Pages\Importar;

use UnitEnum;
use Exception;
use Filament\Pages\Page;
use App\Models\XmlImportJob;
use Filament\Schemas\Schema;
use App\Jobs\ProcessXmlFileBatch;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;

class XmlImport extends Page
{
    protected string $view = 'filament.pages.importar.xml-import';

    protected static ?string $title = 'Importar XML';

    protected static ?string $navigationLabel = 'Importar XML';

    protected static string|UnitEnum|null $navigationGroup = 'Ferramentas';

    public ?array $data = [];

    public bool $isProcessing = false;

    public bool $isLoading = false;

    public int $totalArquivos = 0;

    public int $arquivosProcessados = 0;

    public int $arquivosImportados = 0;

    public int $arquivosComErro = 0;

    public array $erros = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload de XML')
                    ->description('Selecione um ou mais arquivos XML de NF-e para importar')
                    ->schema([
                        FileUpload::make('xmlFiles')
                            ->label('Arquivos XML/ZIP')
                            ->multiple()
                            ->helperText('Você pode enviar arquivos XML (NFe ou CTe) individuais ou um arquivo ZIP contendo vários XMLs')
                            ->maxSize(50000) // 50MB
                            ->required()
                            ->disk('local')
                            ->directory('xml-imports')
                            ->visibility('private')
                            ->previewable(false)
                            ->downloadable(false),

                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {

        $this->isLoading = true;
        $data = $this->form->getState();

        // Verifica se há arquivos selecionados
        if (empty($data['xmlFiles'])) {
            Notification::make()
                ->danger()
                ->title('Nenhum arquivo selecionado')
                ->body('Por favor, selecione pelo menos um arquivo XML ou ZIP para importar.')
                ->send();

            return;
        }

        $user = Auth::user();
        $importJob = XmlImportJob::createQuietly([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'issuer_id' => $user->currentIssuer->id,
            'owner_id' => $user->id,
            'owner_type' => $user::class,
            'status' => XmlImportJob::STATUS_PENDING,
            'processed_files' => 0,
            'imported_files' => 0,
            'error_files' => 0,
            'total_files' => 0,
            'errors' => [],
        ]);

        try {

           ProcessXmlFileBatch::dispatch($data['xmlFiles'], $importJob);

            $importJob->updateQuietly([
                'total_files' => count($data['xmlFiles']),
            ]);

            $this->isProcessing = false;

            $this->dispatch('refresh');

            $this->form->fill([]);

            Notification::make()
                ->success()
                ->title('Importação será processada em segundo plano')
                ->body('A importação foi iniciada. Você receberá notificação quando for concluída.')
                ->send();
        } catch (Exception $e) {
            $this->isProcessing = false;
            $this->erros[] = 'Erro geral: '.$e->getMessage();

            Notification::make()
                ->danger()
                ->title('Erro na importação')
                ->body($e->getMessage())
                ->send();
        } finally {
            $this->isLoading = false;
        }
    }

    
}
