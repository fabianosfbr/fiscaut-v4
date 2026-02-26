<?php

namespace App\Jobs\Sefaz;

use App\Events\NfseCancelada;
use App\Models\Issuer;
use App\Models\LogSefazNfseEvent;
use App\Models\NotaFiscalServico;
use App\Models\XmlImportJob;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SefazNfseProcessDocumentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 60;

    public function __construct(
        protected array $documento,
        protected Issuer $issuer,
        protected XmlImportJob $importJob
    ) {
        $this->onQueue('sefaz');
    }

    public function handle(): void
    {
        try {
            $tipoDocumento = $this->documento['tipo_documento'] ?? null;

            if ($tipoDocumento === 'NFSE') {
                $this->processNfse();
            } elseif ($tipoDocumento === 'EVENTO') {
                $this->processEvento();
            } else {
                Log::info('Tipo de documento não processado', [
                    'issuer_id' => $this->issuer->id,
                    'tipo' => $this->documento['tipo_documento'] ?? 'N/A',
                    'nsu' => $this->documento['nsu'] ?? 'N/A',
                ]);

                return;
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao processar documento NFSe da SEFAZ', [
                'issuer_id' => $this->issuer->id,
                'nsu' => $this->documento['nsu'] ?? 'N/A',
                'tipo' => $this->documento['tipo_documento'] ?? 'N/A',
                'error' => $e->getMessage(),
            ]);

            $this->importJob->addError($e->getMessage());

            throw $e;
        }
    }

    private function processNfse(): void
    {
        $xml = $this->documento['xml'] ?? null;
        if (! is_string($xml) || trim($xml) === '') {
            throw new \RuntimeException('XML da NFSe ausente no documento');
        }

        $xmlObj = simplexml_load_string($xml);
        if (! $xmlObj) {
            throw new \RuntimeException('Falha ao carregar XML da NFSe');
        }

        $tomador = $xmlObj->infNFSe->DPS->infDPS->toma ?? null;
        $intermediario = $xmlObj->infNFSe->DPS->infDPS->interm ?? null;
        if ($intermediario) {
            $tomador = $intermediario;
        }

        $chave = $this->documento['chave_acesso'] ?? null;
        if (! is_string($chave) || $chave === '') {
            $chave = $this->documento['chave'] ?? null;
        }

        $dataEmissao = null;
        $dhEmi = (string) ($xmlObj->infNFSe->DPS->infDPS->dhEmi ?? '');
        if ($dhEmi !== '') {
            $dataEmissao = Carbon::parse($dhEmi);
        }
        $numero = (int) ($xmlObj->infNFSe->nNFSe ?? 0);
        $prestador_cnpj = (string) ($xmlObj->infNFSe->emit->CNPJ ?? $xmlObj->infNFSe->emit->CPF ?? null);


        NotaFiscalServico::updateOrCreate([
            'numero' => $numero,
            'prestador_cnpj' => $prestador_cnpj,
        ], [
            'codigo_verificacao' => (string) ($xmlObj->infNFSe->DPS->infDPS->codVerif ?? null),
            'chave' => $chave,
            'chave_acesso' => $chave,
            'origem' => $this->documento['origem'] ?? 'SEFAZ',
            'valor_servico' => (float) ($xmlObj->infNFSe->valores->vLiq ?? null),
            'data_emissao' => $dataEmissao,
            'prestador_servico' => (string) ($xmlObj->infNFSe->emit->xNome ?? null),
            'prestador_im' => (string) ($xmlObj->infNFSe->emit->IM ?? null),
            'tomador_cnpj' => (string) ($tomador->CNPJ ?? $tomador->CPF ?? null),
            'tomador_servico' => (string) ($tomador->xNome ?? null),
            'tomador_im' => (string) ($tomador->IM ?? null),
            'codigo_municipio' => (int) ($xmlObj->infNFSe->DPS->infDPS->cLocEmi ?? null),
            'xml' => $xml,
        ]);

        $this->importJob->incrementNumDocuments();
    }

    private function processEvento(): void
    {
        $xml = $this->documento['xml'] ?? null;
        if (! is_string($xml) || trim($xml) === '') {
            throw new \RuntimeException('XML do evento da NFSe ausente no documento');
        }

        $xmlObj = simplexml_load_string($xml);
        if (! $xmlObj) {
            throw new \RuntimeException('Falha ao carregar XML do evento da NFSe');
        }

        $chave = $this->documento['chave_acesso'] ?? null;
        if (! is_string($chave) || $chave === '') {
            $chave = $this->documento['chave'] ?? null;
        }

        $dhEvento = null;
        $dataHoraGeracao = $this->documento['data_hora_geracao'] ?? null;
        if (is_string($dataHoraGeracao) && $dataHoraGeracao !== '') {
            $dhEvento = Carbon::parse($dataHoraGeracao);
        }

        $cMotivo = (string) ($xmlObj->infEvento->pedRegEvento->infPedReg->e105103->cMotivo ?? $xmlObj->infEvento->pedRegEvento->infPedReg->e105102->cMotivo ?? $xmlObj->infEvento->pedRegEvento->infPedReg->e101101->cMotivo ?? null);
        $log = LogSefazNfseEvent::updateOrCreate([
            'chave_acesso' => $chave,
            'issuer_id' => $this->issuer->id,
            'c_motivo' => $cMotivo !== '' ? $cMotivo : null,
        ], [
            'dh_evento' => $dhEvento ?? now(),
            'x_desc' => (string) ($xmlObj->infEvento->pedRegEvento->infPedReg->e105103->xDesc ?? $xmlObj->infEvento->pedRegEvento->infPedReg->e105102->xDesc ?? $xmlObj->infEvento->pedRegEvento->infPedReg->e101101->xDesc ?? null),
            'c_motivo' => $cMotivo !== '' ? $cMotivo : null,
            'x_motivo' => (string) ($xmlObj->infEvento->pedRegEvento->infPedReg->e105103->xMotivo ?? $xmlObj->infEvento->pedRegEvento->infPedReg->e105102->xMotivo ?? $xmlObj->infEvento->pedRegEvento->infPedReg->e101101->xMotivo ?? null),
            'ch_substituta' => (string) ($xmlObj->infEvento->pedRegEvento->infPedReg->e105103->chSubstituta ?? $xmlObj->infEvento->pedRegEvento->infPedReg->e105102->chSubstituta ?? $xmlObj->infEvento->pedRegEvento->infPedReg->e101101->chSubstituta ?? null),
            'xml' => $xml,
        ]);

        event(new NfseCancelada($log));

        $this->importJob->incrementNumEvents();
    }
}
