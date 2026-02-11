# Layout Domínio Sistemas - Importação de Dados (TXT com Separador)

Este documento descreve o layout de importação de dados para o sistema Domínio Sistemas, utilizando arquivos TXT com separadores.

## 📌 Informações Gerais

- **Separador Padrão:** `|` (Pipe) ou `;` (Ponto e Vírgula). O separador deve ser consistente em todo o arquivo.
- **Codificação:** O arquivo deve ser gerado em **Windows-1252 (ANSI)**.
- **Formatos de Campo:**
  - **Data:** `dd/mm/aaaa`
  - **Numérico/Decimal:** Utilizar vírgula `,` como separador decimal. Não utilizar separador de milhar.
  - **Caractere:** Texto livre, respeitando o limite de caracteres de cada campo.

---

## 📂 Estrutura de Registros

### Registro 0000 - Identificação da Empresa (Header)
Este registro é obrigatório e deve ser o primeiro do arquivo para identificar a empresa à qual os dados pertencem.

| Campo | Descrição | Tipo | Valor/Formato | Comentário |
| :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | `0000` | Fixo |
| 2 | Inscrição da empresa | Caractere | Números | CNPJ/CPF/CEI/CAEPF da empresa. |

---

### Registro 0010 - Cadastro de Clientes
Utilizado para importar ou atualizar o cadastro de clientes.

| Campo | Descrição | Tipo | Valor/Formato | Comentário |
| :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação | Caractere | `0010` | Fixo |
| 2 | Inscrição | Caractere | Números | CNPJ/CPF/CEI/CAEPF. |
| 3 | Razão Social | Caractere | Texto | Até 150 caracteres. |
| 4 | Apelido | Caractere | Texto | Nome reduzido (até 40 caracteres). |
| 5 | Endereço | Caractere | Texto | Logradouro. |
| 6 | Número | Numérico | Números | |
| 7 | Complemento | Caractere | Texto | |
| 8 | Bairro | Caractere | Texto | |
| 9 | Cód. Município | Numérico | Números | Código IBGE ou RAIS. |
| 10 | UF | Caractere | XX | `EX` para exterior. |
| 11 | Código do País | Numérico | Números | Apenas para exterior. |
| 12 | CEP | Caractere | Números | |
| 13 | Inscrição Estadual | Caractere | Texto | |
| 14 | Inscrição Municipal | Caractere | Texto | |
| 15 | Inscrição Suframa | Caractere | Texto | |
| 16 | DDD | Caractere | Números | |
| 17 | Telefone | Caractere | Números | |
| 18 | FAX | Caractere | Números | |
| 19 | Data do Cadastro | Data | `dd/mm/aaaa` | |
| 20 | Conta Contábil | Numérico | Números | |
| 23 | Natureza Jurídica | Caractere | 1 a 9 | 7=Empresa Privada. |
| 24 | Regime Apuração | Caractere | N, M, E, O, U, I | N=Normal, M=ME, E=EPP. |
| 25 | Contribuinte ICMS | Caractere | S/N | |

---

### Registro 0020 - Cadastro de Fornecedores
Utilizado para importar ou atualizar o cadastro de fornecedores.

| Campo | Descrição | Tipo | Valor/Formato | Comentário |
| :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação | Caractere | `0020` | Fixo 0020 – Identificação do Registro. |
| 2 | Inscrição | Caractere | Números | CNPJ/CPF/CEI/CAEPF do cliente. Apenas números. |
| 3 | Razão Social | Caractere | Texto | Máximo de 150 caracteres. |
| 4 | Apelido | Caractere | Texto | Número reduzido, máximo de 40 caracteres. |
| 5 | Endereço | Caractere | Texto | |
| 6 | Número | Numérico | Números | |
| 7 | Complemento | Caractere | Texto | |
| 8 | Bairro | Caractere | Texto | |
| 9 | Cód. Município | Numérico | Números | Código do município: estadual, federal ou IBGE/RAIS. |
| 10 | UF | Caractere | XX | Quando for exterior, informar EX. |
| 11 | Código do País | Numérico | Números | Informar apenas quando for exterior, o código de cadastro do país. |
| 12 | CEP | Caractere | Números | |
| 13 | Inscrição Estadual | Caractere | Texto | |
| 14 | Inscrição Municipal | Caractere | Texto | |
| 15 | Inscrição Suframa | Caractere | Texto | |
| 16 | DDD | Caractere | Números | |
| 17 | Telefone | Caractere | Números | |
| 18 | FAX | Caractere | Números | |
| 19 | Data do Cadastro | Data | `dd/mm/aaaa` | |
| 20 | Conta Contábil | Numérico | Números | |
| 21 | Conta Contábil Cliente | Numérico | Números | Informar a conta contábil como fornecedor, para quando ocorrer devolução de vendas. |
| 22 | Agropecuário | Caractere | S/N | Informar S=Sim ou N=Não. |
| 23 | Natureza Jurídica | Caractere | 1 a 8 | 1=Órgão Público Federal, 2=Órgão Público Estadual, 3=Órgão Público Municipal, 4=Empresa Pública Federal, 5=Empresa Pública Estadual, 6=Empresa Pública Municipal, 7=Empresa Privada ou 8=Sociedade Cooperativa. |
| 24 | Regime de Apuração | Caractere | N, M, E, O, U, I | N=Normal, M=Microempresa, E=Empresa de pequeno porte, O=Outros, U=Imune do IRPJ ou I=Isenta do IRPJ. |
| 25 | Contribuinte ICMS | Caractere | S/N | Informar S=Sim ou N=Não. |
| 26 | Alíquota ICMS | Decimal | | Quando contribuinte do ICMS=Sim, informar a alíquota de ICMS aplicável ao cliente. |
| 27 | Categoria do Estabelecimento | Caractere | | Informar apenas se a empresa gera o informativo SCANC-CTB. ARM, CNF, CPQ, DIS, FOR, IMP, PRV, REF, TRR, USI ou VGL. |
| 28 | Inscrição Estadual ST | Caractere | Texto | |
| 29 | Email | Caractere | Texto | |
| 30 | Interdependência | Caractere | S/N | Informar S=Sim ou N=Não. |
| 31 | Contribuinte da CPRB | Caractere | S/N | Informar S=Sim ou N=Não. |
| 32 | Processo adm./judicial | Caractere | Texto | Limite de 21 caracteres. |
| 33 | Tipo Inscrição | Caractere | 1 | 1=CAEPF |

---

