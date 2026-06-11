<?php

namespace App\Jobs;

use App\Integrations\DominioSistemas\Services\OrquestradorService;
use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use App\Models\SecureDownload;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GerarArquivoDominio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array<int> IDs das notas fiscais
     */
    public array $notaIds;

    public int $issuerId;

    public int $userId;

    public int $timeout = 600;  // 10 minutos

    /**
     * @param  array<int>  $notaIds
     */
    public function __construct(array $notaIds, int $issuerId, int $userId)
    {
        $this->notaIds = $notaIds;
        $this->issuerId = $issuerId;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $issuer = Issuer::find($this->issuerId);
        if (! $issuer) {
            return;
        }

        $user = User::find($this->userId);
        if (! $user) {
            return;
        }

        // Carregar NFs com relacionamentos
        $notas = NotaFiscalEletronica::whereIn('id', $this->notaIds)
            ->with('tagged.tag')
            ->get();

        if ($notas->isEmpty()) {
            Notification::make()
                ->title('Nenhuma nota fiscal encontrada para processar')
                ->warning()
                ->sendToDatabase($user, isEventDispatched: true);

            return;
        }

        try {
            $orquestrador = new OrquestradorService($issuer);
            $resultado = $orquestrador->gerarTxt($notas);

            $directory = 'downloads/'.now()->format('m-Y');
            $directoryPath = storage_path('app/private/'.$directory);

            if (! is_dir($directoryPath)) {
                mkdir($directoryPath, 0755, true);
            }

            $randomName = Str::random(8).'.txt';

            $filename = $directory.'/'.$randomName;
            $pathFile = storage_path('app/private/'.$filename);

            file_put_contents($pathFile, $resultado['conteudo']);

            // Create secure download record
            $secureDownload = SecureDownload::create([
                'user_id' => $user->id,
                'file_path' => $filename,
                'file_name' => 'dominio_'.now()->format('Ymd_His').'.txt',
                'mime_type' => 'application/text',
                'size' => filesize($pathFile),
                'job_class' => self::class,
                'expires_at' => now()->addDays(7),
            ]);

            // Notificar usuário
            $nfLabels = $notas->map(fn ($n) => "NF {$n->nNF}")->implode(', ');
            $avisos = $resultado['avisos_ipi_bc'] ?? [];

            $body = "{$resultado['nfs']} NFs processadas, {$resultado['linhas']} linhas geradas.";
            if ($resultado['erros'] > 0) {
                $body .= " {$resultado['erros']} erro(s).";
            }
            if (! empty($avisos)) {
                $body .= ' '.count($avisos).' aviso(s) de IPI na BC.';
            }

            Notification::make()
                ->title('Arquivo disponível para download')
                ->body($body)
                ->success()
                ->actions([
                    Action::make('download')
                        ->label('Baixar arquivo')
                        ->button()
                        ->openUrlInNewTab()
                        ->url(route('download', ['uuid' => $secureDownload->id])),
                ])
                ->sendToDatabase($user, isEventDispatched: true);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao gerar arquivo Domínio')
                ->body($e->getMessage())
                ->danger()
                ->sendToDatabase($user, isEventDispatched: true);

            throw $e;
        }
    }
}
