# -*- coding: utf-8 -*-
"""
Gerador Dominio Sistemas — NF Própria (emissão Kopron)
Tipos suportados: Importação (3101), Devolução (1201/2201), CIAP (1604)

Diferenças do gerador de NF de terceiros:
  - Não usa etiquetas nem CFOP_DIRETO: CFOP do XML já É o de entrada
  - Acumulador = CFOP de entrada (ex: 3101→3101)
  - 0020 usa dados do DESTINATÁRIO (não do emitente)
  - 1000 campo 17 = 'P' (Próprio)
  - 1000 campo 13 (vContabil) = vNF direto do XML
  - Importação: 0020 tipo 'O', inscrição=nDI, país de enderDest
  - CIAP: vContabil=0,00; crédito ICMS vai direto no 1020
"""

import os, sys, zipfile, re
import xml.etree.ElementTree as ET

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

# Mapeamento cPais (Banco Central) → código interno Domínio
# Fonte: tabela cadastrais_Países.xls fornecida pelo Grupo Speed
PAISES_BC_TO_DOMINIO = {
    '132': '1',  # AFEGANISTAO
    '7560': '2',  # AFRICA DO SUL
    '175': '3',  # ALBANIA,  REPUBLICA DA
    '230': '4',  # ALEMANHA
    '370': '5',  # ANDORRA
    '400': '6',  # ANGOLA
    '418': '7',  # ANGUILLA
    '434': '8',  # ANTIGUA E BARBUDA
    '477': '9',  # ANTILHAS HOLANDESAS
    '531': '10',  # ARABIA SAUDITA
    '590': '11',  # ARGELIA
    '639': '12',  # ARGENTINA
    '647': '13',  # ARMENIA,  REPUBLICA DA
    '655': '14',  # ARUBA
    '698': '15',  # AUSTRALIA
    '728': '16',  # AUSTRIA
    '736': '17',  # AZERBAIJAO,  REPUBLICA DO
    '779': '18',  # BAHAMAS,  ILHAS
    '809': '19',  # BAHREIN,  ILHAS
    '817': '20',  # BANGLADESH
    '833': '21',  # BARBADOS
    '850': '22',  # Belarus, República da
    '876': '23',  # BELGICA
    '884': '24',  # BELIZE
    '2291': '25',  # BENIN
    '906': '26',  # BERMUDAS
    '973': '27',  # Bolívia, Estado Plurinacional da
    '981': '28',  # Bósnia-Herzegovina, República da
    '1015': '29',  # BOTSUANA
    '1058': '30',  # BRASIL
    '1082': '31',  # BRUNEI
    '1112': '32',  # BULGARIA,  REPUBLICA DA
    '310': '33',  # BURKINA FASO
    '1155': '34',  # BURUNDI
    '1198': '35',  # BUTAO
    '1279': '36',  # CABO VERDE,  REPUBLICA DE
    '1457': '37',  # CAMAROES
    '1414': '38',  # CAMBOJA
    '1490': '39',  # CANADA
    '3212': '40',  # GUERNSEY, ILHA DO CANAL (INCLUI ALDERNEY E SARK)
    '1511': '41',  # CANARIAS,  ILHAS
    '1546': '42',  # CATAR
    '1376': '43',  # CAYMAN,  ILHA
    '1538': '44',  # CAZAQUISTAO,  REPUBLICA DO
    '7889': '45',  # CHADE
    '1589': '46',  # CHILE
    '1600': '47',  # CHINA,  REPUBLICA POPULAR DA
    '1635': '48',  # CHIPRE
    '5118': '49',  # CHRISTMAS,  ILHA (NAVIDAD)
    '7412': '50',  # Singapura
    '1651': '51',  # COCOS (KEELING),  ILHAS
    '1694': '52',  # COLOMBIA
    '1732': '53',  # COMORES,  ILHAS
    '8885': '54',  # CONGO,  REPUBLICA DEMOCRATICA DO
    '1775': '55',  # CONGO,  REPUBLICA DO
    '1830': '56',  # COOK,  ILHA
    '1872': '57',  # Coreia (do Norte), Rep. Pop. Democrática da
    '1902': '58',  # Coreia (do Sul), República da
    '1937': '59',  # COSTA DO MARFIM
    '1961': '60',  # COSTA RICA
    '1988': '61',  # KUWAIT
    '1953': '62',  # CROACIA,  REPUBLICA DA
    '1996': '63',  # CUBA
    '2321': '64',  # DINAMARCA
    '7838': '65',  # DJIBUTI
    '2356': '66',  # DOMINICA,  ILHA
    '2402': '67',  # EGITO
    '6874': '68',  # EL SALVADOR
    '2445': '69',  # EMIRADOS ARABES UNIDOS
    '2399': '70',  # EQUADOR
    '2437': '71',  # ERITREIA
    '6289': '72',  # ESCOCIA
    '2470': '73',  # ESLOVACA,  REPUBLICA
    '2461': '74',  # ESLOVENIA,  REPUBLICA DA
    '2453': '75',  # ESPANHA
    '2496': '76',  # ESTADOS UNIDOS
    '2518': '77',  # ESTONIA,  REPUBLICA DA
    '2534': '78',  # ETIOPIA
    '2550': '79',  # FALKLAND (ILHAS MALVINAS)
    '2593': '80',  # FEROE,  ILHAS
    '8702': '81',  # FIJI
    '2674': '82',  # FILIPINAS
    '2712': '83',  # FINLANDIA
    '1619': '84',  # FORMOSA (TAIWAN)
    '2755': '85',  # FRANCA
    '2810': '86',  # GABAO
    '6289': '87',  # GALES,  PAIS DE
    '2852': '88',  # GAMBIA
    '2895': '89',  # GANA
    '2917': '90',  # GEORGIA,  REPUBLICA DA
    '2933': '91',  # GIBRALTAR
    '6289': '92',  # GRA-BRETANHA
    '2976': '93',  # GRANADA
    '3018': '94',  # GRECIA
    '3050': '95',  # GROENLANDIA
    '3093': '96',  # GUADALUPE
    '3131': '97',  # GUAM
    '3174': '98',  # GUATEMALA
    '3379': '99',  # GUIANA
    '3255': '100',  # GUIANA FRANCESA
    '3298': '101',  # GUINE
    '3344': '102',  # GUINE-BISSAU
    '3310': '103',  # GUINE-EQUATORIAL
    '3417': '104',  # HAITI
    '5738': '105',  # Países Baixos (Holanda)
    '3450': '106',  # HONDURAS
    '3514': '107',  # HONG KONG,  REGIAO ADM. ESPECIAL
    '3557': '108',  # HUNGRIA,  REPUBLICA DA
    '3573': '109',  # IEMEN
    '3611': '110',  # INDIA
    '3654': '111',  # INDONESIA
    '6289': '112',  # INGLATERRA
    '3727': '113',  # IRA,  REPUBLICA ISLAMICA DO
    '3697': '114',  # IRAQUE
    '3751': '115',  # IRLANDA
    '6289': '116',  # IRLANDA DO NORTE
    '3794': '117',  # ISLANDIA
    '3832': '118',  # ISRAEL
    '3867': '119',  # ITALIA
    '3883': '120',  # SERVIA
    '3913': '121',  # JAMAICA
    '3999': '122',  # JAPAO
    '3964': '123',  # JOHNSTON,  ILHAS
    '4030': '124',  # JORDANIA
    '4111': '125',  # KIRIBATI
    '4200': '126',  # LAOS,  REP. POP. DEMOCRATICA DO
    '4235': '127',  # LEBUAN
    '4260': '128',  # LESOTO
    '4278': '129',  # LETONIA,  REPUBLICA DA
    '4316': '130',  # LIBANO
    '4340': '131',  # LIBERIA
    '4383': '132',  # LIBIA
    '4405': '133',  # LIECHTENSTEIN
    '4421': '134',  # LITUANIA,  REPUBLICA DA
    '4456': '135',  # LUXEMBURGO
    '4472': '136',  # MACAU
    '4499': '137',  # MACEDONIA DO NORTE
    '4502': '138',  # MADAGASCAR
    '4525': '139',  # MADEIRA,  ILHA DA
    '4553': '140',  # MALASIA
    '4588': '141',  # MALAVI
    '4618': '142',  # MALDIVAS
    '4642': '143',  # MALI
    '4677': '144',  # MALTA
    '3595': '145',  # MAN,  ILHAS
    '4723': '146',  # MARIANAS DO NORTE
    '4740': '147',  # MARROCOS
    '4766': '148',  # MARSHALL,  ILHAS
    '4774': '149',  # MARTINICA
    '4855': '150',  # MAURICIO
    '4880': '151',  # MAURITANIA
    '4936': '152',  # MEXICO
    '930': '153',  # MIANMAR (BIRMANIA)
    '4995': '154',  # MICRONESIA
    '4901': '155',  # MIDWAY,  ILHAS
    '5053': '156',  # MOCAMBIQUE
    '4944': '157',  # MOLDAVIA,  REPUBLICA DA
    '4952': '158',  # MONACO
    '4979': '159',  # MONGOLIA
    '5010': '160',  # MONTSERRAT,  ILHA
    '5070': '161',  # NAMIBIA
    '5088': '162',  # NAURU
    '5177': '163',  # NEPAL
    '5215': '164',  # NICARAGUA
    '5258': '165',  # NIGER
    '5282': '166',  # NIGERIA
    '5312': '167',  # NIUE,  ILHA
    '5355': '168',  # NORFOLK,  ILHA
    '5380': '169',  # NORUEGA
    '5428': '170',  # NOVA CALEDONIA
    '5487': '171',  # NOVA ZELANDIA
    '5568': '172',  # OMA
    '5754': '173',  # PALAU
    '5800': '174',  # PANAMA
    '5452': '175',  # PAPUA NOVA GUINE
    '5762': '176',  # PAQUISTAO
    '5860': '177',  # PARAGUAI
    '5894': '178',  # PERU
    '5932': '179',  # PITCAIRN,  ILHA
    '5991': '180',  # POLINESIA FRANCESA
    '6033': '181',  # POLONIA,  REPUBLICA DA
    '6114': '182',  # PORTO RICO
    '6076': '183',  # PORTUGAL
    '6238': '184',  # QUENIA
    '6254': '185',  # QUIRGUIZ,  REPUBLICA
    '6289': '186',  # REINO UNIDO
    '6408': '187',  # REPUBLICA CENTRO-AFRICANA
    '6475': '188',  # REPUBLICA DOMINICANA
    '6602': '189',  # REUNIAO,  ILHA
    '6700': '190',  # ROMENIA
    '6750': '191',  # RUANDA
    '6769': '192',  # Rússia, Federação da
    '6858': '193',  # SAARA OCIDENTAL
    '6777': '194',  # SALOMAO,  ILHAS
    '6904': '195',  # SAMOA
    '6912': '196',  # SAMOA AMERICANA
    '6971': '197',  # San Marino
    '7102': '198',  # SANTA HELENA
    '7153': '199',  # SANTA LUCIA
    '6955': '200',  # SAO CRISTOVAO E NEVES
    '7005': '201',  # SAO PEDRO E MIQUELON
    '7200': '202',  # SAO TOME E PRINCIPE,  ILHAS
    '7056': '203',  # SAO VICENTE E GRANADINA
    '7285': '204',  # SENEGAL
    '7358': '205',  # SERRA LEOA
    '7315': '206',  # SEYCHELLE
    '7447': '207',  # SIRIA,  REPUBLICA ARABE DA
    '7480': '208',  # SOMALIA
    '7501': '209',  # SRI LANKA
    '7544': '210',  # eSwatini (Essuatíni, Suazilândia)
    '7595': '211',  # SUDAO
    '7641': '212',  # SUECIA
    '7676': '213',  # SUICA
    '7706': '214',  # SURINAME
    '7722': '215',  # TADJIQUISTAO
    '7765': '216',  # TAILANDIA
    '7803': '217',  # TANZANIA,  REPUBLICA UNIDA DA
    '7919': '218',  # TCHECA,  REPUBLICA
    '7820': '219',  # TERRITORIO BRITANICO OC. INDICO
    '7951': '220',  # TIMOR LESTE
    '8001': '221',  # TOGO
    '8109': '222',  # TONGA
    '8052': '223',  # TOQUELAU,  ILHAS
    '8150': '224',  # TRINIDAD E TOBAGO
    '8206': '225',  # TUNISIA
    '8230': '226',  # TURCAS E CAICOS,  ILHAS
    '8249': '227',  # TURCOMENISTAO,  REPUBLICA DO
    '8273': '228',  # TURQUIA
    '8281': '229',  # TUVALU
    '8311': '230',  # UCRANIA
    '8338': '231',  # UGANDA
    '8451': '232',  # URUGUAI
    '8478': '233',  # UZBEQUISTAO,  REPUBLICA DO
    '5517': '234',  # VANUATU
    '8486': '235',  # VATICANO,  ESTADO DA CIDADE DO
    '8508': '236',  # VENEZUELA
    '8583': '237',  # VIETNA
    '8630': '238',  # VIRGENS,  ILHAS (BRITANICAS)
    '8664': '239',  # VIRGENS,  ILHAS (E.U.A.)
    '8737': '240',  # WAKE,  ILHA
    '8753': '241',  # WALLIS E FUTUNA,  ILHAS
    '8907': '242',  # ZAMBIA
    '6653': '243',  # ZIMBABUE
    '8958': '244',  # ZONA DO CANAL DO PANAMA
    '3883': '245',  # MONTENEGRO
    '0': '246',  # EXTERIOR
    '5665': '248',  # Pacífico, Ilhas do (Possessão dos EUA)
    '153': '252',  # ALAND, ILHAS
    '420': '253',  # ANTARTICA
    '990': '254',  # Bonaire, Saint Eustatius e Saba
    '1023': '255',  # BOUVET, ILHA
    '2003': '256',  # CURACAO
    '3433': '257',  # Heard e Ilhas McDonald, Ilha
    '6980': '258',  # São Martinho, Ilha de (Parte Francesa)
    '2925': '259',  # Geórgia do Sul e Sandwich do Sul, Ilhas
    '3930': '260',  # Jersey. Ilha do Canal
    '4898': '261',  # Mayotte
    '6939': '262',  # São Bartolomeu
    '7552': '263',  # Svalbard e Jan Mayen
    '7811': '264',  # Terras Austrais Francesas
    '6998': '265',  # SAO MARTINHO, ILHA DE (PARTE HOLANDESA)
    '6998': '266',  # SAO MARTINHO, ILHA DE (PARTE HOLANDESA)
    '5780': '267',  # Palestina
    '7600': '268',  # Sudão do Sul
    '1504': '269',  # Guernsey, Ilha do Canal
}

