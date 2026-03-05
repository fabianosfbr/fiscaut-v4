---
title: Checklist - Classificação em lote pela etiqueta “mais aplicada” (por CNPJ emitente)
status: draft
---

## Funcional

- Ação em lote disponível em NF-e Entradas, NF-e Saídas e NFS-e Entradas
- Modal mostra “Data Entrada” somente quando configuração estiver habilitada
- Para cada CNPJ emitente do lote, aplica a etiqueta com maior ocorrência no histórico
- Documentos sem CNPJ ou sem histórico elegível são ignorados (sem alterações)
- Notificação final exibe total selecionado, classificados e ignorados

## Regras / Dados

- Query filtra por `tagging_tags.issuer_id = currentIssuer()->id`
- Query ignora tags desabilitadas (`is_enable = true`)
- Desempate determinístico (count > recência > tag_id)

## UI/UX

- Textos do modal deixam claro que é automático e que pode haver ignorados
- Botão de confirmação e ícone seguem padrão das ações atuais
- Não há travamento perceptível em lotes maiores (sem N+1)

## Qualidade

- Testes cobrindo seleção do “mais aplicado” e filtros (issuer/is_enable)
- Testes cobrindo aplicação em lote e contagens
