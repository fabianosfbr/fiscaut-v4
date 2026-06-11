# PROJETO: Fiscaut x Dominio - Importacao NF-e Entrada
## Documento de Regras e Controle de Versoes

**Empresa destino (producao):** Kopron do Brasil - CNPJ 10251329000189
**Empresa de teste:** CNPJ 99999999000191 (usar enquanto estivermos em fase de testes)
**Modulo:** Dominio Escrita Fiscal - Importacao Padrao com Separador (|)
**Encoding do arquivo:** latin-1 / ANSI (Windows-1252)
**Ultima atualizacao:** 29/05/2026 (v22r — acumuladores atualizados para numeracao de producao Kopron)

---

## 1. REGISTROS GERADOS

| Registro | Descricao                          | Status                                                         |
|----------|------------------------------------|----------------------------------------------------------------|
| 0000     | Identificacao empresa              | OK                                                             |
| 0020     | Cadastro fornecedor                | OK                                                             |
| 0100     | Cadastro produto                   | OK — chave inclui uCom (v22b)                                  |
| 0110     | Produto - vigencia                 | NAO GERAR (aguardar integracao RAGNAI)                         |
| 0120     | Produto - unidades comercializadas | NAO GERAR (suspenso — revisao futura)                          |
| 0135     | Produto - valor unitario           | NAO GERAR (suspenso — revisao futura)                          |
| 0150     | Produto - unidade de medida        | OK                                                             |
| 1000     | Capa da NF                         | OK — campos 11/16/39/90 implementados (v22)                    |
| 1010     | Informacoes complementares         | OK                                                             |
| 1015     | Observacoes ao fisco               | A avaliar                                                      |
| 1020     | Impostos da NF                     | OK                                                             |
| 1030     | Itens da NF                        | OK — 111 campos fixos, IBS/CBS do XML (v22d/v22e/v22f)         |
| 1200     | Simples Nacional                   | OK — leiaute corrigido, base/aliq/valor por XML (v22e)         |
| 1500     | Parcelas                           | OK                                                             |

---

## 2. REGRAS DEFINIDAS (OFICIAIS)

### 2.1 Geral
- Separador: pipe `|`
- Encoding: latin-1 (ANSI / Windows-1252)
- **Separador decimal: virgula `,`** (ex: 1061,00 — NUNCA ponto)
- **Pipe `|` encontrado em qualquer texto do XML deve ser substituido por `-`**
- Especie NF-e mod 55 no Dominio: **36** (campo 2 do registro 1000)
- **CNPJ no 0000:** usar `99999999000191` durante testes; trocar para CNPJ real em producao

### 2.2 Registro 0020 - Fornecedor
- Gerar para todo CNPJ emitente encontrado nos XMLs
- Regime tributario: CRT=1 => M (Simples/Microempresa), CRT=3 => N (Normal)
- DDD: 2 primeiros digitos do fone; Telefone: restante

### 2.3 Registro 0100 - Produto
- **NAO duplicar produto**
- **Chave de unicidade (v22h):** raiz CNPJ emitente (8 digitos) + cProd + xProd + **uCom.upper()**
  - uCom normalizado para maiusculas antes de entrar na chave (v22h)
  - "Un" e "UN" do mesmo produto → mesmo registro no 0100 (evita duplicata + EAN conflitante)
  - Mesmo produto com unidades DISTINTAS (KG vs M) ainda gera 0100 separados
- **Campo 02** (codigo, max 14 char): `raizCNPJ_sequencial` (ex: `14525282_001`)
- **Campo 05** (NCM): NCM do XML
- **Campo 09** (grupo): `1` — REVISAO FUTURA (ver backlog)
- **Campo 10** (unidade): uCom real do XML
- **Campo 11** (unid inv diferente): `S` — permite reimportacao sem rejeicao quando produto ja cadastrado com outra unidade no Dominio
- **Campo 12** (tipo produto): `O` (Outros)
- **Campo 16** (ISSQN): `N`
- **Campo 18** (valor unitario): vUnCom do XML (virgula, 3 casas)
- **Campo 24** (periodicidade IPI): `M` (mensal)
- **Campo 91** (identificador): **sempre o codigo interno** (campo 02 = raizCNPJ_seq)
  - EAN nunca é usado no campo 91 — evita conflitos em reimportacoes e EANs duplicados de fornecedores
  - Razao: Dominio rejeita se o EAN ja existe para outro produto cadastrado (lotes anteriores)
  - Razao: fornecedores frequentemente tem mesmo EAN em produtos com unidades diferentes
- **NAO gerar 0110** (aguardar RAGNAI)

### 2.4 Registro 0120 - NAO GERAR
- **Suspenso:** erro "unidade comercializada nao pode ser a mesma informada como inventariada"
- Revisao futura para tratar a logica correta deste registro

### 2.5 Registro 0135 - NAO GERAR
- **Suspenso:** erro "data do valor unitario invalida"
- Regra ja definida para quando retomar: data = 1o dia do mes da NF mais antiga do lote

### 2.6 Registro 0150 - Unidade de Medida
- Uma linha por sigla unica encontrada no lote
- Descricao: UN=UNIDADE, PC=PECA, KG=QUILOGRAMA, etc.

