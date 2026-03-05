---
title: Neuron Tools - NF-e Entrada e Saída (FiscautAgent)
status: draft
owner: equipe
---

## Contexto

O painel do Fiscaut possui um agente de IA (Neuron AI for Laravel) definido em:

- `app/Neuron/Agents/FiscautAgent.php`

O prompt do agente já orienta que qualquer operação com dados deve acontecer via ferramentas (tools), porém hoje o método `tools()` está vazio, então o agente não consegue consultar ou estruturar dados reais das NF-es.

É necessário criar:

1. Uma tool para o agente lidar com dados de **NF-e de entrada** recebidas, cobrindo os três agrupamentos já existentes no sistema:
   - **Entrada de Terceiros**
   - **Entrada Própria**
   - **Entrada Própria de Terceiros**
2. Uma tool separada para lidar com dados de **NF-e de saída**.

As tools devem reutilizar a modelagem e regras já usadas pelas telas do painel (Filament), principalmente a model `NotaFiscalEletronica` e seus scopes.

## Objetivos

- Permitir que o agente consulte NF-es de **entrada** por filtros usuais (chave, número, CNPJ, período), distinguindo os três tipos de entrada.
- Permitir que o agente consulte NF-es de **saída** (documentos emitidos pelo issuer atual), com filtros usuais.
- Fornecer ao agente dados estruturados e “prontos para conversa” (cabeçalho, emitente/destinatário, totais, CFOPs, status).
- Expor um modo de “detalhe” para retornar itens/produtos quando necessário, evitando payload pesado por padrão.
- Garantir escopo por issuer/tenant (multi-tenancy), sem vazamento de dados entre emissores.

## Não-objetivos

- Não implementar download/manifestação/consulta na SEFAZ via tool nesta entrega.
- Não retornar XML bruto (string) ao modelo.
- Não criar ou alterar esquema de banco/migrations.
- Não mudar a lógica de classificação/etiquetas (tags) existente, apenas expor dados já persistidos.

## Regras de domínio (referência do sistema)

### Entrada (três abas atuais)

As telas do painel já separam entradas usando scopes em `NotaFiscalEletronica`:

- Entrada de Terceiros: `NotaFiscalEletronica::query()->entradasTerceiros()`
- Entrada Própria: `NotaFiscalEletronica::query()->entradasProprias()`
- Entrada Própria de Terceiros: `NotaFiscalEletronica::query()->entradasPropriasTerceiros()`

### Saída (tela atual)

A tela de NF-e Saída usa como critério principal:

- `emitente_cnpj = currentIssuer()->cnpj`

## Tool 1 — consulta_nfe_entrada

### Nome

- `consulta_nfe_entrada`

### Responsabilidade

- Consultar NF-es de **entrada** do issuer atual, com suporte ao recorte por tipo de entrada (terceiros / própria / própria de terceiros).
- Retornar lista paginada (por limite) com campos de resumo.
- Quando solicitado, retornar “detalhe” com produtos (itens) e parcelas.

### Propriedades (input schema)

- `tipo_entrada` (string, opcional): um de
  - `terceiros`
  - `propria`
  - `propria_terceiros`
  - Se ausente, pesquisar em todos os tipos.
- `chave` (string, opcional): chave de acesso da NF-e.
- `nNF` (integer, opcional): número da nota.
- `serie` (string|integer, opcional): série.
- `emitente_cnpj` (string, opcional): CNPJ/CPF do emitente (somente dígitos).
- `destinatario_cnpj` (string, opcional): CNPJ/CPF do destinatário (somente dígitos).
- `data_emissao_inicio` (string, opcional): `YYYY-MM-DD`.
- `data_emissao_fim` (string, opcional): `YYYY-MM-DD`.
- `data_entrada_inicio` (string, opcional): `YYYY-MM-DD`.
- `data_entrada_fim` (string, opcional): `YYYY-MM-DD`.
- `incluir_itens` (boolean, opcional): default `false`.
- `limit` (integer, opcional): default `10`, máximo `50`.

### Regras de query

- Escopo obrigatório:  
  - Baseado em `tipo_entrada` aplicar o scope correspondente; se não vier, aplicar “OR” entre os 3 scopes.
