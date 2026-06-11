# -*- coding: utf-8 -*-
"""
Verificador de credito PIS/COFINS por NCM.
Implementa as 3 camadas de validacao:
  Camada 1: etiqueta permite credito? (cred_piscof)
  Camada 2: NCM tem restricao? (tabela_ncm + excecoes)
  Camada 3: aplica credito com CST/aliq corretos
"""

import sys
sys.path.insert(0, '/home/claude')

from ncm_piscof.tabela_ncm import buscar_restricao_ncm
from ncm_piscof.excecoes   import buscar_excecao_ncm

# CST resultante por tipo de restricao
CST_POR_TIPO = {
    "NORMAL"       : "50",  # credito pleno
    "ALIQUOTA_ZERO": "73",  # direito mas aliq zero
    "MONOFASICO"   : "73",  # recolhido na cadeia
    "SUSPENSAO"    : "73",  # suspensao
    "ISENCAO"      : "73",  # isencao
    "SEM_CREDITO"  : "70",  # etiqueta nao permite
}

def verificar_credito_ncm(ncm, etiq_cfg):
    """
    Retorna dict com:
      cst       : CST PIS/COFINS correto (50, 70 ou 73)
      tipo      : NORMAL | ALIQUOTA_ZERO | MONOFASICO | SUSPENSAO | ISENCAO | SEM_CREDITO
      aplica    : True se deve calcular e aplicar o credito (apenas CST 50)
      grupo     : grupo da tabela (ex: 'MON-001') ou None
      fundamento: base legal ou ''
      obs       : observacao adicional ou ''
    """
    # --- Camada 1: etiqueta ---
    if not etiq_cfg.get('cred_piscof', False):
        return {
            'cst'       : '70',
            'tipo'      : 'SEM_CREDITO',
            'aplica'    : False,
            'grupo'     : None,
            'fundamento': '',
            'obs'       : 'Etiqueta/operacao nao permite credito de PIS/COFINS',
        }

    # --- Camada 2a: excecao especifica ---
    excecao = buscar_excecao_ncm(ncm)
    if excecao:
        tipo = excecao.get('tipo_override', 'NORMAL')
        return {
            'cst'       : CST_POR_TIPO.get(tipo, '73'),
            'tipo'      : tipo,
            'aplica'    : tipo == 'NORMAL',
            'grupo'     : 'EXCECAO',
            'fundamento': excecao.get('fundamento', ''),
            'obs'       : excecao.get('obs', ''),
        }

    # --- Camada 2b: tabela de NCMs ---
    restricao = buscar_restricao_ncm(ncm)
    if restricao:
        tipo = restricao['tipo']
        return {
            'cst'       : CST_POR_TIPO.get(tipo, '73'),
            'tipo'      : tipo,
            'aplica'    : False,  # CST 73: operacao tem direito mas nao aplica credito
            'grupo'     : restricao.get('grupo', ''),
            'fundamento': restricao.get('fundamento', ''),
            'obs'       : restricao.get('obs', ''),
        }

    # --- Camada 3: sem restricao — credito normal ---
    return {
        'cst'       : '50',
        'tipo'      : 'NORMAL',
        'aplica'    : True,
        'grupo'     : None,
        'fundamento': '',
        'obs'       : '',
    }


if __name__ == '__main__':
    import sys
    sys.path.insert(0, '/home/claude')
    from tabela_etiquetas import TABELA_ETIQUETAS

    print("=== TESTE verificador.py ===\n")

    casos = [
        # (ncm,          desc,                    cod_etiq, aplica_esperado, cst_esp)
        ("73089000", "Estrutura metalica",          8647,   True,  "50"),
        ("27101259", "Oleo diesel (GGF)",           9062,   False, "73"),
        ("04061000", "Queijo (empresa alimenticia)",8647,   False, "73"),
        ("30049099", "Medicamento",                 8647,   False, "73"),
        ("73089000", "Estrutura metalica-despesa",  474,    False, "70"),
        ("22021000", "Refrigerante",                8647,   False, "73"),
        ("39269090", "Peca plastica normal",        8647,   True,  "50"),
        ("84313900", "Autopeca",                    8647,   False, "73"),
    ]

    ok = 0
    for ncm, desc, cod_etiq, aplica_esp, cst_esp in casos:
        etiq = TABELA_ETIQUETAS[cod_etiq]
        r = verificar_credito_ncm(ncm, etiq)
        status = "OK" if r['aplica'] == aplica_esp and r['cst'] == cst_esp else "FALHOU"
        if status == "OK": ok += 1
        print(f"  [{status}] {desc} (NCM {ncm}, etiq {cod_etiq})")
        print(f"    CST={r['cst']} tipo={r['tipo']} aplica={r['aplica']}")
        if r['grupo']:    print(f"    grupo={r['grupo']}")
        if r['fundamento']: print(f"    fundamento={r['fundamento']}")
        print()
    print(f"{ok}/{len(casos)} testes passaram.")