### 2.7 Registro 1000 - Capa da NF (campos oficiais)
- **Campo 02** (especie): `36`
- **Campo 03** (CNPJ fornecedor): CNPJ emitente do XML
- **Campo 05** (acumulador): definido pela tabela de etiquetas
- **Campo 06** (CFOP): CFOP de entrada resolvido pela tabela de etiquetas
- **Campo 07** (segmento): numero do segmento dentro da NF
  - Campo descontinuado pela importacao no Dominio (nao ordena segmento)
  - **`0`** quando a NF tem apenas 1 segmento (sem segmentacao de CFOP)
  - **`1`, `2`, `3`...** quando a NF tem mais de um segmento (multiplos CFOPs)
- **Campo 08** (numero NF): nNF
- **Campo 09** (serie): serie da NF
- **Campo 11** (data entrada): **data de entrada do CSV** (prioridade) ou data emissao do XML como fallback
  - A data de entrada e lida do `_resumo_etiquetas.csv` para TODA NF, inclusive as de pasta unica (v22c)
- **Campo 12** (data emissao): data emissao do XML (dd/mm/aaaa)
- **Campo 13** (valor contabil): vContabil do segmento (vProd + vFrete + vSeg + vOutro + **vIPI** - vDesc)
  - **IPI soma no valor contabil** (v22c) — corrige NFs com IPI destacado
- **Campo 15** (informações ao fisco): `infAdFisco` do XML — pipe substituído por `-` via limpar()
- **Campo 16** (modalidade frete): lido da tag `<modFrete>` do XML (v22)
  - Mapa: 0=C (CIF), 1=F (FOB), 2=T (Terceiros), 3=R (Trans.Prop.Rem.), 4=D (Trans.Prop.Dest.), 9=S (Sem frete)
  - Fallback: `C` se a tag nao existir
- **Campo 17** (emitente): `T` (Terceiros)
- **Campo 26** (frete): vFrete do segmento
- **Campo 27** (seguro): vSeg do segmento
- **Campo 28** (desconto): vDesc do segmento
- **Campo 29** (outras despesas): vOutro do segmento
- **Campo 39** (valor produtos): vProd do segmento (v22)
- **Campo 41** (situação do documento): código baseado em `finNFe` do XML
  - `finNFe=1` (Normal) → `00` (Documento regular)
  - `finNFe=2` (Complementar) → `06` (Documento Fiscal Complementar)
  - `finNFe=3` (Ajuste) → `08` (Emitido com base em Regime Especial)
  - `finNFe=4` (Devolução) → `00` (Documento regular)
- **Campo 62** (informações complementares): `infCpl` do XML — pipe substituído por `-`
- **Campo 54** (chave NF-e): chave de 44 digitos
- **Campo 90** (valor IPI): vIPI do segmento — preenchido apenas quando vIPI > 0 (v22c)

**Campos 91 (vST) e 97 (vICMSDeson): BACKLOG** — valores ja lidos do XML, mapeamento futuro

### 2.8 Registro 1020 - Impostos
- Sempre gerar codigo 1 (ICMS) e codigo 2 (IPI), mesmo zerados
- **NAO gerar IBS/CBS (128/129)** — acumulador nao configurado

**Regra geral do valor contabil do 1020:**
```
ICMS: vContabil = vBC + vIsentas + vOutras + vST + vIPI_do_segmento (vICMS NAO entra)
IPI:  vContabil = vBC_IPI + vIPI + vIsentas_IPI + vOutras_IPI (vIPI ENTRA)
```

**1020 ICMS (codigo 1) — Mapeamento CST → campos:**

| CST (2 ultimos) | vBC       | vICMS  | vIsentas        | vOutras | vST   |
|-----------------|-----------|--------|-----------------|---------|-------|
| 00 aliq > 0     | vBC XML   | vICMS  | 0               | 0       | 0     |
| 00 aliq = 0     | 0         | 0      | 0               | vProd   | 0     |
| 10, 70          | vBC XML   | vICMS  | 0               | 0       | vST   |
| 20              | vBC efet. | vICMS  | vProd x %redBC  | 0       | 0     |
| 30, 40          | 0         | 0      | vProd           | 0       | 0     |
| 50, 90          | 0         | 0      | 0               | vProd   | 0     |
| 60              | 0         | 0      | 0               | vProd   | 0     |

- Etiqueta DESPESA (cred_icms=False): vBC=0, vICMS=0, vIsentas=0, vOutras=vContabil, vST=0
- CSOSN (Simples Nacional): vBC=0, vICMS=0, vIsentas=0, vOutras=vProd

**1020 IPI (codigo 2):**

| CST IPI   | vBC_IPI | vIPI  | vIsentas | vOutras |
|-----------|---------|-------|----------|---------|
| Tributado | vBC XML | vIPI  | 0        | 0       |
| Isento    | 0       | 0     | vProd    | 0       |
| Outras    | 0       | 0     | 0        | vProd   |

**DIFAL (codigo 8):**
- Quando: CFOP entrada em {2556, 2551, 2406, 2407} + etiqueta deb_difal=True + nao Simples
- Calculo por dentro LC 190/2022: base_dupla = (vBC - vICMS) / (1 - 0,18); difal = base_dupla x 0,18 - vICMS_origem
- Aliquota interna SP: 18% fixo — REVISAO FUTURA por NCM
- Aliquota interestadual: lida do XML; fallback 7% Norte/NE/CO, 12% demais
- NAO reflete no 1030 e NAO soma no total da NF

### 2.9 Registro 1030 - Itens
- **Tamanho fixo: 111 campos** — inicializado com `['']*111` (v22d)
  - Dominio rejeita registros com tamanho diferente do esperado