### Registro 0030 - Cadastro de Remetente e Destinatário
Utilizado para importar ou atualizar o cadastro de remetentes e destinatários, especialmente para documentos de transporte (CT-e). Somente informar este registro para notas modelo: Nota fiscal de serviço de transporte, modelo 07, código 07; Conhecimento de transporte Rodoviário de Cargas, modelo 08, código 08; Conhecimento de transporte de cargas avulso, código 08B.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `0030` | Fixo 0030 – Identificador do registro. Somente informar este registro para notas modelo: Nota fiscal de serviço de transporte, modelo 07, código 07; Conhecimento de transporte Rodoviário de Cargas, modelo 08, código 08; Conhecimento de transporte de cargas avulso, código 08B. |
| 2 | Inscrição CNPJ / CPF / CEI / Outros /CAEPF | Caractere | | | | Informar somente números. |
| 3 | Razão Social | Caractere | | | | Máximo de 150 caracteres |
| 4 | Endereço | Caractere | | | | |
| 5 | Código do município | Caractere | | | | Informar o código IBGE do município. |
| 6 | UF | Caractere | | | | |
| 7 | Inscrição Estadual | Caractere | | | | |
| 8 | Tipo Inscrição | Caractere | | | | Informar: 1=CAEPF |

---

### Registro 0100 - Cadastro de Produtos
Utilizado para importar ou atualizar o cadastro de produtos.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `0100` | Fixo 0100 – Identificação do Registro. |
| 2 | Código do produto | Caractere | | | | Permitir informar no máximo 14 caracteres. |
| 3 | Descrição do produto | Caractere | | | | |
| 4 | Código NBM | Caractere | | | | |
| 5 | Código NCM | Caractere | | | | |
| 6 | Código NCM Exterior | Numérico | | | | |
| 7 | Código de barras | Caractere | | | | |
| 8 | Código do imposto de importação | Numérico | | | | |
| 9 | Código do grupo de produtos | Numérico | | | | Informar código CFME registro 0160. |
| 10 | Unidade de medida | Caractere | | | | |
| 11 | Unidade de medida inventaria diferente da comercializada | Caractere | | | | Informar S=Sim ou N=Não. |
| 12 | Tipo do produto | Caractere | | | | Informar : A=Arma de fogo, M=Medicamentos, V=Veículos novos ou O=Outros. |
| 13 | Tipo da arma de fogo | Numérico | | | | Preencher apenas quando o tipo do produto for igual a Arma de fogo. Informar: 0=Uso permitido ou 1=Uso restrito. |
| 14 | Descrição da arma de fogo | Caractere | | | | Preencher apenas quando tipo de produto for igual a Arma de fogo. |
| 15 | Tipo de medicamento | Numérico | | | | Preencher apenas quando tipo do produto for igual a Medicamento. Informar: 0=Similar, 1=Genérico, 2=Ético ou marca. |
| 16 | Serviço tributado pelo ISSQN | Caractere | | | | Preencher apenas quando tipo de produto igual a Outros. Informar: S=Sim ou N=Não. |
| 17 | Código do chassi do veículo | Caractere | | | | Preencher apenas quando o tipo do produto for igual a veículos novos. |
| 18 | Valor unitário | Decimal | 3 | | | |
| 19 | Quantidade inicial em estoque | Decimal | 5 | | | |
| 20 | Valor inicial em estoque | Decimal | 3 | | | |
| 21 | Código da situação tributária do ICMS | Numérico | | | | CFME tabela oficial do CONFAZ (www.fazenda.gov.br/confaz) |
| 22 | Alíquota do ICMS | Decimal | 2 | | | |
| 23 | Alíquota do IPI | Decimal | 2 | | | |
| 24 | Periodicidade do IPI | Caractere | | | | Informar: D=Decendial, M=Mensal. |
| 25 | Observação | Caractere | | | | |
| 26 | Exporta produto para DNF | Caractere | | | | Informar: S=Sim ou N=Não |
| 27 | Ex TIPI | Caractere | | | | |
| 28 | DNF – Código da espécie do produto | Numérico | | | | Informar apenas quando exporta para DNF. |
| 29 | DNF – Unidade de medida padrão | Numérico | | | | Informar apenas quando exporta para DNF. |
| 30 | DNF- Fator de conversão | Decimal | 3 | | | Informar apenas quando exporta para DNF. |
| 31 | DNF – Código do produto | Numérico | | | | Informar apenas quando exporta para DNF. Preencher com código CFME Anexo I ou II da DNF. |
| 32 | DNF – Capacidade Volumétrica | Numérico | | | | Informar apenas quando exporta para DNF. |
| 33 | SE/DIC – Código EAN | Caractere | | | | Informar apenas se a empresa for do SE e gerar o informativo DIC. |
| 34 | SE/DIC – Código do produto relevante | Numérico | | | | Informar apenas se a empresa for do SE e gerar o informativo DIC. |
| 35 | SCANC – Gerar para o SCANC | Caractere | | | | Informar: S=Sim ou N=Não. |
| 36 | SCANC – Código do produto no SCANC | Numérico | | | | Informar apenas se gera para o SCANC. |
| 37 | SCANC – Contém gasolina A | Caractere | | | | Informar apenas se gera para o SCANC. Informar: S=Sim ou N=Não. |
| 38 | SCANC – Tipo de produto | Caractere | | | | Informar apenas se gera para o SCANC. |
| 39 | GRF – CTB – Gera para o GRF – CTB | Caractere | | | | Informar: S=Sim ou N=Não. |
| 40 | GRF – CTB – Código do produto | Numérico | | | | Informar apenas se gera para o GRF – CTB. Informar: S=Sim ou N=Não. |
| 41 | DIEF - Unidade | Caractere | | | | Informar apenas se gera para a DIEF. Informar: UN=Unidade, KG=Quilograma, LT=Litro, MT=Metro Linear, M2=Metro quadrado, M3=Metro cúbico, KW=Quilowatt hora ou PR=Par. |
| 42 | DIEF – Tipo de produto/serviço | Numérico | | | | Informar apenas se gera para a DIEF. Informar: 1=Mercadoria, 2= Serviço com incidência de ICMS ou 3=Serviço sem incidência do ICMS. |
| 43 | 88ST - Informa o registro 88ST do Sintegra | Caractere | | | | Informar: S= Sim ou N=Não. |
| 44 | 88ST - Código do produto na tabela Sefaz | Numérico | | | | Informar apenas se gera para o 88STdo Sintegra. |
| 45 | GO – Informações complementares do IPM da DPI | Caractere | | | | Informar apenas se a empresa for de GO e gera informações complementares do IPM da DPI. |
| 46 | GO – Código do produto/serviço do IPM da DPI | Numérico | | | | Informar apenas se a empresa for de GO e gera informações complementares do IPM da DPI. |
| 47 | GO - Produto relacionado | Caractere | | | | Informar apenas se a empresa for de GO. Produto relacionado na Posição 3301 a 3307 do Anexo VII do CTE. Informar: S=Sim ou N=Não. |
| 48 | AM - Cesta básica | Caractere | | | | Informar apenas se a empresa for de AM. Informar: S=Sim ou N=Não. |
| 49 | AM - Código do produto na DAM | Numérico | | | | Informar apenas se a empresa for de AM. |
| 50 | RS - Produto incluído no campo substituição tributária | Caractere | | | | |
| 51 | RS - Data de início da substituição tributária | Data | | dd/mm/aaaa | | Informar apenas se a empresa for do RS. |
| 52 | RS - Produto com preço tabelado | Caractere | | | | Informar apenas se a empresa for de RS. Informar: S=Sim ou N=Não. |
| 53 | RS - Valor unitário da substituição tributária | Decimal | 2 | | | Informar apenas se a empresa for de RS. |
| 54 | RS - MVA da substituição tributária | Decimal | 2 | | | Informar apenas se a empresa for de RS. |
| 55 | RS - Grupo da substituição tributária | Numérico | | | | Informar apenas se a empresa for do RS. Informar: 001-Autopeças, 002-Rações, 003-Colchões, 004-Cosméticos, 005-Arroz beneficiado, 006-Rolamentos e Correias de Transmissão, 007-Tintas, 008-Sucos de frutas e Outras Bebidas Não Alcoólicas, 009-Ferramentas, 010-Materiais Elétricos, 011-Materiais de construção, acabamento, Bricolagem ou Adorno, 012-Bicicletas, 013-Brinquedos, 014-Materiais de Limpeza, 015-Produtos Alimentícios, 016-Artefatos de uso doméstico, 017-Bebidas Quentes, 018-Artigos de papelaria, 019-Instrumentos musicais, 020-Prod. Eletrônicos, Eletroeletrônicos e Eletrodomésticos, 021-PROT 160/09 Artefatos de uso Doméstico, 022-PROT 160/09 Prod. Eletrônicos, Eletroeletrônicos e Eletrodomésticos, 023-PROT 163/09 Material de Limpeza, 024-PROT 167/09 Produtos Alimentícios, 025-PROT 207/09 Artefatos de uso Doméstico 026-PROT 208/09 Ferramentas, 027-PROT 210/09 Materiais Elétricos, 028-PROT 211/09 Materiais de construção, acabamento, Bricolagem ou Adorno, 029-PROT 212/09 Artigos de papelaria, 030-PROT 213/09 Produtos Alimentícios, 031-PROT 058/10 Artigos de papelaria, 032-PROT 116 e 148/10 Produtos Alimentícios, 033-PROT 117 e 143/10 Artigos de Papelaria, 034-PROT 119 e 147/10 Prod. Eletrônicos, Eletroeletrônicos e Eletrodomésticos, 035-PROT 120 e 140/10 Materiais Elétricos, 036-PROT 121 e 137/10 Bicicletas, 037-PROT 122 e 145/10 Brinquedos, 038-PROT 123 e 142/10 Material de Limpeza, 039-PROT 124 e 163/10 Cosméticos, 040-PROT 125 e 139/10 Instrumentos Musicais, 041-PROT 126 e 138/10 Ferramentas, 042-PROT 127 e 146/10 Artefatos de uso domésticos, 043-PROT 136/10 Colchoaria, 044-PROT 141 e 152/10 Materiais de construção, Acabamento, Bricolagem ou Adorno, 045-PROT 144/10 Bebidas Quentes, 046-PROT 05/11 Autopeças, 047-PROT 195/09 Maq., AP. Mec., Elet., Eletroelet. e AutoM. 048-Artigos para Bebê, 049-Artigos de Vestuário. |
| 56 | PR - Equipamento de ECF | Caractere | | | | Informar somente se a empresa for do PR. Informar: S=Sim ou N=Não. |
| 57 | MS - Possui incentivo fiscal | Numérico | | | | Informar somente se a empresa for do MS. Informar: S=Sim ou N=Não. |
| 58 | DF - Produto sujeito ao regime especial | Numérico | | | | Informar somente se a empresa for do DF. Informar: 1=Sim ou 0=Não. |
| 59 | DF - Item padrão regime especial | Numérico | | | | Informar apenas se a empresa for do DF. Item padrão referente ao produto sujeito ao Regime Especial de Apuração REA/ICMS (Decreto 29179/2008). |
| 60 | PE - Tipo do produto | Numérico | | | | Informar apenas se a empresa for do PE. Informar: 1=Mercadoria, 2=Matéria Prima, 3=Produto Intermediário, 4=Materiais de embalagens, 5=Produtos manufaturados, 6=Produtos em fabricação. |
| 61 | SP – Controla ressarcimento Cat 17/99 | Caractere | | | | Informar apenas se a empresa for de SP. Informar: S=Sim ou N=Não. |
| 62 | SP - Data do saldo inicial controle Cat 17/99 | Data | | dd/mm/aaaa | | Informar apenas se a empresa for de SP. |
| 63 | SP - Valor unitários controle Cat 17/99 | Decimal | 3 | | | Informar apenas se a empresa for de SP. |
| 64 | SP - Quantidade controle Cat 17/99 | Decimal | 3 | | | Informar apenas se a empresa for de SP. |
| 65 | SP – Valor final controle Cat 17/99 | Decimal | 2 | | | Informar apenas se empresa for de SP. |
| 66 | SPED - Gênero | Numérico | | | | Informar apenas se a empresa gera o SPED Fiscal. Preencher CFME tabela de Gênero do SPED. Quando não houver a informação neste campo, será gerado os dois primeiros dígitos do campo código NCM do produto. |
| 67 | SPED – Código do Serviço | Numérico | | | | Informar apenas se a empresa gera SPED Fiscal e se o Gênero do produto for 00-Serviço. Preencher CFME tabela de Gênero do SPED. |
| 68 | SPED – Tipo do item | Numérico | | | | Informar apenas se a empresa gera SPED Fiscal. Informar: 0=Mercadoria, 1=Matéria Prima, 2=Produto Intermediário, 3=Produto em Fabricação, 4=Produto Acabado, 5=Embalagem, 6=Subproduto, 7=Material de Uso e Consumo, 8=Ativo Imobilizado, 9=Serviços, 10=Outros Insumos ou 99=Outros. |
| 69 | SPED – Classificação | Numérico | | | | Informar apenas se a empresa gera SPED Fiscal. Preencher CFME tabela de classificação do SPED. |
| 70 | SPED – Conta Contábil estoque – Em seu poder | Numérico | | | | Informar apenas se a empresa gera SPED Fiscal. |
| 71 | SPED – Conta Contábil estoque – Em poder de terceiros | Numérico | | | | Informar apenas se a empresa gera SPED Fiscal. |
| 72 | SPED – Conta Contábil estoque – De terceiros em seu poder | Numérico | | | | Informar apenas se a empresa gera SPED Fiscal. |
| 73 | SPED – Tipo de receita | Caractere | | | | Informar apenas se a empresa gera SPED Fiscal. Informar: 0=Receita própria ou 1=Receita de terceiros. |
| 74 | SPED - Energia elétrica / Gás canalizado | Numérico | | | | Informar apenas se a empresa gera SPED Fiscal. |
| 75 | Data do cadastro | Data | | dd/mm/aaaa | | Informar a data do cadastro do produto. |
| 76 | Produto escriturado no LMC | Caractere | | | | Informar: S= Sim ou N=Não |
| 77 | Código do combustível conforme tabela do DF | Caractere | | | | |
| 78 | Código do combustível conforme tabela da ANP | Caractere | | | | |
| 79 | Produto relacionado nos incisos do Art. 8º da MP nº 540/2011 | Caractere | | | | Informar: S= Sim ou N=Não |
| 80 | Permitir informar a descrição complementar no lancto. das notas | Caractere | | | | |
| 81 | Código de atividade – INSS Folha | Caractere | | | | Produto relacionado nos Arts. 7º e 8º da Lei 12.546/2011. |
| 82 | DACON – Tipo do Produto | Caractere | | | | Informar somente se a empresa gerar SPED PIS/COFINS e o produto possuir Crédito por Alíquota Diferenciada, Débito por Alíquota Diferenciada, Crédito por unidade de medida ou Débito por unidade de medida. Preencher conforme tabela tipo de produto da DACON |
| 83 | DACON - Crédito Presumido Atividade Agroindustriais | Numérico | | | | Informar somente se a empresa gerar SPED PIS/COFINS e o produto possuir em alguma vigência a opção 06-Crédito presumido agroindústria e aquisição de combustível Informar : "1"-Insumos de origem animal; "2"-Insumos de origem vegetal e "0"-sem informação. |
| 84 | Desconsiderar | Numérico | | | | |
| 85 | SPED – Conta Contábil estoque - Em processo | Numérico | | | | Informar apenas se a empresa gera o bloco K no SPED Fiscal e o "Tipo do Item" for "Produto em processo" ou "Produto acabado". |
| 86 | SPED – Conta Contábil estoque - Histórico em processo | Numérico | | | | Informar apenas se a empresa gera o bloco K no SPED Fiscal e o "Tipo do Item" for "Produto em processo" ou "Produto acabado". |
| 87 | SPED – Conta Contábil estoque - Acabado | Numérico | | | | Informar apenas se a empresa gera o bloco K no SPED Fiscal e o "Tipo do Item" for "Produto em processo" ou "Produto acabado". |
| 88 | SPED – Conta Contábil estoque - Histórico acabado | Numérico | | | | Informar apenas se a empresa gera o bloco K no SPED Fiscal e o "Tipo do Item" for "Produto em processo" ou "Produto acabado". |
| 89 | Código CEST | Numérico | | | | Informar o código CEST conforme tabela Código Especificador da Substituição Tributária – CEST |
| 90 | Registro de Exportação (RE) | Numérico | | | | Informar o número do registro de exportação |
| 91 | Identificador | Caractere | | | | Permitir informar no máximo 60 caracteres. |

