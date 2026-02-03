# Dashboard Fiscal (Estatísticas Pré-calculadas)

## Objetivo
Garantir carregamento do dashboard em até 2s consultando apenas estatísticas mensais pré-calculadas, evitando recálculos sobre milhões de documentos.

## KPIs cobertos
- NFe Saída emitidas no mês
- NFe Entrada recebidas no mês
- CTe Emitidos no mês
- CTe Tomados no mês
- NFS-e Tomadas no mês

## Arquitetura (visão geral)

```mermaid
flowchart LR
  A[Base operacional: nfes/ctes/nfses] -->|Agregação mensal em batch| B[ETL: Jobs em fila]
  B -->|Upsert idempotente| C[(statistic_issuers)]
  C -->|Leitura rápida| D[Filament Widgets: Dashboard Fiscal]
  E[Scheduler dinâmico (tabela schedules)] -->|02:00 diário| F[artisan dashboard:refresh-stats]
  F -->|dispara Bus::batch| B
```

## Cache mensal (`statistic_issuers`)
O cache é persistido por tenant + CNPJ + mês + tipo de documento.

### Semântica das colunas
- `tenant_id`: escopo multi-tenant.
- `issuer`: CNPJ (string) usado como chave do emitente/destinatário/tomador.
- `periodo`: `mensal`.
- `doc_tipo`: `nfe` | `cte` | `nfse`.
- `tipo`:
  - `saida`: emitidas (NFe/CTe).
  - `entrada`: recebidas/tomadas (NFe/CTe).
  - `tomador`: tomador (NFS-e).
- `data`: chave do mês `YYYY-MM`.
- `data_ref`: base para particionamento e filtro por data (primeiro dia do mês).
- `valor`: quantidade de documentos no mês (numérico).

### Chave idempotente
O cache é idempotente via índice único:
`(tenant_id, issuer, periodo, doc_tipo, tipo, data)`.

## ETL (Extract, Transform, Load)

### Regras de data
- Emitidas no mês: usa `data_emissao`.
- Recebidas/Tomadas no mês: usa `data_entrada` quando disponível; fallback para `data_emissao` quando `data_entrada` for nula.
- NFS-e tomadas: considera apenas não-canceladas (`cancelada` nula ou `false`).

### Como executar manualmente
- Rodar para mês atual e anterior (padrão):
  - `php artisan dashboard:refresh-stats`
- Rodar para um tenant específico:
  - `php artisan dashboard:refresh-stats --tenant=123`
- Rodar para um CNPJ específico:
  - `php artisan dashboard:refresh-stats --issuer=12345678000199`
- Backfill por range:
  - `php artisan dashboard:refresh-stats --from=2025-01 --to=2025-12`

## Scheduler dinâmico
Existe um agendamento diário padrão cadastrado na tabela `schedules`:
- expressão: `0 2 * * *` (02:00)
- `withoutOverlapping`: habilitado
- `onOneServer`: habilitado
- `runInBackground`: habilitado

## Dashboard no Filament
Página: “Dashboard Fiscal”, acessível no menu “Relatórios”.

Widgets:
- KPIs do mês atual.
- Série mensal dos últimos 12 meses.

## Índices e performance
Para acelerar agregações mensais e leitura do dashboard, foram adicionados índices compostos por tenant + CNPJ + data nas tabelas fonte e índices no cache.

## Particionamento
O particionamento está preparado via coluna `data_ref` no cache. Se necessário, pode-se particionar por RANGE em `data_ref` por mês/ano.

