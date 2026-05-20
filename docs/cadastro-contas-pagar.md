# Cadastro de Contas a Pagar via API Superlógica

## Visão Geral

Este documento descreve o plano de implementação de uma **Action do Filament** na página `ListarContaPagar` que permite ao usuário cadastrar novas despesas (contas a pagar) através da API da Superlógica.

A página já existe em `app/Filament/Condominio/Pages/ListarContaPagar.php` e exibe uma tabela de contas a pagar consultadas via endpoint `/despesas/index`. O objetivo é adicionar a capacidade de **criar** novas despesas através do endpoint `POST /despesas`.

---

## 1. Referências

| Arquivo/Documento | Descrição |
|:---|:---|
| `params-nova-despesa.md` | Especificação dos campos obrigatórios e opcionais da API de despesa |
| `app/Filament/Condominio/Pages/ListarContaPagar.php` | Página atual com tabela de contas a pagar |
| `app/Services/SuperLogica/Condominio/SuperLogicaDespesaConnector.php` | Connector que consome endpoints de despesa |
| `app/Services/SuperLogica/Condominio/SuperLogicaCondominioPlanoDeContaConnector.php` | Connector para buscar plano de contas |
| `app/Services/SuperlogicaConnectionService.php` | Service facade com acesso a todos os conectores |

---

## 2. Campos da API de Despesa

### 2.1 Campos Obrigatórios

| Campo API | Descrição | Tipo | Origem |
|:---|:---|:---|:---|
| `ID_CONDOMINIO_COND` | ID do condomínio | int | `$issuer->superlogica_condominio_id` (fixo) |
| `ST_NOME_CON` | Nome do fornecedor | string | Seleção do dropdown |
| `ID_CONTATO_CON` | ID do fornecedor (contato) | int | Selecionado junto com fornecedor |
| `DT_VENCIMENTOPRIMEIRAPARCELA` | Data de vencimento | date | Formulário |
| `ID_FORMA_PAG` | Forma de pagamento | int | Seleção (mapa fixo) |
| `APROPRIACAO[0][ST_CONTA_CONT]` | Código da conta (categoria) | string | Seleção do plano de contas |
| `APROPRIACAO[0][ST_DESCRICAO_CONT]` | Nome da conta (categoria) | string | Trazido junto com conta |
| `APROPRIACAO[0][VL_VALOR_PDES]` | Valor da despesa | float | Formulário |
| `FL_RECORRENTEMANUAL_DES` | Indica despesa recorrente | int | Fixo = `1` |

### 2.2 Campos Opcionais (Expostos no Formulário)

| Campo API | Descrição | Tipo | Default |
|:---|:---|:---|:---|
| `DT_DESPESA_DES` | Data do documento | date | - |
| `ID_TIPO_DOC` | Tipo de documento (1-7) | int | - |
| `ST_DOCUMENTO_DES` | Número do documento | string | - |
| `ST_SERIENOTA_DES` | Série da nota | string | - |
| `APROPRIACAO[0][ST_COMPLEMENTO_APRO]` | Complemento da categoria | string | - |
| `FL_RECORRENTE_DES` | Tipo de recorrência (-1/0/1/2) | int | `-1` |

### 2.3 Campos Fixos (não expostos, enviados via lógica)

| Campo API | Valor | Motivo |
|:---|:---|:---|
| `APROPRIACAO[0][ST_NOMEGRUPOSALDO_GS]` | `Ordinário` | Grupo padrão |
| `APROPRIACAO[0][ID_GRUPOSALDO_GS]` | `1` | ID do grupo Ordinário |
| `FL_ACAO_IMPRESSAO` | `1` | Apenas registrar |
| `FL_RECORRENTEMANUAL_DES` | `1` | Para recorrentes |

### 2.4 Campos Não Implementados

Conforme acordo com o usuário, os seguintes campos **não serão implementados** nesta versão:

- `RETENCOES` (impostos/retenções)
- `CHECK_LIQUIDAR_TODOS_CH` e campos de liquidação
- `ID_CONTABANCO_CB` (conta bancária)
- `ARQUIVOS` (vínculo de arquivos)
- `DADOS_PAGAMENTOS` / `ID_FAVORECIDO_FAV`
- `NM_NUMERO_CH` (número do cheque)
- `ST_ENVELOPEETIQUETA_PDES`

---

## 3. Endpoints API Utilizados

| Método | Endpoint | Connector | Objetivo |
|:---|:---|:---|:---|
| `GET` | `/despesas/index` | `SuperLogicaDespesaConnector` | Listar despesas (já existente) |
| `POST` | `/despesas` | `SuperLogicaDespesaConnector` | **Cadastrar despesa (novo)** |
| `GET` | `/fornecedores/index` | `SuperLogicaDespesaConnector::listarFavorecido()` | Buscar lista de fornecedores |
| `GET` | `/planocontas/index` | `SuperLogicaCondominioPlanoDeContaConnector` | Buscar contas/categorias |

---

## 4. Arquitetura

