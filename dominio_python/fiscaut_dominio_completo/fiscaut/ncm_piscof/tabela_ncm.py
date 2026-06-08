# -*- coding: utf-8 -*-
"""
Tabela de NCMs com restricao de credito PIS/COFINS
Projeto: Fiscaut — modulo ncm_piscof
Versao: 1.0 — Fase 1 (Kopron + base geral)

Tipos de restricao:
  ALIQUOTA_ZERO → produto tem aliq 0% por lei          → CST 73
  MONOFASICO    → PIS/COFINS concentrado na cadeia      → CST 73
  SUSPENSAO     → tributacao suspensa (ZFM, drawback)   → CST 73
  ISENCAO       → isencao especifica por lei             → CST 73
  NORMAL        → sem restricao, credito permitido       → CST 50
"""

TABELA_NCM = [

    # =========================================================
    # GRUPO 1 — ALIMENTOS CESTA BASICA (aliquota zero)
    # Base: Lei 10.925/2004, Lei 10.865/2004 art. 28
    # =========================================================
    {
        "grupo": "110", "desc": "Leite e creme de leite",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "prefixo", "valor_match": ["0401","0402"],
        "fundamento": "Lei 10.865/2004, art. 28, III",
        "setores": ["alimentos"],
    },
    {
        "grupo": "111", "desc": "Queijos e requeijao",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "prefixo", "valor_match": ["0406"],
        "fundamento": "Lei 10.865/2004, art. 28, III",
        "setores": ["alimentos"],
    },
    {
        "grupo": "113", "desc": "Farinha de trigo",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "exato", "valor_match": ["11010010"],
        "fundamento": "Lei 10.925/2004, art. 1o, XIV",
        "setores": ["alimentos"],
    },
    {
        "grupo": "114", "desc": "Trigo em grao",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "prefixo", "valor_match": ["1001"],
        "fundamento": "Lei 10.925/2004, art. 1o, XV",
        "setores": ["alimentos"],
    },
    {
        "grupo": "115", "desc": "Pre-misturas e pao comum",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "exato", "valor_match": ["19012000","19059090"],
        "fundamento": "Lei 10.925/2004, art. 1o, XVI",
        "setores": ["alimentos"],
        "possui_ex": True,
    },
    {
        "grupo": "116", "desc": "Horticolas e frutas",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "capitulo", "valor_match": ["07","08"],
        "fundamento": "Lei 10.865/2004, art. 28, III",
        "setores": ["alimentos"],
    },
    {
        "grupo": "117", "desc": "Ovos",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "prefixo", "valor_match": ["0407"],
        "fundamento": "Lei 10.865/2004, art. 28, III",
        "setores": ["alimentos"],
    },
    {
        "grupo": "119", "desc": "Massas alimenticias",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "exato",
        "valor_match": ["19021100","19021900","19022000","19023000"],
        "fundamento": "Lei 10.925/2004, art. 1o, XVIII",
        "setores": ["alimentos"],
    },
    {
        "grupo": "121", "desc": "Carnes e produtos de origem animal",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "prefixo",
        "valor_match": [
            "0201","0202","02061000","02062","02102000",
            "05069000","05100010","1502101",
            "0203","02063000","02064","0207","0209",
            "02101","02109900","0204","02068000"
        ],
        "fundamento": "MP 609/2013, art. 1o",
        "setores": ["alimentos"],
    },
    {
        "grupo": "122", "desc": "Peixes",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "prefixo", "valor_match": ["0302","0303","0304"],
        "excluir_ncm": ["03029000"],
        "fundamento": "MP 609/2013, art. 1o",
        "setores": ["alimentos"],
    },
    {
        "grupo": "123", "desc": "Cafe",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "prefixo", "valor_match": ["0901","21011"],
        "fundamento": "MP 609/2013, art. 1o",
        "setores": ["alimentos"],
    },
    {
        "grupo": "124", "desc": "Acucar",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "exato", "valor_match": ["17011400","17019900"],
        "fundamento": "MP 609/2013, art. 1o",
        "setores": ["alimentos"],
    },
    {
        "grupo": "125", "desc": "Oleos vegetais",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "faixa_prefixo", "valor_match": [("1508","1514"),"1507"],
        "fundamento": "MP 609/2013, art. 1o",
        "setores": ["alimentos"],
    },
    {
        "grupo": "126", "desc": "Manteiga",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "exato", "valor_match": ["04051000"],
        "fundamento": "MP 609/2013, art. 1o",
        "setores": ["alimentos"],
    },
    {
        "grupo": "127", "desc": "Margarina",
        "tipo": "ALIQUOTA_ZERO",
        "tipo_match": "exato", "valor_match": ["15171000"],
        "fundamento": "MP 609/2013, art. 1o",
        "setores": ["alimentos"],
    },

    # =========================================================
    # GRUPO 2 — MONOFASICOS (PIS/COFINS concentrado na cadeia)
    # =========================================================
    {
        "grupo": "MON-001", "desc": "Combustiveis derivados de petroleo",
        "tipo": "MONOFASICO",
        "tipo_match": "prefixo", "valor_match": ["2710"],
        "fundamento": "Lei 9.718/1998, art. 4o; Lei 10.865/2004",
        "setores": ["combustiveis","industria","geral"],
        "obs": "Gasolina, diesel, querosene, oleos lubrificantes. "
               "Credito presumido possivel para transportadoras — avaliar caso a caso.",
    },
    {
        "grupo": "MON-002", "desc": "Gas natural e GLP",
        "tipo": "MONOFASICO",
        "tipo_match": "prefixo", "valor_match": ["2711"],
        "fundamento": "Lei 9.718/1998, art. 4o",
        "setores": ["combustiveis","industria","geral"],
    },
    {
        "grupo": "MON-003", "desc": "Alcool etilico combustivel",
        "tipo": "MONOFASICO",
        "tipo_match": "exato", "valor_match": ["22072000","22071000"],
        "fundamento": "Lei 9.718/1998, art. 5o",
        "setores": ["combustiveis","geral"],
    },
    {
        "grupo": "MON-004", "desc": "Medicamentos",
        "tipo": "MONOFASICO",
        "tipo_match": "prefixo", "valor_match": ["3003","3004"],
        "fundamento": "Lei 10.147/2000, art. 1o",
        "setores": ["farma","geral"],
        "obs": "Medicamentos e produtos farmaceuticos — monofasico.",
    },
    {
        "grupo": "MON-005", "desc": "Bebidas frias (refrigerantes, cervejas, aguas)",
        "tipo": "MONOFASICO",
        "tipo_match": "prefixo",
        "valor_match": ["2201","2202","2203","2204","2205","2206","2208"],
        "fundamento": "Lei 10.833/2003, art. 58-A a 58-V",
        "setores": ["alimentos","bebidas","geral"],
    },
    {
        "grupo": "MON-006", "desc": "Autopecas",
        "tipo": "MONOFASICO",
        "tipo_match": "prefixo",
        "valor_match": [
            "8407","8408","8409","8413","8421","8431",
            "8483","8484","8485","8708","8714"
        ],
        "fundamento": "Lei 10.485/2002, art. 3o",
        "setores": ["automovel","industria","geral"],
        "obs": "Autopecas — lista nao exaustiva; conferir TIPI completa.",
    },
    {
        "grupo": "MON-007", "desc": "Pneus e camaras de ar",
        "tipo": "MONOFASICO",
        "tipo_match": "prefixo",
        "valor_match": ["4011","4012","4013"],
        "fundamento": "Lei 10.485/2002",
        "setores": ["automovel","industria","geral"],
    },
    {
        "grupo": "MON-008", "desc": "Embarcacoes",
        "tipo": "MONOFASICO",
        "tipo_match": "prefixo", "valor_match": ["8901","8902","8903","8904","8905"],
        "fundamento": "Lei 10.865/2004, art. 28",
        "setores": ["naval","geral"],
    },

    # =========================================================
    # GRUPO 3 — SUSPENSAO (ZFM, drawback, exportacao indireta)
    # =========================================================
    {
        "grupo": "SUP-ZFM", "desc": "Produtos destinados a ZFM",
        "tipo": "SUSPENSAO",
        "tipo_match": "capitulo",
        "valor_match": [],   # ZFM e definido pela operacao/CFOP, nao pelo NCM
        "fundamento": "Lei 10.637/2002, art. 2o, §3o; Decreto-lei 288/1967",
        "setores": ["zfm"],
        "obs": "Suspensao definida pelo CFOP e UF destino (AM), nao pelo NCM. "
               "Tratar na logica de CFOP quando implementar ZFM.",
    },
    {
        "grupo": "SUP-DRW", "desc": "Insumos importados com drawback",
        "tipo": "SUSPENSAO",
        "tipo_match": "capitulo",
        "valor_match": [],   # drawback e definido pelo regime aduaneiro
        "fundamento": "Lei 11.945/2009, art. 12",
        "setores": ["importacao","industria"],
        "obs": "Suspensao por regime aduaneiro especial. "
               "Tratar quando implementar NF de importacao.",
    },

    # =========================================================
    # GRUPO 4 — ISENCAO especifica
    # =========================================================
    {
        "grupo": "ISE-001", "desc": "Livros jornais e periodicos",
        "tipo": "ISENCAO",
        "tipo_match": "prefixo", "valor_match": ["4901","4902","4903","4904","4905"],
        "fundamento": "CF/1988, art. 150, VI, d; Lei 10.865/2004",
        "setores": ["editorial","geral"],
    },
    {
        "grupo": "ISE-002", "desc": "Produtos quimicos e farmaceuticos prioritarios",
        "tipo": "ISENCAO",
        "tipo_match": "prefixo", "valor_match": ["2936","2937","2941"],
        "fundamento": "Lei 10.865/2004, art. 28, II",
        "setores": ["farma","quimica","geral"],
        "obs": "Vitaminas, hormonios, antibioticos — isencao especifica.",
    },
]