- Codigo produto: referencia o codigo interno do 0100 (mesma chave com uCom)
- Unidade (campo 57): uCom real do XML — **NUNCA "S"**
- CST/CSOSN (campo 10): CSOSN se Simples (CRT=1), CST se regime normal
- Todos os valores com virgula decimal

**Campos ICMS no 1030 (regra v22e):**

| Campo | Condicao | Valor |
|-------|----------|-------|
| 13 — Base BC ICMS | SN com credito E vCredSN > 0 | vProd do item |
| 13 — Base BC ICMS | SN com credito E vCredSN = 0 | 0,00 |
| 13 — Base BC ICMS | Regime normal cred_icms=True | vBC do item (XML) |
| 13 — Base BC ICMS | cred_icms=False | 0,00 |
| 15 — Aliquota ICMS | SN com credito E vCredSN > 0 | pCredSN do item (ex: 4,44) |
| 15 — Aliquota ICMS | SN com credito E vCredSN = 0 | 0,00 |
| 15 — Aliquota ICMS | Regime normal cred_icms=True | pICMS do item (XML) |
| 15 — Aliquota ICMS | cred_icms=False | 0,00 |
| 22 — Valor ICMS | SN com credito E vCredSN > 0 | vCredICMSSN do item |
| 22 — Valor ICMS | SN com credito E vCredSN = 0 | 0,00 |
| 22 — Valor ICMS | Regime normal cred_icms=True | vICMS do item (XML) |
| 22 — Valor ICMS | cred_icms=False | 0,00 |

**Regra geral campos 6/13/15/22:** espelham o 1020. Se o 1020 nao gera BC/aliq/vICMS, o 1030 tambem nao gera.

**Campos IBS/CBS no 1030 (v22d/v22e — leiaute oficial confirmado):**

| Campo | Indice | Descricao | Valor atual |
|-------|--------|-----------|-------------|
| 104 | 103 | IBS — cClass Trib (Caractere) | Lido de `IBSCBS/cClassTrib` do XML (ex: '000001', '550007', '620006') |
| 105 | 104 | IBS — Base de calculo | Lido de `IBSCBS/gIBSCBS/vBC` |
| 106 | 105 | IBS — Aliquota | Calculado: `pIBSUF + pIBSMun` (de `gIBSCBS/gIBSUF` e `gIBSMun`) |
| 107 | 106 | IBS — Valor | Lido de `IBSCBS/gIBSCBS/vIBS` |
| 108 | 107 | CBS — cClass Trib (Caractere) | Mesmo cClassTrib do IBS |
| 109 | 108 | CBS — Base de calculo | Mesma vBC do IBS |
| 110 | 109 | CBS — Aliquota | Lido de `IBSCBS/gIBSCBS/gCBS/pCBS` |
| 111 | 110 | CBS — Valor | Lido de `IBSCBS/gIBSCBS/gCBS/vCBS` |

**Estrutura XML do bloco IBSCBS (NF-e):**
- `IBSCBS/CST` — situacao tributaria IBS/CBS
- `IBSCBS/cClassTrib` — codigo de classificacao tributaria
- `IBSCBS/gIBSCBS` — grupo principal (CST 000-499)
  - `vBC` — base de calculo compartilhada IBS e CBS
  - `gIBSUF/pIBSUF` + `gIBSMun/pIBSMun` — aliquotas IBS
  - `vIBS` — valor IBS total
  - `gCBS/pCBS` + `gCBS/vCBS` — aliquota e valor CBS
- `IBSCBS/gIBSCBSMono` — variante monofasica (CST 6xx) — campos zerados por ora
- Quando o XML nao tem bloco IBSCBS (ex: SN): campos ficam vazios/zero

**Campo 56 (movimentacao fisica):** `S` — REVISAO FUTURA

**Outros campos notaveis:**
- Campo 04: vProd do item (Base Cal. + IPI conforme leiaute)
- Campo 34: CFOP de entrada do segmento
- Campo 40: vContabil do item (vProd + vFrete + vSeg + vOutro - vDesc)
- Campo 57: uCom real do XML
- Campo 60: vContabil do item (idem campo 40)
- Campo 67: base do credito PIS/COFINS (01-18 — apenas quando cred_piscof=True)

### 2.10 Registro 1200 - Simples Nacional (v22e — leiaute e logica corrigidos)

**Leiaute correto: apenas 4 campos**

| Campo | Descricao | Valor |
|-------|-----------|-------|
| 1 | Identificacao | `1200` |
| 2 | Base de calculo ICMS SN | sum(vProd) dos itens onde vCredICMSSN > 0 |
| 3 | Aliquota ICMS SN | vCred / base x 100 (calculo reverso — arredondado 2 casas) |
| 4 | Valor ICMS SN | sum(vCredICMSSN) de todos os itens |

**Regras:**
- Gerar para toda NF de fornecedor Simples (CRT=1) — com ou sem credito
- **Posicao no arquivo: dentro do segmento** — gerado imediatamente apos os 1030s do segmento que contem os itens com vCredSN > 0
  - NF com multiplos CFOPs (ex: 1124+1902): 1200 gerado apenas no segmento 1124 (com credito), nao no 1902 (retorno)
  - NF com 1 CFOP: 1200 gerado normalmente apos os 1030s do unico segmento
- **Base** = soma apenas de vProd dos itens do segmento que possuem `vCredICMSSN > 0`
  - Itens sem vCredSN (ex: retorno de industrializacao, insumos sem credito) NAO entram na base