---

### Registro 0110 - Produtos - Vigência
Produtos - Período de validade. Este é um registro filho do registro 0100 (Cadastro de produtos).

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `0110` | Fixo 0110 – Identificação do Registro. Registro filho do registro 0100. |
| 2 | Descrição | Caractere | | | | Descrição da vigência |
| 3 | CST – Entrada | Numérico | | | | |
| 4 | Vínculo do Crédito | Numérico | | | | |
| 5 | Base do Crédito | Numérico | | | | Informar 01=Aquisição de bens para revenda; 02=Aquisição de bens utilizados como insumo; 03=Aquisição de serviços utilizados como insumo; 04=Energia elétrica e térmica, inclusive sob a forma de vapor; 05=Aluguéis de prédios; 06=Aluguéis de máquinas e equipamentos; 07=Armazenagem de mercadoria e frete na operação de venda; 08=Contraprestações de arrendamento mercantil; 09=Máquinas, equipamentos e outros bens incorporados ao ativo imobilizado (crédito sobre encargos de depreciação); 10=Máquinas, equipamentos e outros bens incorporados ao ativo imobilizado (crédito com base no valor de aquisição); 11=Amortização de edificações e benfeitorias em imóveis; 12=Devolução de Vendas Sujeitas à Incidência Não-Cumulativa; 13=Outras Operações com Direito a Crédito; 14=Atividade de Transporte de Cargas – Subcontratação; 15=Atividade Imobiliária – Custo Incorrido de Unidade Imobiliária; 16=Atividade Imobiliária – Custo Orçado de unidade não concluída; 17=Atividade de Prestação de Serviços de Limpeza, Conservação e Manutenção – vale-transporte, vale-refeição ou vale-alimentação, fardamento ou uniforme; 18=Estoque de abertura de bens. |
| 6 | Aproveitar crédito proporcional somente a receita não cumulativa | Caractere | | | | Informar: S= Sim ou N=Não |
| 7 | Crédito por alíquota diferenciada - Entradas | Caractere | | | | Informar: S= Sim ou N=Não |
| 8 | Alíquota do PIS – Entradas | Decimal | 4 | | | |
| 9 | Alíquota do COFINS – Entradas | Decimal | 4 | | | |
| 10 | Crédito por unidade de medida – Entradas | Caractere | | | | Informar: S= Sim ou N=Não |
| 11 | Unidade tributada diferente da inventariada - Entradas | Caractere | | | | Informar: S= Sim ou N=Não |
| 12 | Unidade tributável – Entradas | Caractere | | | | |
| 13 | Fator de conversão – Entradas | Decimal | 6 | | | |
| 14 | Valor de PIS – Entradas | Decimal | 4 | | | |
| 15 | Valor de COFINS - Entradas | Decimal | 4 | | | |
| 16 | CST – Saídas | Numérico | | | | |
| 17 | Tipo de contribuição | Caractere | | | | Informar: N=Não cumulativo, C= Cumulativo ou S=Sem incidência. |
| 18 | Natureza de receita | Numérico | | | | |
| 19 | Código de recolhimento PIS - Saída | Caractere | | | | Informar o código de recolhimento existente no cadastro do imposto PIS-ST. |
| 20 | Código de recolhimento COFINS - Saída | Caractere | | | | Informar o código de recolhimento existente no cadastro do imposto COFINS-ST. |
| 21 | Débito por alíquota diferenciada - Saídas | Caractere | | | | Informar: S= Sim ou N=Não |
| 22 | Alíquota do PIS – Saídas | Decimal | 4 | | | |
| 23 | Alíquota do COFINS – Saídas | Decimal | 4 | | | |
| 24 | Debito por unidade de medida – Saídas | Caractere | | | | Informar: S= Sim ou N=Não |
| 25 | Unidade tributada diferente da inventariada - Saídas | Caractere | | | | Informar: S= Sim ou N=Não |
| 26 | Unidade tributável – Saídas | Caractere | | | | |
| 27 | Fator de conversão – Saídas | Decimal | 6 | | | |
| 28 | Valor de PIS – Saídas | Decimal | 4 | | | |
| 29 | Valor de COFINS - Saídas | Decimal | 4 | | | |
| 30 | Tabela SPED | Numérico | | | | |
| 31 | Marca/Grupo SPED | Numérico | | | | |
| 32 | PIS com incidência cumulativa, conforme lei 12.693/12, Art. 6 | Caractere | | | | Informar: S= Sim ou N=Não |
| 33 | COFINS com incidência cumulativa, conforme lei 10.833/2003, Art. 10 | Caractere | | | | Informar: S= Sim ou N=Não |
| 34 | ICMS – CST/CSOSN Entradas | Numérico | | | | Informar somente os códigos CST/CSOSN constantes nas tabelas CRT - Código do Regime Tributário Normal e CSOSN - Código da Situação da Operação no Simples Nacional. |
| 35 | ICMS – CST/CSOSN Saídas | Numérico | | | | Informar somente os códigos CST/CSOSN constantes nas tabelas CRT - Código do Regime Tributário Normal e CSOSN - Código da Situação da Operação no Simples Nacional |
| 36 | ICMS – Alíquota ICMS | Decimal | 2 | | | |
| 37 | IPI – CST Entradas | Numérico | | | | Informar somente os códigos CST constantes na tabela CST do IPI |
| 38 | IPI – CST Saídas | Numérico | | | | Informar somente os códigos CST constantes na tabela CST do IPI |
| 39 | IPI - Periodicidade | Caractere | | | | Informar: D =Decendial, M=Mensal. |
| 40 | IPI - Alíquota | Decimal | 2 | | | |
| 41 | Simples Nacional - Produto sujeito a tributação de PIS e COFINS com incidência | Caractere | | | | Informar S=Sim ou N=Não |
| 42 | Excluir da base de cálculo os valores de frete, seguros e despesas acessórias nas operações de importação | Caractere | | | | Informar: S=Sim ou N=Não |
| 43 | Produto sujeito ao cálculo do Fundo para o Desenvolvimento da Agropecuária do Estado de Goiás – FUNDEPEC | Caractere | | | | Valores válidos: Informar S-Sim ou N-Não. Informar somente para empresa com UF GO |
| 44 | Tipo do produto | Numérico | | | | Informar somente para empresa com UF GO. Informar o código constante da tabela do FUNDEPEC |
| 45 | Calcular o incentivo do Programa de Desenvolvimento do Estado de Pernambuco – PRODEPE | Caractere | | | | Informar: S= Sim ou N=Não |
| 46 | Código da apuração | Caractere | | | | Informar o código da tabela de Incentivo PRODEPE. |
| 47 | Produto possui percentual de redução na base de cálculo | Caractere | | | | Informar S-Sim e N-Não |
| 48 | PIS/COFINS - Percentual de redução na base de cálculo | Decimal | 2 | | | Informar o percentual de redução. |
| 49 | Simples Nacional - Tipo de tributação de PIS e COFINS | Numérico | | | | Informar: 1= Tributação monofásica ou 2=Substituição tributária |
| 50 | Código de recolhimento PIS – Entrada | Numérico | | | | |
| 51 | Código de recolhimento COFINS – Entrada | Numérico | | | | |
| 52 | Base de cálculo ST | Caractere | | | | Informar: M = "Margem Valor Agregado (Percentual)" P = "Pauta(Valor)" A = "Maior valor entre Margem de valor agregado(%) e Pauta(valor)". Observação: Campo disponivel somente para empresa com UF SC, RS, DF, PR, RO, RJ, SP, GO e CE. |
| 53 | Percentual margem de valor adic. ST | Decimal | 2 | | | Apenas Informar quando no campo base de cálculo ST estiver informado "M". Observação: Campo disponivel somente para empresa com UF SC, RS, DF, PR, RO, RJ, SP, GO e CE. |
| 54 | Valor unitário ST | Decimal | 2 | | | Apenas Informar quando no campo base de cálculo ST estiver informado "P". Observação: Campo disponivel somente para empresa com UF SC, RS, DF, PR, RO, RJ, SP, GO e CE. |
| 55 | IPI - Código de recolhimento | Caractere | | | | |
| 56 | RS- Detalhamento Anexo VA/VB | Caractere | | | | Informar apenas se a empresa for do RS. Informar: S= Sim ou N=Não |
| 57 | RS- Código do detalhamento para Anexo VA | Numérico | | | | Informar apenas se a empresa for do RS. Informar o código do detalhamento para anexo VA. |
| 58 | RS- Código do detalhamento para Anexo VB | Numérico | | | | Informar apenas se a empresa for do RS. Informar o código do detalhamento para anexo VB. |
| 59 | Bebidas frias – Alíquota para Simples Nacional | Caractere | | | | Informar: S = Sim ou N = Não. |
| 60 | Alíquota de PIS – Entradas | Decimal | 4 | | | |
| 61 | Alíquota de COFINS - Entradas | Decimal | 4 | | | |
| 62 | Produto incluído no cálculo do ressarcimento/complemento do ICMS ST conf. Art. 25-A a 25-C, Livro III do RICMS | Caractere | | | | Apenas para o Estado do RS. Informar: S= Sim ou N=Não. |
| 63 | Percentual da base de cálculo: | Decimal | 2 | | | Apenas para o Estado do RS. |
| 64 | Calcular o montante do imposto presumido com base na tabela de PMPF de Combustíveis | Caractere | | | | Apenas para o Estado do RS. Informar: S= Sim ou N=Não. |
| 65 | Produto fora do benef. fiscal estab. Comercial Atacadista nas saídas interestaduais - Art. 530-LRK RICMS - ES | Caractere | | | | Apenas para o estado do ES. Informar: S- Sim ou N- Não |
| 66 | Produto fora do benef. fiscal estab. Comercial Atacadista nas saídas internas - Art. 534-ZZA RICMS - ES | Caractere | | | | Apenas para o estado do ES. Informar: S- Sim ou N- Não |
| 67 | IBS - cClass Trib | Caractere | | | | No cadastro do produto, na guia 'Impostos', subguia 'IBS', preencher o campo "cClass Trib:". |
| 68 | CBS - cClass Trib | Caractere | | | | No cadastro do produto, na guia 'Impostos', subguia 'CBS', preencher o campo "cClass Trib:". |
| 69 | IBS - Utiliza a tabela de cClass Trib vinculada ao NCM/NBS | Caractere | | | | Informar "S = Sim" ou "N = Não" |
| 70 | IBS - Utiliza a tabela de cClass Trib vinculada ao NCM/NBS | Caractere | | | | Informar "S = Sim" ou "N = Não" |

