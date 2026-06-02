<?php

namespace App\Jobs;

use App\Integrations\DominioSistemas\Services\OrquestradorService;
use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use App\Models\TempFile;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;

class GerarArquivoDominio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array<int> IDs das notas fiscais */
    public array $notaIds;

    public int $issuerId;

    public int $timeout = 600; // 10 minutos

    /**
     * @param array<int> $notaIds
     */
    public function __construct(array $notaIds, int $issuerId)
    {
        $this->notaIds = $notaIds;
        $this->issuerId = $issuerId;
    }

    public function handle(): void
    {
        $issuer = Issuer::find($this->issuerId);
        if (!$issuer) {
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
                ->sendToUser(auth()->user());

            return;
        }

        try {
            $orquestrador = new OrquestradorService($issuer);
            $resultado = $orquestrador->gerarTxt($notas);

            // Salvar arquivo como temp_file
            $dataHora = now()->format('Ymd_His');
            $nomeArquivo = "dominio_kopron_{$dataHora}.txt";
            $caminho = "temp/dominio/{$nomeArquivo}";
            
            $disk = \Illuminate\Support\Facades\Storage::disk('local');
            $disk->put($caminho, $resultado['conteudo']);

            $tempFile = TempFile::create([
                'original_name' => $nomeArquivo,
                'file_path' => $caminho,
                'disk' => 'local',
                'expires_at' => now()->addDays(1),
            ]);

            // Notificar usuário
            $nfLabels = $notas->map(fn($n) => "NF {$n->nNF}")->implode(', ');
            $avisos = $resultado['avisos_ipi_bc'] ?? [];

            $body = "{$resultado['nfs']} NFs processadas, {$resultado['linhas']} linhas geradas.";
            if ($resultado['erros'] > 0) {
                $body .= " {$resultado['erros']} erro(s).";
            }
            if (!empty($avisos)) {
                $body .= " " . count($avisos) . " aviso(s) de IPI na BC.";
            }

            Notification::make()
                ->title("Arquivo Domínio gerado: {$nomeArquivo}")
                ->body($body)
                ->success()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('download')
                        ->label('Baixar arquivo')
                        ->url(route('temp-files.download', $tempFile->id))
                        ->button(),
                ])
                ->sendToUser(auth()->user());

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao gerar arquivo Domínio')
                ->body($e->getMessage())
                ->danger()
                ->sendToUser(auth()->user());

            throw $e;
        }
    }
}