# =========================================================
# Funcoes de match (reaproveitadas do RAGNAI com melhorias)
# =========================================================

def limpar_digitos(valor):
    return "".join(ch for ch in str(valor) if ch.isdigit())

def normalizar_ncm(ncm):
    return limpar_digitos(ncm).zfill(8)

def normalizar_prefixo(valor):
    return limpar_digitos(valor)

def match_regra(ncm_norm, regra):
    # Verificar exclusoes
    for exc in regra.get("excluir_ncm", []):
        if ncm_norm == normalizar_ncm(exc):
            return False

    tipo = regra["tipo_match"]
    valores = regra["valor_match"]

    if not valores:  # ZFM, drawback — match por operacao, nao NCM
        return False

    if tipo == "exato":
        return ncm_norm in [normalizar_ncm(v) for v in valores]

    if tipo in ("prefixo", "capitulo"):
        return any(ncm_norm.startswith(normalizar_prefixo(v)) for v in valores)

    if tipo == "faixa_prefixo":
        for item in valores:
            if isinstance(item, tuple):
                ini = normalizar_prefixo(item[0])
                fim = normalizar_prefixo(item[1])
                if ini <= ncm_norm[:4] <= fim:
                    return True
            else:
                if ncm_norm.startswith(normalizar_prefixo(item)):
                    return True
        return False

    return False