---

### Registro 0120 - Produtos - Unidades comercializadas
Produtos - Unidades comercializadas. Este é um registro filho do registro 0100.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `0120` | Fixo 0120 – Identificação do Registro. Registro filho do registro 0100. |
| 2 | Sigla da unidade comercializada | Caractere | | | | |
| 3 | Fator de conversão | Decimal | 3 | | | |
| 4 | Código de Barras | Caractere | | | | |

---

### Registro 0135 - Produtos - Valor Unitário
Produtos - Valor Unitário. Este é um registro filho do registro 0100.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `0135` | Fixo 0135 – Identificação do Registro. Registro filho do registro 0100. |
| 2 | Data | Data | | | | |
| 3 | Valor Unitário | Decimal | 6 | | | Permitir informar no máximo 15 caracteres numéricos, sendo deles 6 casas decimais, como exemplo: 111.111.111,111111. |

---

### Registro 0150 - Produtos - Unidade de medida
Produtos - Unidade de medida.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | - | - | `0150` | Fixo 0150 – Identificação do Registro. |
| 2 | Sigla | Caractere | - | - | - | - |
| 3 | Descrição | Caractere | - | - | - | - |

---

### Registro 1000 - Notas Fiscais de Entrada
Notas Fiscais de Entrada.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `1000` | Fixo 1000 – Identificação do Registro |
| 2 | Código da espécie | Numérico | | | | |
| 3 | Inscrição fornecedor | Caractere | | | | CNPJ, CPF, CEI, Outros ou CAEPF |
| 4 | Código de Exclusão da DIEF | Numérico | | | | |
| 5 | Código do acumulador | Numérico | | | | |
| 6 | CFOP | Numérico | | | | |
| 7 | Segmento | Numérico | | | | |
| 8 | Número do documento | Numérico | | | | |
| 9 | Série | Caractere | | | | |
| 10 | Numero do documento final | Numérico | | | | |
| 11 | Data da entrada | Data | | dd/mm/aaaa | | |
| 12 | Data emissão | Data | | dd/mm/aaaa | | |
| 13 | Valor contábil | Decimal | 2 | | | |
| 14 | Valor da exclusão da DIEF | Decimal | 2 | | | |
| 15 | Observação | Caractere | | | | Quando a Nota Fiscal importada possuir observação, a mesma deverá ser gerada na guia na Complementar subguia Observações no grupo Observação de interese do fisco |
| 16 | Modalidade do frete | Caractere | | | | Informar : C = CIF, F = FOB, S = Sem frete, T = Terceiros, R = Trans. Próp. por conta Rem., D = Trans. Próp. por conta Dest |
| 17 | Emitente da nota fiscal | Caractere | | | | Informar: P=Próprio ou T=Terceiros |
| 18 | CFOP estendido/detalhamento | Numérico | | | | Apenas para estado de SE |
| 19 | Código da transferência de crédito | Numérico | | | | Apenas para estado de RS |
| 20 | Código do Recolhimento do ISS Retido | Caractere | | | | |
| 21 | Código do Recolhimento do IRRF | Caractere | | | | |
| 22 | Código da observação | Numérico | | | | |
| 23 | Data do visto notas de transf. Crédito ICMS | Data | | dd/mm/aaaa | | Apenas para estado de MG |
| 24 | Fato gerador da CRF | Caractere | | | | Informar: E = Emissão ou P = Pagamento |
| 25 | Fato gerador do IRRF | Caractere | | | | Informar: E = Emissão ou P = Pagamento |
| 26 | Valor do frete | Decimal | 2 | | | |
| 27 | Valor do seguro | Decimal | 2 | | | |
| 28 | Valor da despesas | Decimal | 2 | | | |
| 29 | Valor do PIS | Decimal | 2 | | | |
| 30 | Código que Identifica o tipo de Antecipação Tributária | Numérico | | | | |
| 31 | Valor do COFINS | Decimal | 2 | | | |
| 32 | Valor calculado referente a DARE da nota | Decimal | 2 | | | Apenas para o estado SE |
| 33 | Alíquota do valor calculado referente a DARE da nota | Decimal | 2 | | | Apenas para o estado SE |
| 34 | Valor da base de cálculo do ICMS ST | Numérico | | | | Informar: 0=informações Complementares / 1=Quadro calculado do imposto / 2=Apurado pelo informante |
| 35 | Entradas cuja saídas é isenta | Decimal | 2 | | | Apenas para o estado MG |
| 36 | Outras entradas isentas | Decimal | 2 | | | Apenas para o estado MG |
| 37 | Valor transporte incluído na base | Decimal | 2 | | | Apenas para o estado MG |
| 38 | Código de ressarcimento | Numérico | | | | |
| 39 | Valor produtos | Decimal | 2 | | | |
| 40 | Município Origem | Numérico | | cMunIni ide | | |
| 41 | Situação da Nota | Numérico | | | | 0 - Documento Regular, 1 - Documento Regular Extemporâneo, 2 - Documento Cancelado, 6 - Documento Fiscal Complementar/Referenciado, 7 - NFe, NFCe ou CTe Denegado, 8 - NFe, NFCe ou CTe Numeração Inutilizada, 9 - Documento Fiscal Emitido com Base Regime Especial ou Norma Específica, 10- Documento Fiscal Complementar Extemporâneo |
| 42 | Código da situação tributária | Numérico | | | | |
| 43 | Sub serie | Numérico | | | | |
| 44 | Inscrição estadual do fornecedor | Caractere | | | | |
| 45 | Inscrição municipal do fornecedor | Caractere | | | | |
| 46 | Código da operação e prestação | Caractere | | | | |
| 47 | Valor a ser deduzido da receita tributável | Decimal | 2 | | | |
| 48 | Competência | Data | | dd/mm/aaaa | | |
| 49 | Operação | Numérico | | | | Apenas para o estado PA |
| 50 | Número do parecer fiscal | Caractere | | | | |
| 51 | Data do parecer fiscal | Data | | dd/mm/aaaa | | |
| 52 | Número da declaração de Importação | Caractere | | | | |
| 53 | Possui benefício fiscal | Caractere | | | | Informar: S ou N |
| 54 | Chave da nota fiscal eletrônica | Caractere | | | | |
| 55 | Código de recolhimento do FETHAB | Caractere | | | | |
| 56 | Responsável pelo recolhimento do FETHAB | Caractere | | | | Informar: E=Empresa / C=Cliente |
| 57 | CFOP documento fiscal | Numérico | | | | |
| 58 | Tipo de CT-e | Numérico | | | | Informar: 0=CT-e Normal / 1=CT-e de Complemento de valores / 2=CT-e Emitido em hipótese de anulação de débito |
| 59 | CT-e referência | Caractere | | | | |
| 60 | Modalidade da importação | Numérico | | | | Informar: 1=Com direito a crédito / 2=Compensação / 3=Regime especial / 4=Sem direito a crédito / 5= Outras situações – sem crédito |
| 61 | Código da informação complementar | Numérico | | | | |
| 62 | Informação complementar | Caractere | | | | |
| 63 | Classe de consumo | Numérico | | | | |
| 64 | Tipo de ligação | Numérico | | | | |
| 65 | Grupo de tensão | Numérico | | | | |
| 66 | Tipo de assinante | Numérico | | | | |
| 67 | KWH consumido | Numérico | | | | |
| 68 | Valor fornecido / consumido de gás ou energia elétrica | Decimal | 2 | | | |
| 69 | Valor cobrado de terceiros | Decimal | 2 | | | |
| 70 | Tipo do documento de importação | Numérico | | | | Informar: 10=Declaração de Importação / 1=Declaração Simplificada de Importação |
| 71 | Número do Ato Concessório do regime Drawback | Caractere | | | | |
| 72 | Natureza do frete PIS/COFINS | Numérico | | | | Apenas p/ modelos de espécies 08, 08B, 09, 10, 11, 26, 27 e 57. Informar: 0=Op. de venda, com ônus suportado pelo estab. Vendedor / 1=Op. de venda, com ônus suportado pelo adquirente / 2=Op. de compra (bens para revenda, matérias - prima e outros produtos geradores de crédito) / 3=Op. de compra (bens para revenda, matérias - prima e outros produtos não geradores de crédito) / 4=Transf. de prod. acabados entre estabelecimento pessoa jurídica / 5=Transf. De prod. em elaboração entre estabelecimento pessoa jurídica / 9=Outras |
| 73 | CST – PIS/COFINS | Numérico | | | | Informar somente CSTs de código 50 à 99 quando utilizado um dos seguintes modelos de espécie: 08, 08B, 09, 10, 11, 26, 27 e 57 |
| 74 | Base do crédito PIS/COFINS | Numérico | | | | Apenas para os modelos de espécie 08, 08B, 09, 10, 11, 26, 27 e 57. Informar: 03=Aquisição de serviços utilizados como insumo / 07=Armazenagem de mercadoria e frete na operação de venda / 13=Outras operações com direito a crédito / 14=Atividades de transporte de cargas – Subcontratação |
| 75 | Valor serviços / itens PIS/COFINS | Decimal | 2 | | | Apenas para os modelos de espécie 08, 08B, 09, 10, 11, 26, 27 e 57 |
| 76 | Base de cálculo PIS/COFINS | Decimal | 2 | | | Apenas para os modelos de espécie 08, 08B, 09, 10, 11, 26, 27 e 57 |
| 77 | Alíquota de PIS | Decimal | 2 | | | |
| 78 | Alíquota de COFINS | Decimal | 2 | | | |
| 79 | Chave de NFSe | Caractere | | | | |
| 80 | Número do processo ou ato concessório | Caractere | | | | Apenas quando informado para a Natureza do frete os códigos 1, 3, 4 ou 5 |
| 81 | Origem do processo | Caractere | | | | Apenas para os modelos de espécie 08, 08B, 09, 10, 11, 26, 27 e 57 e quando informado para a Natureza do frete os códigos 1, 3, 4 e 5. Informar: 1=Justiça Federal/3=Secretaria da Receita Federal do Brasil / 9=Outros |
| 82 | Data da escrituração | Data | | dd/mm/aaaa | | Data da escrituração quando a situação for Documento Extemporâneo |
| 83 | CFPS | Numérico | | | | Apenas para o estado de DF |
| 84 | Natureza da receita – PIS/COFINS | Numérico | | | | Apenas para os modelos de espécie 07, 08, 08B, 09, 10, 11 e 57 |
| 85 | CST IPI – Código da Situação Tributária do IPI | Caractere | | | | Somente informar para notas de modelo/espécie "Nota Fiscal Eletrônica, código 55", ou "Nota Fiscal, modelo 01, código 01", "Nota Fiscal Avulsa, código 1B", "Nota Fiscal de Produtor, modelo 04, código 04" com situação do documento igual a "Documento fiscal emitido com base em regime especial" e que possuam os impostos 2-IPI ou 30-IPIM. Valores válidos: 00 - Entrada com recuperação de crédito, 01 - Entrada tributada com alíquota zero, 02 - Entrada isenta, 03 - Entrada não-tributada, 04 - Entrada imune, 05 - Entrada com suspensão, 49 - Outras entradas |
| 86 | Lançamentos de SCP | Numérico | | | | Informar correspondente ao código SCP |
| 87 | Tipo de serviço | Numérico | | | | Informar 1 = "Transporte de cargas" ou 2 = "Transporte de passageiros" |
| 88 | Município destino | Numérico | | | | Somente importar quando a espécie da nota estiver vinculada a "CT-e modelo 57", no acumulador estiver selecionada a opção "Devolução", na guia "Impostos" do acumulador estiver informado o imposto "145-DIFAL" e o CFOP inicie em 2-XXX |
| 89 | Pedágio | Decimal | 2 | | | |
| 90 | IPI | Decimal | 2 | | | |
| 91 | ICMS ST | Decimal | 2 | | | |
| 92 | Classificação de Serviços Prestados mediante cessão de mão de obra/Empreitada - Tipo de serviço - EFD-Reinf | Numérico | | | | |

