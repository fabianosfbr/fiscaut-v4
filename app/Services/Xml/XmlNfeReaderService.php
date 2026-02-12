<?php

namespace App\Services\Xml;

use App\Events\NfeCancelada;
use App\Jobs\Sefaz\CheckNfeData;
use App\Models\ConhecimentoTransporteEletronico;
use App\Models\Issuer;
use App\Models\LogSefazNfeEvent;
use App\Models\LogSefazResumoNfe;
use App\Models\NotaFiscalEletronica;
use Exception;
use Illuminate\Support\Facades\Log;

class XmlNfeReaderService
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
            Log::error('Erro ao carregar XML NFe: '.$e->getMessage());
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
            case XmlIdentifierService::TIPO_NFE:
                $this->processNfeCompleta();
                break;

            case XmlIdentifierService::TIPO_NFE_RESUMO:
                $this->processNfeResumo();
                break;

            case XmlIdentifierService::TIPO_EVENTO_NFE:
                $this->processNfeEvento();
                break;

            default:
                throw new Exception('Tipo de XML não suportado: '.$tipoXml);
        }
    }

    private function processNfeCompleta(): void
    {

        $params = $this->preparaDadosNfe();
        Log::info('Registrando/Atualizando NFe no Fiscaut - Chave:  '.$params['chave']);

        $params['origem'] = $this->origem;

        $params['tenant_id'] = $this->issuer->tenant_id;

        $params['xml'] = gzcompress($this->xml);

        $nfe = NotaFiscalEletronica::updateOrCreate(
            [
                'chave' => $params['chave'],
                'tenant_id' => $this->issuer->tenant_id,
            ],
            $params
        );

        if ($nfe->vICMSUFDest > 0.0) {
            $nfe->updateQuietly([
                'difal' => $nfe->calcularDifalProdutos(),
            ]);
        }

        if (! is_null($params['refNFe'])) {

            $nfeRef = NotaFiscalEletronica::where('nNF', $params['refNFe'])
                ->where('emitente_cnpj', '!=', $params['emitente_cnpj'])
                ->first();
            if ($nfeRef) {
                $nfeRef->nfeReferenciada()->where('nfe_referenciada', $nfe->id)->delete();
                $nfeRef->nfeReferenciada()->create(['nfe_referenciada' => $nfe->id]);
            }
        }

        $ctes = ConhecimentoTransporteEletronico::query()
            ->whereNfeChave($params['chave'])
            ->get();
        if ($ctes->count() > 0) {
            $ctes->each(function (ConhecimentoTransporteEletronico $cte) {
                // Disparar evento de verificar NFe associada
                info('Disparando evento de verificar NFe associada - CTE: '.$cte->id);
                CheckNfeData::dispatch($cte);
            });
        }
    }

    private function processNfeResumo(): void
    {

        $resumoList = xml_list($this->data['resNFe'] ?? null);
        $resumo = $resumoList[0] ?? [];

        LogSefazResumoNfe::updateOrCreate(
            [
                'chave' => $resumo['chNFe'],
                'issuer_id' => $this->issuer->id,
                'tenant_id' => $this->issuer->tenant_id,
            ],
            [
                'chave' => $resumo['chNFe'],
                'cnpj' => $resumo['CNPJ'],
                'razao_social' => $resumo['xNome'],
                'iscricao_estadual' => isset($resumo['IE']) ? $resumo['IE'] : null,
                'tipo_nfe' => $resumo['tpNF'],
                'valor_nfe' => $resumo['vNF'],
                'created_at' => date('Y-m-d h:i:s'),
                'dh_emissao' => explode('T', $resumo['dhRecbto'])[0].' '.explode('-', explode('T', $resumo['dhRecbto'])[1])[0],
                'issuer_id' => $this->issuer->id,
                'tenant_id' => $this->issuer->tenant_id,
                'xml' => $this->xml,
            ]
        );
    }

    private function processNfeEvento(): void
    {
        $evento = $this->extractEventoNfeData();

        $chave = $evento['chave'];
        $tpEvento = $evento['tpEvento'];
        $nSeqEvento = $evento['nSeqEvento'];
        $dhEvento = $evento['dhEvento'];
        $xEvento = $evento['xEvento'];
       
        $carta_correcao = [];
        $log = LogSefazNfeEvent::updateOrCreate(
            [
                'chave' => $chave,
                'tp_evento' => (int) $tpEvento,
                'n_seq_evento' => (int) $nSeqEvento,
                'issuer_id' => $this->issuer->id,
                'tenant_id' => $this->issuer->tenant_id,
            ],
            [
                'chave' => $chave,
                'tp_evento' => (int) $tpEvento,
                'n_seq_evento' => (int) $nSeqEvento,
                'dh_evento' => $dhEvento,
                'x_evento' => $xEvento,
                'xml' => $this->xml,
                'issuer_id' => $this->issuer->id,
                'tenant_id' => $this->issuer->tenant_id,
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
            event(new NfeCancelada($log));
        }
    }

    private function extractEventoNfeData(): array
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

    private function preparaDadosNfe(): array
    {
        $nfeProc = $this->data['nfeProc'] ?? [];
        $infNFe = $nfeProc['NFe']['infNFe'] ?? [];
        $ide = $infNFe['ide'] ?? [];
        $emit = $infNFe['emit'] ?? [];
        $dest = $infNFe['dest'] ?? [];
        $transp = $infNFe['transp'] ?? [];
        $transporta = $transp['transporta'] ?? [];
        $total = $infNFe['total']['ICMSTot'] ?? [];
        $protInfProt = $nfeProc['protNFe']['infProt'] ?? [];

        $autXmlEntries = xml_list($infNFe['autXML'] ?? null);
        $autXml = null;
        if (count($autXmlEntries) > 1) {
            $autXml = [];
            foreach ($autXmlEntries as $aut) {
                $autXml[] = $this->preparaAutXml();
            }
        } elseif (count($autXmlEntries) === 1) {
            $autXml = $this->preparaAutXml();
        }

        $nfRefList = xml_list($ide['NFref'] ?? null);
        $refNFe = $nfRefList[0]['refNF']['nNF'] ?? null;

        return [
            'nNF' => $ide['nNF'] ?? null,
            'nat_op' => $ide['natOp'] ?? null,
            'status_nota' => $protInfProt['cStat'] ?? null,
            'vNfe' => $total['vNF'] ?? null,
            'data_emissao' => $this->formatIsoDateTime($ide['dhEmi'] ?? null),
            'chave' => $protInfProt['chNFe'] ?? null,
            'emitente_razao_social' => $emit['xNome'] ?? null,
            'emitente_cnpj' => $this->verificaTipoDePessoaEmitente(),
            'emitente_ie' => $emit['IE'] ?? null,
            'emitente_im' => $emit['IM'] ?? null,
            'enderEmit_xLgr' => $emit['enderEmit']['xLgr'] ?? null,
            'enderEmit_nro' => $emit['enderEmit']['nro'] ?? null,
            'enderEmit_xBairro' => $emit['enderEmit']['xBairro'] ?? null,
            'enderEmit_xMun' => $emit['enderEmit']['xMun'] ?? null,
            'enderEmit_UF' => $emit['enderEmit']['UF'] ?? null,
            'enderEmit_CEP' => $emit['enderEmit']['CEP'] ?? null,
            'enderEmit_xPais' => $emit['enderEmit']['xPais'] ?? null,
            'enderEmit_fone' => $emit['enderEmit']['fone'] ?? null,
            'tpNf' => $ide['tpNF'] ?? null,
            'destinatario_ie' => $dest['IE'] ?? null,
            'destinatario_im' => $dest['IM'] ?? null,
            'destinatario_cnpj' => $this->verificaTipoDePessoaDestinatario(),
            'destinatario_razao_social' => $dest['xNome'] ?? null,
            'enderDest_xLgr' => $dest['enderDest']['xLgr'] ?? null,
            'enderDest_nro' => $dest['enderDest']['nro'] ?? null,
            'enderDest_xCpl' => $dest['enderDest']['xCpl'] ?? null,
            'enderDest_xBairro' => $dest['enderDest']['xBairro'] ?? null,
            'enderDest_xMun' => $dest['enderDest']['xMun'] ?? null,
            'enderDest_UF' => $dest['enderDest']['UF'] ?? null,
            'enderDest_CEP' => $dest['enderDest']['CEP'] ?? null,
            'enderDest_xPais' => $dest['enderDest']['xPais'] ?? null,
            'enderDest_fone' => $dest['enderDest']['fone'] ?? null,
            'transportador_cnpj' => $transporta['CNPJ'] ?? null,
            'transportador_razao_social' => $transporta['xNome'] ?? null,
            'transportador_IE' => $transporta['IE'] ?? null,
            'transportador_modFrete' => $this->checkTipoFrete(),
            'transportador_xEnder' => $transporta['xEnder'] ?? null,
            'transportador_xMun' => $transporta['xMun'] ?? null,
            'transportador_UF' => $transporta['UF'] ?? null,
            'aut_xml' => json_encode($autXml),
            'nProt' => $protInfProt['nProt'] ?? null,
            'digVal' => $protInfProt['digVal'] ?? null,
            'cobranca' => xml_list($infNFe['pag'] ?? null),
            'pagamento' => json_encode(xml_list($infNFe['cobr'] ?? null)),
            'vBC' => $total['vBC'] ?? null,
            'vICMS' => $total['vICMS'] ?? null,
            'vICMSDeson' => $total['vICMSDeson'] ?? null,
            'vFCPUFDest' => $total['vFCPUFDest'] ?? 0,
            'vICMSUFDest' => $total['vICMSUFDest'] ?? 0,
            'vICMSUFRemet' => $total['vICMSUFRemet'] ?? 0,
            'vFCP' => $total['vFCP'] ?? null,
            'vBCST' => $total['vBCST'] ?? null,
            'vST' => $total['vST'] ?? null,
            'vFCPST' => $total['vFCPST'] ?? null,
            'vFCPSTRet' => $total['vFCPSTRet'] ?? null,
            'vProd' => $total['vProd'] ?? null,
            'vFrete' => $total['vFrete'] ?? null,
            'vSeg' => $total['vSeg'] ?? null,
            'vDesc' => $total['vDesc'] ?? null,
            'vII' => $total['vII'] ?? null,
            'vIPI' => $total['vIPI'] ?? null,
            'vIPIDevol' => $total['vIPIDevol'] ?? null,
            'vPIS' => $total['vPIS'] ?? null,
            'vCOFINS' => $total['vCOFINS'] ?? null,
            'vOutro' => $total['vOutro'] ?? null,
            'serie' => $ide['serie'] ?? null,
            'modFrete' => $transp['modFrete'] ?? null,
            'vTotTrib' => $total['vTotTrib'] ?? 0,
            'num_produtos' => count(xml_list($infNFe['det'] ?? null)),
            'cfops' => $this->preparaCfops(),
            'refNFe' => $refNFe,
        ];
    }

    protected function preparaAutXml()
    {
        $autXmlContent = [];

        if (is_array($this->data['nfeProc']['NFe']['infNFe']['autXML'])) {
            foreach ($this->data['nfeProc']['NFe']['infNFe']['autXML'] as $key => $value) {

                $autXmlContent[] = $value;
            }

            return $autXmlContent;
        }

        return [$this->data['nfeProc']['NFe']['infNFe']['autXML']];
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

    private function preparaCfops()
    {
        $produtos = xml_list($this->data['nfeProc']['NFe']['infNFe']['det'] ?? null);
        array_walk($produtos, function (&$value, $key) use (&$cfops) {
            $cfops[] = $value['prod']['CFOP'] ?? null;
        });

        $cfops = array_filter($cfops ?? [], fn ($cfop) => ! is_null($cfop) && $cfop !== '');
        $values = array_unique($cfops);
        rsort($values);

        return $values;
    }
}