def buscar_restricao_ncm(ncm):
    """
    Retorna a regra de restricao para um NCM, ou None se nao houver.
    None = produto normal, credito permitido (CST 50).
    """
    ncm_norm = normalizar_ncm(ncm)
    for regra in TABELA_NCM:
        if match_regra(ncm_norm, regra):
            return regra
    return None


if __name__ == '__main__':
    testes = [
        ("27101259", "Oleo diesel",         "MONOFASICO"),
        ("04061000", "Queijo minas",         "ALIQUOTA_ZERO"),
        ("73089000", "Estrutura metalica",   None),
        ("84313900", "Peca equipamento",     "MONOFASICO"),
        ("30049099", "Medicamento",          "MONOFASICO"),
        ("02013000", "Carne bovina",         "ALIQUOTA_ZERO"),
        ("22021000", "Refrigerante",         "MONOFASICO"),
        ("49011000", "Livro",                "ISENCAO"),
        ("39269090", "Peca plastica",        None),
    ]
    print("=== TESTE tabela_ncm.py ===\n")
    ok_count = 0
    for ncm, desc, esperado in testes:
        regra = buscar_restricao_ncm(ncm)
        tipo_encontrado = regra["tipo"] if regra else None
        status = "OK" if tipo_encontrado == esperado else "FALHOU"
        if status == "OK": ok_count += 1
        print(f"  [{status}] NCM {ncm} ({desc})")
        print(f"    Esperado: {esperado} | Encontrado: {tipo_encontrado}")
        if regra:
            print(f"    Grupo: {regra['grupo']} — {regra['desc']}")
            print(f"    Fundamento: {regra['fundamento']}")
        print()
    print(f"{ok_count}/{len(testes)} testes passaram.")
