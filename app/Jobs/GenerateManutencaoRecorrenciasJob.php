<?php

namespace App\Jobs;

use App\Models\Manutencao;
use App\Models\ManutencaoRecorrencia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateManutencaoRecorrenciasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Iniciando geração automática de manutenções recorrentes');

        // Buscar todas as recorrências ativas que precisam gerar manutenções
        $recorrencias = ManutencaoRecorrencia::ativas()->get();

        $totalGeradas = 0;
        $totalProcessadas = 0;

        foreach ($recorrencias as $recorrencia) {
            try {
                $totalProcessadas++;

                // Verificar se a recorrência precisa gerar manutenções
                if (! $recorrencia->podeGerar()) {
                    continue;
                }

                // Calcular a data da próxima manutenção
                $dataManutencao = $recorrencia->calcularDataManutencao();

                // Verificar se já existe manutenção programada para essa data
                $existeManutencao = Manutencao::where('recorrencia_id', $recorrencia->id)
                    ->where('data_programada', $dataManutencao->toDateString())
                    ->exists();

                if ($existeManutencao) {
                    Log::info("Manutenção já existe para recorrência {$recorrencia->id} na data {$dataManutencao->toDateString()}");

                    continue;
                }

                // Verificar se a data está dentro do período de vigência
                if ($recorrencia->data_fim && $dataManutencao->isAfter($recorrencia->data_fim)) {
                    Log::info("Data {$dataManutencao->toDateString()} está fora do período de vigência para recorrência {$recorrencia->id}");

                    continue;
                }

                // Gerar a manutenção
                $manutencao = $recorrencia->gerarManutencao();

                $totalGeradas++;

                Log::info("Manutenção gerada com sucesso: {$manutencao->id} - {$manutencao->titulo}");
            } catch (\Exception $e) {
                Log::error("Erro ao processar recorrência {$recorrencia->id}: ".$e->getMessage());
                Log::error($e->getTraceAsString());
            }
        }

        Log::info("Processamento concluído: {$totalProcessadas} recorrências processadas, {$totalGeradas} manutenções geradas");
    }
}