- **Aliquota** = calculada reversamente a partir do XML (nao copiada do texto da NF)
  - Posicao pro-fisco: usar o que esta nas tags estruturadas da NF (assinado digitalmente)
- **Validacao automatica:** se aliq calculada divergir > 0,10% do infCpl → log de aviso no processamento
  - O valor do XML prevalece mesmo com divergencia
  - Casos reais do lote: NF 981 (XML 4,44% vs infCpl 3,85%) e NF 24297 (XML 5,17% vs infCpl 3,58%)
- **ATENCAO — solucao temporaria (v22q):** o campo "ICMS SN" do 1030 aba Estoque nao tem disponivel no leiaute Dominio
  - **SN com credito (cred_icms=True):** credito vai no 1020 (BC+aliq+val), 1200 NAO gerado (evita duplicidade)
  - **SN sem credito:** 1200 zerado gerado normalmente no primeiro segmento
  - Backlog: quando Dominio liberar o campo no leiaute, retomar o 1200 com credito e limpar o 1020

**Como o 1030 reflete o 1200:**
- Campo 13 (BC) = vProd do item — apenas itens com vCredSN > 0
- Campo 15 (aliq) = pCredSN do item (lido da tag `<pCredSN>` do ICMSSN101/201/900)
- Campo 22 (val) = vCredICMSSN do item
- Itens sem vCredSN: campos 13/15/22 = 0,00
- Rateio por multiplas etiquetas: cada segmento usa os valores proporcionais ao rateio

**Nota sobre o XML dos fornecedores SN:**
- Alguns fornecedores emitem o XML matematicamente correto (vCredSN = vProd x pCredSN)
  mas com textos em infCpl/infAdFisco calculados por outro sistema, gerando divergencias
- A fonte de verdade e sempre o XML estruturado (assinado pela SEFAZ)
- infCpl e infAdFisco sao texto livre sem validade fiscal para sobrepor as tags

### 2.11 Registro 1500 - Parcelas
- **Campo 01** (ident): `1500`
- **Campo 02** (vencimento): dd/mm/aaaa
- **Campo 03** (valor): valor da duplicata com virgula decimal
- **Campos 04 a 13** (retencoes): `0,00` (zerados — sem retencoes por enquanto)
- **Campo 14** (numero do titulo): nDup do XML

---

## 3. PROBLEMAS JA RESOLVIDOS

| Problema | Solucao aplicada |
|----------|-----------------|
| Produto duplicado no 0100 | Chave unica raizCNPJ+cProd+xProd |
| Conflito de codigo SPED | Campo 91: EAN ou codigo interno |
| Acentuacao quebrada | Encoding latin-1 + substituicao de caracteres |
| Registro 0110 erro | Removido (aguardar RAGNAI) |
| Unidade "S" no 1030 | Sempre usa uCom real do XML |
| Grupo/tipo produto obrigatorios no 0100 | grupo=1, tipo=O, ISSQN=N, periodicidade=M |
| Campos 0100 fora de posicao | Remapeados conforme leiaute oficial |
| Campos 1000 fora de posicao | Remapeados conforme leiaute oficial |
| Valor contabil com ponto decimal | Todos os valores usam virgula decimal |
| Pipe no texto XML quebra leitura | Substituido por `-` antes de gravar |
| 0120 erro unidade inventariada | Registro suspenso (revisao futura) |
| 0135 erro data invalida | Registro suspenso (revisao futura) |
| Campo 56 do 1030 com valor invalido | Preenchido com `S` (provisorio) |
| CST PIS/COFINS obrigatorios | CST 50 com credito / 70 sem credito (v20) |
| 1500 campos fora de ordem | Remapeado: venc-valor-retencoes(0)-nroTitulo |
| CST 61/62 monofasico | vOutras no 1020; vICMSMonoRet no 1030 informativo |
| 1030 acumulador em campo errado | Acumulador NAO existe no 1030 — definido no 1000 |
| CFOP nao definido — 5949/6949 | Adicionados ao CFOP_DIRETO: 5949->1949, 6949->2949 (v22b) |
| Unidade comercializada x inventariada | Chave 0100 inclui uCom — gera registros separados por unidade (v22b) |
| 1000 campo 11 data entrada igual emissao | Leitura da data de entrada do CSV para toda NF, inclusive pasta unica (v22c) |
| 1000 campo 39 valor produtos vazio | Preenchido com vProd do segmento (v22) |
| 1000 campo 16 frete fixo em C | Lido da tag modFrete do XML com mapa 0/1/2/3/4/9 (v22) |
| vContabil sem IPI | IPI soma no vContabil do segmento e do campo 13 do 1000 (v22c) |
| 1000 campo 90 IPI nao gerado | Preenchido com vIPI do segmento quando > 0 (v22c) |
| 1030 campos 13/15/22 com ICMS quando 1020 nao tem | Zerados quando cred_icms=False ou SN sem vCredSN (v22c/v22e) |
| 1030 com 111 campos quebrando parse do Dominio | Inicializado com ['']*111 fixo — eliminado o while de expansao (v22d) |
| IBS/CBS campos errados no 1030 | Reposicionados: 104=cClass(Char), 105=BC, 106=Aliq, 107=Val IBS; 108-111 CBS (v22d) |
| 1200 leiaute errado (6 campos com valor errado) | Corrigido para 4 campos: base/aliq/valor conforme leiaute oficial (v22e) |
| 1200 base = vNF total (inclui itens sem credito) | Base = sum(vProd) apenas de itens com vCredSN > 0 (v22e) |
| 1200 aliquota nao calculada | Calculada reversamente: vCred/base x 100 (v22e) |
| 1030 campos 13/15 zerados para SN com credito | BC=vProd, aliq=pCredSN por item elegivel (v22e) |
| DIFAL nao gerado para fornecedor SN interestadual | Removida restricao not is_simpl; SN uso/consumo fora do estado gera DIFAL normalmente (v22g) |
| Campo 91 com EAN causava rejeicao em reimportacao e EANs duplicados | Campo 91 sempre usa codigo interno (raizCNPJ_seq) — nunca EAN (v22h) |
| Produto cadastrado com uCom diferente rejeita a NF inteira | Nos testes: usar opcao "sobrescrever cadastro" na importacao; em producao: avaliar (backlog 4.4b) |
| uCom "Un" vs "UN" gerava dois 0100 com mesmo EAN | uCom normalizado para maiusculas na chave de unicidade (v22h) |
| CFOPs 6908/6911/6915/5656 sem mapeamento | Adicionados ao CFOP_DIRETO: 908->1908/2908, 911->1911/2911, 915->1915/2915, 5656->1653 (v22h) |
| 1000 campo 7 sempre preenchido com n_seg | Campo 7=0 para NF sem segmentacao; n_seg sequencial apenas quando >1 CFOP (v22f) |
| 1200 gerado fora do segmento correto (NF 10639: era no 1902) | Movido para dentro do loop de segmentos — gerado apenas no segmento com vCredSN>0 (v22f) |
| IBS/CBS campos 104-111 vazios/zerados | Preenchidos com dados reais do bloco IBSCBS do XML: cClassTrib, BC, aliq, val (v22f) |