from gerar_dominio_v21  import (fmt_dec, fmt_data, fmt_qtd, limpar, raiz, tag as _tag,
                                 CNPJ_EMPRESA, NS)
from gerar_1020          import gerar_1020_icms, gerar_1020_ipi

MOD_FRETE_MAP = {'0':'C','1':'F','2':'T','3':'R','4':'D','9':'S'}


# ──────────────────────────────────────────────────────────────────
def _txt(el, field):
    """Lê tag direta de um elemento."""
    if el is None: return ''
    x = el.find(f'{{{NS}}}{field}')
    return x.text.strip() if x is not None and x.text else ''


def _ender_txt(el, field):
    """Lê tag de endereco (enderEmit/enderDest) diretamente."""
    return _txt(el, field)


# ──────────────────────────────────────────────────────────────────
def ler_nfe_proprio(xml_bytes):
    """
    Lê XML de NF própria e retorna dict com todos os dados necessários.
    """
    root    = ET.fromstring(xml_bytes)
    infNFe  = root.find(f'.//{{{NS}}}infNFe')
    chave   = infNFe.get('Id','').replace('NFe','')

    ide     = infNFe.find(f'{{{NS}}}ide')
    emit    = infNFe.find(f'{{{NS}}}emit')
    dest    = infNFe.find(f'{{{NS}}}dest')
    ender_d = dest.find(f'{{{NS}}}enderDest') if dest else None
    transp  = infNFe.find(f'{{{NS}}}transp')
    tot     = infNFe.find(f'.//{{{NS}}}ICMSTot')
    infAd   = infNFe.find(f'{{{NS}}}infAdic')

    nNF     = _txt(ide,'nNF')
    serie   = _txt(ide,'serie')
    dhEmi   = (_txt(ide,'dhEmi') or _txt(ide,'dEmi'))[:10]
    natOp   = _txt(ide,'natOp')
    modFrete_raw = _txt(transp,'modFrete') if transp else '9'
    modFrete = MOD_FRETE_MAP.get(modFrete_raw,'S')

    # Emitente (Kopron)
    emit_cnpj = _txt(emit,'CNPJ')
    emit_uf   = _txt(emit.find(f'{{{NS}}}enderEmit') if emit else None,'UF')

    # Destinatário — será o "fornecedor" no Domínio
    dest_cnpj  = _txt(dest,'CNPJ')
    dest_cpf   = _txt(dest,'CPF')
    dest_nome  = _txt(dest,'xNome')
    dest_uf    = _txt(ender_d,'UF')
    dest_bairro= _txt(ender_d,'xBairro') or ''
    dest_lgr   = _txt(ender_d,'xLgr')
    dest_nro   = _txt(ender_d,'nro')
    dest_cep   = _txt(ender_d,'CEP')
    dest_fone  = _txt(ender_d,'fone') or ''
    dest_pais_cod  = _txt(ender_d,'cPais')
    dest_pais_nome = _txt(ender_d,'xPais')
    dest_cMun  = _txt(ender_d,'cMun')

    # Totais
    vNF    = float(_txt(tot,'vNF')    or 0)
    vProd  = float(_txt(tot,'vProd')  or 0)
    vIPI   = float(_txt(tot,'vIPI')   or 0)
    vICMS  = float(_txt(tot,'vICMS')  or 0)
    vFrete = float(_txt(tot,'vFrete') or 0)
    vSeg   = float(_txt(tot,'vSeg')   or 0)
    vDesc  = float(_txt(tot,'vDesc')  or 0)
    vOutro = float(_txt(tot,'vOutro') or 0)
    vII    = float(_txt(tot,'vII')    or 0)

    infCpl = _txt(infAd,'infCpl') if infAd else ''

    # DI — para importação: primeiro nDI encontrado
    di_num = ''
    for di in infNFe.findall(f'.//{{{NS}}}DI'):
        n = _txt(di,'nDI')
        if n: di_num = n; break

    # Itens
    itens = []
    for det in infNFe.findall(f'.//{{{NS}}}det'):
        prod = det.find(f'{{{NS}}}prod')
        imp  = det.find(f'{{{NS}}}imposto')

        cfop    = _txt(prod,'CFOP')
        cProd   = _txt(prod,'cProd')
        xProd   = _txt(prod,'xProd')
        NCM     = _txt(prod,'NCM')
        uCom    = _txt(prod,'uCom')
        qCom    = _txt(prod,'qCom')
        vUnCom  = _txt(prod,'vUnCom')
        vProd_i = float(_txt(prod,'vProd')  or 0)
        vFrete_i= float(_txt(prod,'vFrete') or 0)
        vSeg_i  = float(_txt(prod,'vSeg')   or 0)
        vDesc_i = float(_txt(prod,'vDesc')  or 0)
        vOutro_i= float(_txt(prod,'vOutro') or 0)
        ean     = _txt(prod,'cEAN')
        if ean in ('','SEM GTIN'): ean = ''

        # ICMS
        icms_cst=icms_csosn=''; icms_vBC=icms_pICMS=icms_vICMS=0.0
        icms_vST=icms_pRedBC=0.0
        for tn in ['ICMS00','ICMS10','ICMS20','ICMS30','ICMS40',
                   'ICMS51','ICMS60','ICMS70','ICMS90']:
            nd = imp.find(f'.//{{{NS}}}{tn}') if imp else None
            if nd is not None:
                icms_cst   = _txt(nd,'CST')
                icms_vBC   = float(_txt(nd,'vBC')   or 0)
                icms_pICMS = float(_txt(nd,'pICMS') or 0)
                icms_vICMS = float(_txt(nd,'vICMS') or 0)
                icms_vST   = float((_txt(nd,'vICMSST') or _txt(nd,'vST') or '0'))
                icms_pRedBC= float(_txt(nd,'pRedBC') or 0)
                break

        # IPI
        ipi_vIPI=ipi_pIPI=ipi_vBC=0.0
        for tn in ['IPITrib','IPINT']:
            nd = imp.find(f'.//{{{NS}}}{tn}') if imp else None
            if nd is not None:
                ipi_vIPI = float(_txt(nd,'vIPI') or 0)
                ipi_pIPI = float(_txt(nd,'pIPI') or 0)
                ipi_vBC  = float(_txt(nd,'vBC')  or 0)
                break

        itens.append({
            'nItem':det.get('nItem'), 'cProd':cProd, 'ean':ean,
            'xProd':xProd, 'NCM':NCM, 'cfop':cfop,
            'uCom':uCom, 'qCom':qCom, 'vUnCom':vUnCom,
            'vProd':vProd_i, 'vFrete':vFrete_i, 'vSeg':vSeg_i,
            'vDesc':vDesc_i, 'vOutro':vOutro_i,
            'icms_cst':icms_cst, 'icms_csosn':icms_csosn,
            'icms_vBC':icms_vBC, 'icms_pICMS':icms_pICMS,
            'icms_vICMS':icms_vICMS, 'icms_vST':icms_vST,
            'icms_pRedBC':icms_pRedBC, 'icms_vCredSN':0.0, 'icms_pCredSN':0.0,
            'icms_vIPI':ipi_vIPI, 'ipi_pIPI':ipi_pIPI, 'ipi_vBC':ipi_vBC,
            'pis_pPIS':0.0, 'pis_vPIS':0.0,
            'cof_pCOFINS':0.0, 'cof_vCOFINS':0.0,
        })

    # Detectar tipo de NF
    cfops = set(i['cfop'] for i in itens)
    if cfops <= {'3101','3102'}:
        tipo = 'importacao'
    elif any(c in cfops for c in {'1604','2604'}):
        tipo = 'ciap'
    elif any(c.startswith(('1201','1202','2201','2202','1410','2410')) for c in cfops):
        tipo = 'devolucao'
    else:
        tipo = 'proprio'

    return {
        'chave': chave, 'nNF': nNF, 'serie': serie,
        'dhEmi': fmt_data(dhEmi), 'natOp': natOp,
        'modFrete': modFrete,
        'emit_cnpj': emit_cnpj, 'emit_uf': emit_uf,
        'dest_cnpj': dest_cnpj, 'dest_cpf': dest_cpf,
        'dest_nome': dest_nome, 'dest_uf': dest_uf,
        'dest_lgr': dest_lgr, 'dest_nro': dest_nro,
        'dest_bairro': dest_bairro, 'dest_cep': dest_cep,
        'dest_fone': dest_fone, 'dest_cMun': dest_cMun,
        'dest_pais_cod': dest_pais_cod,
        'dest_pais_nome': dest_pais_nome,
        'di_num': di_num,
        'vNF': vNF, 'vProd': vProd, 'vIPI': vIPI,
        'vICMS': vICMS, 'vFrete': vFrete, 'vSeg': vSeg,
        'vDesc': vDesc, 'vOutro': vOutro, 'vII': vII,
        'infCpl': infCpl,
        'itens': itens, 'cfops': sorted(cfops),
        'tipo': tipo,
    }