---

### Registro 1010 - Notas Fiscais de Entrada - Informações Complementares
Notas Fiscais de Entrada - Informações Complementares. Este é um registro filho do registro 1000.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `1010` | Fixo 1010 – Identificação do Registro. Registro filho do registro 1000 |
| 2 | Informações complementares | Caractere | | | | Informações complementares da nota fiscal |

---

### Registro 1015 - Notas Fiscais de Entrada - Observações de interesse do Fisco ou Contribuinte
Notas Fiscais de Entrada - Observações de interesse do Fisco ou Contribuinte. Este é um registro filho do registro 1000.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `1015` | Fixo 1015 – Identificação do Registro. Registro filho do registro 1000 |
| 2 | Observações de interesse do Fisco ou Contribuinte | Caractere | | | | Observações de interesse do Fisco ou Contribuinte |

---

### Registro 1020 - Notas Fiscais de Entrada - Impostos
Notas Fiscais de Entrada - Impostos. Este é um registro filho do registro 1000.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `1020` | Fixo 1020 – Identificação do Registro. Registro filho do registro 1000 |
| 2 | Código do imposto | Numérico | | | | |
| 3 | Valor do imposto | Decimal | 2 | | | |
| 4 | Base de cálculo | Decimal | 2 | | | |
| 5 | Alíquota | Decimal | 2 | | | |
| 6 | Valor do crédito | Decimal | 2 | | | |
| 7 | CST | Numérico | | | | |
| 8 | CFOP | Numérico | | | | |
| 9 | Natureza da operação | Caractere | | | | |
| 10 | Código de situação tributária | Numérico | | | | |
| 11 | Código de enquadramento legal | Caractere | | | | |
| 12 | Valor da base de cálculo do ICMS | Decimal | 2 | | | |
| 13 | Valor do ICMS | Decimal | 2 | | | |
| 14 | Valor da base de cálculo do ICMS ST | Decimal | 2 | | | |
| 15 | Valor do ICMS ST | Decimal | 2 | | | |
| 16 | Valor do IPI | Decimal | 2 | | | |
| 17 | Valor do PIS | Decimal | 2 | | | |
| 18 | Valor da COFINS | Decimal | 2 | | | |
| 19 | Valor do ISSQN | Decimal | 2 | | | |
| 20 | Valor do INSS | Decimal | 2 | | | |
| 21 | Valor do IRRF | Decimal | 2 | | | |
| 22 | Valor do IOF | Decimal | 2 | | | |
| 23 | Outros valores | Decimal | 2 | | | |

