# -*- coding: utf-8 -*-
# Gerador Dominio Sistemas - v22o

import sys, os, zipfile, re, xml.etree.ElementTree as ET
sys.path.insert(0, '/home/claude')
from tabela_etiquetas       import TABELA_ETIQUETAS, resolver_cfop
from gerar_1020             import gerar_1020_icms, gerar_1020_ipi
from gerar_1030_piscof      import calcular_piscof_item
from ler_csv_rateio         import ler_csv_rateio

NS           = 'http://www.portalfiscal.inf.br/nfe'
UF_EMPRESA   = 'SP'
CNPJ_EMPRESA = '10251329000189'  # Kopron do Brasil — CNPJ producao
ALIQ_INTERNA_SP = 0.18
CFOPS_DIFAL     = {'2556','2551','2406'}   # 2407 removido: retorno conserto
UF_7PCT = {'AC','AL','AM','AP','BA','CE','MA','MT','MS','PA',
           'PB','PE','PI','RN','RO','RR','SE','TO'}

# ─── Helpers ────────────────────────────────────────────────────────
def tag(el,n):
    if el is None: return ''
    x = el.find(f'{{{NS}}}{n}')
    return x.text.strip() if x is not None and x.text else ''

def fmt_data(s):
    s = s.split('T')[0]
    if '-' in s:
        p=s.split('-'); return f"{p[2]}/{p[1]}/{p[0]}"
    return s

def fmt_dec(v,c=2):
    try: return f"{float(v or 0):.{c}f}".replace('.', ',')
    except: return '0,'+'0'*c

def fmt_qtd(v):
    try: return f"{float(v or 0):.4f}".replace('.', ',')
    except: return '0,0000'

def raiz(cnpj): return re.sub(r'\D','',cnpj)[:8]

def limpar(t):
    if not t: return ''
    for k,v in {'Ç':'C','ç':'c','Ã':'A','ã':'a','Â':'A','â':'a','Á':'A','á':'a',
                'À':'A','à':'a','É':'E','é':'e','Ê':'E','ê':'e','Í':'I','í':'i',
                'Ó':'O','ó':'o','Ô':'O','ô':'o','Õ':'O','õ':'o','Ú':'U','ú':'u',
                'Ü':'U','ü':'u','Ñ':'N','ñ':'n','|':'-'}.items():
        t=t.replace(k,v)
    return t

def extrair_cod_etiqueta(nome):
    m = re.match(r'^(\d+)',nome.strip())
    return int(m.group(1)) if m else None

def aliq_interestadual(uf, itens):
    for i in itens:
        if i['icms_vBC']>0 and i['icms_vICMS']>0:
            return round(i['icms_vICMS']/i['icms_vBC']*100,2)
        if i['icms_pICMS']>0: return i['icms_pICMS']
    return 7.0 if uf.upper() in UF_7PCT else 12.0

def calcular_difal(itens):
    """
    Calcula DIFAL agrupando por aliquota interestadual (pICMS do XML).
    Retorna lista de dicts: [{'base_dup', 'aliq_inter', 'difal'}, ...]
    Um 1020|8 e gerado por grupo.
    """
    from collections import defaultdict
    grupos = defaultdict(lambda: {'vBC': 0.0, 'vICMS': 0.0})
    for i in itens:
        vBC   = i['icms_vBC']
        vICMS = i['icms_vICMS']
        pICMS = round(i['icms_pICMS'], 2)
        if vBC <= 0:          # SN ou sem destaque: usa vProd, sem origem
            vBC   = i['vProd']
            vICMS = 0.0
            pICMS = 0.0
        grupos[pICMS]['vBC']   += vBC
        grupos[pICMS]['vICMS'] += vICMS
    resultado = []
    for aliq_inter, vals in sorted(grupos.items()):
        vBC   = round(vals['vBC'],   2)
        vICMS = round(vals['vICMS'], 2)
        base_sem = vBC - vICMS
        base_dup = round(base_sem / (1 - ALIQ_INTERNA_SP), 2)
        icms_dst = round(base_dup * ALIQ_INTERNA_SP, 2)
        difal    = max(round(icms_dst - vICMS, 2), 0.0)
        if difal > 0:
            resultado.append({'base_dup': base_dup, 'aliq_inter': aliq_inter, 'difal': difal})
    return resultado

