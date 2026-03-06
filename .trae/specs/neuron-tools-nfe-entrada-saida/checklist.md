---
title: Checklist - Neuron Tools NF-e Entrada e Saída
status: draft
---

## Funcional

- Tool `consulta_nfe_entrada` retorna apenas dados do issuer/tenant atual
- `tipo_entrada` filtra corretamente (terceiros / própria / própria de terceiros)
- Tool `consulta_nfe_saida` retorna apenas documentos com `emitente_cnpj` do issuer atual
- Filtros por chave, número, CNPJ e período funcionam
- `limit` respeita máximo e ordenação default é consistente

## Dados / Payload

- Resumo não inclui XML
- `incluir_itens=true` retorna itens apenas em modo “detalhe”
- Campos retornados seguem o contrato (emitente/destinatário, totais, CFOPs, status)

## Segurança

- Não há logs com chave/CNPJ/XML por padrão
- Não existe caminho para consultar dados de outro issuer/tenant

## Testes

- Testes cobrindo: entrada (3 tipos), saída, filtros, limite, modo detalhe
- Testes evitam dependências de migrations frágeis (quando aplicável)
