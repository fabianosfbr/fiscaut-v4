<?php

namespace App\Services\Sefaz\Traits;

use App\Events\NfeCancelada;
use App\Models\LogSefazCteContent;
use App\Models\LogSefazCteEvent;
use App\Models\LogSefazNfeContent;
use App\Models\LogSefazNfeEvent;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Log;

trait HasLogSefaz
{
    public function registerLogCteContent($issuer, $numnsu, $maxNSU, $xml)
    {
        $logContent = LogSefazCteContent::updateOrCreate(
            [
                'issuer_id' => $issuer->id,
                'nsu' => $numnsu,
            ],
            [
                'issuer_id' => $issuer->id,
                'nsu' => $numnsu,
                'max_nsu' => $maxNSU,
                'xml' => $xml,
            ]
        );

        Log::notice('CTe NSU consulta SEFAZ: '.$numnsu.' maxnsu: '.$maxNSU.' Empresa: '.$this->issuer->razao_social);

        return $logContent;
    }

    public function registerLogNfeContent($issuer, $numnsu, $maxNSU, $xml)
    {
        $logContent = LogSefazNfeContent::updateOrCreate(
            [
                'issuer_id' => $issuer->id,
                'nsu' => $numnsu,
            ],
            [
                'issuer_id' => $issuer->id,
                'nsu' => $numnsu,
                'max_nsu' => $maxNSU,
                'xml' => $xml,
            ]
        );

        Log::notice('NFe NSU consulta SEFAZ: '.$numnsu.' maxnsu: '.$maxNSU.' Empresa: '.$this->issuer->razao_social);

        return $logContent;
    }

    public function registerLogCteEvent($issuer, $xml, $element)
    {
        $infEvento = $element['procEventoCTe']['eventoCTe']['infEvento'] ?? $element['eventoCTe']['infEvento'] ?? $element['evento']['infEvento'] ?? [];
        $chave = $infEvento['chCTe'] ?? null;
        $tpEvento = $infEvento['tpEvento'] ?? null;
        $nSeqEvento = $infEvento['nSeqEvento'] ?? null;
        $dhEvento = $this->formatIsoDateTime($infEvento['dhEvento'] ?? null);

        LogSefazCteEvent::updateOrCreate(
            [
                'chave' => $chave,
                'tp_evento' => $tpEvento,
                'n_seq_evento' => $nSeqEvento,
                'issuer_id' => $issuer->id,
                'tenant_id' => $issuer->tenant_id,
            ],
            [
                'chave' => $chave,
                'tp_evento' => $tpEvento,
                'n_seq_evento' => $nSeqEvento,
                'dh_evento' => $dhEvento,
                'xml' => $xml,
                'issuer_id' => $issuer->id,
                'tenant_id' => $issuer->tenant_id,
            ]
        );
    }

    public function registerLogProcNfeEvent($issuer, $xml, $element)
    {
        $infEvento = $element['procEventoNFe']['evento']['infEvento'] ?? $element['evento']['infEvento'] ?? [];
        $chave = $infEvento['chNFe'] ?? null;
        $tpEvento = $infEvento['tpEvento'] ?? null;
        $nSeqEvento = $infEvento['nSeqEvento'] ?? null;
        $dhEvento = $this->formatIsoDateTime($infEvento['dhEvento'] ?? null);
        $xEvento = $infEvento['detEvento']['descEvento'] ?? null;

        $carta_correcao = [];
        $log = LogSefazNfeEvent::updateOrCreate(
            [
                'chave' => $chave,
                'tp_evento' => $tpEvento,
                'n_seq_evento' => $nSeqEvento,
                'issuer_id' => $issuer->id,
                'tenant_id' => $issuer->tenant_id,
            ],
            [
                'chave' => $chave,
                'tp_evento' => $tpEvento,
                'n_seq_evento' => $nSeqEvento,
                'dh_evento' => $dhEvento,
                'x_evento' => $xEvento,
                'xml' => $xml,
                'issuer_id' => $issuer->id,
                'tenant_id' => $issuer->tenant_id,
            ]
        );

        $nfe = NotaFiscalEletronica::where('chave', $chave)->first();

        if ($log->tp_evento == 110110 && $nfe) {

            if (isset($nfe->carta_correcao) && ! empty($nfe->carta_correcao)) {

                $carta_correcao = $nfe->carta_correcao;
            }

            if (! in_array($log->id, $carta_correcao)) {
                $carta_correcao[] = $log->id;
            }

            $nfe->update(['carta_correcao' => $carta_correcao]);
        }

        if ($log->tp_evento == 110111) {
            // Disparar evento de cancelamento
            event(new NfeCancelada($log));
        }
    }

    public function registerLogNfeEvent($issuer, $xml, $element)
    {
        $resEvento = $element['resEvento'] ?? [];
        $chave = $resEvento['chNFe'] ?? null;
        $tpEvento = $resEvento['tpEvento'] ?? null;
        $nSeqEvento = $resEvento['nSeqEvento'] ?? null;
        $dhEvento = $this->formatIsoDateTime($resEvento['dhRecbto'] ?? null);
        $xEvento = $resEvento['xEvento'] ?? null;

        $params = [
            'chave' => $chave,
            'tp_evento' => $tpEvento,
            'n_seq_evento' => $nSeqEvento,
            'dh_evento' => $dhEvento,
            'x_evento' => $xEvento,
            'xml' => $xml,
            'issuer_id' => $issuer->id,
            'tenant_id' => $issuer->tenant_id,
        ];

        LogSefazNfeEvent::updateOrCreate(
            [
                'chave' => $chave,
                'tp_evento' => $tpEvento,
                'n_seq_evento' => $nSeqEvento,
                'issuer_id' => $issuer->id,
                'tenant_id' => $issuer->tenant_id,
            ],
            $params
        );
    }

    private function formatIsoDateTime(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $parts = explode('T', $value, 2);
        if (count($parts) !== 2) {
            return $value;
        }

        $date = $parts[0];
        $time = explode('-', $parts[1], 2)[0];

        return $date.' '.$time;
    }
}
