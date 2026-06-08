# -*- coding: utf-8 -*-
"""
Modulo: geracao do registro 1020 (ICMS e IPI)
Regras fechadas em 14/05/2026
"""

# CSTs que vao em isentas (dois ultimos digitos)
CST_ISENTAS  = {'30','40'}
# CSTs que vao em outras
CST_OUTRAS   = {'50','60','61','62','90'}  # 61=monofasico retido, 62=monofasico diferido
# CSTs que tem BC + ICMS (tributados)
CST_TRIBUTADOS = {'00','10','20','51','70'}
# CSTs com ST
CST_COM_ST   = {'10','70'}

def dois_ultimos(cst):
    """Retorna os dois ultimos digitos do CST (ex: '100' -> '00', '060' -> '60')"""
    cst = str(cst).strip().zfill(3)
    return cst[-2:]

def fmt_dec(v, c=2):
    try: return f"{float(v or 0):.{c}f}".replace('.', ',')
    except: return '0,' + '0'*c

def gerar_1020_icms(segmento, vCont_seg, is_simples, cred_icms, nota_uf):
    """
    Gera linhas 1020 de ICMS (codigo 1) para um segmento.
    Segmenta por aliquota. Retorna lista de strings.
    
    Regra vContabil por linha:
      vBC + vIsentas + vOutras + vST + vIPI_seg = vContabil
      (vICMS NAO entra)
    
    vCont_seg = valor contabil real do segmento (mesmo do 1000 campo 13)
    """
    linhas = []
    itens  = segmento['itens']
    n_seg  = segmento.get('n_seg', 1)

    # ── CSOSN / Simples Nacional ──
    if is_simples:
        if cred_icms:
            # SN com crédito: mover crédito do 1200 para o 1020
            # O campo "ICMS Simples Nacional" no 1030 (aba Estoque) não tem
            # campo disponível no leiaute Domínio — solução temporária até
            # a Domínio liberar o campo: usar 1020 com BC+aliq+val
            # O 1200 NÃO será gerado para estes casos (evita duplicidade)
            base_sn = sum(i.get('icms_vCredSN', 0) and i.get('vProd', 0)
                         for i in itens if i.get('icms_vCredSN', 0) > 0)
            cred_sn = sum(i.get('icms_vCredSN', 0) for i in itens)
            # Calcular base corretamente: sum(vProd) dos itens com vCredSN>0
            base_sn = sum(i.get('vProd', 0) for i in itens if i.get('icms_vCredSN', 0) > 0)
            if base_sn > 0 and cred_sn > 0:
                aliq_sn = round(cred_sn / base_sn * 100, 2)
                linhas.append(
                    f"|1020|1|0,00|{fmt_dec(base_sn)}|{fmt_dec(aliq_sn)}|{fmt_dec(cred_sn)}"
                    f"|0,00|0,00|0,00|0,00|{fmt_dec(vCont_seg)}|||||"
                )
            else:
                # SN com cred_icms mas sem itens elegíveis neste segmento
                vIPI_seg = sum(i.get('icms_vIPI', 0) for i in itens)
                linhas.append(
                    f"|1020|1|0,00|0,00|0,00|0,00"
                    f"|0,00|{fmt_dec(vCont_seg)}|0,00|0,00|{fmt_dec(vCont_seg)}|||||"
                )
        else:
            # SN sem crédito: tudo em outras (comportamento original)
            linhas.append(
                f"|1020|1|0,00|0,00|0,00|0,00"
                f"|0,00|{fmt_dec(vCont_seg)}|0,00|0,00|{fmt_dec(vCont_seg)}|||||"
            )
        return linhas

    # ── Etiqueta de DESPESA (cred_icms=False) ──
    if not cred_icms:
        linhas.append(
            f"|1020|1|0,00|0,00|0,00|0,00"
            f"|0,00|{fmt_dec(vCont_seg)}|0,00|0,00|{fmt_dec(vCont_seg)}|||||"
        )
        return linhas

    # ── Etiqueta de CUSTO/ESTOQUE (cred_icms=True) ──
    # Agrupar itens por aliquota de ICMS
    grupos = {}  # aliq -> lista de itens
    for item in itens:
        cst2 = dois_ultimos(item.get('icms_cst','00'))
        aliq = round(float(item.get('icms_pICMS',0)), 4)
        # CST 60 com crédito CAT 14/2009: tratar como tributado (não vai para outras)
        # Nos demais casos: isentos/outras agrupam em aliq=0.0
        eh_cat14 = item.get('icms_vBC', 0) > 0 and cst2 == '60'
        if not eh_cat14 and (cst2 in CST_ISENTAS or cst2 in CST_OUTRAS):
            aliq = 0.0
        key = aliq
        if key not in grupos:
            grupos[key] = []
        grupos[key].append(item)

    # vIPI total do segmento (distribui proporcionalmente entre grupos)
    vIPI_seg_total = sum(float(i.get('icms_vIPI',0)) for i in itens)
    vProd_seg_total = sum(float(i.get('vProd',0)) for i in itens) or 1

    aliq_keys = sorted(grupos.keys(), reverse=True)
    n_grupos  = len(aliq_keys)

    # Acumuladores para ajuste de arredondamento no ultimo grupo
    vCont_acum = 0.0

    for idx, aliq in enumerate(aliq_keys):
        grupo = grupos[aliq]
        ultimo = (idx == n_grupos - 1)

        vBC_g      = sum(float(i.get('icms_vBC',0))    for i in grupo)
        vICMS_g    = sum(float(i.get('icms_vICMS',0))  for i in grupo)
        vST_g      = sum(float(i.get('icms_vST',0))    for i in grupo)
        vProd_g    = sum(float(i.get('vProd',0))        for i in grupo)

        # IPI proporcional ao vProd do grupo
        vIPI_g = round(vIPI_seg_total * vProd_g / vProd_seg_total, 2)

        # Campos por CST
        vIsentas_g = 0.0
        vOutras_g  = 0.0

        for item in grupo:
            cst2   = dois_ultimos(item.get('icms_cst','00'))
            vprod_i= float(item.get('vProd',0))

            if cst2 in CST_ISENTAS:
                vIsentas_g += vprod_i
            elif cst2 in CST_OUTRAS:
                vOutras_g  += vprod_i
            elif cst2 == '20':
                # Reducao de BC: valor reduzido vai em isentas
                vBC_orig  = float(item.get('icms_vBC',0))
                pRedBC    = float(item.get('icms_pRedBC',0))
                vIsentas_g += round(vprod_i * pRedBC / 100, 2)
            elif cst2 == '51':
                # Diferimento: quando vBC=0 (diferimento total), vProd vai em outras
                # Quando vBC>0 (diferimento parcial), fica no vBC ja somado acima
                if float(item.get('icms_vBC', 0)) <= 0:
                    vOutras_g += vprod_i
            # 00, 10, 70 → fica no vBC (ja somado acima)

        # Valor contabil desta linha
        if ultimo:
            # Ultimo grupo absorve diferenca de arredondamento
            vCont_g = round(vCont_seg - vCont_acum, 2)
        else:
            vCont_g = round(vBC_g + vIsentas_g + vOutras_g + vST_g + vIPI_g, 2)
            vCont_acum += vCont_g

        aliq_fmt = fmt_dec(aliq)
        linhas.append(
            f"|1020|1|0,00|{fmt_dec(vBC_g)}|{aliq_fmt}|{fmt_dec(vICMS_g)}"
            f"|{fmt_dec(vIsentas_g)}|{fmt_dec(vOutras_g)}"
            f"|{fmt_dec(vIPI_g)}|{fmt_dec(vST_g)}|{fmt_dec(vCont_g)}|||||"
        )

    return linhas


