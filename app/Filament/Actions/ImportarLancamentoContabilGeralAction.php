<?php

namespace App\Filament\Actions;

use Exception;
use App\Models\Layout;
use Filament\Actions\Action;
use App\Jobs\ImportarLancamentoContabilJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Imports\OptimizedExcelImport;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

use Filament\Forms\Components\FileUpload;
use App\Filament\Actions\Traits\ImportarLancamentoContabilTrait;

class ImportarLancamentoContabilGeralAction
{
    use ImportarLancamentoContabilTrait;


    public static function make(): Action
    {
        return Action::make('importar-lancamento-contabil-geral')
            ->label('Importar Arquivo')
            ->modalHeading('Importar Arquivo Excel')
            ->modalSubmitActionLabel('Sim, importar arquivo')
            ->action(function (array $data, Action $action) {
                $layout = Layout::find($data['layout_id']);

                $relativePath = $data['excel_file'];
                $filePath = Storage::disk('local')->path($relativePath);

                if (! file_exists($filePath)) {
                    Notification::make()
                        ->title('Arquivo não encontrado')
                        ->body('Não foi possível localizar o arquivo enviado para importação.')
                        ->danger()
                        ->duration(2000)
                        ->send();
                    $action->halt();
                }

                try {
                    $fileReader = (new OptimizedExcelImport($layout, $filePath));
                    $missingColumns = $fileReader->validateExcelColumns();

                    if (!empty($missingColumns)) {
                        Notification::make()
                            ->title('Colunas Ausentes')
                            ->body('As seguintes colunas estão faltando no arquivo Excel: ' . implode(', ', $missingColumns))
                            ->danger()
                            ->persistent()
                            ->send();

                        Storage::disk('local')->delete($relativePath);
                        $action->halt();
                    }

                    // Dispara o Job em background
                    ImportarLancamentoContabilJob::dispatch(
                        $layout->id,
                        $relativePath,
                        Auth::user()->id
                    );

                    Notification::make()
                        ->title('Importação Iniciada')
                        ->body('O arquivo está sendo processado em segundo plano. Você será notificado quando terminar.')
                        ->success()
                        ->send();

                } catch (Exception $e) {
                    Log::error($e->getMessage());
                    Notification::make()
                        ->title('Erro na Importação')
                        ->body('Ocorreu um erro ao iniciar a importação: ' . $e->getMessage())
                        ->danger()
                        ->send();
                    
                    if (Storage::disk('local')->exists($relativePath)) {
                        Storage::disk('local')->delete($relativePath);
                    }
                }
            })
            ->schema([
                Select::make('layout_id')
                    ->label('Layout utilizado para importação')
                    ->required()
                    ->default(1)
                    ->options(function () {
                        return Layout::where('issuer_id', Auth::user()->currentIssuer->id)->pluck('name', 'id');
                    }),

                FileUpload::make('excel_file')
                    ->label('Arquivo Excel')
                    ->required()
                    ->directory('upload-importacao')
                    ->validationMessages([
                        'required' => 'O arquivo Excel é obrigatório.',
                        'file.mimes' => 'O arquivo deve ser um arquivo Excel válido.',
                        'file.max' => 'O arquivo deve ter no máximo 10MB.',
                    ])
                    ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
            ]);
    }
}