---

## 3.5 REGRAS DO REGISTRO 1020 — IMPOSTOS (DETALHAMENTO)

### Classificacao Dominio: Lancados x Calculados
- **Lancados** (vem do XML): codigo 1=ICMS, codigo 2=IPI, codigo 8=DIFAL
- **Calculados** (apurados): PIS, COFINS, IRPJ, CSLL, etc.

### Nota sobre CST: sempre 3 digitos, os dois ultimos definem a regra
- 000 = x00, 100 = x00 (mesmo comportamento)
- Regra sempre pelos 2 ultimos digitos do CST

### Consistencia obrigatoria 1000 x 1020 x 1030
O que esta na capa (1000/1020) DEVE ser identico ao registro 1030 (estoque).
Obrigatorio para SPED Fiscal. Validar que vContabil do 1020 = vContabil do 1030.

---

## 3.6 REGRAS PIS/COFINS — REGISTRO 1030

### Onde ocorre
- PIS/COFINS **apenas no 1030** — nunca no 1020
- Dominio valida internamente pelo acumulador se ha debito ou credito

### Regime tributario (Kopron = Lucro Real)
- PIS: imposto 17 (nao cumulativo) | COFINS: imposto 19 (nao cumulativo)
- Aliquotas: PIS 1,65% | COFINS 7,6%
- Base de calculo: calculada por nos (nao copiada do XML)
- Excecao: NF de importacao (CFOP 3101/3102) → usar valores do XML

### CST PIS/COFINS
- `cred_piscof=True`  → CST **50** (operacao com direito a credito)
- `cred_piscof=False` → CST **70** (sem incidencia / sem direito a credito)

### Modulo ncm_piscof — 3 camadas de validacao
- Camada 1 — Etiqueta: cred_piscof=False → CST 70, encerra
- Camada 2 — NCM: ALIQUOTA_ZERO / MONOFASICO / SUSPENSAO / ISENCAO → CST 73
- Camada 3 — Credito normal: CST 50, aliq 1,65%/7,6%, campo 67 correto

### Codigo de credito (campo 67 do 1030 — apenas CST 50)
| Codigo | Descricao EFD | Etiquetas |
|--------|--------------|-----------|
| 101 | Aquisicao bens para revenda | revenda |
| 102 | Aquisicao bens para fabricacao | 8647, 8655, 8664, 8784 |
| 103 | Servicos para fabricacao | 8681 |
| 104 | Energia eletrica | 8758 |
| 108 | Credito de devolucao de vendas | 358 |
| 109 | Outros com direito a credito | 8719, 9062, 9059, 10586, 12724 |

---

## 4. BACKLOG OFICIAL

### 4.1 Campos provisorios (funcionando, valor fixo)
- [ ] **1030 campo 56** — Movimentacao fisica: fixo em `S`; revisar por tipo de operacao
- [ ] **0100 campo 09** — Grupo do produto: fixo em `1`; definir grupos no Dominio
- [ ] **Registros 0120 e 0135** — suspensos; retomar quando definir logica correta

### 4.2 Campos backlog lidos do XML mas ainda sem mapeamento no 1000
Os seguintes campos ja sao lidos e rateados por item — apenas falta definir o mapeamento no registro 1000:

| Campo 1000 | Tag XML | Chave no item | Observacao |
|------------|---------|--------------|------------|
| 91 (vST) | `vST` (ICMSTot) | `icms_vST` | ICMS Substituicao Tributaria total |
| 97 (vICMSDeson) | `vICMSDeson` | `vICMSDeson` | Valor desonerado |
| — | `vII` | `vII` | Imposto de Importacao |
| — | `vPISST` | `vPISST` | PIS Substituicao Tributaria |
| — | `vCOFINSST` | `vCOFINSST` | COFINS Substituicao Tributaria |

