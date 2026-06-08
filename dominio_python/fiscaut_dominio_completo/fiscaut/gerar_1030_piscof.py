import sys
sys.path.insert(0,'/home/claude')
from ncm_piscof.verificador import verificar_credito_ncm
# -*- coding: utf-8 -*-
"""
Modulo: calculo de PIS e COFINS para o registro 1030
Regras fechadas em 14/05/2026
"""

# Aliquotas Lucro Real
ALIQ_PIS_LR     = 1.65
ALIQ_COFINS_LR  = 7.60

# Aliquotas Lucro Presumido
ALIQ_PIS_LP     = 0.65
ALIQ_COFINS_LP  = 3.00

# Regime por empresa (parametrizado)
REGIME_EMPRESA = {
    '99999999000191': 'LR',   # empresa de teste
    '10251329000189': 'LR',   # Kopron
}

def get_regime(cnpj_empresa):
    return REGIME_EMPRESA.get(cnpj_empresa, 'LR')

def fmt_dec(v, c=2):
    try: return f"{float(v or 0):.{c}f}".replace('.', ',')
    except: return '0,' + '0'*c

def calcular_piscof_item(item, etiq_cfg, cnpj_empresa, cfop_entrada):
    """
    Calcula os campos de PIS e COFINS para um item do 1030.
    Retorna dict com todos os campos necessarios.
    """
    regime     = get_regime(cnpj_empresa)
    is_simples = item.get('is_simples', False)
    ncm        = item.get('NCM', '') or item.get('ncm', '')

    # Camadas 1+2+3: etiqueta + NCM + credito
    verif = verificar_credito_ncm(ncm, etiq_cfg)
    cst        = verif['cst']
    cred_piscof= verif['aplica']
    cod_cred   = etiq_cfg.get('base_credito_campo67', None)

    # Importacao: usar valores do XML (tratar depois nos casos especiais)
    cfop_str = str(cfop_entrada)
    if cfop_str in ('3101','3102'):
        return {
            'aliq_pis'   : fmt_dec(item.get('pis_pPIS',0), 4),
            'vlr_pis'    : fmt_dec(item.get('pis_vPIS',0)),
            'aliq_cofins': fmt_dec(item.get('cof_pCOFINS',0), 4),
            'vlr_cofins' : fmt_dec(item.get('cof_vCOFINS',0)),
            'cst_pis'    : item.get('pis_cst','50'),
            'bc_pis'     : fmt_dec(item.get('vProd',0)),
            'cst_cofins' : item.get('cof_cst','50'),
            'bc_cofins'  : fmt_dec(item.get('vProd',0)),
            'base_credito': '',  # campo 67
            # IBS/CBS — zerados (reforma tributária: pendente regulamentação)
            'aliq_ibs':'0,0000','bc_ibs':'0,00','vlr_ibs':'0,00','vlr_ibs_cred':'0,00',
            'aliq_cbs':'0,0000','bc_cbs':'0,00','vlr_cbs':'0,00','vlr_cbs_cred':'0,00',
        }

    # Simples Nacional: sem credito de PIS/COFINS
    if is_simples:
        return _sem_credito(item)

    if not cred_piscof:
        return _sem_credito(item)

    # Com credito
    vProd = float(item.get('vProd', 0))

    if regime == 'LR':
        aliq_pis    = ALIQ_PIS_LR
        aliq_cofins = ALIQ_COFINS_LR
    else:
        aliq_pis    = ALIQ_PIS_LP
        aliq_cofins = ALIQ_COFINS_LP

    vlr_pis    = round(vProd * aliq_pis    / 100, 2)
    vlr_cofins = round(vProd * aliq_cofins / 100, 2)

    return {
        'aliq_pis'   : fmt_dec(aliq_pis, 4),
        'vlr_pis'    : fmt_dec(vlr_pis),
        'aliq_cofins': fmt_dec(aliq_cofins, 4),
        'vlr_cofins' : fmt_dec(vlr_cofins),
        'cst_pis'    : cst,
        'bc_pis'     : fmt_dec(vProd),
        'cst_cofins' : cst,
        'bc_cofins'  : fmt_dec(vProd),
        'base_credito': str(cod_cred) if cod_cred else '',  # campo 67 do 1030 (01-18)
        # IBS/CBS — zerados (reforma tributária: pendente regulamentação)
        'aliq_ibs':'0,0000','bc_ibs':'0,00','vlr_ibs':'0,00','vlr_ibs_cred':'0,00',
        'aliq_cbs':'0,0000','bc_cbs':'0,00','vlr_cbs':'0,00','vlr_cbs_cred':'0,00',
    }

def _sem_credito(item):
    return {
        'aliq_pis'   : '0,0000',
        'vlr_pis'    : '0,00',
        'aliq_cofins': '0,0000',
        'vlr_cofins' : '0,00',
        'cst_pis'    : '70',
        'bc_pis'     : '0,00',
        'cst_cofins' : '70',
        'bc_cofins'  : '0,00',
        'base_credito': '',  # campo 67
        # IBS/CBS — zerados
        'aliq_ibs':'0,0000','bc_ibs':'0,00','vlr_ibs':'0,00','vlr_ibs_cred':'0,00',
        'aliq_cbs':'0,0000','bc_cbs':'0,00','vlr_cbs':'0,00','vlr_cbs_cred':'0,00',
    }


if __name__ == '__main__':
    import sys; sys.path.insert(0,'/home/claude')
    from ncm_piscof.verificador import verificar_credito_ncm
    from tabela_etiquetas import TABELA_ETIQUETAS

    print("=== TESTES PIS/COFINS 1030 ===\n")

    casos = [
        # (cod_etiq, desc_caso, vProd, cfop, is_simples)
        (8647, 'Materia Prima (cred=True, LR, 102)',  1000.0, '1101', False),
        (9062, 'Combustivel GGF (cred=True, LR, 109)',500.0, '1101', False),
        (474,  'Limpeza ADM (cred=False)',             200.0, '1556', False),
        (133,  'Imobilizado (sem cred)',               5000.0,'1551', False),
        (358,  'Devolucao (cred=True, 108)',           800.0, '1201', False),
        (8647, 'Materia Prima Simples (sem cred)',    1000.0, '1101', True),
        (8655, 'Importacao (usa XML)',                2000.0, '3101', False),
    ]

    for cod, desc, vprod, cfop, simpl in casos:
        etiq = TABELA_ETIQUETAS[cod]
        item = {
            'vProd': vprod, 'is_simples': simpl,
            'pis_pPIS': 1.65, 'pis_vPIS': round(vprod*0.0165,2),
            'cof_pCOFINS': 7.6, 'cof_vCOFINS': round(vprod*0.076,2),
            'pis_cst':'50', 'cof_cst':'50'
        }
        r = calcular_piscof_item(item, etiq, '10251329000189', cfop)
        print(f"{desc}:")
        print(f"  PIS:    aliq={r['aliq_pis']:>8} vlr={r['vlr_pis']:>8} "
              f"CST={r['cst_pis']:>3} BC={r['bc_pis']:>10}")
        print(f"  COFINS: aliq={r['aliq_cofins']:>8} vlr={r['vlr_cofins']:>8} "
              f"CST={r['cst_cofins']:>3} BC={r['bc_cofins']:>10}")
        print(f"  base_credito={r['base_credito']}")
        print()
