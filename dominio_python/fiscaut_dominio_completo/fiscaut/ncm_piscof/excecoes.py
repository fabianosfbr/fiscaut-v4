# -*- coding: utf-8 -*-
"""
Excecoes globais de credito PIS/COFINS por NCM.
Excecoes sao globais (nao por empresa).
Casos onde a regra geral da tabela_ncm nao se aplica.
"""

EXCECOES_NCM = [
    # Exemplo de estrutura — preencher conforme surgirem casos reais:
    # {
    #     "ncm": "27101259",
    #     "tipo_override": "NORMAL",   # sobrescreve o MONOFASICO da tabela
    #     "condicao": "insumo_industrial",
    #     "fundamento": "IN RFB 404/2004 + Lei 10.833/2003 art. 3o, II",
    #     "obs": "Combustivel usado como insumo na industrializacao "
    #            "pode gerar credito pela modalidade de insumo, "
    #            "dependendo do regime e da atividade da empresa.",
    # },
]

def buscar_excecao_ncm(ncm):
    """Retorna a excecao para um NCM, ou None se nao houver."""
    from ncm_piscof.tabela_ncm import normalizar_ncm
    ncm_norm = normalizar_ncm(ncm)
    for exc in EXCECOES_NCM:
        if normalizar_ncm(exc["ncm"]) == ncm_norm:
            return exc
    return None