### 4.3 Classificacao IBS/CBS no 1030
- [x] **Campos 104-111 IBS/CBS** — IMPLEMENTADOS: lidos do bloco IBSCBS do XML por item (v22f)
  - cClassTrib lido diretamente do XML
  - BC, aliquota e valor lidos das tags gIBSCBS/vBC, gIBSUF, gIBSMun, gCBS
  - NFs sem bloco IBSCBS (ex: SN) ficam com campos vazios/zero
- [ ] **gIBSCBSMono (CST 6xx)** — estrutura monofasica lida mas campos zerados — avaliar mapeamento futuro
- [ ] **gTribRegular dentro de gIBSCBS** — campos de tributacao regular (ex: CST 550) — avaliar mapeamento

### 4.4 Logica fiscal e tributaria
- [ ] **Contas contabeis** — definir plano de contas para 0020 e 0100
- [ ] **Integrar registro 0110 com RAGNAI**
- [ ] **Expandir tabela_ncm Fase 2** — empresas alimenticias, ZFM completo
- [ ] **Aliquota interna SP no DIFAL** — 18% fixo; revisar por NCM via RICMS-SP quando necessario
- [ ] **1020 ICMS/IPI refinamento** — isentas x outras por CST, CST 60, IPI CST completo
- [ ] **1020 PIS/COFINS calculados** — codigos de imposto, formato Dominio
- [ ] **1020 Devolucao sem credito** — fluxo consulta NF origem
- [ ] **Registro 0110** — implementar junto com integracao RAGNAI

### 4.4a Campo ICMS SN no 1030 aba Estoque — aguardando Domínio (BACKLOG)

**Situação:** o campo "ICMS Simples Nacional" da aba Estoque (1030) não possui campo disponível no leiaute de importação do Domínio.

**Solução temporária (v22q):**
- SN com crédito (`cred_icms=True`): crédito registrado no 1020 com BC + alíq + val
- Registro 1200: não gerado para SN com crédito (evita duplicidade de crédito)
- 1030: mantido como está (campos 13/15/22 com pCredSN/vCredSN por item)

**Quando a Domínio liberar o campo:**
- Reverter o 1020 para o comportamento antigo (tudo em outras)
- Reativar o 1200 com base/alíq/valor do crédito
- O campo do 1030 receberá o vCredSN diretamente

**Status:** melhoria já solicitada à Domínio Sistemas.

### 4.4b Conflito de unidade em reimportação (BACKLOG)

**Problema:** produto já cadastrado no Domínio com unidade X; NF nova chega com unidade Y → Domínio rejeita a NF inteira.

**Histórico de tentativas:**
- v22i: campo 11 do 0100 = `'S'` → **não resolveu** (o erro é no 1030, não no 0100)
- v22j: arquivo `unidade_excecoes.csv` (cod_produto;unidade_dominio) → implementado e funcional, mas não testado em produção

**Solução adotada nos testes:** importar com opção "sobrescrever cadastro" no Domínio → zero erros.

**Pendência para produção:**
- [ ] Confirmar se "sobrescrever cadastro" pode ser usado sempre em produção sem perder configurações específicas de produtos já existentes
- [ ] Se não puder sobrescrever: ativar o mecanismo de `unidade_excecoes.csv` (já implementado na v22j) — preencher o arquivo com os produtos conflitantes consultando o Domínio

**Como usar o unidade_excecoes.csv (se necessário):**
1. Domínio rejeita: "produto X não aceita unidade Y"
2. Abre o produto X no Domínio → anota a unidade cadastrada (ex: PC)
3. Adiciona linha no arquivo: `X;PC`
4. Roda novamente → 0100 e 1030 saem com a unidade correta
- O arquivo deve estar na mesma pasta do ZIP ou na pasta do script
- Comentários com `#` são ignorados

### 4.5 Tipos especiais de NF de entrada
- [ ] **NF propria de energia eletrica / CIAP** — tratativa especifica de credito ICMS energia
- [ ] **NF propria de importacao** — fornecedor sem CNPJ, despesas acessorias, II, frete internacional
- [ ] **CT-e** — especie diferente de 36, campos especificos tipo CT-e/referencia
- [ ] **NFS-e (Notas de Servico Tomado)** — ISS, municipio, especie especifica

### 4.6 Segmentacao e validacao
- [x] **NF com mais de um CFOP** — IMPLEMENTADO (segmentacao automatica por cfop_entrada)
- [x] **NF rateada por multiplas etiquetas** — IMPLEMENTADO (CSV rateio + proporcional)
- [ ] **Regra de consistencia de totais** — validar soma de todos os 1000 + filhos = total da NF
- [ ] **Registro 1300 (Lancamentos Contabeis)** — aguardando plano de contas e historicos

---

## 5. ARQUITETURA DO GERADOR

### Fluxo de entrada
```
lote.zip/
  8647 - Materia Prima no Mercado Interno/
    nfe_001.xml
  #Multiplas Etiquetas/
    nfe_002.xml
    _resumo_etiquetas.csv
```
- O codigo da etiqueta e extraido do inicio do nome da pasta
- Cada XML dentro da pasta herda a etiqueta da pasta
- `_resumo_etiquetas.csv` contem: chave_nfe; cod_etiq; valor; dt_emissao; dt_entrada
- Um unico TXT consolidado e gerado com todas as NFs do lote