```
ListarContaPagar
├── getHeaderActions() → CadastrarDespesaAction (Action)
├── Action Schema
│   ├── FornecedorSelect  → GET /fornecedores/index (dinâmico)
│   ├── DataVencimento   → DT_VENCIMENTOPRIMEIRAPARCELA
│   ├── FormaPagamento   → ID_FORMA_PAG (mapa fixo)
│   ├── ContaSelect      → GET /planocontas/index (dinâmico)
│   ├── Complemento      → APROPRIACAO[0][ST_COMPLEMENTO_APRO]
│   ├── Valor           → APROPRIACAO[0][VL_VALOR_PDES]
│   ├── DataDocumento   → DT_DESPESA_DES (opcional)
│   ├── TipoDocumento   → ID_TIPO_DOC (opcional)
│   ├── NumeroDocumento → ST_DOCUMENTO_DES (opcional)
│   ├── SerieNota       → ST_SERIENOTA_DES (opcional)
│   └── Recorrente      → FL_RECORRENTE_DES (opcional)
├── mutateFormDataBeforeCreate() → Monta APROPRIACAO[0] + campos fixos
└── handleAction() → $service->despesa()->cadastrar($data)
```

---

## 5. Alterações por Arquivo

### 5.1 `app/Services/SuperLogica/Condominio/SuperLogicaDespesaConnector.php`

**Mudança:** Adicionar método `cadastrar()`

```php
public function cadastrar(array $params = []): array
{
    return $this->postForm('/despesas', $params);
}
```

### 5.2 `app/Services/SuperlogicaConnectionService.php`

**Mudança:** Adicionar acesso ao plano de contas.

```php
public function planoDeContas()
{
    return new SuperLogicaCondominioPlanoDeContaConnector($this->tenant);
}
```

### 5.3 `app/Filament/Condominio/Pages/ListarContaPagar.php`

**Mudanças:**

1. **Novas importações:**
   - `Filament\Forms\Components\Select`
   - `Filament\Support\Facades\FilamentNotification`

2. **`getHeaderActions()`:** adiciona `Action::make('cadastrarDespesa')` com formulário

3. **`getDespesaFormSchema()`:** array com todos os campos

4. **`getFornecedoresOptions()`:** busca `$service->despesa()->listarFavorecido()`

5. **`getPlanoContasOptions()`:** busca `$service->planoDeContas()->listar()`

6. **`handleCadastrarDespesa()`:** monta payload e chama API

7. **`mutateDespesaData()`:** constrói array `APROPRIACAO[0]` + campos fixos

---

## 6. Validações

| Campo | Regra |
|:---|:---|
| Fornecedor | Obrigatório (`required()`) |
| Data Vencimento | Obrigatório, data válida (`required()`, `date()`) |
| Forma Pagamento | Obrigatório (`required()`) |
| Conta/Categoria | Obrigatório (`required()`) |
| Valor | Obrigatório, numérico > 0 (`required()`, `numeric()`, `min:0.01`) |

---

## 7. Mapas de Referência

**Forma de Pagamento:**
```php
['0'=>'Boleto','1'=>'Cheque','2'=>'Dinheiro','3'=>'Cartão de Crédito',
 '4'=>'Cartão de Débito','7'=>'Débito Automático','8'=>'Trans. Bancária',
 '9'=>'Doc/Ted','10'=>'Outros','11'=>'Tributo s/ código','12'=>'Pix',
 '13'=>'DCTFWeb','14'=>'Pix Copia e Cola']
```

**Recorrência:** `-1=Auto | 0=Extraordinária | 1=Recorrente fixa | 2=Recorrente variável`

**Tipo Documento:** `1=Nota Fiscal | 2=Imposto | 3=Fatura | 4=Recibo | 5=Cupom | 6=Outros | 7=Folha`

---

## 8. Tratamento de Erros

| Cenário | Comportamento |
|:---|:---|
| Sucesso (status 1) | Notificação verde + recarrega |
| Erro da API | Notificação vermelha com mensagem |
| Validação | Filament native errors |
| Timeout/rede | Notificação vermelha genérica |

---

## 9. Checklist de Implementação

- [x] `SuperLogicaDespesaConnector::cadastrar()`
- [x] `SuperlogicaConnectionService::planoDeContas()`
- [x] `getHeaderActions()` com `cadastrarDespesa`
- [x] `getDespesaFormSchema()`
- [x] `getFornecedoresOptions()`
- [x] `getPlanoContasOptions()`
- [x] `handleCadastrarDespesa()`
- [x] `mutateDespesaData()`
- [x] Mapas `tipoDocumento` e `recorrencia`
- [x] Testes
- [x] `pint --dirty --format agent`

---

## 10. Testes Propostos

```php
// Sucesso
$this->service->despesa()->cadastrar([...])->assertStatus(1);

// Validação de campos obrigatórios
livewire(ListarContaPagar::class)
    ->callAction('cadastrarDespesa')
    ->assertHasFormErrors(['fornecedor','data_vencimento','forma_pagamento','conta','valor']);
```