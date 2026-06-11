# -*- coding: utf-8 -*-
"""
Modulo: leitura do CSV de rateio de multiplas etiquetas
Formato esperado:
  Chave;Data de Emissao;Data de Entrada;Valor Contabil;Etiqueta;Valor Etiqueta
  ="35260..."  ;dd/mm/aaaa;dd/mm/aaaa;1.915,64;8758 - Energia;1.340,95
"""
import csv, re, io

def limpar_chave(valor):
    """Remove formatacao Excel ='' e aspas da chave da NF"""
    v = str(valor).strip()
    # Remove ="..." ou "..." ou =""..."""
    v = re.sub(r'^[="]+"?', '', v)
    v = re.sub(r'""+$', '', v)
    v = v.replace('"','').strip()
    return v

def limpar_valor(valor):
    """Converte 1.915,64 ou 1915,64 para float"""
    v = str(valor).strip().replace('.','').replace(',','.')
    try: return float(v)
    except: return 0.0

def limpar_data(valor):
    """Retorna data como dd/mm/aaaa — ja vem neste formato"""
    return str(valor).strip()

def limpar_etiqueta_cod(valor):
    """Extrai codigo numerico da etiqueta: '8758 - Energia' -> 8758"""
    m = re.match(r'^\s*(\d+)', str(valor))
    return int(m.group(1)) if m else None

def ler_csv_rateio(zip_file, nome_csv='_resumo_etiquetas.csv'):
    """
    Le o CSV de rateio de dentro do ZIP.
    Retorna dict:
      chave_nfe -> {
        'dt_emissao': 'dd/mm/aaaa',
        'dt_entrada': 'dd/mm/aaaa',
        'valor_contabil': float,
        'etiquetas': [
          {'cod': int, 'desc': str, 'valor': float, 'pct': float},
          ...
        ]
      }
    """
    import zipfile
    resultado = {}

    with zipfile.ZipFile(zip_file,'r') as zf:
        # Encontrar o CSV (pode estar na raiz ou em subpasta)
        csvs = [n for n in zf.namelist()
                if n.lower().endswith('.csv') and '_resumo' in n.lower()]
        if not csvs:
            csvs = [n for n in zf.namelist() if n.lower().endswith('.csv')]
        if not csvs:
            print("AVISO: CSV de rateio nao encontrado no ZIP")
            return {}

        csv_name = csvs[0]
        content  = zf.read(csv_name).decode('utf-8-sig', errors='replace')

    reader = csv.DictReader(io.StringIO(content), delimiter=';')

    for row in reader:
        chave   = limpar_chave(list(row.values())[0])
        if len(chave) != 44:
            continue  # linha invalida

        dt_emissao = limpar_data(list(row.values())[1])
        dt_entrada = limpar_data(list(row.values())[2])
        vCont      = limpar_valor(list(row.values())[3])
        etiq_str   = list(row.values())[4]
        vEtiq      = limpar_valor(list(row.values())[5])
        cod_etiq   = limpar_etiqueta_cod(etiq_str)

        if chave not in resultado:
            resultado[chave] = {
                'dt_emissao'   : dt_emissao,
                'dt_entrada'   : dt_entrada,
                'valor_contabil': vCont,
                'etiquetas'    : [],
            }

        resultado[chave]['etiquetas'].append({
            'cod'  : cod_etiq,
            'desc' : str(etiq_str).strip(),
            'valor': vEtiq,
            'pct'  : 0.0,  # calculado abaixo
        })

    # Calcular percentuais
    for chave, dados in resultado.items():
        total = sum(e['valor'] for e in dados['etiquetas'])
        for e in dados['etiquetas']:
            e['pct'] = round(e['valor'] / total, 10) if total > 0 else 0.0

    return resultado


if __name__ == '__main__':
    rateio = ler_csv_rateio('/mnt/user-data/uploads/nfe_20260520_122405.zip')
    print(f"Total de NFs com multiplas etiquetas: {len(rateio)}\n")
    for chave, dados in rateio.items():
        print(f"Chave: {chave}")
        print(f"  Emissao: {dados['dt_emissao']} | Entrada: {dados['dt_entrada']}")
        print(f"  Valor contabil: R$ {dados['valor_contabil']:.2f}")
        soma = sum(e['valor'] for e in dados['etiquetas'])
        print(f"  Etiquetas ({len(dados['etiquetas'])}) — soma: R$ {soma:.2f}")
        for e in dados['etiquetas']:
            print(f"    [{e['cod']}] {e['desc'][:45]} "
                  f"R$ {e['valor']:.2f} ({e['pct']*100:.2f}%)")
        diff = abs(dados['valor_contabil'] - soma)
        print(f"  Diferenca: R$ {diff:.2f} {'OK' if diff < 0.02 else 'DIVERGE!'}")
        print()