# ─── Leitura do XML ─────────────────────────────────────────────────
def ler_nfe(xml_bytes, cod_etiqueta, dt_emissao_csv=None, dt_entrada_csv=None):
    root   = ET.fromstring(xml_bytes)
    infNFe = root.find(f'.//{{{NS}}}infNFe')
    ide    = infNFe.find(f'{{{NS}}}ide')
    emit   = infNFe.find(f'{{{NS}}}emit')
    ender  = emit.find(f'{{{NS}}}enderEmit')
    totais = infNFe.find(f'.//{{{NS}}}ICMSTot')
    cobr   = infNFe.find(f'{{{NS}}}cobr')
    infAd  = infNFe.find(f'{{{NS}}}infAdic')

    uf_emit    = tag(ender,'UF') if ender is not None else ''
    is_simples = tag(emit,'CRT')=='1'
    etiq_cfg   = TABELA_ETIQUETAS.get(cod_etiqueta,{})

    # Datas: prioridade CSV (data entrada real), fallback XML
    dt_emi_xml = fmt_data(tag(ide,'dhEmi') or tag(ide,'dEmi'))
    dt_emi     = dt_emissao_csv or dt_emi_xml
    dt_ent     = dt_entrada_csv or dt_emi_xml  # fallback: mesma emissao

    # modFrete: 0=CIF, 1=FOB, 2=Terceiros, 3=Rem, 4=Dest, 9=Sem frete
    transp     = infNFe.find(f'{{{NS}}}transp')
    modFrete_raw = tag(transp,'modFrete') if transp is not None else '0'
    MOD_FRETE_MAP = {'0':'C','1':'F','2':'T','3':'R','4':'D','9':'S'}
    modFrete   = MOD_FRETE_MAP.get(str(modFrete_raw).strip(), 'C')

    vFrete_tot    = float(tag(totais,'vFrete')    or 0)
    vSeg_tot      = float(tag(totais,'vSeg')      or 0)
    vDesc_tot     = float(tag(totais,'vDesc')     or 0)
    vOutro_tot    = float(tag(totais,'vOutro')    or 0)
    vNF_tot       = float(tag(totais,'vNF')       or 0)
    vICMS_tot     = float(tag(totais,'vICMS')     or 0)
    # Totais adicionais — backlog campos 1000: 90=vIPI, 91=vST, 97=vICMSDeson
    vII_tot       = float(tag(totais,'vII')       or 0)
    vIPI_tot      = float(tag(totais,'vIPI')      or 0)
    vST_tot       = float(tag(totais,'vST')       or 0)
    vICMSDeson_tot= float(tag(totais,'vICMSDeson')or 0)
    vPISST_tot    = float(tag(totais,'vPISST')    or 0)
    vCOFINSST_tot = float(tag(totais,'vCOFINSST') or 0)

    itens = []
    for det in infNFe.findall(f'{{{NS}}}det'):
        prod = det.find(f'{{{NS}}}prod')
        imp  = det.find(f'{{{NS}}}imposto')

        cfop_saida   = tag(prod,'CFOP')
        cfop_entrada = resolver_cfop(cod_etiqueta, cfop_saida, uf_emit)

        # ICMS
        icms_csosn=''; icms_cst=''; icms_vBC=0.0; icms_pICMS=0.0
        icms_vICMS=0.0; icms_vST=0.0; icms_pRedBC=0.0; icms_vCredSN=0.0; icms_pCredSN=0.0
        icms_vBCEfet=0.0; icms_pICMSEfet=0.0; icms_vICMSEfet=0.0  # CAT 14/2009 (CST 60)
        for tn in ['ICMSSN102','ICMSSN101','ICMSSN400','ICMSSN500','ICMSSN900']:
            nd = imp.find(f'.//{{{NS}}}{tn}') if imp is not None else None
            if nd is not None:
                icms_csosn   = tag(nd,'CSOSN')
                icms_vCredSN = float(tag(nd,'vCredICMSSN') or 0)
                icms_pCredSN = float(tag(nd,'pCredSN')     or 0)
                break
        for tn in ['ICMS00','ICMS10','ICMS20','ICMS30','ICMS40',
                   'ICMS51','ICMS60','ICMS61','ICMS62','ICMS70','ICMS90']:
            nd = imp.find(f'.//{{{NS}}}{tn}') if imp is not None else None
            if nd is not None:
                icms_cst=tag(nd,'CST'); icms_vBC=float(tag(nd,'vBC') or 0)
                icms_pICMS=float(tag(nd,'pICMS') or 0)
                icms_vICMS=float(tag(nd,'vICMS') or 0)
                icms_vST=float(tag(nd,'vICMSST') or tag(nd,'vST') or 0)
                icms_pRedBC=float(tag(nd,'pRedBC') or 0)
                # CST 60: ler campos de ICMS efetivo (Portaria CAT 14/2009)
                if tn == 'ICMS60':
                    icms_vBCEfet   = float(tag(nd,'vBCEfet')   or 0)
                    icms_pICMSEfet = float(tag(nd,'pICMSEfet') or 0)
                    icms_vICMSEfet = float(tag(nd,'vICMSEfet') or 0)
                # CST 61/62: monofasico — vICMSMonoRet e informativo
                if tn in ('ICMS61','ICMS62'):
                    icms_vICMS = float(tag(nd,'vICMSMonoRet') or
                                      tag(nd,'vICMSMono') or 0)
                    icms_cst = '61' if tn=='ICMS61' else '62'
                break

        # PIS
        pis_pPIS=0.0; pis_vPIS=0.0
        for tn in ['PISAliq','PISNT','PISQtde','PISOutr']:
            nd = imp.find(f'.//{{{NS}}}{tn}') if imp is not None else None
            if nd is not None:
                pis_pPIS=float(tag(nd,'pPIS') or 0)
                pis_vPIS=float(tag(nd,'vPIS') or 0); break

        # COFINS
        cof_pCOFINS=0.0; cof_vCOFINS=0.0
        for tn in ['COFINSAliq','COFINSNT','COFINSQtde','COFINSOutr']:
            nd = imp.find(f'.//{{{NS}}}{tn}') if imp is not None else None
            if nd is not None:
                cof_pCOFINS=float(tag(nd,'pCOFINS') or 0)
                cof_vCOFINS=float(tag(nd,'vCOFINS') or 0); break

        # IPI
        ipi_pIPI=0.0; ipi_vBC=0.0; ipi_vIPI=0.0
        for tn in ['IPITrib','IPINT']:
            nd = imp.find(f'.//{{{NS}}}{tn}') if imp is not None else None
            if nd is not None:
                ipi_pIPI=float(tag(nd,'pIPI') or 0)
                ipi_vBC=float(tag(nd,'vBC') or 0)
                ipi_vIPI=float(tag(nd,'vIPI') or 0); break

        # IBS/CBS — bloco <IBSCBS> dentro de <imposto>
        # Estrutura XML NF-e:
        #   IBSCBS → CST, cClassTrib, gIBSCBS → vBC, gIBSUF→pIBSUF+vIBSUF, gIBSMun, vIBS, gCBS→pCBS+vCBS
        #   Variante monofásica: gIBSCBSMono
        ibs_cclass=''; ibs_bc=0.0; ibs_aliq=0.0; ibs_val=0.0
        cbs_cclass=''; cbs_bc=0.0; cbs_aliq=0.0; cbs_val=0.0
        ibscbs_nd = imp.find(f'.//{{{NS}}}IBSCBS') if imp is not None else None
        if ibscbs_nd is not None:
            ibs_cclass = tag(ibscbs_nd, 'cClassTrib') or ''
            cbs_cclass = ibs_cclass  # mesmo cClassTrib para IBS e CBS
            # Grupo principal gIBSCBS (CST 000-499)
            gibscbs = ibscbs_nd.find(f'{{{NS}}}gIBSCBS')
            if gibscbs is not None:
                ibs_bc  = float(tag(gibscbs, 'vBC')  or 0)
                ibs_val = float(tag(gibscbs, 'vIBS') or 0)
                cbs_bc  = ibs_bc  # mesma base
                # alíquota IBS = pIBSUF + pIBSMun
                gibsuf  = gibscbs.find(f'{{{NS}}}gIBSUF')
                gibsmun = gibscbs.find(f'{{{NS}}}gIBSMun')
                p_uf    = float(tag(gibsuf,  'pIBSUF')  or 0) if gibsuf  is not None else 0.0
                p_mun   = float(tag(gibsmun, 'pIBSMun') or 0) if gibsmun is not None else 0.0
                ibs_aliq = round(p_uf + p_mun, 4)
                gcbs = gibscbs.find(f'{{{NS}}}gCBS')
                if gcbs is not None:
                    cbs_aliq = float(tag(gcbs, 'pCBS') or 0)
                    cbs_val  = float(tag(gcbs, 'vCBS') or 0)

        ean = tag(prod,'cEAN')
        if ean in ('','SEM GTIN'): ean=''

        vProd_item    = float(tag(prod,'vProd')    or 0)
        vFrete_item   = float(tag(prod,'vFrete')   or 0)
        vSeg_item     = float(tag(prod,'vSeg')     or 0)
        vDesc_item    = float(tag(prod,'vDesc')    or 0)
        vOutro_item   = float(tag(prod,'vOutro')   or 0)
        # Backlog campos 1000: leitura por item para rateio correto
        vII_item      = float(tag(prod,'vII')      or 0)
        vIPI_item     = float(tag(prod,'vIPI')     or 0)  # <prod>/vIPI (NF importacao)
        vICMSDeson_item = float(tag(imp.find(f'.//{{{NS}}}ICMS') if imp else None,'vICMSDeson') or 0) if imp else 0.0
        # PIS-ST e COFINS-ST: dentro do <imposto>
        pisST_nd      = imp.find(f'.//{{{NS}}}PISST')   if imp else None
        cofinsST_nd   = imp.find(f'.//{{{NS}}}COFINSST') if imp else None
        vPISST_item   = float(tag(pisST_nd,'vPISST')     or 0) if pisST_nd   else 0.0
        vCOFINSST_item= float(tag(cofinsST_nd,'vCOFINSST') or 0) if cofinsST_nd else 0.0

        # Detectar se o IPI está incluído na base do ICMS
        # Condição: ipi_vIPI > 0 E vBC_ICMS ≈ vProd + ipi_vIPI
        # Nesse caso, para tomadores contribuintes do IPI, a base deve ser corrigida
        ipi_na_bc = (
            ipi_vIPI > 0 and
            abs(icms_vBC - vProd_item - ipi_vIPI) < 0.02
        )

        itens.append({
            'nItem':det.get('nItem'), 'cProd':tag(prod,'cProd'),
            'ean':ean, 'xProd':tag(prod,'xProd'), 'NCM':tag(prod,'NCM'),
            'uCom':tag(prod,'uCom'), 'qCom':tag(prod,'qCom'),
            'vUnCom':tag(prod,'vUnCom'),
            'cfop_saida':cfop_saida, 'cfop_entrada':cfop_entrada,
            'vProd':vProd_item, 'vFrete':vFrete_item, 'vSeg':vSeg_item,
            'vDesc':vDesc_item, 'vOutro':vOutro_item,
            # Backlog campos adicionais 1000 (campos 90/91/97 e outros)
            'vII':vII_item, 'vIPI_prod':vIPI_item,
            'vICMSDeson':vICMSDeson_item,
            'vPISST':vPISST_item, 'vCOFINSST':vCOFINSST_item,
            'icms_csosn':icms_csosn, 'icms_cst':icms_cst,
            'icms_vBC':icms_vBC, 'icms_pICMS':icms_pICMS,
            'icms_vICMS':icms_vICMS, 'icms_vST':icms_vST,
            'icms_pRedBC':icms_pRedBC, 'icms_vCredSN':icms_vCredSN,
            'icms_pCredSN':icms_pCredSN,
            'icms_vIPI':ipi_vIPI, 'ipi_pIPI':ipi_pIPI, 'ipi_vBC':ipi_vBC,
            'pis_pPIS':pis_pPIS, 'pis_vPIS':pis_vPIS,
            'cof_pCOFINS':cof_pCOFINS, 'cof_vCOFINS':cof_vCOFINS,
            # IBS/CBS: lidos do bloco <IBSCBS> do XML
            'ibs_cclass':ibs_cclass, 'ibs_bc':ibs_bc,
            'ibs_aliq':ibs_aliq,     'ibs_val':ibs_val,
            'cbs_cclass':cbs_cclass, 'cbs_bc':cbs_bc,
            'cbs_aliq':cbs_aliq,     'cbs_val':cbs_val,
            'ipi_na_bc':ipi_na_bc,   # flag: IPI incluído na BC do ICMS pelo fornecedor
            'icms_vBCEfet':icms_vBCEfet,     # CAT 14/2009: BC efetivo (CST 60)
            'icms_pICMSEfet':icms_pICMSEfet, # CAT 14/2009: aliq efetiva
            'icms_vICMSEfet':icms_vICMSEfet, # CAT 14/2009: valor efetivo
            'cred_icms':etiq_cfg.get('cred_icms',False),
            'cred_piscof':etiq_cfg.get('cred_piscof',False),
            'is_simples':is_simples,
        })

    # Rateio frete/seg/desc/outro + campos backlog
    def ratear(campo_tot, campo_item):
        tot_inf = sum(i[campo_item] for i in itens)
        if abs(tot_inf-campo_tot)>0.01 and campo_tot>0:
            base = sum(i['vProd'] for i in itens) or 1
            for i in itens:
                i[campo_item]=round(campo_tot*i['vProd']/base,2)
    ratear(vFrete_tot,'vFrete'); ratear(vSeg_tot,'vSeg')
    ratear(vDesc_tot,'vDesc');   ratear(vOutro_tot,'vOutro')
    # Rateio campos backlog (totais XML → distribuir por item proporcional a vProd)
    ratear(vII_tot,       'vII')
    ratear(vIPI_tot,      'vIPI_prod')
    ratear(vST_tot,       'icms_vST')    # reforça consistência com ICMSTot/vST
    ratear(vICMSDeson_tot,'vICMSDeson')
    ratear(vPISST_tot,    'vPISST')
    ratear(vCOFINSST_tot, 'vCOFINSST')

    parcelas=[]
    cobr_el = infNFe.find(f'{{{NS}}}cobr')
    if cobr_el is not None:
        for dup in cobr_el.findall(f'{{{NS}}}dup'):
            parcelas.append({'nDup':tag(dup,'nDup'),
                             'dVenc':tag(dup,'dVenc'),'vDup':tag(dup,'vDup')})

    fone = tag(ender,'fone') if ender is not None else ''
    return {
        'chave':infNFe.get('Id','').replace('NFe',''),
        'nNF':tag(ide,'nNF'), 'serie':tag(ide,'serie'),
        'dt_emissao':dt_emi, 'dt_entrada':dt_ent,
        'emit_cnpj':tag(emit,'CNPJ'), 'emit_nome':tag(emit,'xNome'),
        'emit_ie':tag(emit,'IE'), 'emit_crt':tag(emit,'CRT'),
        'emit_lgr':tag(ender,'xLgr') if ender is not None else '',
        'emit_nro':tag(ender,'nro')  if ender is not None else '',
        'emit_bairro':tag(ender,'xBairro') if ender is not None else '',
        'emit_cMun':tag(ender,'cMun') if ender is not None else '',
        'emit_UF':uf_emit,
        'emit_CEP':tag(ender,'CEP')  if ender is not None else '',
        'emit_ddd':fone[:2] if len(fone)>=2 else '',
        'emit_tel':fone[2:]  if len(fone)>2  else fone,
        'vNF':vNF_tot, 'vICMS':vICMS_tot,
        'modFrete':modFrete,
        'infCpl':    tag(infAd,'infCpl')     if infAd is not None else '',
        'infAdFisco':tag(infAd,'infAdFisco') if infAd is not None else '',
        'finNFe':    tag(ide,'finNFe') or '1',
        'is_simples':is_simples,
        'cod_etiqueta':cod_etiqueta, 'etiq_cfg':etiq_cfg,
        'itens':itens, 'parcelas':parcelas,
    }

