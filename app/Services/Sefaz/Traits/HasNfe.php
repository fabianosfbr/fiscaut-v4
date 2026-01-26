<?php

namespace App\Services\Sefaz\Traits;

use App\Models\LogSefazResumoNfe;
use App\Models\NfeProdut;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Log;

trait HasNfe
{
    public function preparaDadosNfe($element)
    {
        $nfeProc = $element['nfeProc'] ?? [];
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
                $autXml[] = $this->preparaAutXml($aut);
            }
        } elseif (count($autXmlEntries) === 1) {
            $autXml = $this->preparaAutXml($autXmlEntries[0]);
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
            'emitente_cnpj' => $this->verificaTipoDePessoaEmitente($element),
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
            'destinatario_cnpj' => $this->verificaTipoDePessoaDestinatario($element),
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
            'transportador_modFrete' => $this->checkTipoFrete($transp['modFrete'] ?? null),
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
            'difal' => $this->getDifal($element),
            'serie' => $ide['serie'] ?? null,
            'modFrete' => $transp['modFrete'] ?? null,
            'vTotTrib' => $total['vTotTrib'] ?? 0,
            'num_produtos' => count(xml_list($infNFe['det'] ?? null)),
            'cfops' => $this->preparaCfops($element),
            'refNFe' => $refNFe,
        ];
    }

    public function preparaDadosProdutos($produto)
    {
        return [
            'codigo_produto' => searchValueInArray($produto, 'cProd'),
            'descricao_produto' => searchValueInArray($produto, 'xProd'),
            'ncm' => searchValueInArray($produto, 'NCM'),
            'cfop' => searchValueInArray($produto, 'CFOP'),
            'unidade' => searchValueInArray($produto, 'uCom'),
            'quantidade' => searchValueInArray($produto, 'qCom'),
            'cEANTrib' => searchValueInArray($produto, 'cEANTrib'),
            'nbm' => searchValueInArray($produto, 'nbm'),
            'valor_unit' => searchValueInArray($produto, 'vUnCom'),
            'valor_total' => searchValueInArray($produto, 'vProd'),
            'valor_desc' => searchValueInArray($produto, 'vDesc') ?? 0,
            'valor_seguro' => searchValueInArray($produto, 'vSeg') ?? 0,
            'valor_frete' => searchValueInArray($produto, 'vFrete') ?? 0,
            'base_icms' => isset($produto['imposto']['ICMS']) ? searchValueInArray($produto['imposto']['ICMS'], 'vBC') : 0,
            'valor_icms' => isset($produto['imposto']['ICMS']) ? searchValueInArray($produto['imposto']['ICMS'], 'vICMS') : 0,
            'aliq_icms' => isset($produto['imposto']['ICMS']) ? searchValueInArray($produto['imposto']['ICMS'], 'pICMS') : 0,
            'cst_icms' => isset($produto['imposto']['ICMS']) ? searchValueInArray($produto['imposto']['ICMS'], 'CST') : null,
            'csosn' => isset($produto['imposto']['ICMS']) ? searchValueInArray($produto['imposto']['ICMS'], 'CSOSN') : null,
            'vCredICMSSN' => isset($produto['imposto']['ICMS']) ? searchValueInArray($produto['imposto']['ICMS'], 'pCredSN') : 0,
            'pCredSN' => isset($produto['imposto']['ICMS']) ? searchValueInArray($produto['imposto']['ICMS'], 'vCredICMSSN') : 0,
            'base_st' => isset($produto['imposto']['ICMS']) ? searchValueInArray($produto['imposto']['ICMS'], 'vBCST') : 0,
            'aliq_st' => isset($produto['imposto']['ICMS']) ? searchValueInArray($produto['imposto']['ICMS'], 'pICMSST') : 0,
            'valor_st' => isset($produto['imposto']['ICMS']) ? searchValueInArray($produto['imposto']['ICMS'], 'vICMSST') : 0,
            'base_ipi' => isset($produto['imposto']['IPI']) ? searchValueInArray($produto['imposto']['IPI'], 'vBC') : 0,
            'valor_ipi' => isset($produto['imposto']['IPI']) ? searchValueInArray($produto['imposto']['IPI'], 'vIPI') : 0,
            'aliq_ipi' => isset($produto['imposto']['IPI']) ? searchValueInArray($produto['imposto']['IPI'], 'pIPI') : 0,
            'cst_ipi' => isset($produto['imposto']['IPI']) ? searchValueInArray($produto['imposto']['IPI'], 'CST') : null,
            'valor_pis_st' => isset($produto['imposto']['PISST']) ? searchValueInArray($produto['imposto']['PISST'], 'vPIS') : 0,
            'base_pis' => isset($produto['imposto']['PIS']) ? searchValueInArray($produto['imposto']['PIS'], 'vBC') : 0,
            'valor_pis' => isset($produto['imposto']['PIS']) ? searchValueInArray($produto['imposto']['PIS'], 'vPIS') : 0,
            'aliq_pis' => isset($produto['imposto']['PIS']) ? searchValueInArray($produto['imposto']['PIS'], 'pPIS') : 0,
            'cst_pis' => isset($produto['imposto']['PIS']) ? searchValueInArray($produto['imposto']['PIS'], 'CST') : null,
            'base_cofins' => isset($produto['imposto']['COFINS']) ? searchValueInArray($produto['imposto']['COFINS'], 'vBC') : 0,
            'valor_cofins' => isset($produto['imposto']['COFINS']) ? searchValueInArray($produto['imposto']['COFINS'], 'vCOFINS') : 0,
            'aliq_cofins' => isset($produto['imposto']['COFINS']) ? searchValueInArray($produto['imposto']['COFINS'], 'pCOFINS') : 0,
            'cst_cofins' => isset($produto['imposto']['COFINS']) ? searchValueInArray($produto['imposto']['COFINS'], 'CST') : null,
            'valor_ii' => isset($produto['imposto']['II']) ? searchValueInArray($produto['imposto']['II'], 'vII') : 0,
        ];
    }

    public function prepareDocs($response, $reader, $origem)
    {
        $maxNSU = $reader['retDistDFeInt']['maxNSU'] ?? null;
        $docs = $this->extractDocs($response);

        foreach ($docs as $doc) {

            $numnsu = intval($doc->getAttribute('NSU'));

            $xml = gzdecode(base64_decode($doc->nodeValue));

            $xmlReader = loadXmlReader($xml);

            $this->registerLogNfeContent($this->issuer, $numnsu, $maxNSU, $xml);

            $this->exec($xmlReader, $xml, $origem);
        }
    }

    public function exec($xmlReader, $xml, $origem)
    {
   
        if ($this->checkIsType($xmlReader, 'resEvento')) {

            $this->registerLogNfeEvent($this->issuer, $xml, $xmlReader);

            return;
        }

        if ($this->checkIsType($xmlReader, 'procEventoNFe')) {

            $this->registerLogProcNfeEvent($this->issuer, $xml, $xmlReader);

            return;
        }

        if ($this->checkIsType($xmlReader, 'evento')) {

            $this->registerLogProcNfeEvent($this->issuer, $xml, $xmlReader);

            return;
        }

        if ($this->checkIsType($xmlReader, 'resNFe')) {

            $resumoList = xml_list($xmlReader['resNFe'] ?? null);
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
                    'dh_emissao' => explode('T', $resumo['dhRecbto'])[0] . ' ' . explode('-', explode('T', $resumo['dhRecbto'])[1])[0],
                    'issuer_id' => $this->issuer->id,
                    'tenant_id' => $this->issuer->tenant_id,
                    'xml' => $xml,
                ]
            );

            return;
        }

        if ($this->checkIsType($xmlReader, 'nfeProc')) {

            $chave = $xmlReader['nfeProc']['protNFe']['infProt']['chNFe'] ?? null;
            Log::info('Registrando/Atualizando NFe no Fiscaut - Chave:  ' . $chave);
            $params = $this->preparaDadosNfe($xmlReader);

            $params['xml'] = gzcompress($xml);

            $params['origem'] = $origem;


            $params['tenant_id'] = $this->issuer->tenant_id;


            $nfe = NotaFiscalEletronica::updateOrCreate(
                [
                    'chave' => $params['chave'],
                    'tenant_id' => $this->issuer->tenant_id,
                ],
                $params
            );


            if (!is_null($params['refNFe'])) {

                $nfeRef = NotaFiscalEletronica::where('nNF', $params['refNFe'])
                    ->where('emitente_cnpj', '!=', $params['emitente_cnpj'])
                    ->first();
                if ($nfeRef) {
                    $nfeRef->nfeReferenciada()->where('nfe_referenciada', $nfe->id)->delete();
                    $nfeRef->nfeReferenciada()->create(['nfe_referenciada' => $nfe->id]);
                }
            }


            return;
        }
    }

    private function preparaCfops($element)
    {
        $produtos = xml_list($element['nfeProc']['NFe']['infNFe']['det'] ?? null);
        array_walk($produtos, function (&$value, $key) use (&$cfops) {
            $cfops[] = $value['prod']['CFOP'] ?? null;
        });

        $cfops = array_filter($cfops ?? [], fn($cfop) => ! is_null($cfop) && $cfop !== '');
        $values = array_unique($cfops);
        rsort($values);
        return  $values;
    }

    public function checkIsType($content, $type)
    {
        return isset($content[$type]);
    }

    protected function preparaAutXml($content)
    {
        $autXmlContent = [];

        if (is_array($content)) {
            foreach ($content as $key => $value) {

                $autXmlContent[] = $value;
            }

            return $autXmlContent;
        }

        return [$content];
    }

    private function getDifal($element): null|array
    {
        $difal = [];
        $produtos = xml_list($element['nfeProc']['NFe']['infNFe']['det'] ?? null);
        $ufEmitente = $element['nfeProc']['NFe']['infNFe']['emit']['enderEmit']['UF'] ?? null;
        $ufDestinatario = $element['nfeProc']['NFe']['infNFe']['dest']['enderDest']['UF'] ?? null;

        foreach ($produtos as $key => $produto) {

            $difalCalculado = $this->calculaDifalProduto($produto, $ufEmitente, $ufDestinatario);
            if (isset($difalCalculado['valor_imposto']) && $difalCalculado['valor_imposto'] > 0) {
                array_push($difal, $difalCalculado);
            }
        }

        return count($difal) > 0 ? $difal : null;
    }

    private function calculaDifalProduto($data, $ufEmitente, $ufDestinatario)
    {
        $aliqs = config('admin.aliqs');

        $produto = $data['prod'];

        $imposto = $data['imposto'];

        $is_SN = searchValueInArray($imposto, 'CSOSN');

        if (!is_null($is_SN)) {
            $index = array_search($ufDestinatario, $aliqs['UF']);
            $aliq_interestadual = searchValueInArray($imposto, 'pICMSInter') ?? $aliqs[$ufEmitente][$index];
            $aliq_destino = searchValueInArray($imposto, 'pICMSUFDest') ?? $aliqs[$ufDestinatario][$index];

            $base_operacao = $produto['vProd'];

            $icms_operacao = $base_operacao * $aliq_interestadual / 100;

            $nova_base = ($base_operacao - $icms_operacao) / (1 - $aliq_destino / 100);

            $valor_difal = ($nova_base * $aliq_destino / 100) - $icms_operacao;

            $valores = [
                'produto' => $produto['xProd'],
                'valor_contabil' => number_format($produto['vProd'], 2),
                'base_calculo' => number_format($nova_base, 2),
                'aliq_interna' => number_format($aliq_interestadual, 2),
                'aliq_destino' => number_format($aliq_destino, 2),
                'valor_icms' => number_format($icms_operacao, 2),
                'valor_imposto' => number_format($valor_difal, 2),
            ];

            return $valores;
        } else {
            $index = array_search($ufDestinatario, $aliqs['UF']);
            $aliq_interestadual = $aliqs[$ufEmitente][$index] ?? 0;
            $aliq_destino = $aliqs[$ufDestinatario][$index] ?? 0;

            $base_operacao = $produto['vProd'];

            $icms_operacao = $base_operacao * $aliq_interestadual / 100;

            $nova_base = ($base_operacao - $icms_operacao) / (1 - $aliq_destino / 100);

            $valor_difal = ($nova_base * $aliq_destino / 100) - $icms_operacao;

            $valores = [
                'produto' => $produto['xProd'],
                'valor_contabil' => number_format($produto['vProd'], 2),
                'base_calculo' => number_format($nova_base, 2),
                'aliq_interna' => number_format($aliq_interestadual, 2),
                'aliq_destino' => number_format($aliq_destino, 2),
                'valor_icms' => number_format($icms_operacao, 2),
                'valor_imposto' => number_format($valor_difal, 2),
            ];

            return $valores;
        }
    }

    public function checkTipoFrete($modFrete)
    {
        $texto = '';
        switch ($modFrete) {
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

    public function checkEmptyOrError($element)
    {
        $cStat = $element['retDistDFeInt']['cStat'] ?? null;
        if (! in_array($cStat, ['137', '656'])) {
            return false;
        }

        Log::info('Log de consulta NFe - SEFAZ - retorno -  ' . $cStat . ' Empresa: ' . explode(':', $this->issuer->razao_social)[0]);

        return true;
    }

    public function verificaTipoDePessoaDestinatario($element)
    {
        $dest = $element['nfeProc']['NFe']['infNFe']['dest'] ?? [];

        return $dest['CNPJ'] ?? $dest['CPF'] ?? null;
    }

    public function verificaTipoDePessoaEmitente($element)
    {
        $emit = $element['nfeProc']['NFe']['infNFe']['emit'] ?? [];

        return $emit['CNPJ'] ?? $emit['CPF'] ?? null;
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

        return $date . ' ' . $time;
    }
}