---

### Registro 1030 - Notas Fiscais de Entrada - Estoque
Notas Fiscais de Entrada - Estoque. Este é um registro filho do registro 1000.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `1030` | Fixo 1030 – Identificação do Registro. Registro filho do registro 1000 |
| 2 | Código do produto | Caractere | | | | |
| 3 | Quantidade | Decimal | 3 | | | |
| 4 | Valor unitário | Decimal | 3 | | | |
| 5 | Valor total | Decimal | 2 | | | |
| 6 | Unidade de medida | Caractere | | | | |
| 7 | Centro de custo | Numérico | | | | |
| 8 | Conta contábil | Numérico | | | | |
| 9 | Data de validade | Data | | dd/mm/aaaa | | |
| 10 | Lote | Caractere | | | | |
| 11 | Número de série | Caractere | | | | |
| 12 | Localização física | Caractere | | | | |
| 13 | Código do fornecedor | Caractere | | | | |
| 14 | Número do documento fiscal | Numérico | | | | |
| 15 | Série do documento fiscal | Caractere | | | | |
| 16 | Data do documento fiscal | Data | | dd/mm/aaaa | | |
| 17 | CFOP | Numérico | | | | |
| 18 | Código da situação tributária | Numérico | | | | |
| 19 | Base de cálculo do ICMS | Decimal | 2 | | | |
| 20 | Valor do ICMS | Decimal | 2 | | | |
| 21 | Base de cálculo do ICMS ST | Decimal | 2 | | | |
| 22 | Valor do ICMS ST | Decimal | 2 | | | |
| 23 | Valor do IPI | Decimal | 2 | | | |
| 24 | Valor do PIS | Decimal | 2 | | | |
| 25 | Valor da COFINS | Decimal | 2 | | | |