# ─── Rateio proporcional por etiqueta ───────────────────────────────
def ratear_nota_por_etiqueta(nota, etiquetas_rateio):
    """
    Gera N cópias rateadas da nota, uma por etiqueta.
    etiquetas_rateio: lista de {'cod','desc','valor','pct'}
    Retorna lista de notas rateadas, cada uma com seus itens ajustados.
    """
    notas_rateadas = []
    campos_numericos = [
        'vProd','vFrete','vSeg','vDesc','vOutro',
        'icms_vBC','icms_vICMS','icms_vST','icms_vCredSN','icms_vIPI',
        'ipi_vBC','pis_vPIS','cof_vCOFINS',
        # Backlog campos 1000 (campos 90/91/97 e outros)
        'vII','vIPI_prod','vICMSDeson','vPISST','vCOFINSST',
    ]

    n_etiq = len(etiquetas_rateio)
    pct_acum = 0.0

    for idx, etiq_rat in enumerate(etiquetas_rateio):
        ultimo = (idx == n_etiq - 1)
        pct    = etiq_rat['pct']
        cod    = etiq_rat['cod']
        etiq_cfg_novo = TABELA_ETIQUETAS.get(cod, nota['etiq_cfg'])

        # Copiar itens com valores rateados
        itens_rat = []
        for item in nota['itens']:
            item_novo = dict(item)
            # Rateio de campos numericos
            for campo in campos_numericos:
                item_novo[campo] = round(item[campo]*pct, 4)
            # Recalcular CFOP com a nova etiqueta
            item_novo['cfop_entrada'] = resolver_cfop(
                cod, item['cfop_saida'], nota['emit_UF'])
            # Atualizar flags da nova etiqueta
            item_novo['cred_icms']   = etiq_cfg_novo.get('cred_icms',False)
            item_novo['cred_piscof'] = etiq_cfg_novo.get('cred_piscof',False)
            itens_rat.append(item_novo)

        # Parcelas rateadas
        parcelas_rat = []
        for dup in nota['parcelas']:
            parcelas_rat.append({
                'nDup' : dup['nDup'],
                'dVenc': dup['dVenc'],
                'vDup' : round(float(dup['vDup'].replace(',','.') if isinstance(dup['vDup'],str) else dup['vDup'])*pct,2),
            })

        nota_rat = dict(nota)
        nota_rat['itens']        = itens_rat
        nota_rat['parcelas']     = parcelas_rat
        nota_rat['cod_etiqueta'] = cod
        nota_rat['etiq_cfg']     = etiq_cfg_novo
        nota_rat['vNF']          = round(nota['vNF']*pct, 2)
        nota_rat['vICMS']        = round(nota['vICMS']*pct, 2)
        nota_rat['_valor_etiq']  = etiq_rat['valor']
        nota_rat['_pct_etiq']    = pct
        nota_rat['_ultimo_etiq'] = ultimo
        notas_rateadas.append((cod, nota_rat))

    return notas_rateadas