# ──────────────────────────────────────────────────────────────────
def gerar_txt_proprio(notas, output_path):
    """
    Recebe lista de dicts (saída de ler_nfe_proprio) e gera TXT Domínio.
    """
    linhas = []

    # ── 0000 ──────────────────────────────────────────────────────
    linhas.append(f'|0000|{CNPJ_EMPRESA}|')

    # ── 0020 fornecedores (baseado no DEST) ───────────────────────
    fornec_vistos = set()

    def _add_fornecedor(nota):
        """Gera linha 0020 para o destinatário da NF própria.
        Formato idêntico ao gerar_dominio_v21 (26 campos)."""

        if nota['tipo'] == 'importacao':
            # Fornecedor estrangeiro: tipo inscrição Outros, inscrição = nDI
            cod_forn = nota['di_num'] or nota['dest_nome'][:14]
            if cod_forn in fornec_vistos: return
            fornec_vistos.add(cod_forn)
            nome    = limpar(nota['dest_nome'])[:150]
            apelido = limpar(nota['dest_nome'])[:40]
            end     = limpar(nota['dest_lgr'])
            nro     = nota['dest_nro']
            bairro  = 'EXTERIOR'
            cMun    = ''   # campo 9 vazio para exterior
            uf      = 'EX'
            cep     = ''
            ie      = ''
            ddd     = ''
            tel     = ''
            regime  = 'O'   # Outros
            # Traduzir cPais (Banco Central) → código interno Domínio
            pais_cod_dominio = PAISES_BC_TO_DOMINIO.get(
                nota['dest_pais_cod'], nota['dest_pais_cod'])
            linhas.append(
                f"|0020|{cod_forn}|{nome}|{apelido}|{end}|{nro}||{bairro}"
                f"|{cMun}|{uf}|{pais_cod_dominio}|{cep}"
                f"|{ie}|||{ddd}|{tel}"
                f"|||||||{regime}|N||"
            )
        else:
            # CNPJ/CPF nacional — mesmo formato do gerar_dominio
            cnpj = nota['dest_cnpj'] or nota['dest_cpf']
            if not cnpj or cnpj in fornec_vistos: return
            fornec_vistos.add(cnpj)
            nome    = limpar(nota['dest_nome'])[:150]
            apelido = limpar(nota['dest_nome'])[:40]
            end     = limpar(nota['dest_lgr'])
            nro     = nota['dest_nro']
            bairro  = limpar(nota['dest_bairro'])
            cMun    = nota['dest_cMun']
            uf      = nota['dest_uf']
            cep     = nota['dest_cep']
            ie      = ''
            fone    = nota['dest_fone']
            ddd     = fone[:2] if len(fone) >= 2 else ''
            tel     = fone[2:] if len(fone) > 2 else ''
            regime  = 'N'   # Normal (devoluções são empresas regime normal)
            linhas.append(
                f"|0020|{cnpj}|{nome}|{apelido}|{end}|{nro}||{bairro}"
                f"|{cMun}|{uf}||{cep}"
                f"|{ie}|||{ddd}|{tel}"
                f"|||||||{regime}|N||"
            )

    for nota in notas:
        _add_fornecedor(nota)

    # ── 0100 produtos + 0150 unidades ─────────────────────────────
    catalogo = {}; seq_r = {}; prods_g = set(); unids_g = set()

    for nota in notas:
        # código do "fornecedor" do produto = raiz do emit (Kopron)
        r = raiz(nota['emit_cnpj'])
        for item in nota['itens']:
            uCom_norm = item['uCom'].upper().strip()
            chave_p = f"{r}|{item['cProd']}|{item['xProd']}|{uCom_norm}"
            if chave_p not in catalogo:
                seq_r[r] = seq_r.get(r, 0) + 1
                catalogo[chave_p] = f"{r}_{seq_r[r]:03d}"

    for nota in notas:
        r = raiz(nota['emit_cnpj'])
        for item in nota['itens']:
            uCom_norm = item['uCom'].upper().strip()
            chave_p = f"{r}|{item['cProd']}|{item['xProd']}|{uCom_norm}"
            if chave_p in prods_g: continue
            prods_g.add(chave_p)
            cod  = catalogo[chave_p]
            desc = limpar(item['xProd'])[:60]
            uCom = item['uCom']
            vUn  = fmt_dec(item['vUnCom'], 3)
            c0100 = ['0100',cod,desc,'',item['NCM'],'','','',
                     '1',uCom,'S','O','','','','N','',vUn,'','','','','','M']
            while len(c0100) < 90: c0100.append('')
            c0100.append(cod)  # campo 91 = código interno
            linhas.append('|'+'|'.join(c0100)+'|')
            if uCom_norm not in unids_g:
                unids_g.add(uCom_norm)
                du = {'UN':'UNIDADE','PC':'PECA','KG':'QUILOGRAMA','CX':'CAIXA',
                      'LT':'LITRO','MT':'METRO','M2':'METRO QUADRADO',
                      'GL':'GALAO','SC':'SACO','PR':'PAR'}.get(uCom_norm, uCom_norm)
                linhas.append(f'|0150|{uCom}|{du}|')

    # ── Notas: 1000/1010/1020/1030/1500 ──────────────────────────
    for n_seq, nota in enumerate(notas, 1):
        r = raiz(nota['emit_cnpj'])

        # Segmentar por CFOP (mesmo que seja 1 único)
        segs = {}
        for item in nota['itens']:
            cfop = item['cfop']
            if cfop not in segs: segs[cfop] = []
            segs[cfop].append(item)

        # Fornecedor = dest
        forn_cnpj = nota['dest_cnpj'] or nota['dest_cpf']
        if nota['tipo'] == 'importacao':
            forn_cnpj = nota['di_num'] or nota['dest_nome'][:14]

        n_segs_total = len(segs)
        vNF = nota['vNF']

        for n_seg, (cfop, itens_seg) in enumerate(sorted(segs.items()), 1):
            acum = cfop  # acumulador = CFOP
            campo7 = str(n_seg) if n_segs_total > 1 else '0'
            vCont = fmt_dec(vNF)  # vContabil = vNF para NF própria

            # ── 1000 ──────────────────────────────────────────────
            c1000 = ['']*90
            c1000[0]  = '1000'; c1000[1] = '36'
            c1000[2]  = forn_cnpj
            c1000[4]  = acum; c1000[5] = cfop
            c1000[6]  = campo7; c1000[7] = nota['nNF']
            c1000[8]  = nota['serie']
            c1000[10] = nota['dhEmi']   # campo 11 data entrada = data emissão
            c1000[11] = nota['dhEmi']   # campo 12 data emissão
            c1000[12] = vCont
            c1000[14] = limpar(nota['infCpl'])[:250]
            c1000[15] = nota['modFrete']
            c1000[16] = 'P'             # campo 17 = Próprio
            c1000[25] = fmt_dec(nota['vFrete'])
            c1000[26] = fmt_dec(nota['vSeg'])
            c1000[27] = fmt_dec(nota['vDesc'])
            c1000[28] = fmt_dec(nota['vOutro'])
            c1000[38] = fmt_dec(sum(i['vProd'] for i in itens_seg))
            c1000[53] = nota['chave']
            if nota['vIPI'] > 0:
                c1000[89] = fmt_dec(nota['vIPI'])
            linhas.append('|'+'|'.join(c1000)+'|')

            # ── 1010 ──────────────────────────────────────────────
            linhas.append(f'|1010|{nota["natOp"][:60]}|')

            # ── 1020 ICMS ─────────────────────────────────────────
            seg_dict = {'itens': itens_seg, 'vContabil': vNF,
                        'vICMS': sum(i['icms_vICMS'] for i in itens_seg)}
            cred_icms = nota['tipo'] not in ('ciap',)  # CIAP: crédito direto no valor

            # CIAP: valor do crédito está no vICMS do item, não na BC normal
            if nota['tipo'] == 'ciap':
                vICMS_ciap = sum(i['icms_vICMS'] for i in itens_seg)
                linhas.append(
                    f"|1020|1|0,00|0,00|0,00|{fmt_dec(vICMS_ciap)}"
                    f"|0,00|0,00|0,00|0,00|{vCont}||||"
                )
            else:
                for l in gerar_1020_icms(seg_dict, vNF, False, cred_icms,
                                          nota['emit_uf']):
                    linhas.append(l)

            # ── 1020 IPI ──────────────────────────────────────────
            cred_ipi = nota['tipo'] == 'importacao'  # importação tem crédito IPI
            for l in gerar_1020_ipi(seg_dict, vNF, False, cred_ipi):
                linhas.append(l)

            # ── 1030 itens ────────────────────────────────────────
            for item in itens_seg:
                uCom_norm = item['uCom'].upper().strip()
                chave_p   = f"{r}|{item['cProd']}|{item['xProd']}|{uCom_norm}"
                cod_prod  = catalogo[chave_p]
                uCom      = item['uCom']
                cst       = item['icms_cst'] or item['icms_csosn']

                vCont_item = round(item['vProd']+item['vFrete']+item['vSeg']
                                   +item['vOutro']-item['vDesc'], 2)

                # Para importação: BC/aliq/val ICMS (cred_icms=True)
                if nota['tipo'] == 'importacao':
                    bc_1030   = item['icms_vBC']
                    aliq_1030 = item['icms_pICMS']
                    val_1030  = item['icms_vICMS']
                elif nota['tipo'] == 'ciap':
                    bc_1030   = item['icms_vBC']
                    aliq_1030 = item['icms_pICMS']
                    val_1030  = item['icms_vICMS']
                else:
                    bc_1030   = item['icms_vBC']
                    aliq_1030 = item['icms_pICMS']
                    val_1030  = item['icms_vICMS']

                # CST IPI
                cst_ipi = '00' if (cred_ipi and item['icms_vIPI'] > 0) else '49'

                c1030 = ['']*111
                c1030[0]  = '1030'
                c1030[1]  = cod_prod
                c1030[2]  = fmt_qtd(item['qCom'])
                c1030[3]  = fmt_dec(item['vProd'])
                c1030[4]  = fmt_dec(item['icms_vIPI'])
                c1030[5]  = fmt_dec(bc_1030)
                c1030[6]  = '1'
                c1030[7]  = nota['dhEmi']
                c1030[9]  = cst
                c1030[10] = fmt_dec(item['vProd'])
                c1030[11] = fmt_dec(item['vDesc'])
                c1030[12] = fmt_dec(bc_1030)
                c1030[13] = fmt_dec(item['icms_vST'])
                c1030[14] = fmt_dec(aliq_1030)
                c1030[15] = 'N'
                c1030[16] = '0'
                c1030[17] = fmt_dec(item['vFrete'])
                c1030[18] = fmt_dec(item['vSeg'])
                c1030[19] = fmt_dec(item['vOutro'])
                c1030[20] = '0,000'
                c1030[21] = fmt_dec(val_1030)
                c1030[22] = fmt_dec(item['icms_vST'])
                c1030[26] = fmt_dec(item['vUnCom'])
                c1030[28] = cst_ipi
                c1030[29] = fmt_dec(item['ipi_pIPI'])
                c1030[33] = cfop
                c1030[35] = '0,0000'
                c1030[36] = '0,00'
                c1030[37] = '0,0000'
                c1030[38] = '0,00'
                c1030[39] = fmt_dec(vCont_item)
                c1030[40] = '70'
                c1030[41] = '0,00'
                c1030[42] = '70'
                c1030[43] = '0,00'
                c1030[54] = '999'
                c1030[55] = 'S'
                c1030[56] = uCom
                c1030[59] = fmt_dec(vCont_item)
                linhas.append('|'+'|'.join(c1030)+'|')

        # ── 1500 parcelas ─────────────────────────────────────────
        # NF própria geralmente não tem duplicatas; verificar
        cobr = infNFe.find(f'{{{NS}}}cobr') if False else None  # placeholder

        print(f'    OK  NF {nota["nNF"]:>8} | {nota["tipo"]:<12} | '
              f'CFOPs:{nota["cfops"]} | R${vNF:>12.2f}')

    # Escrever arquivo
    conteudo = '\n'.join(linhas) + '\n'
    with open(output_path, 'w', encoding='latin-1', errors='replace') as f:
        f.write(conteudo)

    n_nfs   = len(notas)
    n_linhas= len(linhas)
    print(f'\n{"="*60}')
    print(f'Arquivo : {output_path}')
    print(f'Linhas  : {n_linhas}')
    print(f'NFs     : {n_nfs}')
    print(f'{"="*60}\n')
    return linhas