def gerar_1020_ipi(segmento, vCont_seg, is_simples, cred_ipi):
    """
    Gera linhas 1020 de IPI (codigo 2) para um segmento.

    cred_ipi=True  E vIPI>0: BC + vIPI normais, vOutras=0, sem cod_rec
    cred_ipi=False OU vIPI=0: BC=0, vIPI=0, vOutras=vCont (total segmento,
                               inclui frete/IPI), cod_rec=1097 (Demais Produtos)
    """
    linhas = []
    itens  = segmento['itens']

    # Agrupar por alíquota IPI
    grupos = {}
    for item in itens:
        aliq_ipi = round(float(item.get('ipi_pIPI', 0)), 4)
        if aliq_ipi not in grupos:
            grupos[aliq_ipi] = []
        grupos[aliq_ipi].append(item)

    aliq_keys  = sorted(grupos.keys(), reverse=True)
    n_grupos   = len(aliq_keys)
    vCont_acum = 0.0

    for idx, aliq in enumerate(aliq_keys):
        grupo  = grupos[aliq]
        ultimo = (idx == n_grupos - 1)

        vBC_ipi_g = sum(float(i.get('ipi_vBC',   0)) for i in grupo)
        vIPI_g    = sum(float(i.get('icms_vIPI', 0)) for i in grupo)

        if ultimo:
            vCont_g = round(vCont_seg - vCont_acum, 2)
        else:
            vCont_g = round(vBC_ipi_g + vIPI_g, 2)
            vCont_acum += vCont_g

        if cred_ipi and not is_simples and vIPI_g > 0:
            # Com crédito: BC e vIPI normais, outras=0, sem cod_rec
            vIsentas_g = 0.0
            vOutras_g  = 0.0
            cod_rec    = ''
        else:
            # Sem crédito: tudo em outras = vCont (inclui frete + IPI)
            vBC_ipi_g  = 0.0
            vIPI_g     = 0.0
            vIsentas_g = 0.0
            vOutras_g  = vCont_g   # ← vCont total, não só vProd
            cod_rec    = '1097'    # Demais Produtos

        linhas.append(
            f"|1020|2|0,00|{fmt_dec(vBC_ipi_g)}|{fmt_dec(aliq)}|{fmt_dec(vIPI_g)}"
            f"|{fmt_dec(vIsentas_g)}|{fmt_dec(vOutras_g)}"
            f"|0,00|0,00|{fmt_dec(vCont_g)}|{cod_rec}|||"
        )

    return linhas


