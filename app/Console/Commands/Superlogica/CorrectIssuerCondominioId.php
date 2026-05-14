<?php

namespace App\Console\Commands\Superlogica;

use App\Models\Issuer;
use App\Models\SuperLogicaCondominio;
use Illuminate\Console\Command;

class CorrectIssuerCondominioId extends Command
{
    protected $signature = 'app:correct-issuer-condominio-id {--tenant= : ID do tenant para correção específica}';

    protected $description = 'Corrige o atributo superlogica_condominio_id na tabela issuers baseado no id_condominio_cond da tabela super_logica_condominios';

    private function normalizeCnpj(string $cnpj): string
    {
        return preg_replace('/[^0-9]/', '', $cnpj);
    }

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        $issuers = Issuer::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->whereNotNull('cnpj')
            ->where('cnpj', '!=', '')
            ->get();

        $this->info("Processando {$issuers->count()} issuers...");

        dd($issuers);

        $updated = 0;
        $notFound = 0;

        foreach ($issuers as $issuer) {
            $issuerCnpj = $this->normalizeCnpj($issuer->cnpj);

            $condominio = SuperLogicaCondominio::where('tenant_id', $issuer->tenant_id)
                ->get()
                ->first(function ($cond) use ($issuerCnpj) {
                    $condominioCnpj = $this->normalizeCnpj($cond->metadados['st_cpf_cond'] ?? '');
                    return $condominioCnpj === $issuerCnpj;
                });

            if ($condominio) {
                $oldValue = $issuer->superlogica_condominio_id;
                $issuer->update(['superlogica_condominio_id' => $condominio->id_condominio_cond]);

                $this->line("Issuer {$issuer->id} ({$issuer->cnpj}): {$oldValue} -> {$condominio->id_condominio_cond}");
                $updated++;
            } else {
                $this->warn("Issuer {$issuer->id} ({$issuer->cnpj}): Condomínio não encontrado");
                $notFound++;
            }
        }

        $this->info("Concluído: {$updated} atualizados, {$notFound} não encontrados");

        return self::SUCCESS;
    }
}