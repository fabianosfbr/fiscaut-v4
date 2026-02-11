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


## 🛠️ Notas de Implementação

1. **Geração do Arquivo:** Recomenda-se o uso de `mb_convert_encoding($content, 'Windows-1252', 'UTF-8')` no Laravel para garantir a compatibilidade com o sistema Domínio.
2. **Campos Vazios:** Caso um campo não possua valor, deve-se manter o separador (ex: `valor1||valor3`).
3. **Ordem dos Registros:** O registro `0000` deve sempre preceder os registros de dados. Registros filhos (ex: `0011`) devem vir imediatamente após seu registro pai (ex: `0010`).

---
*Baseado na documentação oficial Domínio Sistemas (Código 672).*
