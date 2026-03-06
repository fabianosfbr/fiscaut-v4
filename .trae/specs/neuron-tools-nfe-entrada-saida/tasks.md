---
title: Tarefas - Neuron Tools NF-e Entrada e Saída
status: draft
---

## Implementação

1. Criar tool de consulta para NF-e de entrada
   - Implementar `ConsultaNfeEntradaTool` com schema de propriedades, filtros e saída padronizada.
   - Reutilizar scopes de `NotaFiscalEletronica` para os três tipos de entrada.

2. Criar tool de consulta para NF-e de saída
   - Implementar `ConsultaNfeSaidaTool` com schema de propriedades, filtros e saída padronizada.
   - Reutilizar a regra do painel: `emitente_cnpj = currentIssuer()->cnpj`.

3. Integrar tools no FiscautAgent
   - Registrar as tools em `FiscautAgent::tools()`.
   - Atualizar o texto de ferramentas no prompt (`toolsUsage`) para incluir as novas tools.

## Qualidade

4. Cobrir com testes
   - Testes unitários das tools validando filtros, escopo por tenant e “modo detalhe” de itens.
   - Garantir que testes não dependam de migrations frágeis (preferir factories/stubs e banco isolado quando necessário).

5. Revisão de segurança
   - Garantir ausência de retorno de XML bruto e limitação de payload.
   - Garantir que consultas respeitam `currentIssuer()` e `tenant_id`.