# ─── Segmentação por CFOP ───────────────────────────────────────────
def segmentar_nf(nota):
    grupos = {}
    for item in nota['itens']:
        cfop = item['cfop_entrada']
        if cfop not in grupos: grupos[cfop]=[]
        grupos[cfop].append(item)
    acum = nota['etiq_cfg'].get('acumulador',8000)
    segs=[]
    for cfop,itens in grupos.items():
        vProd=sum(i['vProd']        for i in itens)
        vFrete=sum(i['vFrete']      for i in itens)
        vSeg=sum(i['vSeg']          for i in itens)
        vDesc=sum(i['vDesc']        for i in itens)
        vOutro=sum(i['vOutro']      for i in itens)
        vIPI=sum(i.get('icms_vIPI',0) for i in itens)   # IPI soma no contábil
        vICMS=sum(i['icms_vICMS']   for i in itens)
        vST=sum(i['icms_vST']       for i in itens)
        vCredSN=sum(i['icms_vCredSN']for i in itens)
        vCont=round(vProd+vFrete+vSeg+vOutro+vIPI-vDesc,2)
        segs.append({'cfop':cfop,'acumulador':acum,'itens':itens,
                     'vContabil':vCont,'vProd':vProd,'vFrete':vFrete,'vSeg':vSeg,
                     'vDesc':vDesc,'vOutro':vOutro,'vIPI':vIPI,'vICMS':vICMS,
                     'vST':vST,'vCredSN':vCredSN})
    return segs