# ── Teste rapido com os casos do lote ──
if __name__ == '__main__':
    print("=== TESTE 1020 ICMS ===\n")

    # Caso 1: CST 00, aliq 18%, com IPI (NF 40411)
    seg1 = {
        'n_seg': 1,
        'itens': [{
            'vProd':4648.92, 'icms_cst':'00', 'icms_pICMS':18.0,
            'icms_vBC':4648.92, 'icms_vICMS':836.81, 'icms_vST':0,
            'icms_pRedBC':0, 'icms_vIPI':151.09, 'ipi_pIPI':0, 'ipi_vBC':0
        }]
    }
    vCont1 = 4800.01
    print(f"NF 40411 (CST00, 18%, IPI=151.09, vCont={vCont1}):")
    for l in gerar_1020_icms(seg1, vCont1, False, True, 'SP'):
        print(f"  {l}")
    print()

    # Caso 2: CST 60, despesa (NF 28989)
    seg2 = {
        'n_seg': 1,
        'itens': [{
            'vProd':360.0, 'icms_cst':'60', 'icms_pICMS':0,
            'icms_vBC':0, 'icms_vICMS':0, 'icms_vST':0,
            'icms_pRedBC':0, 'icms_vIPI':0, 'ipi_pIPI':0, 'ipi_vBC':0
        }]
    }
    vCont2 = 360.0
    print(f"NF 28989 (CST60, despesa, vCont={vCont2}):")
    for l in gerar_1020_icms(seg2, vCont2, False, False, 'PA'):
        print(f"  {l}")
    print()

    # Caso 3: CSOSN 102, Simples (NF 2148)
    seg3 = {
        'n_seg': 1,
        'itens': [{
            'vProd':688.35, 'icms_cst':'', 'icms_csosn':'102', 'icms_pICMS':0,
            'icms_vBC':0, 'icms_vICMS':0, 'icms_vST':0,
            'icms_pRedBC':0, 'icms_vIPI':0, 'ipi_pIPI':0, 'ipi_vBC':0
        }]
    }
    vCont3 = 688.35
    print(f"NF 2148 (CSOSN 102, Simples, vCont={vCont3}):")
    for l in gerar_1020_icms(seg3, vCont3, True, False, 'PA'):
        print(f"  {l}")
    print()

    # Caso 4: CST 00, dois itens aliq 18% (NF 56985) -> 1 linha agrupada
    seg4 = {
        'n_seg': 1,
        'itens': [
            {'vProd':3663.76,'icms_cst':'00','icms_pICMS':18.0,'icms_vBC':3733.76,
             'icms_vICMS':672.08,'icms_vST':0,'icms_pRedBC':0,'icms_vIPI':0,'ipi_pIPI':0,'ipi_vBC':0},
            {'vProd':1663.24,'icms_cst':'00','icms_pICMS':18.0,'icms_vBC':1663.24,
             'icms_vICMS':299.38,'icms_vST':0,'icms_pRedBC':0,'icms_vIPI':0,'ipi_pIPI':0,'ipi_vBC':0},
        ]
    }
    vCont4 = 5397.0
    print(f"NF 56985 (2 itens CST00 18%, vCont={vCont4}):")
    for l in gerar_1020_icms(seg4, vCont4, False, True, 'SP'):
        print(f"  {l}")
    print()

    # Caso 5: CST 00 aliq 12%, custo (NF 284864)
    seg5 = {
        'n_seg': 1,
        'itens': [{
            'vProd':12726.0,'icms_cst':'00','icms_pICMS':12.0,
            'icms_vBC':12726.0,'icms_vICMS':1527.12,'icms_vST':0,
            'icms_pRedBC':0,'icms_vIPI':0,'ipi_pIPI':0,'ipi_vBC':0
        }]
    }
    vCont5 = 12726.0
    print(f"NF 284864 (CST00, 12%, fora do estado, vCont={vCont5}):")
    for l in gerar_1020_icms(seg5, vCont5, False, True, 'ES'):
        print(f"  {l}")
    print()

    print("=== TESTE 1020 IPI ===\n")

    # IPI real (NF 40411: vIPI=151.09)
    print(f"NF 40411 (IPI=151.09, cred_ipi=True):")
    seg1['itens'][0]['icms_vIPI'] = 151.09
    seg1['itens'][0]['ipi_pIPI']  = 10.0
    seg1['itens'][0]['ipi_vBC']   = 4648.92
    for l in gerar_1020_ipi(seg1, vCont1, False, True):
        print(f"  {l}")
    print()

    print(f"NF 28989 (sem IPI, despesa, cred_ipi=False):")
    for l in gerar_1020_ipi(seg2, vCont2, False, False):
        print(f"  {l}")
