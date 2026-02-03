## Estado Atual (levantamento rápido)
- A tabela `statistic_issuers` já existe, mas hoje não há Model/Service/Jobs consumindo ou populando esses dados; ela só aparece em migrations.
- O painel é Filament/Livewire; a forma correta de exibir o dashboard é via Widgets/Pages do Filament.
- Já existe infraestrutura de processamento assíncrono com `Bus::batch()` e jobs (importação XML/SEFAZ), e há scheduler dinâmico via tabela `schedules`.

## Objetivo da Solução
- Resposta do dashboard em até 2s consultando apenas dados agregados.
- Processamento assíncrono em lote (batch) para milhões de documentos/mês.
- Cache mensal por emitente/tomador usando `statistic_issuers`.
- Exibição via Widgets Filament (sem APIs REST e sem criação de tests).

## Modelo de Dados (cache em `statistic_issuers`)
- Padronização:
  - `periodo = 'mensal'`
  - `doc_tipo ∈ { 'nfe', 'cte', 'nfse' }`
  - `tipo`:
    - NFe Saída emitidas → `saida`
    - NFe Entrada recebidas → `entrada`
    - CTe Emitidos → `saida`
    - CTe Tomados → `entrada`
    - NFS-e Tomadas → `tomador`
  - `issuer` = CNPJ (compatível com o padrão atual de filtragem no Filament).
  - `data` = `YYYY-MM`.
  - `valor` = quantidade de documentos (numérico).
- Idempotência:
  - Criar índice **único** `(tenant_id, issuer, periodo, doc_tipo, tipo, data)` para viabilizar upsert.

## Estratégia de ETL (batch assíncrono)
- Pipeline em duas camadas:
  1) **Orquestrador** (Command/Job) define o range (default: mês atual + anterior) e dispara o batch.
  2) **Jobs agregadores** executam queries por tenant+mês+doc_tipo/tipo com `GROUP BY` do CNPJ e fazem upsert em `statistic_issuers`.
- Regras de data:
  - Emitidas no mês: `data_emissao`.
  - Recebidas/Tomadas: preferir `data_entrada` (fallback `data_emissao` quando nulo).
  - NFS-e tomadas: considerar apenas não-canceladas (ou expandir depois com métrica separada).
- Controle de concorrência:
  - Locks por tenant+mês (cache lock) + scheduler `withoutOverlapping`.

## Índices Otimizados
- Criar/ajustar índices compostos nas tabelas fonte para acelerar as agregações mensais:
  - `nfes`: (tenant_id, emitente_cnpj, data_emissao) e (tenant_id, destinatario_cnpj, data_entrada) + fallback (tenant_id, destinatario_cnpj, data_emissao).
  - `ctes`: (tenant_id, emitente_cnpj, data_emissao) e (tenant_id, destinatario_cnpj, data_entrada) + fallback (tenant_id, destinatario_cnpj, data_emissao).
  - `nfses`: (tenant_id, tomador_cnpj, data_entrada) + fallback (tenant_id, tomador_cnpj, data_emissao) e (tenant_id, tomador_cnpj, cancelada).
- Em `statistic_issuers`: índice único acima + índice de leitura (tenant_id, issuer, data).

## Particionamento por Mês/Ano
- Fase 1 (baixo risco): particionar o cache `statistic_issuers` por mês/ano para acelerar leitura e facilitar retenção.
- Fase 2 (alto impacto, opcional): avaliar particionar `nfes/ctes/nfses`.
  - Nota: em MySQL, particionar tabelas existentes pode exigir alterações em PK/unique (janela de manutenção).

## Dashboard no Filament (Widgets)
- Criar um “Fiscal Dashboard” no Filament com Widgets que leem apenas `statistic_issuers`:
  - **StatsOverviewWidget** com os 5 KPIs do mês selecionado (default: mês atual).
  - **ChartWidget** com série dos últimos 12 meses por doc_tipo/tipo.
  - Opcional: filtro (issuer/mês) respeitando `currentIssuer` e `tenant_id`.
- SLA 2s:
  - Queries sempre por `tenant_id` + `issuer` + range de `data` curto (12–24 meses).
  - Cache em memória no service/widget (TTL curto) e invalidação após ETL do mês atual.

## Jobs Agendados (fora do horário comercial)
- Criar `dashboard:refresh-stats` para disparar o ETL em batch.
- Registrar na infraestrutura de schedules dinâmicos para rodar diariamente (ex.: 02:00) com:
  - `withoutOverlapping`, `onOneServer`, `runInBackground`.

## Disponibilidade 99.9%, Replicação e Degradação
- Recomendar read-replica para leitura do dashboard (quando habilitado), mantendo escrita do ETL no primário.
- Se dados do mês estiverem ausentes, widget mostra “processando” + última atualização (sem recompute síncrono).

## Monitoramento e Alertas
- Usar `job_batches`, `failed_jobs` e histórico do scheduler.
- Emitir logs estruturados e alertas via canal `slack` em falhas/timeout de batch.
- Notificações em banco (Filament) ao concluir o batch (sucesso/erro).

## Documentação Técnica (com diagramas)
- Documentar em `docs/`:
  - Diagrama C4 do ETL + Filament.
  - Modelo de dados (statistic_issuers + índices).
  - Runbook operacional (backfill/reprocessamento/SLAs/troubleshooting).

## Implementação no Repositório (o que será criado/alterado)
- Model/Service: `StatisticIssuer` + service de leitura para widgets + service ETL.
- Jobs/Commands: orquestrador + jobs agregadores em batch.
- Migrations: índices (incluindo unique) e suporte ao particionamento (se aprovado).
- Filament: Dashboard/Page + Widgets consumindo o cache.