# ─── Geração das linhas ─────────────────────────────────────────────
def gerar_linhas(notas_por_etiqueta, excecoes_unidade=None):
    if excecoes_unidade is None:
        excecoes_unidade = {}
    linhas=[]
    linhas.append(f"|0000|{CNPJ_EMPRESA}|")

    # 0020
    forns=set()
    for _,nota in notas_por_etiqueta:
        c=nota['emit_cnpj']
        if c in forns: continue
        forns.add(c)
        nome=limpar(nota['emit_nome'])[:150]
        apelido=limpar(nota['emit_nome'])[:40]
        end=limpar(nota['emit_lgr'])
        bairro=limpar(nota['emit_bairro'])
        regime='M' if nota['emit_crt']=='1' else 'N'
        linhas.append(
            f"|0020|{c}|{nome}|{apelido}|{end}|{nota['emit_nro']}||{bairro}"
            f"|{nota['emit_cMun']}|{nota['emit_UF']}||{nota['emit_CEP']}"
            f"|{nota['emit_ie']}|||{nota['emit_ddd']}|{nota['emit_tel']}"
            f"|||||||{regime}|N||")

    # Catálogo produtos
    # Chave inclui uCom normalizado (maiúsculas) para evitar duplicatas por capitalização
    # ex: "Un" e "UN" do mesmo produto → mesmo registro no 0100
    # Campo 91 (identificador SPED) = sempre código interno (raizCNPJ_seq)
    # Usar EAN causava conflitos em reimportações e quando fornecedor tem EANs duplicados
    catalogo={}; seq_r={}; prods_g=set(); unids_g=set()
    for _,nota in notas_por_etiqueta:
        r=raiz(nota['emit_cnpj'])
        for item in nota['itens']:
            uCom_norm = item['uCom'].upper().strip()
            chave=f"{r}|{item['cProd']}|{item['xProd']}|{uCom_norm}"
            if chave not in catalogo:
                seq_r[r]=seq_r.get(r,0)+1
                catalogo[chave]=f"{r}_{seq_r[r]:03d}"

    for _,nota in notas_por_etiqueta:
        r=raiz(nota['emit_cnpj'])
        for item in nota['itens']:
            uCom_norm = item['uCom'].upper().strip()
            chave=f"{r}|{item['cProd']}|{item['xProd']}|{uCom_norm}"
            if chave in prods_g: continue
            prods_g.add(chave)
            cod=catalogo[chave]; desc=limpar(item['xProd'])[:60]
            uCom=item['uCom']; vUn=fmt_dec(item['vUnCom'],3)
            # Se produto tem exceção de unidade, usar a unidade do Domínio
            if cod in excecoes_unidade:
                uCom = excecoes_unidade[cod]
                print(f'    [exceção] {cod}: uCom forçada para {uCom!r} (Domínio)')
            ident=cod
            c0100=['0100',cod,desc,'',item['NCM'],'','','',
                   '1',uCom,'S','O','','','','N','',vUn,'','','','','','M']
            while len(c0100)<90: c0100.append('')
            c0100.append(ident)
            linhas.append('|'+'|'.join(c0100)+'|')
            if uCom_norm not in unids_g:
                unids_g.add(uCom_norm)
                du={'UN':'UNIDADE','PC':'PECA','KG':'QUILOGRAMA','CX':'CAIXA',
                    'LT':'LITRO','MT':'METRO','M2':'METRO QUADRADO',
                    'GL':'GALAO','SC':'SACO','PR':'PAR','MWH':'MEGAWATT HORA',
                    'KWH':'KILOWATT HORA','L':'LITRO',
                   }.get(uCom_norm, uCom_norm)
                linhas.append(f"|0150|{uCom}|{du}|")

    # NFs
    # Acumula avisos de IPI incluído na BC para exibir no log e na tela
    avisos_ipi_bc = []

    for cod_etiqueta,nota in notas_por_etiqueta:
        r=raiz(nota['emit_cnpj'])
        segs=segmentar_nf(nota)
        is_simpl=nota['is_simples']
        etiq_cfg=nota['etiq_cfg']
        dt_emi=nota['dt_emissao']
        dt_ent=nota['dt_entrada']

        # Campo 15 = informações ao fisco (infAdFisco) — pipe substituído por -
        info_fisco = limpar(nota.get('infAdFisco',''))[:250]

        # Campo 62 = informações complementares (infCpl) — pipe substituído por -
        info_cpl = limpar(nota.get('infCpl',''))[:250]

        # Corrigir BC do ICMS quando IPI foi incluído pelo fornecedor
        # Aplica apenas em etiquetas que tomam crédito de ICMS (não SN, não despesa)
        if etiq_cfg.get('cred_icms') and not is_simpl:
            for item in nota['itens']:
                if item.get('ipi_na_bc') and item['icms_vBC'] > 0:
                    vBC_orig   = item['icms_vBC']
                    vICMS_orig = item['icms_vICMS']
                    # Base corrigida = vProd (sem IPI)
                    vBC_corr   = item['vProd']
                    vICMS_corr = round(vBC_corr * item['icms_pICMS'] / 100, 2)
                    item['icms_vBC']   = vBC_corr
                    item['icms_vICMS'] = vICMS_corr
                    msg = (f'NF {nota["nNF"]} item {item["nItem"]} '
                           f'({limpar(item["xProd"])[:30]}): '
                           f'IPI excluido da BC ICMS '
                           f'({fmt_dec(vBC_orig)}→{fmt_dec(vBC_corr)}, '
                           f'ICMS {fmt_dec(vICMS_orig)}→{fmt_dec(vICMS_corr)})')
                    avisos_ipi_bc.append(msg)
                    print(f'    ⚠️  BC-IPI: {msg}')

        # CAT 14/2009 — crédito de ICMS-ST para CFOP 1401/2401
        # Portaria CAT 14/2009: adquirente pode se creditar do ICMS retido por ST
        # Condição: cfop_entrada 1401/2401 + fornecedor regime normal (tag ICMS, não ICMSSN)
        #           + etiqueta com cred_icms=True
        # Base: vBCEfet do XML (preferencial) ou vProd como fallback
        # Alíq: pICMSEfet do XML (preferencial) ou 18% fixo SP como fallback
        for item in nota['itens']:
            cfop_ent = item.get('cfop_entrada','')
            eh_sn_item = bool(item.get('icms_csosn'))  # tag ICMSSN = Simples Nacional
            if (cfop_ent in ('1401','2401') and
                    not eh_sn_item and
                    etiq_cfg.get('cred_icms')):
                vBCEfet   = item.get('icms_vBCEfet',   0.0)
                pICMSEfet = item.get('icms_pICMSEfet', 0.0)
                vICMSEfet = item.get('icms_vICMSEfet', 0.0)

                if vBCEfet > 0 and vICMSEfet > 0:
                    vBC_c14   = vBCEfet
                    aliq_c14  = pICMSEfet
                    vICMS_c14 = vICMSEfet
                    fonte = f'XML vBCEfet={fmt_dec(vBCEfet)} pICMSEfet={fmt_dec(aliq_c14)}%'
                else:
                    vBC_c14   = item['vProd']
                    aliq_c14  = 18.0
                    vICMS_c14 = round(vBC_c14 * 0.18, 2)
                    fonte = f'fallback vProd x 18%'

                item['icms_vBC']   = vBC_c14
                item['icms_pICMS'] = aliq_c14
                item['icms_vICMS'] = vICMS_c14

                msg_c14 = (f'NF {nota["nNF"]} item {item["nItem"]} '
                           f'({limpar(item["xProd"])[:30]}): '
                           f'CAT 14/2009 CFOP {cfop_ent} '
                           f'BC={fmt_dec(vBC_c14)} aliq={fmt_dec(aliq_c14)}% '
                           f'ICMS={fmt_dec(vICMS_c14)} [{fonte}]')
                avisos_ipi_bc.append(f'[CAT14] {msg_c14}')
                print(f'    ℹ️  CAT14: {msg_c14}')

        # Campo 41 = código situação do documento (baseado em finNFe)
        # finNFe: 1=Normal→00, 2=Complementar→06, 3=Ajuste→08, 4=Devolução→00
        FIN_NFE_SITUACAO = {'1':'00','2':'06','3':'08','4':'00'}
        cod_situacao = FIN_NFE_SITUACAO.get(nota.get('finNFe','1'), '00')

        n_segs_total = len(segs)

        for n_seg,seg in enumerate(segs,1):
            cfop=seg['cfop']; acum=seg['acumulador']
            vCont=fmt_dec(seg['vContabil'])
            vCont_float=seg['vContabil']
            vICMS_s=fmt_dec(seg['vICMS'])

            # campo 7 = numero do segmento (apenas quando NF tem mais de 1 segmento)
            # Notas sem segmentacao: campo 7 = '0'
            campo7 = str(n_seg) if n_segs_total > 1 else '0'

            # 1000
            c1000=['']*90
            c1000[0]='1000'; c1000[1]='36'
            c1000[2]=nota['emit_cnpj']
            c1000[4]=str(acum); c1000[5]=cfop
            c1000[6]=campo7; c1000[7]=nota['nNF']
            c1000[8]=nota['serie']
            c1000[10]=dt_ent    # campo 11 - data entrada (real)
            c1000[11]=dt_emi    # campo 12 - data emissao
            c1000[12]=vCont
            c1000[14]=info_fisco          # campo 15 - informações ao fisco (infAdFisco)
            c1000[15]=nota['modFrete']    # campo 16 - modalidade frete
            c1000[16]='T'                 # campo 17 - emitente (T=Terceiros)
            c1000[25]=fmt_dec(seg['vFrete'])  # campo 26 - frete
            c1000[26]=fmt_dec(seg['vSeg'])    # campo 27 - seguro
            c1000[27]=fmt_dec(seg['vDesc'])   # campo 28 - desconto
            c1000[28]=fmt_dec(seg['vOutro'])  # campo 29 - outras despesas
            c1000[38]=fmt_dec(seg['vProd'])   # campo 39 - valor produtos
            c1000[40]=cod_situacao            # campo 41 - situação do documento
            c1000[53]=nota['chave']           # campo 54 - chave NF-e
            c1000[61]=info_cpl               # campo 62 - informações complementares (infCpl)
            # campo 90 - valor IPI (só preenche se houver IPI no segmento)
            if seg.get('vIPI',0) > 0:
                c1000[89]=fmt_dec(seg['vIPI'])  # campo 90 - valor IPI
            linhas.append('|'+'|'.join(c1000)+'|')

            if info_cpl: linhas.append(f"|1010|{n_seg}|{info_cpl}|")

            # 1020 ICMS e IPI
            seg['n_seg']=n_seg
            for l in gerar_1020_icms(seg,vCont_float,is_simpl,
                                     etiq_cfg.get('cred_icms',False),nota['emit_UF']):
                linhas.append(l)
            for l in gerar_1020_ipi(seg,vCont_float,is_simpl,
                                    etiq_cfg.get('cred_ipi',False)):
                linhas.append(l)

            # 1020 DIFAL — um registro por grupo de alíquota interestadual
            # CFOP 2407 fora do conjunto (retorno conserto, não gera DIFAL)
            if (cfop in CFOPS_DIFAL and etiq_cfg.get('deb_difal')):
                grupos_difal = calcular_difal(seg['itens'])
                for grupo in grupos_difal:
                    # campo 15 = aliq interestadual (3 pipes antes = campos 12,13,14 vazios)
                    linhas.append(
                        f"|1020|8|0,00|{fmt_dec(grupo['base_dup'])}|"
                        f"{fmt_dec(ALIQ_INTERNA_SP*100)}|{fmt_dec(grupo['difal'])}|"
                        f"0,00|0,00|0,00|0,00|{vCont}|||"
                        f"|{fmt_dec(grupo['aliq_inter'])}|||")

            # 1030
            for item in seg['itens']:
                uCom_norm = item['uCom'].upper().strip()
                chave_p=f"{r}|{item['cProd']}|{item['xProd']}|{uCom_norm}"
                cod_prod=catalogo[chave_p]
                # Aplica exceção de unidade se existir — usa a unidade cadastrada no Domínio
                uCom = excecoes_unidade.get(cod_prod, item['uCom'])
                cst=item['icms_csosn'] if is_simpl else item['icms_cst']
                vCont_item=round(
                    item['vProd']+item['vFrete']+item['vSeg']
                    +item['vOutro']-item['vDesc'],2)

                # Regra 1030 campo 22/13/15: espelha o 1020
                # Simples Nacional com crédito: BC=vProd, alíq=pCredSN, val=vCredSN
                # SN sem crédito ou não-SN sem ICMS: todos zerados
                tem_icms_1020 = etiq_cfg.get('cred_icms', False) and not is_simpl
                if is_simpl and etiq_cfg.get('cred_icms') and item['icms_vCredSN'] > 0:
                    # Simples com crédito e item elegível: base=vProd, alíq=pCredSN, val=vCredSN
                    vlr_icms_1030  = item['icms_vCredSN']
                    bc_icms_1030   = item['vProd']
                    aliq_icms_1030 = item['icms_pCredSN']
                elif is_simpl and etiq_cfg.get('cred_icms') and item['icms_vCredSN'] == 0:
                    # Simples com crédito mas item sem vCredSN (ex: retorno industrialização)
                    vlr_icms_1030  = 0.0
                    bc_icms_1030   = 0.0
                    aliq_icms_1030 = 0.0
                elif tem_icms_1020:
                    vlr_icms_1030  = item['icms_vICMS']
                    bc_icms_1030   = item['icms_vBC']
                    aliq_icms_1030 = item['icms_pICMS']
                else:
                    # 1020 sem ICMS → 1030 também zera
                    vlr_icms_1030  = 0.0
                    bc_icms_1030   = 0.0
                    aliq_icms_1030 = 0.0

                pc=calcular_piscof_item(item,etiq_cfg,CNPJ_EMPRESA,cfop)

                # Inicializa com 111 posições — tamanho fixo do leiaute Domínio 1030
                c1030=['']*111
                # Mapeamento conforme leiaute oficial Domínio 1030
                # campo N do leiaute = índice N-1 no array (base-zero)
                c1030[0]  = '1030'                          # 01 identificação
                c1030[1]  = cod_prod                         # 02 código produto
                c1030[2]  = fmt_qtd(item['qCom'])            # 03 quantidade
                c1030[3]  = fmt_dec(item['vProd'])           # 04 valor total (Base Cal.+IPI)
                c1030[4]  = fmt_dec(item.get('icms_vIPI',0)) # 05 valor IPI
                c1030[5]  = fmt_dec(bc_icms_1030)            # 06 base cálculo ICMS
                c1030[6]  = '1'                              # 07 tipo lançamento (1=produto vinc nota)
                c1030[7]  = dt_ent                           # 08 data
                c1030[8]  = ''                               # 09 número DI
                c1030[9]  = cst                              # 10 CST/CSOSN ICMS
                c1030[10] = fmt_dec(item['vProd'])           # 11 valor bruto produto
                c1030[11] = fmt_dec(item['vDesc'])           # 12 valor desconto
                c1030[12] = fmt_dec(bc_icms_1030)            # 13 base cálculo ICMS
                c1030[13] = fmt_dec(item['icms_vST'])        # 14 base cálculo ICMS ST
                c1030[14] = fmt_dec(aliq_icms_1030)          # 15 alíquota ICMS
                c1030[15] = 'N'                              # 16 produto incentivado (PE)
                c1030[16] = '0'                              # 17 código apuração (PE)
                c1030[17] = fmt_dec(item['vFrete'])          # 18 valor frete
                c1030[18] = fmt_dec(item['vSeg'])            # 19 valor seguro
                c1030[19] = fmt_dec(item['vOutro'])          # 20 valor despesas acessórias
                c1030[20] = '0,000'                          # 21 qtd gasolina
                c1030[21] = fmt_dec(vlr_icms_1030)           # 22 valor ICMS
                c1030[22] = fmt_dec(item['icms_vST'])        # 23 valor SUBTRI
                c1030[23] = '0,00'                           # 24 isentas IPI
                c1030[24] = '0,00'                           # 25 outras IPI
                c1030[25] = '0,00'                           # 26 ICMS NFP
                c1030[26] = fmt_dec(item['vUnCom'])          # 27 valor unitário
                c1030[27] = '0,00'                           # 28 alíquota ST
                # campo 29 — CST IPI: 00=entrada c/crédito, 49=outras entradas
                cst_ipi = '00' if (etiq_cfg.get('cred_ipi') and
                                   not is_simpl and
                                   item.get('icms_vIPI', 0) > 0) else '49'
                c1030[28] = cst_ipi                          # 29 CST IPI
                c1030[29] = fmt_dec(item.get('ipi_pIPI',0)) # 30 alíquota IPI
                c1030[30] = '0,00'                           # 31 base cálculo ISSQN
                c1030[31] = '0,00'                           # 32 alíquota ISSQN
                c1030[32] = '0,00'                           # 33 valor ISSQN
                c1030[33] = cfop                             # 34 CFOP
                c1030[34] = ''                               # 35 série ECF
                c1030[35] = pc['aliq_pis']                  # 36 alíquota PIS
                c1030[36] = pc['vlr_pis']                   # 37 valor PIS
                c1030[37] = pc['aliq_cofins']               # 38 alíquota COFINS
                c1030[38] = pc['vlr_cofins']                # 39 valor COFINS
                c1030[39] = fmt_dec(vCont_item)              # 40 custo total produto
                c1030[40] = pc['cst_pis']                   # 41 CST PIS
                c1030[41] = pc['bc_pis']                    # 42 base cálculo PIS
                c1030[42] = pc['cst_cofins']                # 43 CST COFINS
                c1030[43] = pc['bc_cofins']                 # 44 base cálculo COFINS
                # 45-54 vazios: chassi, tipo op.veículo, lote med., qtd lote,
                #               val. lote, fab. med., ref.BC, val.tab., série arma,
                #               série cano
                c1030[54] = '999'                            # 55 enquadramento IPI (fixo)
                c1030[55] = 'S'                              # 56 movimentação física
                c1030[56] = uCom                             # 57 unidade comercializada (com exceção aplicada)
                # 58 vazio: complemento CFOP CAT 17/99
                # 59 vazio: tanque combustível
                c1030[59] = fmt_dec(vCont_item)              # 60 valor contábil produto
                # 61-65 vazios: qtd/val PIS/COFINS por unidade
                c1030[66] = pc['base_credito']              # 67 base do crédito (01-18)
                # 68-73 vazios: nota devolvida (nº, desc, CST PIS/COF, vínculos)
                # 74-78 vazios: exclusão PIS/COF, ICMS Carga Média BC/aliq/val
                # 79-89 vazios: série ECF devol., %redução PIS/COF, cód. PIS/COF,
                #               cred. presumido, ICMS ST Antec. BC/aliq/val
                # 90 vazio: código recolhimento IPI (Caractere)
                # 91 vazio: código CEST
                # 92-94 vazios: ICMS ST Retido BC/val/tag XML
                # 95 vazio: identificador
                # 96 vazio: ICMS Próprio Substituto
                # 97 vazio: valor desonerado (backlog — mapeamento futuro)
                # 98-103 vazios: código, ICMS não creditado, ICMS Monofásico qtd/aliq/val/FCV
                # IBS — campos 104-107 (índices 103-106), leiaute oficial Domínio
                # Fonte: tag <IBSCBS> do XML por item
                c1030[103] = item.get('ibs_cclass', '')           # 104 IBS cClass Trib (Caractere)
                c1030[104] = fmt_dec(item.get('ibs_bc',   0.0))   # 105 IBS base de cálculo
                c1030[105] = fmt_dec(item.get('ibs_aliq', 0.0), 4)# 106 IBS alíquota (4 casas)
                c1030[106] = fmt_dec(item.get('ibs_val',  0.0))   # 107 IBS valor
                # CBS — campos 108-111 (índices 107-110), leiaute oficial Domínio
                c1030[107] = item.get('cbs_cclass', '')           # 108 CBS cClass Trib (Caractere)
                c1030[108] = fmt_dec(item.get('cbs_bc',   0.0))   # 109 CBS base de cálculo
                c1030[109] = fmt_dec(item.get('cbs_aliq', 0.0), 4)# 110 CBS alíquota (4 casas)
                c1030[110] = fmt_dec(item.get('cbs_val',  0.0))   # 111 CBS valor
                linhas.append('|'+'|'.join(c1030)+'|')

            # 1200 Simples Nacional
            # ATENÇÃO (backlog): o campo "ICMS SN" do 1030 aba Estoque não tem
            # campo disponível no leiaute Domínio. Solução temporária:
            #   - SN com crédito: crédito vai no 1020 (BC+aliq+val) — 1200 NÃO gerado
            #   - SN sem crédito: 1200 zerado gerado normalmente (apenas 1 vez)
            # Quando a Domínio liberar o campo no leiaute, retomar o 1200 com crédito.
            if is_simpl and not etiq_cfg.get('cred_icms') and n_seg == 1:
                # SN sem crédito: 1200 zerado no primeiro segmento
                linhas.append('|1200|0,00|0,00|0,00|')

        # 1500
        for dup in nota['parcelas']:
            dVenc=fmt_data(dup['dVenc'])
            vDup=dup['vDup'] if isinstance(dup['vDup'],str) else fmt_dec(dup['vDup'])
            if ',' not in str(vDup): vDup=fmt_dec(vDup)
            nDup=dup['nDup']
            linhas.append(
                f"|1500|{dVenc}|{vDup}|0,00|0,00|0,00|0,00|0,00|0,00"
                f"|0,00|0,00|0,00|0,00|{nDup}|")

    # Resumo dos avisos de BC com IPI incluído
    if avisos_ipi_bc:
        print(f'\n    {"─"*55}')
        print(f'    ⚠️  ATENÇÃO — {len(avisos_ipi_bc)} item(s) com IPI excluído da BC ICMS:')
        for a in avisos_ipi_bc:
            print(f'       • {a}')
        print(f'    Oriente o(s) cliente(s) a emitir carta de nao aproveitamento.')
        print(f'    {"─"*55}\n')

    return linhas, avisos_ipi_bc

