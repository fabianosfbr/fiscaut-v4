<?php

namespace App\Services\Xml;

use App\Enums\StatusNfeEnum;
use App\Models\Issuer;
use App\Models\LogSefazNfeEvent;
use App\Models\NotaFiscalConsumidor;
use Exception;
use Illuminate\Support\Facades\Log;
use NFePHP\NFe\Complements;

class XmlNfceReaderService
{
    private array $data = [];

    private string $xml;

    private Issuer $issuer;

    private string $origem = 'SEFAZ';

    public function __construct()
    {
        //
    }

    public function loadXml(string $xmlContent): self
    {
        try {
            $this->xml = $xmlContent;

            return $this;
        } catch (Exception $e) {
            Log::error('Erro ao carregar XML: '.$e->getMessage());
            throw new Exception('XML inválido ou mal formatado');
        }
    }

    /**
     * Extrai e mapeia os dados do XML para um array estruturado
     */
    public function parse(): self
    {
        if (! $this->xml) {
            throw new Exception('XML não foi carregado');
        }

        $this->data = app(XmlReaderService::class)->read($this->xml);

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setOrigem(string $origem = 'SEFAZ'): self
    {
        if (! in_array($origem, ['IMPORTADO', 'SEFAZ', 'SIEG'])) {
            throw new Exception('Origem inválida. Use: IMPORTADO, SEFAZ ou SIEG');
        }

        $this->origem = $origem;

        return $this;
    }

    public function setIssuer(Issuer $issuer): self
    {
        $this->issuer = $issuer;

        return $this;
    }

    public function save(): void
    {
        if (empty($this->data)) {
            throw new Exception('Dados não foram extraídos. Execute parse() primeiro.');
        }

        $tipoXml = XmlIdentifierService::identificarTipoXml($this->xml);

        switch ($tipoXml) {
            case XmlIdentifierService::TIPO_NFCE:
                $this->processNfceCompleta();
                break;

            case XmlIdentifierService::TIPO_EVENTO_NFE:
                $this->processNfceEvento();
                break;

            default:
                throw new Exception('Tipo de XML não suportado: '.$tipoXml);
        }
    }

    private function processNfceCompleta(): void
    {
        $params = $this->preparaDadosNfce();

        $params['origem'] = $this->origem;

        $params['xml'] = gzcompress($this->xml);

        NotaFiscalConsumidor::updateOrCreate(
            [
                'chave' => $params['chave'],
            ],
            $params
        );
    }

    private function processNfceEvento(): void
    {
        $evento = $this->extractEventoNfceData();

        $chave = $evento['chave'];
        $tpEvento = $evento['tpEvento'];
        $nSeqEvento = $evento['nSeqEvento'];
        $dhEvento = $evento['dhEvento'];
        $xEvento = $evento['xEvento'];

        $log = LogSefazNfeEvent::updateOrCreate(
            [
                'chave' => $chave,
                'tp_evento' => (int) $tpEvento,
                'modelo' => (int) 65,
                'n_seq_evento' => (int) $nSeqEvento,
                'issuer_id' => $this->issuer->id,
                'tenant_id' => $this->issuer->tenant_id,
            ],
            [
                'chave' => $chave,
                'tp_evento' => (int) $tpEvento,
                'modelo' => (int) 65,
                'n_seq_evento' => (int) $nSeqEvento,
                'dh_evento' => $dhEvento,
                'x_evento' => $xEvento,
                'xml' => $this->xml,
                'issuer_id' => $this->issuer->id,
                'tenant_id' => $this->issuer->tenant_id,
            ]
        );

        $nfce = NotaFiscalConsumidor::where('chave', $chave)->first();

        if ($log->tp_evento == 110111 && $nfce->status_nota != StatusNfeEnum::CANCELADA) {
            $xml = Complements::cancelRegister(gzuncompress($nfce->xml), $this->xml);
            $nfce->updateQuietly([
                'xml' => gzcompress($xml),
                'status_nota' => StatusNfeEnum::CANCELADA,
            ]);
        }
    }

    private function extractEventoNfceData(): array
    {
        $infEvento = $this->data['procEventoNFe']['evento']['infEvento']
            ?? $this->data['evento']['infEvento']
            ?? null;

        $resEvento = $this->data['resEvento'] ?? null;

        $chave = $infEvento['chNFe'] ?? $resEvento['chNFe'] ?? null;
        $tpEvento = $infEvento['tpEvento'] ?? $resEvento['tpEvento'] ?? null;
        $nSeqEvento = $infEvento['nSeqEvento'] ?? $resEvento['nSeqEvento'] ?? 1;
        $dhEventoRaw = $infEvento['dhEvento'] ?? $resEvento['dhEvento'] ?? $resEvento['dhRecbto'] ?? null;
        $dhEvento = $this->formatIsoDateTime($dhEventoRaw) ?? now()->toDateTimeString();
        $xEvento = $infEvento['detEvento']['descEvento'] ?? $resEvento['xEvento'] ?? null;

        if (! $chave || ! $tpEvento) {
            throw new Exception('Estrutura de evento NFe não reconhecida ou incompleta.');
        }

        return [
            'chave' => $chave,
            'tpEvento' => $tpEvento,
            'nSeqEvento' => $nSeqEvento,
            'dhEvento' => $dhEvento,
            'xEvento' => $xEvento,
        ];
    }

    private function preparaDadosNfce()
    {
        $nfeProc = $this->data['nfeProc'] ?? [];
        $infNFe = $nfeProc['NFe']['infNFe'] ?? [];
        $ide = $infNFe['ide'] ?? [];
        $emit = $infNFe['emit'] ?? [];
        $total = $infNFe['total']['ICMSTot'] ?? [];
        $protInfProt = $nfeProc['protNFe']['infProt'] ?? [];

        return [
            'chave' => $protInfProt['chNFe'] ?? $infNFe['@Id'] ?? null,
            'data_emissao' => $this->formatIsoDateTime($ide['dhEmi'] ?? null),
            'mod' => (int) ($ide['mod'] ?? null),
            'emitente_razao_social' => $emit['xNome'] ?? null,
            'emitente_ie' => $emit['IE'] ?? null,
            'emitente_cnpj' => $emit['CNPJ'] ?? $emit['CPF'] ?? null,
            'nProt' => $protInfProt['nProt'] ?? null,
            'nNF' => $ide['nNF'] ?? null,
            'status_nota' => (int) ($protInfProt['cStat'] ?? null),
            'vNfe' => isset($total['vNF']) ? (float) $total['vNF'] : null,
            'tpNf' => (int) ($ide['tpNF'] ?? null),
        ];
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

    public function verificaTipoDePessoaDestinatario()
    {
        $dest = $this->data['nfeProc']['NFe']['infNFe']['dest'] ?? [];

        return $dest['CNPJ'] ?? $dest['CPF'] ?? null;
    }

    public function verificaTipoDePessoaEmitente()
    {
        $emit = $this->data['nfeProc']['NFe']['infNFe']['emit'] ?? [];

        return $emit['CNPJ'] ?? $emit['CPF'] ?? null;
    }
}