- Aplicar filtros informados (chave/nNF/serie/CNPJs/períodos).
- Ordenação default: `data_emissao desc`, `id desc`.
- Quando `incluir_itens = true`:
  - Se `limit > 1`, retornar apenas resumo + `produtos_count` (não retornar lista de produtos).
  - Retornar itens apenas quando o resultado for inequivocamente “detalhe” (ex.: `chave` informada e encontrou 1 registro, ou `limit=1`), para evitar resposta gigante.

### Formato de saída (output)

```json
{
  "count": 1,
  "items": [
    {
      "id": 123,
      "tipo_entrada": "terceiros",
      "chave": "....",
      "nNF": 456,
      "serie": "1",
      "data_emissao": "2026-02-10 10:15:00",
      "data_entrada": "2026-02-11 00:00:00",
      "emitente": { "cnpj": "....", "razao_social": "...." },
      "destinatario": { "cnpj": "....", "razao_social": "...." },
      "status_nota": "AUTORIZADA",
      "vNfe": "999.90",
      "cfops": ["5102"],
      "produtos_count": 12,
      "difal_total": 0.0,
      "tags": [{ "id": 1, "name": "..." }]
    }
  ],
  "warnings": []
}
```

Campos obrigatórios no resumo:

- Identificação: `id`, `chave`, `nNF`, `serie`
- Datas: `data_emissao`, `data_entrada`
- Partes: emitente/destinatário (cnpj + razão)
- Totais: `vNfe`, `difal_total`
- Classificação: `tipo_entrada`
- Fiscal: `cfops`

Quando retornando detalhe, incluir:

- `produtos`: array (via accessor `NotaFiscalEletronica->produtos`)
- `parcelas`: array (via accessor `NotaFiscalEletronica->parcelas`)

## Tool 2 — consulta_nfe_saida

### Nome

- `consulta_nfe_saida`

### Responsabilidade

- Consultar NF-es de **saída** (documentos emitidos pelo issuer atual), com filtros usuais.
- Retornar resumo e opcionalmente detalhe com itens.

### Propriedades (input schema)

- `chave` (string, opcional)
- `nNF` (integer, opcional)
- `serie` (string|integer, opcional)
- `destinatario_cnpj` (string, opcional)
- `data_emissao_inicio` (string, opcional): `YYYY-MM-DD`
- `data_emissao_fim` (string, opcional): `YYYY-MM-DD`
- `incluir_itens` (boolean, opcional): default `false`
- `limit` (integer, opcional): default `10`, máximo `50`

### Regras de query

- Escopo obrigatório:
  - `emitente_cnpj = currentIssuer()->cnpj`
- Aplicar filtros informados.
- Ordenação default: `data_emissao desc`, `id desc`.
- Mesma regra de “detalhe” do `incluir_itens` para evitar payload grande.

### Formato de saída (output)

Mesmo formato do `consulta_nfe_entrada`, sem `tipo_entrada` (ou com `tipo_documento: "saida"`).

## Arquitetura / Implementação

### Novas classes

- `app/Neuron/Tools/ConsultaNfeEntradaTool.php`
- `app/Neuron/Tools/ConsultaNfeSaidaTool.php`

Ambas devem:

- Estender `NeuronAI\Tools\Tool`
- Definir `properties()` via `ToolProperty`/`PropertyType`
- Implementar `__invoke(...)` retornando array simples serializável

### Integração no agente

Atualizar `FiscautAgent::tools()` para registrar as novas tools, garantindo que o modelo possa chamá-las durante o chat.

Também atualizar o bloco `toolsUsage` do prompt do agente para incluir:

- `consulta_nfe_entrada`
- `consulta_nfe_saida`

## Segurança e conformidade

- Restringir acesso por issuer/tenant com `currentIssuer()`.
- Nunca retornar o conteúdo do XML bruto na resposta da tool.
- Sanitizar e limitar payload:
  - `limit` máximo 50
  - itens retornados apenas em modo “detalhe”
- Não registrar payload de tool em logs por padrão (CNPJ/chave podem ser sensíveis).

## Critérios de aceitação

- As duas tools existem e estão registradas no `FiscautAgent`.
- `consulta_nfe_entrada` permite filtrar por `tipo_entrada` e retorna `tipo_entrada` corretamente.
- `consulta_nfe_saida` retorna apenas documentos emitidos pelo issuer atual.
- Respostas são determinísticas, estruturadas e não incluem XML.
- `incluir_itens=true` retorna itens somente em modo de detalhe (sem estourar payload em listas).