---

### Registro 1200 - Notas Fiscais de Entrada - ICMS Aquisição de Empresa do Simples Nacional - SP
Notas Fiscais de Entrada - ICMS Aquisição de Empresa do Simples Nacional - SP. Este é um registro filho do registro 1000.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `1200` | Fixo 1200 – Identificação do Registro. Registro filho do registro 1000 |
| 2 | Código do produto | Caractere | | | | |
| 3 | CFOP | Numérico | | | | |
| 4 | Código da situação tributária | Numérico | | | | |
| 5 | Base de cálculo do ICMS | Decimal | 2 | | | |
| 6 | Alíquota do ICMS | Decimal | 2 | | | |
| 7 | Valor do ICMS | Decimal | 2 | | | |
| 8 | Valor do crédito | Decimal | 2 | | | |
| 9 | Percentual do diferimento | Decimal | 2 | | | |
| 10 | Valor do ICMS diferido | Decimal | 2 | | | |
| 11 | Valor do ICMS próprio | Decimal | 2 | | | |
| 12 | Código fiscal de operação e prestação | Numérico | | | | |
| 13 | Natureza da operação | Caractere | | | | |
| 14 | Código de enquadramento legal | Caractere | | | | |
| 15 | Código do modelo do documento fiscal | Numérico | | | | |
| 16 | Série do documento fiscal | Caractere | | | | |
| 17 | Número do documento fiscal | Numérico | | | | |
| 18 | Data do documento fiscal | Data | | dd/mm/aaaa | | |
| 19 | Inscrição estadual do contribuinte | Caractere | | | | |
| 20 | Valor da operação | Decimal | 2 | | | |

