<?php

namespace App\Services\Xml;

use App\Models\Issuer;
use App\Models\LogSefazNfseEvent;
use Exception;
use Illuminate\Support\Facades\Log;

class XmlNfseEventReaderService
{
    private string $xml;

    private \SimpleXMLElement $simpleXml;

    private Issuer $issuer;

    private array $data = [];

    /**
     * Carrega e valida o XML
     *
     * @param  string  $xml  String XML ou string compactada em gzip
     *
     * @throws Exception
     */
    public function loadXml(string $xml): self
    {
        try {
            // Verifica se o conteúdo está compactado em gzip
            $decoded = @gzdecode(base64_decode($xml));
            if ($decoded !== false) {
                $this->xml = $decoded;
            } else {
                $this->xml = $xml;
            }

            // Carrega o XML usando SimpleXML
            $this->simpleXml = simplexml_load_string($this->xml);
            if ($this->simpleXml === false) {
                throw new Exception('Falha ao carregar XML de evento: XML mal formatado');
            }

            // Valida se é um XML de evento NFSe
            if ($this->simpleXml->getName() !== 'evento') {
                throw new Exception('XML inválido: Tag evento não encontrada');
            }

            return $this;
        } catch (Exception $e) {
            Log::error('Erro ao carregar XML de evento NFSe: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Extrai e mapeia os dados do XML de evento para um array estruturado
     *
     * @return self
     *
     * @throws Exception
     */
    public function parse(): self
    {
        if (! isset($this->simpleXml)) {
            throw new Exception('XML não foi carregado. Execute loadXml() primeiro.');
        }

        // Namespace do XML de evento NFSe
        $namespaces = $this->simpleXml->getNamespaces(true);
        $ns = $namespaces[''] ?? null;

        $infEvento = $ns
            ? $this->simpleXml->children($ns)->infEvento
            : $this->simpleXml->infEvento;

        if (empty($infEvento)) {
            throw new Exception('Tag infEvento não encontrada no XML de evento NFSe');
        }

        $nDFSe = (string) $infEvento->nDFSe;
        $dhProc = (string) $infEvento->dhProc;

        $pedRegEvento = $infEvento->pedRegEvento;
        $infPedReg = $pedRegEvento->infPedReg;

        $chNFSe = (string) $infPedReg->chNFSe;

        // Tag do evento (e105102 = cancelamento/substituição)
        $eventoData = null;
        foreach ($infPedReg->children() as $child) {
            $childName = $child->getName();
            if (str_starts_with($childName, 'e')) {
                $eventoData = $child;
                break;
            }
        }

        $xDesc = $eventoData ? (string) $eventoData->xDesc : null;
        $cMotivo = $eventoData ? (string) $eventoData->cMotivo : null;
        $xMotivo = $eventoData ? (string) $eventoData->xMotivo : null;
        $chSubstituta = $eventoData ? (string) $eventoData->chSubstituta : null;

        $this->data = [
            'chave_acesso' => $chNFSe ?: $nDFSe,
            'dh_evento' => str_replace('T', ' ', $dhProc),
            'x_desc' => $xDesc,
            'c_motivo' => $cMotivo,
            'x_motivo' => $xMotivo,
            'ch_substituta' => $chSubstituta,
            'xml' => base64_encode($this->xml),
        ];

        return $this;
    }

    /**
     * Salva o evento no banco de dados
     */
    public function save(): LogSefazNfseEvent
    {
        return LogSefazNfseEvent::updateOrCreate(
            [
                'chave_acesso' => $this->data['chave_acesso'],
            ],
            [
                'issuer_id' => $this->issuer->id,
                'dh_evento' => $this->data['dh_evento'],
                'x_desc' => $this->data['x_desc'],
                'c_motivo' => $this->data['c_motivo'],
                'x_motivo' => $this->data['x_motivo'],
                'ch_substituta' => $this->data['ch_substituta'],
                'xml' => $this->data['xml'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Retorna os dados extraídos do XML
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Define o emissor (issuer) para o qual o evento pertence
     */
    public function setIssuer(Issuer $issuer): self
    {
        $this->issuer = $issuer;

        return $this;
    }
}