# ─── Processamento do ZIP ───────────────────────────────────────────
def carregar_excecoes_unidade(zip_path):
    """
    Lê o arquivo unidade_excecoes.csv do mesmo diretório do ZIP (ou do script).
    Formato: codigo_produto;unidade_dominio
    Retorna dict: {'29367934_001': 'PC', ...}
    """
    excecoes = {}
    # Procura na mesma pasta do ZIP primeiro, depois na pasta do script
    candidatos = [
        os.path.join(os.path.dirname(os.path.abspath(zip_path)), 'unidade_excecoes.csv'),
        os.path.join(os.path.dirname(os.path.abspath(__file__)),  'unidade_excecoes.csv'),
    ]
    for caminho in candidatos:
        if os.path.exists(caminho):
            with open(caminho, encoding='utf-8') as f:
                for linha in f:
                    linha = linha.strip()
                    if not linha or linha.startswith('#'): continue
                    partes = linha.split(';')
                    if len(partes) >= 2:
                        cod  = partes[0].strip()
                        uCom = partes[1].strip().upper()
                        if cod and uCom:
                            excecoes[cod] = uCom
            if excecoes:
                print(f"  [exceções] {len(excecoes)} produto(s) com unidade forçada: {caminho}")
            break
    return excecoes

def processar_zip(zip_path, output_path):
    notas=[]

    # Lê CSV de rateio (se existir)
    rateio_csv = ler_csv_rateio(zip_path)

    # Lê arquivo de exceções de unidade (se existir)
    excecoes_unidade = carregar_excecoes_unidade(zip_path)

    with zipfile.ZipFile(zip_path,'r') as zf:
        pastas=set()
        for name in zf.namelist():
            parts=name.replace('\\','/').split('/')
            if len(parts)>=2 and parts[0]: pastas.add(parts[0])

        print(f"\n{'='*60}")
        print(f"Lote: {os.path.basename(zip_path)}")
        print(f"{'='*60}")

        for pasta in sorted(pastas):
            is_multi = pasta.strip().startswith('#')
            cod_etiq = extrair_cod_etiqueta(pasta) if not is_multi else None

            xmls=[n for n in zf.namelist()
                  if n.replace('\\','/').startswith(pasta+'/')
                  and n.lower().endswith('.xml')]

            if not xmls: continue

            if is_multi:
                # Pasta de múltiplas etiquetas
                print(f"\n  [#Multiplas Etiquetas] {len(xmls)} NF(s)")
                for xml_name in sorted(xmls):
                    xml_bytes=zf.read(xml_name)
                    try:
                        # Descobre a chave para buscar no CSV
                        root=ET.fromstring(xml_bytes)
                        infNFe=root.find(f'.//{{{NS}}}infNFe')
                        chave=infNFe.get('Id','').replace('NFe','')

                        if chave not in rateio_csv:
                            print(f"    AVISO: {chave[:20]}... sem rateio no CSV — ignorada")
                            continue

                        dados_csv = rateio_csv[chave]
                        etiquetas = dados_csv['etiquetas']
                        dt_emi    = dados_csv['dt_emissao']
                        dt_ent    = dados_csv['dt_entrada']

                        # Usa primeira etiqueta para ler a NF base
                        cod_base  = etiquetas[0]['cod']
                        nota_base = ler_nfe(xml_bytes, cod_base, dt_emi, dt_ent)

                        # Gera notas rateadas
                        notas_rat = ratear_nota_por_etiqueta(nota_base, etiquetas)
                        notas.extend(notas_rat)

                        cfops_unicos = set()
                        for _,n in notas_rat:
                            for s in segmentar_nf(n):
                                cfops_unicos.add(s['cfop'])

                        print(f"    OK  NF {nota_base['nNF']:>8} | "
                              f"{len(etiquetas)} etiquetas | "
                              f"{len(notas_rat)*len(cfops_unicos)} segs aprox | "
                              f"emi={dt_emi} ent={dt_ent}")
                    except Exception as e:
                        import traceback
                        print(f"    ERRO {os.path.basename(xml_name)}: {e}")
                        traceback.print_exc()
            else:
                # Pasta de etiqueta única
                if cod_etiq not in TABELA_ETIQUETAS:
                    print(f"  AVISO: etiqueta {cod_etiq} nao encontrada — ignorada")
                    continue
                etiq_desc=TABELA_ETIQUETAS[cod_etiq]['desc']
                print(f"\n  [{cod_etiq}] {etiq_desc} — {len(xmls)} NF(s)")
                for xml_name in sorted(xmls):
                    try:
                        xml_bytes = zf.read(xml_name)
                        # Busca datas no CSV pelo Id da NF (prioridade sobre XML)
                        root_tmp = ET.fromstring(xml_bytes)
                        infNFe_tmp = root_tmp.find(f'.//{{{NS}}}infNFe')
                        chave_tmp  = infNFe_tmp.get('Id','').replace('NFe','')
                        dados_csv_tmp = rateio_csv.get(chave_tmp, {})
                        dt_emi_csv = dados_csv_tmp.get('dt_emissao') or None
                        dt_ent_csv = dados_csv_tmp.get('dt_entrada') or None
                        nota=ler_nfe(xml_bytes, cod_etiq, dt_emi_csv, dt_ent_csv)
                        notas.append((cod_etiq, nota))
                        segs=segmentar_nf(nota)
                        print(f"    OK  NF {nota['nNF']:>8} | {nota['emit_UF']} | "
                              f"R${nota['vNF']:>10.2f} | "
                              f"CFOPs:{[s['cfop'] for s in segs]} | "
                              f"emi={nota['dt_emissao']} ent={nota['dt_entrada']}"
                              f"{'  [SN]' if nota['is_simples'] else ''}")
                    except Exception as e:
                        print(f"    ERRO {os.path.basename(xml_name)}: {e}")

    if not notas:
        print("Nenhuma nota processada."); return

    linhas, avisos_ipi_bc = gerar_linhas(notas, excecoes_unidade)
    with open(output_path,'w',encoding='latin-1',errors='replace') as f:
        f.write('\r\n'.join(linhas))

    print(f"\n{'='*60}")
    print(f"Arquivo : {output_path}")
    print(f"Linhas  : {len(linhas)}")
    print(f"NFs     : {len(notas)}")
    if avisos_ipi_bc:
        print(f"⚠️  IPI excluído da BC: {len(avisos_ipi_bc)} item(s) — veja log acima")
    print(f"{'='*60}\n")
    return linhas, avisos_ipi_bc

if __name__=='__main__':
    import sys
    if len(sys.argv) == 3:
        processar_zip(sys.argv[1], sys.argv[2])
    elif len(sys.argv) == 2:
        # só o ZIP — gera TXT na mesma pasta com nome automático
        import os, datetime
        zip_path = sys.argv[1]
        nome_base = os.path.splitext(os.path.basename(zip_path))[0]
        data_hoje = datetime.date.today().strftime('%Y%m%d')
        out_path  = os.path.join(os.path.dirname(zip_path) or '.', f'dominio_{nome_base}_{data_hoje}.txt')
        processar_zip(zip_path, out_path)
    else:
        print('Uso: python gerar_dominio_v22g.py <arquivo_lote.zip> [saida.txt]')
        print('  Exemplo: python gerar_dominio_v22g.py abril_2026.zip')
        print('  Exemplo: python gerar_dominio_v22g.py abril_2026.zip C:/Dominio/abril_2026.txt')
        sys.exit(1)