### Resolucao de CFOP de entrada
1. **CFOP_DIRETO:** mapeamentos fixos independente de familia (5124→1124, 5902→1902, 5949→1949, 6949→2949, etc.)
2. **Familia** da etiqueta + **dentro/fora do estado** (UF emitente vs SP)
3. **ST ou sem ST**: detectado pelo CFOP de saida do fornecedor
4. Resultado: CFOP de entrada correto para o 1000 e 1030

### Segmentacao por CFOP
- Itens com CFOPs de entrada diferentes geram registros 1000 separados
- Valor contabil de cada 1000 = soma dos itens do segmento (incluindo IPI)
- Impostos (1020) e itens (1030) seguem o mesmo segmento

### Rateio de frete/seguro/desconto/outras despesas
- Se informado por item no XML → usa o valor do item
- Se informado apenas no total → rateia proporcionalmente ao vProd de cada item
- Mesma logica aplicada para: vII, vIPI_prod, icms_vST, vICMSDeson, vPISST, vCOFINSST

### Multiplas Etiquetas (pasta #Multiplas Etiquetas)
- CSV formato: `chave_nfe;cod_etiqueta;valor;dt_emissao;dt_entrada`
- Logica: percentual por etiqueta (valor_etiq / vNF_total) aplicado a todos os valores de cada item
- Cada etiqueta gera seus proprios 1000/1020/1030 com as regras fiscais da etiqueta
- Data de entrada lida do CSV mesmo para NFs de pasta unica (v22c)

### Arquivos do gerador
- `gerar_dominio_v22e.py` — gerador principal
- `tabela_etiquetas.py` — tabela etiquetas + CFOP_DIRETO + resolver_cfop()
- `gerar_1020.py` — modulo ICMS/IPI/DIFAL
- `gerar_1030_piscof.py` — modulo PIS/COFINS + IBS/CBS
- `ncm_piscof/` — verificador 3 camadas (tabela_ncm, verificador, excecoes)
- `ler_csv_rateio.py` — leitura CSV multiplas etiquetas

---

## 6. HISTORICO DE VERSOES

| Versao | Data       | Descricao |
|--------|------------|-----------|
| v1-v11 | ChatGPT    | Evolucao inicial (ver historico anterior) |
| v12    | 04/05/2026 | Primeira versao Claude — reconstrucao limpa |
| v13    | 06/05/2026 | Correcao 0100 campos, 0120 estrutura, 0135 data, 1000 remapeado, virgula dec |
| v14    | 06/05/2026 | 0000 CNPJ teste, remove 0120 e 0135, 1500 remapeado (leiaute oficial) |
| v15    | 06/05/2026 | 1030 campos 41 e 43 (CST PIS/COFINS) = 50 provisorio |
| v16    | 06/05/2026 | 1030 campo 56 (movimentacao fisica) = S provisorio |
| v17    | 06/05/2026 | Motor completo: leitura de ZIP por etiqueta, resolucao CFOP, segmentacao NF |
| v18    | 06/05/2026 | SN: 1020 sem cred + 1030 vCredSN + 1200; industrializacao; lote real 10 NFs |
| v19    | 07/05/2026 | DIFAL: leiaute 1020 correto (19 campos), aliq inter por XML ou UF fallback |
| v20    | 14/05/2026 | PIS/COFINS 1030 completo: CST 50/70, aliq calculada, campo 67 correto |
| v20b   | 15/05/2026 | Modulo ncm_piscof: 3 camadas (etiq+NCM+credito), CST 73 para restricoes NCM |
| v21    | 20/05/2026 | Multiplas etiquetas: CSV rateio, rateio proporcional, datas separadas 1000 |
| v21b   | 20/05/2026 | 1030 remapeado: CFOP campo 34, ISSQN campos 31-33, acumulador nao existe 1030 |
| v21c   | 20/05/2026 | CST 61/62 (monofasico retido/diferido): vOutras, vICMSMonoRet informativo |
| v22    | 21/05/2026 | 1000: campo 16 modFrete do XML, campo 39 vProd, leitura vII/vIPI/vICMSDeson/vPISST/vCOFINSST |
| v22b   | 21/05/2026 | 0100 chave inclui uCom (evita conflito unidade); 5949->1949 e 6949->2949 |
| v22c   | 21/05/2026 | 1000 campo 11 data entrada do CSV para pasta unica; vContabil inclui IPI; campo 90 IPI; 1030 ICMS zerado quando sem 1020; IBS/CBS 104-111 estruturados |
| v22d   | 21/05/2026 | 1030 inicializado com 111 posicoes fixas; IBS/CBS reposicionados conforme leiaute oficial (corrige erro "aliquota ICMS invalida") |
| v22e   | 21/05/2026 | 1200 leiaute corrigido (4 campos); base=sum(vProd com vCredSN>0); aliq calculada reversamente; log divergencia >0,10%; 1030 campos 13/15 SN: BC=vProd, aliq=pCredSN por item |
| v22f   | 21/05/2026 | 1000 campo 7=0 para NF sem segmentacao, n_seg para multiplos CFOPs; 1200 dentro do loop de segmentos (apenas no segmento com vCredSN>0); 1030 campos 104-111 IBS/CBS preenchidos do XML (bloco IBSCBS: cClassTrib, BC, aliq, val) |
| v22g   | 21/05/2026 | DIFAL: removida restricao not is_simpl — fornecedor SN interestadual de uso/consumo tambem gera 1020 codigo 8; base = vProd/(1-0,18) pois SN sem icms_origem; NF 5437 RS: base_dup=143,90 aliq=18% DIFAL=25,90 aliq_inter=12% |
| v22h   | 22/05/2026 | 0100 campo 91: sempre codigo interno (nunca EAN); uCom normalizado maiusculas na chave; CFOP_DIRETO: 908/911/915/5656 adicionados; lote abril: 370 NFs 4258 linhas zero erros |
| v22i   | 22/05/2026 | 0100 campo 11 = S: tentativa de resolver conflito de unidade — nao resolveu (erro é no 1030) |
| v22j   | 22/05/2026 | Mecanismo unidade_excecoes.csv: mapeia cod_produto->unidade_dominio; aplicado em 0100 e 1030; solucao adotada nos testes: importar com opcao "sobrescrever cadastro" |
| v22k   | 22/05/2026 | DIFAL: calcular_difal agrupa por aliq interestadual (pICMS do XML), um 1020|8 por grupo; campo 15 aliq inter preenchido (3 pipes antes); CFOP 2407 removido de CFOPS_DIFAL |
| v22l   | 22/05/2026 | 1020 IPI sem credito: outras=vCont (nao vProd), cod_rec=1097; 1030 campo 29 CST IPI: 00 com cred / 49 sem cred; 1030 campo 55 enquadramento IPI: fixo 999 |
| v22m   | 22/05/2026 | 1000 campo 41: codigo situacao do documento (00=regular, 06=complementar, 08=regime especial) baseado em finNFe; campo 15: infAdFisco; campo 62: infCpl; 1010 usa infCpl |
| v22n   | 28/05/2026 | 1020 CST 51 (diferimento): quando vBC=0 (diferimento total) vProd vai para vOutras; diferimento parcial (vBC>0) permanece no vBC |
| v22o   | 28/05/2026 | Deteccao IPI incluido na BC ICMS pelo fornecedor: quando cred_icms=True e vBC~=vProd+vIPI → vBC corrigido para vProd, vICMS recalculado; reflete em 1020 e 1030; aviso vermelho na tela do app; orienta carta de nao aproveitamento |
| v22r   | 29/05/2026 | tabela_etiquetas.py: acumuladores atualizados para numeracao do banco de producao Kopron (fonte: equivalencia_de_etiqueta_x_cfop_-_atual_REAL.xlsx); 65 de 69 etiquetas alteradas; CFOP e demais regras inalterados |

