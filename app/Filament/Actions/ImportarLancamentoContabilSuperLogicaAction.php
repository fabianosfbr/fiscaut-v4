<?php

namespace App\Filament\Actions;

use App\Jobs\ImportarLancamentoContabilSuperLogicaJob;
use App\Models\ImportarLancamentoContabil;
use App\Models\JobProgress;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportarLancamentoContabilSuperLogicaAction
{
    public static function make(): Action
    {
        return Action::make('importar-lancamento-contabil-super-logica')
            ->label('Importar Arquivo')
            ->modalHeading('Importar Arquivo Excel')
            ->modalSubmitActionLabel('Sim, importar arquivo')
            ->before(function () {
                $user = Auth::user();
                ImportarLancamentoContabil::where('issuer_id', $user->currentIssuer->id)
                    ->where('user_id', $user->id)
                    ->whereJsonContains('metadata->type', 'super_logica')
                    ->delete();
            })
            ->action(function (array $data, Action $action) {
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
                    $user = Auth::user();
                    $issuer = $user->currentIssuer;

                    // Cria o registro de progresso
                    $jobProgress = JobProgress::create([
                        'status' => 'pending',
                        'progress' => 0,
                        'message' => 'Aguardando início do processamento...',
                    ]);

                    session()->put('lancamento_super_logica', $jobProgress->id);

                    // Dispara o Job em background
                    ImportarLancamentoContabilSuperLogicaJob::dispatch(
                        $relativePath,
                        $user->id,
                        $issuer->id,
                        $jobProgress->id
                    );

                    Notification::make()
                        ->title('Importação Iniciada')
                        ->body('O arquivo será processado em segundo plano.')
                        ->success()
                        ->send();

                    redirect(request()->header('Referer'));
                } catch (Exception $e) {
                    Log::error($e->getMessage());
                    Notification::make()
                        ->title('Erro na Importação')
                        ->body('Ocorreu um erro ao iniciar a importação: '.$e->getMessage())
                        ->danger()
                        ->send();

                    if (Storage::disk('local')->exists($relativePath)) {
                        Storage::disk('local')->delete($relativePath);
                    }
                }
            })
            ->schema([
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
