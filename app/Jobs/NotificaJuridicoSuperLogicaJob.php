<?php

namespace App\Jobs;

use App\Models\GeneralSetting;
use App\Models\Issuer;
use App\Models\SuperLogicaUnidade;
use App\Services\SuperlogicaConnectionService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotificaJuridicoSuperLogicaJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Issuer $issuer
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tenant = $this->issuer->tenant;
        $settings = GeneralSetting::where('issuer_id', $this->issuer->id)
            ->where('name', 'configuracoes_cobranca')
            ->first();

        if (! $settings || empty($settings->payload['notificacao_juridico'])) {

            return;
        }

        $config = $settings->payload['notificacao_juridico'];

        if (empty($config['enabled'])) {

            return;
        }

        $diasConfig = array_filter(array_map('trim', explode(',', $config['dias'] ?? '')));

        if (empty($diasConfig)) {

            return;
        }

        $service = new SuperlogicaConnectionService($tenant);

        $inadimplencias = $service
            ->receita()
            ->listarInadimplencia([
                'idCondominio' => $this->issuer->superlogica_condominio_id,
            ]);

        if (empty($inadimplencias)) {

            return;
        }

        $today = Carbon::today();

        $processosJudiciais = $service
            ->receita()
            ->listarProcessosJudiciais([
                'idCondominio' => $this->issuer->superlogica_condominio_id,
            ]);

        $processoJudicialIds = collect($processosJudiciais)->pluck('id_unidade_uni')->toArray();

        foreach ($inadimplencias as $record) {

            // Ignora se a unidade estiver em processo judicial
            if (in_array(data_get($record, 'id_unidade_uni'), $processoJudicialIds)) {
                continue;
            }

            if (! isset($record['recebimento']) || ! is_array($record['recebimento'])) {
                continue;
            }

            $titulosAtrasados = [];
            $deveNotificar = false;

            foreach ($record['recebimento'] as $recb) {

                $vencimentoStr = data_get($recb, 'dt_vencimento_recb');
                if (! $vencimentoStr) {
                    continue;
                }

                try {
                    $vencimento = Carbon::createFromFormat('m/d/Y H:i:s', $vencimentoStr)->startOfDay();
                } catch (\Exception $e) {
                    try {
                        $vencimento = Carbon::parse($vencimentoStr)->startOfDay();
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                $diasAtraso = data_get($recb, 'encargos.0.diasatraso');

                $valor = number_format((float) data_get($recb, 'encargos.0.valorcorrigido', data_get($recb, 'vl_emitido_recb', 0)), 2, ',', '.');
                $titulosAtrasados[] = "Vencimento: {$vencimento->format('d/m/Y')} - Valor: R$ {$valor}";

                if (in_array((string) $diasAtraso, $diasConfig)) {
                    $deveNotificar = true;
                }
            }

            if ($deveNotificar) {

                $email = data_get($record, 'recebimento.0.contatosunidade.0.proprietario.0.email');

                if (! $email) {
                    $idUnidade = data_get($record, 'id_unidade_uni') ?? data_get($record, 'st_unidade_uni');
                    $unidade = SuperLogicaUnidade::where('id_unidade_uni', $idUnidade)
                        ->where('id_condominio', $this->issuer->superlogica_condominio_id)
                        ->first();

                    $email = $unidade ? data_get($unidade, 'metadados.email_proprietario') : null;
                }

                if ($email) {
                    $titulosHtml = '<ul>';
                    foreach ($titulosAtrasados as $t) {
                        $titulosHtml .= "<li>{$t}</li>";
                    }
                    $titulosHtml .= '</ul>';

                    $unidadeData = [
                        'razao_social' => $this->issuer->razao_social,
                        'numero_unidade' => data_get($record, 'st_unidade_uni', ''),
                        'bloco_quadra' => data_get($record, 'st_bloco_uni', ''),
                        'nome_morador' => data_get($record, 'st_sacado_uni', ''),
                        'titulos_aberto' => $titulosHtml,
                        'id_condominio_cond' => data_get($recb, 'id_condominio_cond'),
                        'id_recebimento_recb' => data_get($recb, 'id_recebimento_recb'),
                        'id_unidade_uni' => data_get($recb, 'id_unidade_uni'),
                        'recebimento' => $recb,

                    ];

                    SendCobrancaEmailJob::dispatch($this->issuer->id, 'giron61861@ellbit.com; gerencia.cont@speedgrupo.com.br;cobranca.adm.2@speedgrupo.com.br ', $unidadeData, true);
                    sleep(10);
                }
            }
        }
    }
}