---

## 7. COMO USAR ESTE DOCUMENTO

**A cada novo chat com Claude, anexe este arquivo e diga:**
> "Estou continuando o projeto Fiscaut x Dominio. Segue o documento de regras atual. Quero continuar a partir daqui sem alterar regras ja definidas."

---

## 8. DADOS DE REFERENCIA

### Empresa de producao
- **Kopron do Brasil** — CNPJ 10251329000189 — Lucro Real — SP
- UF base: SP (usado para calculo DIFAL e resolucao CFOP dentro/fora estado)

### Lote de validacao (21/05/2026 — 50 NFs)
- Arquivo gerado: `dominio_kopron_v22q_abril2026.txt` (370 NFs, 4235 linhas)
- 56 segmentos, 819 linhas, zero erros de importacao
- NFs com divergencia SN logada: 981 (4,44% vs 3,85%) e 24297 (5,17% vs 3,58%)

### XMLs de referencia para testes
- NF 981 — PROFFER (Simples, CRT=1, CSOSN 101) — 5 itens — R$ 43.550,00 — XML correto, infCpl diverge
- NF 16904 — regime normal, IPI destacado — R$ 127.507,55 (base 116.180 + IPI 11.327,55)
- NF 10639 — industrializacao (SN, 2 CFOPs: 1124+1902, 3 unidades diferentes)
- NF 50298 — CFOP 5949 (remessa geral) → 1949
---

## 9. MÓDULO NF PRÓPRIA — gerar_proprio.py

### Tipos suportados
| Tipo | CFOP | Particularidade |
|------|------|-----------------|
| Importação | 3101 | Fornecedor estrangeiro sem CNPJ; vContabil=vNF |
| Devolução | 1201/2201 | Dest = cliente que devolveu |
| CIAP | 1604 | vProd=0; crédito ICMS direto no 1020 |

### Regras específicas
- **Campo 17 do 1000**: `P` (Próprio) — Kopron é a emitente
- **Acumulador = CFOP de entrada** (o CFOP já está correto no XML, sem necessidade de resolver)
- **0020 = dados do DESTINATÁRIO** (não do emitente)
- **vContabil = vNF direto do XML** (inclui ICMS/PIS/COFINS para importação)
- **Chave NF-e**: preenchida normalmente no campo 54

### Importação (CFOP 3101)
- **0020 tipo inscrição**: `O` (Outros)
- **0020 inscrição**: número da DI (tag `nDI` do bloco `<DI>`)
- **0020 nome**: `xNome` do destinatário
- **0020 UF**: `EX`, bairro: `EXTERIOR`
- **0020 país**: `cPais` e `xPais` de `<enderDest>` do XML
- **1030 CST IPI**: `00` (crédito de importação)

### CIAP (CFOP 1604)
- **vContabil = 0,00** (vNF=0 no XML)
- **1020 ICMS**: BC=0, valor=vICMS do item (crédito direto)
- Não gera parcelas

### Formato do ZIP de entrada
XMLs na raiz do ZIP (sem subpastas de etiquetas):
```
lote_proprio.zip
  NF_20301.xml
  NF_20334.xml
  ...
```

### Uso
```
python gerar_proprio.py lote_proprio.zip
python gerar_proprio.py lote_proprio.zip saida.txt
```
