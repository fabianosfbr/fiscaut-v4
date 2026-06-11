# -*- coding: utf-8 -*-
"""
Tabela de equivalencia etiqueta x CFOP x acumulador
Fonte: planilha equivalencia_de_etiqueta_x_cfop_-_atual_REAL.xlsx
v22r - acumuladores atualizados para numeracao de producao (Kopron)
"""

TABELA_ETIQUETAS = {
    # === ESTOQUE DE PRODUCAO AQUISICAO ===
    8681: {'desc':'Industrializacao Efetuada para Terceiros','cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'industrializacao','acumulador':8012,'cst_piscof':'50','base_credito_campo67':'03','vinculo_credito_0115':'01',},
    8655: {'desc':'Materia Prima no Mercado Externo',        'cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'importacao','acumulador':3101,'cst_piscof':'50','base_credito_campo67':'02','vinculo_credito_0115':'01',},
    8647: {'desc':'Materia Prima no Mercado Interno',        'cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'compra','acumulador':8000,'cst_piscof':'50','base_credito_campo67':'02','vinculo_credito_0115':'01',},
    8664: {'desc':'Material de Embalagem no Mercado Interno','cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'compra','acumulador':8006,'cst_piscof':'50','base_credito_campo67':'02','vinculo_credito_0115':'01',},
    # === ESTOQUE EM ELABORACAO ===
    8784: {'desc':'Produtos em Elaboracao',                  'cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'compra','acumulador':8902,'cst_piscof':'50','base_credito_campo67':'02','vinculo_credito_0115':'01',},
    # === GGF ===
    9062: {'desc':'Combustiveis e Lubrificantes (GGF)',      'cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'combustivel','acumulador':8999,'cst_piscof':'50','base_credito_campo67':'13','vinculo_credito_0115':'01',},
    10167:{'desc':'Consumo Material Uso/Consumo (GGF)',      'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    8752: {'desc':'Cursos e Treinamentos (GGF)',             'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    8758: {'desc':'Energia Eletrica (GGF)',                  'cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'energia','acumulador':8999,'cst_piscof':'50','base_credito_campo67':'04','vinculo_credito_0115':'01',},
    8775: {'desc':'Manutencao e Reparo (GGF)',               'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8023,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    8719: {'desc':'Material Auxiliar Indireto c/Credito',    'cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'compra','acumulador':8719,'cst_piscof':'50','base_credito_campo67':'13','vinculo_credito_0115':'01',},
    9824: {'desc':'Material Auxiliar Indireto s/Credito',    'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':False,'familia':'compra','acumulador':8004,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    8747: {'desc':'PAT (GGF)',                               'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':False,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    # === MAO DE OBRA DIRETA ===
    9059: {'desc':'Combustiveis e Lubrificantes (MOD)',      'cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'combustivel','acumulador':8999,'cst_piscof':'50','base_credito_campo67':'13','vinculo_credito_0115':'01',},
    9061: {'desc':'Consumo Material Uso/Consumo (MOD)',      'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    8712: {'desc':'Cursos e Treinamentos (MOD)',             'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    8713: {'desc':'EPI (MOD)',                               'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8713,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    8872: {'desc':'Ferramentas (MOD)',                       'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8011,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    8707: {'desc':'PAT (MOD)',                               'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':False,'familia':'uso_consumo','acumulador':8723,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    8714: {'desc':'Uniformes (MOD)',                         'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8722,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    # === MAO DE OBRA SERVICOS ===
    10590:{'desc':'Alugueis de Veiculos (MOS)',              'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    10575:{'desc':'Assistencia Medica (MOS)',                'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    10586:{'desc':'Combustiveis e Lubrificantes (MOS)',      'cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'combustivel','acumulador':8999,'cst_piscof':'50','base_credito_campo67':'13','vinculo_credito_0115':'01',},
    10576:{'desc':'Cursos e Treinamentos (MOS)',             'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    10577:{'desc':'EPI (MOS)',                               'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8715,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    10582:{'desc':'Ferramentas (MOS)',                       'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8714,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    10571:{'desc':'PAT (MOS)',                               'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':False,'familia':'uso_consumo','acumulador':8716,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    10578:{'desc':'Uniformes (MOS)',                         'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8724,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    10585:{'desc':'Viagens e Estadias (MOS)',                'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    # === CUSTOS COM SERVICOS PRESTADOS ===
    12724:{'desc':'Combustiveis e Lubrificantes (CSP)',      'cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'combustivel','acumulador':8718,'cst_piscof':'50','base_credito_campo67':'13','vinculo_credito_0115':'01',},
    12726:{'desc':'Consumo Material Uso/Consumo (CSP)',      'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8717,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    8890: {'desc':'Locacao de Terceiros (CSP)',              'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    12722:{'desc':'Seguranca e Medicina do Trabalho (CSP)',  'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    # === DESPESAS ADMINISTRATIVAS ===
    469:  {'desc':'Agua e Esgoto',                          'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':False,'familia':'sem_cfop','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    460:  {'desc':'Assistencia Medica e Social (ADM)',       'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    507:  {'desc':'Bens de Pequeno Valor (ADM)',             'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8020,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    16153:{'desc':'Cesta Basica (ADM)',                      'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8025,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    458:  {'desc':'Cestas Basicas (ADM)',                    'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    477:  {'desc':'Combustiveis e Lubrificantes (ADM)',      'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':False,'familia':'combustivel','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    488:  {'desc':'Confraternizacoes (ADM)',                 'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8729,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    472:  {'desc':'Consumo Material de Escritorio (ADM)',    'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8010,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    474:  {'desc':'Consumo Material de Limpeza (ADM)',       'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8013,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    473:  {'desc':'Consumo Material Uso/Consumo (ADM)',      'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8006,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    483:  {'desc':'Consumo de Remedios (ADM)',               'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8026,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    2866: {'desc':'Copa e Cozinha (ADM)',                    'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8015,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    481:  {'desc':'Dispendios com Alimentacao (ADM)',        'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8019,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    470:  {'desc':'Energia Eletrica (ADM)',                  'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':False,'familia':'energia','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    494:  {'desc':'Manutencao de Sistemas Operacionais(ADM)','cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8720,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    475:  {'desc':'Manutencao e Conservacao Predial (ADM)',  'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8009,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    480:  {'desc':'Materiais de Informatica (ADM)',          'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8018,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    457:  {'desc':'PAT (ADM)',                               'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8006,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    3419: {'desc':'Seguranca e Medicina do Trabalho (ADM)', 'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8016,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    # === DESPESAS COM VENDAS ===
    578:  {'desc':'Brindes (VEN)',                           'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8027,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    566:  {'desc':'Combustiveis e Lubrificantes (VEN)',      'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':False,'familia':'combustivel','acumulador':8028,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    584:  {'desc':'Comissoes sem Vendas (VEN)',              'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8007,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    577:  {'desc':'Confraternizacoes (VEN)',                 'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8022,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    582:  {'desc':'Feiras-Congressos-Cursos (VEN)',          'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8730,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    543:  {'desc':'PAT (VEN)',                               'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8999,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    587:  {'desc':'Propagandas e Publicidades (VEN)',        'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'uso_consumo','acumulador':8721,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    # === IMOBILIZADO ===
    133:  {'desc':'Benfeitorias em Propriedades',            'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'imobilizado','acumulador':8551,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    3359: {'desc':'Equipamentos de Seguranca',               'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'imobilizado','acumulador':8551,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    6299: {'desc':'Equipamentos para Processamento de Dados','cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'imobilizado','acumulador':8551,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    6302: {'desc':'Instalacoes',                             'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'imobilizado','acumulador':8551,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    137:  {'desc':'Maquinas e Equipamentos',                 'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'imobilizado','acumulador':8551,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    141:  {'desc':'Moldes e Ferramentas',                    'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'imobilizado','acumulador':8551,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    135:  {'desc':'Moveis e Utensilios',                     'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':True,'familia':'imobilizado','acumulador':8551,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
    # === DEVOLUCOES ===
    358:  {'desc':'Devolucoes de Vendas',                    'cred_icms':True,'cred_ipi':True,'cred_piscof':True,'deb_difal':False,'familia':'devolucao','acumulador':8202,'cst_piscof':'50','base_credito_campo67':'12','vinculo_credito_0115':'01',},
    # === REMESSAS ===
    11088:{'desc':'Remessas em Geral',                       'cred_icms':False,'cred_ipi':False,'cred_piscof':False,'deb_difal':False,'familia':'sem_cfop','acumulador':8949,'cst_piscof':'70','base_credito_campo67':None,'vinculo_credito_0115':None,},
}

# =====================================================================
# Mapeamento CFOP saida do fornecedor => tem ST (True) ou nao (False)
# =====================================================================
CFOP_SAIDA_TEM_ST = {
    # SEM ST
    '5101':False,'5102':False,'5103':False,'5104':False,'5105':False,
    '5108':False,'5109':False,'5110':False,'5111':False,'5112':False,
    '5113':False,'5114':False,'5115':False,'5116':False,'5117':False,
    '5118':False,'5119':False,'5120':False,'5122':False,'5123':False,
    '5124':False,'5125':False,
    '6101':False,'6102':False,'6103':False,'6104':False,'6105':False,
    '6106':False,'6107':False,'6108':False,'6109':False,'6110':False,
    '6111':False,'6112':False,'6113':False,'6114':False,'6115':False,
    '6116':False,'6117':False,'6118':False,'6119':False,'6120':False,
    '6122':False,'6123':False,'6124':False,'6125':False,
    '3101':False,'3102':False,
    '5551':False,'5552':False,'6551':False,'6552':False,
    '5556':False,'6556':False,
    '5653':False,'6653':False,
    '5201':False,'5202':False,'6201':False,'6202':False,
    '1201':False,'1202':False,'1410':False,'2410':False,
    '5252':False,'6252':False,
    '5902':False,'5903':False,'6902':False,'6903':False,  # retorno industrializacao
    # COM ST
    '5401':True,'5402':True,'5403':True,'5405':True,
    '6401':True,'6402':True,'6403':True,'6405':True,
    '5407':True,'6407':True,
}

# =====================================================================
# Mapeamento direto: CFOP saida => CFOP entrada
# Para CFOPs que nao seguem a logica de familia (industrializacao, etc.)
# =====================================================================
CFOP_DIRETO = {
    # Industrializacao
    '5124': '1124',  # industrializacao efetuada
    '6124': '2124',
    '5125': '1125',
    '6125': '2125',
    '5901': '1901',  # remessa p/ industrializacao
    '6901': '2901',
    '5902': '1902',  # retorno de industrializacao
    '6902': '2902',
    '5903': '1903',
    '6903': '2903',
    # Devolucao
    '5201': '1201',
    '5202': '1202',
    '6201': '2201',
    '6202': '2202',
    # Outras saidas / operacoes diversas
    '5949': '1949',  # outras saidas (remessa, bonificacao, etc.)
    '6949': '2949',
    # Retorno de mercadoria / devol. de remessa p/ industria
    '5908': '1908',  # retorno de remessa p/ industrializacao por encomenda
    '6908': '2908',
    '5911': '1911',  # retorno de remessa p/ industrializacao por conta e ordem
    '6911': '2911',
    '5915': '1915',  # retorno de remessa de mercadoria
    '6915': '2915',
    # Prestacao de servico / energia / comunicacao
    '5656': '1653',  # energia eletrica para uso e consumo -> entrada energia
    # Importacao
    '3101': '3101',
    '3102': '3102',
}

def tem_st_cfop(cfop_saida):
    return CFOP_SAIDA_TEM_ST.get(str(cfop_saida).strip(), False)

def resolver_cfop(cod_etiqueta, cfop_saida, uf_emitente):
    """
    Retorna o CFOP de entrada correto dado:
      - cod_etiqueta : codigo numerico da etiqueta
      - cfop_saida   : CFOP que o fornecedor usou na NF (ex: '5101')
      - uf_emitente  : UF do emitente (ex: 'SP', 'MG', 'PA')
    """
    cfop_saida = str(cfop_saida).strip()

    # 1. Mapeamento direto tem prioridade (industrializacao, retorno, etc.)
    if cfop_saida in CFOP_DIRETO:
        return CFOP_DIRETO[cfop_saida]

    etiq = TABELA_ETIQUETAS.get(cod_etiqueta)
    if not etiq:
        return '1101'  # fallback

    familia = etiq['familia']
    dentro  = uf_emitente.upper() == 'SP'
    st      = tem_st_cfop(cfop_saida)

    if familia == 'compra':
        if dentro: return '1401' if st else '1101'
        else:      return '2401' if st else '2101'

    elif familia == 'imobilizado':
        if dentro: return '1406' if st else '1551'
        else:      return '2406' if st else '2551'

    elif familia == 'uso_consumo':
        if dentro: return '1407' if st else '1556'
        else:      return '2407' if st else '2556'

    elif familia == 'energia':
        return '1252' if dentro else '2252'

    elif familia == 'combustivel':
        return '1653' if dentro else '2653'

    elif familia == 'industrializacao':
        # Fallback para industrializacao nao mapeada diretamente
        if dentro: return '1124'
        else:      return '2124'

    elif familia == 'devolucao':
        if dentro: return '1201'
        else:      return '2410'

    elif familia == 'importacao':
        return cfop_saida  # usa o da NF propria

    elif familia == 'sem_cfop':
        return ''

    return '1101'

if __name__ == '__main__':
    testes = [
        (8647,'5101','SP','1101'),(8647,'5101','MG','2101'),
        (8647,'5401','SP','1401'),(8647,'5401','PA','2401'),
        (8647,'6403','MG','2401'),(8647,'6101','ES','2101'),
        (10577,'6108','PA','2556'),  # EPI fora do estado, sem ST
        (8681,'5124','SP','1124'),   # industrializacao
        (8681,'5902','SP','1902'),   # retorno industrializacao
        (133, '5551','SP','1551'),(133,'5551','MG','2551'),
        (8758,'5252','SP','1252'),(9062,'6653','MG','2653'),
        (12726,'6403','PA','2407'),  # uso_consumo fora, com ST
    ]
    print("=== Teste de resolucao de CFOP ===")
    ok = 0
    for etiq, cfop_s, uf, esperado in testes:
        resultado = resolver_cfop(etiq, cfop_s, uf)
        status = 'OK' if resultado == esperado else 'FALHOU'
        if status == 'OK': ok += 1
        print(f"  [{status}] etiq={etiq} cfop={cfop_s} UF={uf} => {resultado} (esperado {esperado})")
    print(f"\n{ok}/{len(testes)} testes passaram.")
