---
title: Tarefas - Classificação em lote pela etiqueta “mais aplicada” (por CNPJ emitente)
status: draft
---

## Implementação

1. Criar action em lote “mais aplicada”
   - Adicionar `ClassificarDocumentoMaisAplicadaEmLoteAction` seguindo o padrão das ações existentes.
   - Incluir modal com “Data Entrada” condicional (mesma regra do fluxo individual).
   - Implementar aplicação em lote agrupando por CNPJ emitente e usando `retag()`.

2. Estender serviço de sugestões para retornar “top 1”
   - Atualizar `TagSuggestionService` para retornar o `tag_id` mais aplicado por CNPJ para NF-e.
   - Adicionar suporte a NFS-e (prestador) com filtros de issuer e is_enable.
   - Garantir desempate determinístico.

3. Integrar a ação nas tabelas
   - Adicionar a nova BulkAction em:
     - NF-e Entradas
     - NF-e Saídas
     - NFS-e Entradas
   - Ajustar “after hooks”/notificações para evitar mensagens duplicadas.

## Qualidade

4. Testes
   - Cobrir o serviço (query) com testes que validem:
     - seleção do “mais aplicado” e desempate
     - filtro por issuer e is_enable
   - Cobrir a action com teste de integração (quando viável) validando:
     - agrupamento por CNPJ e aplicação correta
     - contagem de ignorados

5. Revisão de performance
   - Validar que o algoritmo não faz N+1 queries por documento.
   - Validar comportamento com muitos registros selecionados.