---

### Registro 1500 - Notas Fiscais de Entrada – Parcelas
Notas Fiscais de Entrada – Parcelas. Este é um registro filho do registro 1000.

| Campo | Nº Campo | Tipo | Casas Decimais | Formato | Valor | Comentário |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | Identificação do registro | Caractere | | | `1500` | Fixo 1500 – Identificação do Registro. Registro filho do registro 1000 |
| 2 | Número da parcela | Numérico | | | | |
| 3 | Data de vencimento | Data | | dd/mm/aaaa | | |
| 4 | Valor da parcela | Decimal | 2 | | | |
| 5 | Código de condição de pagamento | Numérico | | | | |
| 6 | Data de pagamento | Data | | dd/mm/aaaa | | |
| 7 | Valor pago | Decimal | 2 | | | |
| 8 | Valor de desconto | Decimal | 2 | | | |
| 9 | Valor de acréscimo | Decimal | 2 | | | |
| 10 | Valor de juros | Decimal | 2 | | | |
| 11 | Valor de multa | Decimal | 2 | | | |
| 12 | Forma de pagamento | Caractere | | | | |
| 13 | Número do documento de pagamento | Caractere | | | | |
| 14 | Banco | Numérico | | | | |
| 15 | Agência | Numérico | | | | |
| 16 | Conta corrente | Caractere | | | | |
| 17 | Status da parcela | Caractere | | | | |
| 18 | Observações | Caractere | | | | |
| 19 | Código de liquidação | Numérico | | | | |
| 20 | Data de liquidação | Data | | dd/mm/aaaa | | |
| 21 | Hora de liquidação | Caractere | | | hh:mm:ss | |
| 22 | Meio de pagamento | Caractere | | | | |
| 23 | Código de barras | Caractere | | | | |
| 24 | Linha digitável | Caractere | | | | |
| 25 | Nosso número | Caractere | | | | |
| 26 | Código do cliente | Caractere | | | | |
| 27 | Código do fornecedor | Caractere | | | | |
| 28 | Número do documento fiscal | Numérico | | | | |
| 29 | Série do documento fiscal | Caractere | | | | |
| 30 | Data do documento fiscal | Data | | dd/mm/aaaa | | |

## 🛠️ Notas de Implementação

1. **Geração do Arquivo:** Recomenda-se o uso de `mb_convert_encoding($content, 'Windows-1252', 'UTF-8')` no Laravel para garantir a compatibilidade com o sistema Domínio.
2. **Campos Vazios:** Caso um campo não possua valor, deve-se manter o separador (ex: `valor1||valor3`).
3. **Ordem dos Registros:** O registro `0000` deve sempre preceder os registros de dados. Registros filhos (ex: `0011`) devem vir imediatamente após seu registro pai (ex: `0010`).

---
*Baseado na documentação oficial Domínio Sistemas (Código 672).*
