<?php

namespace App\Services\Xml;

use App\Events\CteCancelada;
use App\Jobs\Sefaz\CheckNfeData;
use App\Models\ConhecimentoTransporteEletronico;
use App\Models\LogSefazCteEvent;
use App\Models\Issuer;
use Exception;
use Illuminate\Support\Facades\Log;

class XmlCteReaderService
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
            case XmlIdentifierService::TIPO_CTE:
                $this->processCteCompleta();
                break;

            case XmlIdentifierService::TIPO_EVENTO_CTE:
                $this->processCteEvento();
                break;

            default:
                throw new Exception('Tipo de XML não suportado: '.$tipoXml);
        }

    }

    private function processCteCompleta(): void
    {
            
        $params = $this->preparaDadosCte();
        
        Log::info('Registrando/Atualizando CTe no Fiscaut - Chave:  '.$params['chave']);

        $params['origem'] = $this->origem;
        
        $params['tenant_id'] = $this->issuer->tenant_id;

        $params['xml'] = gzcompress($this->xml);

        $cte = ConhecimentoTransporteEletronico::updateOrCreate(
            [
                'chave' => $params['chave'],
                'tenant_id' => $this->issuer->tenant_id,
            ],
            $params
        );

        if (! is_null($params['nfe_chave'])) {

            // Disparar evento de verificar NFe associada
            CheckNfeData::dispatch($cte)->onQueue('low');
        }
    }

    private function processCteEvento(): void
    {
        $infEvento = $this->data['procEventoCTe']['eventoCTe']['infEvento'] ?? $this->data['eventoCTe']['infEvento'] ?? $this->data['evento']['infEvento'] ?? [];
        $chave = $infEvento['chCTe'] ?? null;
        $tpEvento = $infEvento['tpEvento'] ?? null;
        $nSeqEvento = $infEvento['nSeqEvento'] ?? null;
        $dhEvento = $this->formatIsoDateTime($infEvento['dhEvento'] ?? null);

        $log = LogSefazCteEvent::updateOrCreate(
            [
                'chave' => $chave,
                'tp_evento' => $tpEvento,
                'n_seq_evento' => $nSeqEvento,
                'issuer_id' => $this->issuer->id,
                'tenant_id' => $this->issuer->tenant_id,
            ],
            [
                'chave' => $chave,
                'tp_evento' => $tpEvento,
                'n_seq_evento' => $nSeqEvento,
                'dh_evento' => $dhEvento,
                'xml' => $this->xml,
                'issuer_id' => $this->issuer->id,
                'tenant_id' => $this->issuer->tenant_id,
            ]
        );
 
        if ($tpEvento == 110111) {
            $cte = ConhecimentoTransporteEletronico::where('chave', $chave)->first();
            if ($cte) {
                $cte->is_cancelada = true;
                $cte->save();
            }
 
            event(new CteCancelada($log));
        }
    }

    private function preparaDadosCte(): array
    {
        $cteProc = $this->data['cteProc'] ?? [];
        $infCte = $cteProc['CTe']['infCte'] ?? [];
        $ide = $infCte['ide'] ?? [];
        $protInfProt = $cteProc['protCTe']['infProt'] ?? [];
        $emit = $infCte['emit'] ?? [];
        $dest = $infCte['dest'] ?? null;
        $rem = $infCte['rem'] ?? null;
        $dhEmi = $this->formatIsoDateTime($ide['dhEmi'] ?? null);

        return [
            'nCTe' => $ide['nCT'] ?? null,
            'tpCTe' => $ide['tpCTe'] ?? null,
            'status_cte' => $protInfProt['cStat'] ?? null,
            'vCTe' => $infCte['vPrest']['vRec'] ?? null,
            'emitente_razao_social' => $emit['xNome'] ?? null,
            'emitente_cnpj' => $emit['CNPJ'] ?? $emit['CPF'] ?? null,
            'destinatario_razao_social' => is_array($dest) ? ($dest['xNome'] ?? null) : null,
            'destinatario_cnpj' => is_array($dest) ? ($dest['CNPJ'] ?? $dest['CPF'] ?? null) : null,
            'aut_xml' => $this->preparaAutXml(),
            'remetente_razao_social' => is_array($rem) ? ($rem['xNome'] ?? null) : null,
            'remetente_cnpj' => is_array($rem) ? ($rem['CNPJ'] ?? $rem['CPF'] ?? null) : null,
            'data_emissao' => $dhEmi,
            'chave' => $protInfProt['chCTe'] ?? null,
            'uf_origem' => $ide['UFIni'] ?? null,
            'xMunIni' => $ide['xMunIni'] ?? null,
            'uf_destino' => $ide['UFFim'] ?? null,
            'xMunFim' => $ide['xMunFim'] ?? null,
            'nProt' => $protInfProt['nProt'] ?? null,
            'nfe_chave' => $this->getNfeChaves(),
            'tomador_razao_social' => $this->preparaTomador('xNome'),
            'tomador_cnpj' => $this->preparaTomador('CNPJ'),
        ];
    }

    private function getNfeChaves()
    {
        $cteProc = $this->data['cteProc'] ?? [];
        $infCte = $cteProc['CTe']['infCte'] ?? [];
        $nfeChaves = xml_list($infCte['infCTeNorm']['infDoc']['infNFe'] ?? null);

        if ($nfeChaves === []) {
            return null;
        }

        return json_encode($nfeChaves);
    }

    protected function preparaAutXml()
    {
        $cteProc = $this->data['cteProc'] ?? [];
        $infCte = $cteProc['CTe']['infCte'] ?? [];
        $autXml = $infCte['autXML'] ?? null;

        if ($autXml === null) {
            return null;
        }

        return json_encode(xml_list($autXml));
    }

    private function preparaTomador($attribute)
    {
        $cteProc = $this->data['cteProc'] ?? [];
        $infCte = $cteProc['CTe']['infCte'] ?? [];
        $toma = searchValueInArray($infCte, 'toma');

        switch ((int) $toma) {
            case 0:
                return $infCte['rem'][$attribute] ?? $infCte['rem']['CPF'] ?? null;
            case 1:
                return $infCte['exped'][$attribute] ?? null;
            case 2:
                return $infCte['receb'][$attribute] ?? null;
            case 3:
                return $infCte['dest'][$attribute] ?? $infCte['dest']['CPF'] ?? null;
            case 4:
                return $infCte['toma4'][$attribute] ?? $infCte['toma4']['CPF'] ?? null;
        }

        return null;
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

    public function checkTipoFrete()
    {
        $texto = '';
        switch ($this->data['nfeProc']['NFe']['infNFe']['transp']['modFrete']) {
            case 0:
                $texto = '0-Por conta do Emit';
                break;
            case 1:
                $texto = '1-Por conta do Dest';
                break;
            case 2:
                $texto = '2-Por conta de Terceiros';
                break;
            case 3:
                $texto = '3-Próprio por conta do Rem';
                break;
            case 4:
                $texto = '4-Próprio por conta do Dest';
                break;
            case 9:
                $texto = '9-Sem Transporte';
                break;
        }

        return $texto;
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
