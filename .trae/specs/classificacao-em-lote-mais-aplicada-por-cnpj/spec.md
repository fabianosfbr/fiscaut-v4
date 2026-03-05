---
title: Classificação em lote pela etiqueta “mais aplicada” (por CNPJ emitente)
status: draft
owner: equipe
---

## Contexto

Hoje existe “Classificar Documento” individual e em lote, onde o usuário escolhe manualmente uma etiqueta. Também existe a ação “Sugerir etiquetas” (individual) baseada no histórico de etiquetas aplicadas para o **CNPJ emitente**.

Precisa existir uma **ação em lote** que, para os documentos fiscais selecionados, aplique automaticamente a etiqueta **mais aplicada** (maior número de ocorrências no histórico) para **cada CNPJ emitente**.

Também precisa incluir a opção de informar a **data de entrada** no modal, no mesmo padrão da classificação individual (visibilidade controlada por configuração).

## Objetivos

- Disponibilizar uma ação em lote para aplicar automaticamente a etiqueta “mais aplicada” por CNPJ emitente.
- Permitir informar “Data Entrada” no modal, quando a configuração estiver habilitada, como ocorre na classificação individual.
- Manter o comportamento consistente com o padrão atual:
  - aplicar uma única etiqueta por documento (substituindo classificação anterior);
  - usar o valor do documento como “value” da tag via `retag()`;
  - exibir feedback ao final (sucesso e contagem de ignorados quando aplicável).

## Não-objetivos

- Não criar/alterar fluxo de “classificação avançada” (múltiplas etiquetas por documento).
- Não alterar o algoritmo de sugestões existente para o modal individual; apenas reutilizar/estender para atender ao uso em lote.
- Não criar novos modelos/tabelas de auditoria.

## Escopo (onde aparece)

Adicionar a nova ação em lote nas tabelas que já possuem “Classificar Documento” em lote:

- NF-e Entradas
- NF-e Saídas
- NFS-e Entradas
- CT-e Tomadas

## UX do modal

### Texto e confirmação

- Título: “Classificar por mais aplicada”
- Descrição: “Aplica automaticamente, para cada CNPJ emitente, a etiqueta mais utilizada no histórico. Documentos sem histórico serão ignorados.”
- Botão de confirmação: “Sim, etiquetar”

### Campo de data

- Campo: “Data Entrada”
- Comportamento igual ao fluxo individual:
  - `default(now())`
  - `required()` quando visível
  - `visible()` condicionado a `GeneralSetting::getValue(name: 'configuracoes_gerais', key: 'isNfeClassificarNaEntrada', ...)`
- Persistência:
  - Quando o campo estiver presente no payload, aplicar em todos os registros do lote.
  - Quando não estiver presente (campo invisível), manter o comportamento atual do fluxo em lote: preencher `data_entrada` com `now()` (para manter consistência com o lote já existente).

## Regras de negócio

### Determinação da “mais aplicada”

Para cada CNPJ emitente presente no lote:

1. Buscar no histórico de documentos do mesmo “tipo” (ex.: NF-e para NF-e; NFS-e para NFS-e) a etiqueta com maior número de ocorrências (COUNT de `tagging_tagged`).
2. Considerar apenas etiquetas:
   - habilitadas (`tagging_tags.is_enable = true`);
   - pertencentes ao emissor atual (`tagging_tags.issuer_id = currentIssuer()->id`), para impedir cross-issuer.
3. Critérios de desempate (determinístico):
   - maior `COUNT(*)`
   - depois, maior `tagging_tagged.id` (mais recente), se necessário
   - depois, menor `tag_id` (estável), se necessário

### Aplicação no lote

- Para cada registro selecionado:
  - Identificar seu CNPJ emitente:
    - NF-e: `emitente_cnpj`
    - NFS-e: `prestador_cnpj`
    - CT-e: `emitente_cnpj`
  - Se o CNPJ estiver vazio/nulo, ignorar o registro.
  - Se não houver etiqueta “mais aplicada” elegível para aquele CNPJ, ignorar o registro.
  - Caso exista, aplicar via `$record->retag($tagId)`.
  - Aplicar `data_entrada` conforme a regra do modal.

### Feedback ao usuário

- Mostrar uma notificação ao final com:
  - total selecionado
  - total classificado com sucesso
  - total ignorado (sem CNPJ / sem histórico elegível)
- Manter os hooks atuais de “after” nas tabelas para limpar caches existentes (ex.: `tags_used_in_nfe_*`) e disparar notificação de sucesso, ajustando se necessário para evitar duplicidade de mensagens.

## Arquitetura / Implementação

### Novas/alteradas classes

- Nova action Filament (BulkAction):
  - `app/Filament/Actions/ClassificarDocumentoMaisAplicadaEmLoteAction.php`
- Serviço de apoio (extensão do existente):
  - `App\Services\Tagging\TagSuggestionService`
    - adicionar métodos voltados a retornar **um** `tag_id` (mais aplicado) por CNPJ, com filtros de issuer e is_enable.

### Estratégia de query (eficiência)

- Otimização desejada: calcular a etiqueta “mais aplicada” **uma vez por CNPJ** do lote (não por documento).
- Implementação sugerida:
  - extrair lista única de CNPJs do lote;
  - realizar query agregada por CNPJ e tag_id;
  - reduzir para “top 1 por CNPJ” em PHP (aplicando desempate determinístico) ou via SQL com window functions (MySQL 8).

### Observabilidade e segurança

- Não registrar CNPJs completos e tags em logs por padrão.
- Erros de query devem virar notificação amigável, sem stack trace no UI.

## Critérios de aceitação

- Ação aparece nas telas de NF-e Entradas, NF-e Saídas e NFS-e Entradas junto das ações em lote existentes.
- Ao executar em lote:
  - cada documento recebe a etiqueta “mais aplicada” do seu CNPJ emitente, quando existir histórico elegível;
  - documentos sem CNPJ ou sem histórico elegível são ignorados (não são alterados);
  - “Data Entrada” pode ser informada quando a configuração estiver habilitada e é aplicada a todos do lote.
- A execução exibe notificação final com contagens (total, classificados, ignorados).