# ──────────────────────────────────────────────────────────────────
def processar_zip_proprio(zip_path, output_path):
    """
    Lê um ZIP de NFs próprias (sem estrutura de etiquetas) e gera TXT.
    XMLs podem estar na raiz do ZIP ou em qualquer subpasta.
    """
    print(f'\n{"="*60}')
    print(f'Lote próprio: {os.path.basename(zip_path)}')
    print(f'{"="*60}')

    notas = []
    with zipfile.ZipFile(zip_path, 'r') as zf:
        xmls = sorted(n for n in zf.namelist() if n.endswith('.xml'))
        print(f'XMLs encontrados: {len(xmls)}')
        for xml_name in xmls:
            try:
                nota = ler_nfe_proprio(zf.read(xml_name))
                notas.append(nota)
            except Exception as e:
                print(f'  ERRO {os.path.basename(xml_name)}: {e}')

    return gerar_txt_proprio(notas, output_path)


# ──────────────────────────────────────────────────────────────────
if __name__ == '__main__':
    if len(sys.argv) == 3:
        processar_zip_proprio(sys.argv[1], sys.argv[2])
    elif len(sys.argv) == 2:
        import datetime
        zip_path = sys.argv[1]
        nome_base = os.path.splitext(os.path.basename(zip_path))[0]
        data_hoje = datetime.date.today().strftime('%Y%m%d')
        out_path  = os.path.join(os.path.dirname(zip_path) or '.',
                                  f'dominio_proprio_{nome_base}_{data_hoje}.txt')
        processar_zip_proprio(zip_path, out_path)
    else:
        print('Uso: python gerar_proprio.py <lote.zip> [saida.txt]')
        sys.exit(1)
