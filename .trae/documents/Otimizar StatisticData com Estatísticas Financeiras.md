## Objetivo
- Expandir `StatisticData` para suportar estatísticas financeiras (total, média, tendência e comparativos) mantendo performance.
- Implementar cache persistente no banco seguindo o padrão do cache de quantidades (sem criar coluna `categoria_fiscal`).

## 1) Ajuste de Modelo de Cache (statistic_issuers)
- Adicionar coluna `metrica` (string) em `statistic_issuers` com default `qtd`.
- Ajustar o índice único existente para incluir `metrica`, evitando colisão entre quantidade (`qtd`) e valores monetários (`valor_*`).
- Backfill: preencher `metrica = 'qtd'` para linhas existentes.

## 2) ETL mensal para valores financeiros (batch assíncrono)
- Criar um job agregador mensal de valores (similar ao agregador de contagens) que grava em `statistic_issuers` com:
  - `doc_tipo` = `nfe|cte|nfse`
  - `tipo` = `entrada|saida|tomador`
  - `metrica` =
    - `valor_total` (ex.: NFe: `SUM(vNfe)`, CTe: `SUM(vCTe)`, NFSe: `SUM(valor_servico)`)
    - `icms`, `icms_st`, `ipi`, `pis`, `cofins` (para NFe via `SUM(vICMS)`, `SUM(vST)`, `SUM(vIPI)`, `SUM(vPIS)`, `SUM(vCOFINS)`)
  - `data` = `YYYY-MM`
- Regras e filtros:
  - Sempre por `tenant_id`.
  - NFe/CTe apenas documentos “autorizados” (status 100, como já usado hoje em `StatisticData`).
  - NFSe ignorar canceladas.
- Integrar a orquestração no comando existente `dashboard:refresh-stats` para disparar também os jobs financeiros no mesmo batch.

## 3) Otimização da classe StatisticData
- Manter os métodos existentes (assinaturas atuais) para não quebrar widgets atuais.
- Adicionar novos métodos de alto nível, por exemplo:
  - `getMonthlyFinancialSeries(tenantId, issuerCnpj, fromMonth, toMonth, docTipo, tipo, metrica)`
  - `computeTotal(series)`
  - `computeAverage(series)`
  - `computeTrend(series)` (ex.: slope simples + direção)
  - `computePeriodComparisons(series)` (MoM e YoY quando aplicável)
- Validação de dados monetários:
  - Normalizar `null`/não-numérico → 0.
  - Tratar `NaN/INF` e valores negativos (regra: clamp 0 ou manter e sinalizar via log; definiremos e aplicaremos consistentemente).
  - Padronizar arredondamento/precisão (4 casas, compatível com `decimal(10,4)`).
- Tratamento de erros:
  - `try/catch` ao consultar cache/tabelas de origem; em falha retorna série zerada e registra erro.

## 4) Filtros por período, tipo de documento e categoria fiscal (sem coluna)
- Período e tipo de documento serão resolvidos via `statistic_issuers` (cache persistente).
- Categoria fiscal (quando aplicável) será suportada **sem persistir no banco**:
  - Implementar query otimizada (ex.: NFe via itens + CFOP) apenas quando um filtro de categoria for solicitado.
  - Cache em `Cache::remember()` com chave que inclui `tenantId/issuerCnpj/periodo/docTipo/tipo/metrica/categoria/intervalo`.
  - Sem alteração de schema (não cria `categoria_fiscal`).

## 5) Testes unitários
- Adicionar testes unitários para:
  - cálculos de total/média/tendência/comparativos (com séries determinísticas)
  - validação/normalização de valores monetários
  - comportamento em erros (mock de consulta retornando exceção → fallback zerado)
- Ajustar migration `2021_11_09_184855_drop_foreign_plan_in_tenants_table.php` para ser compatível com SQLite (trocar `dropForeign('...')` por `dropForeign(['plan_id'])` e/ou guardas), permitindo rodar `php artisan test` no ambiente atual.

## 6) Documentação
- Adicionar PHPDoc para todos os novos métodos e parâmetros na `StatisticData`.
- Atualizar `docs/dashboard-fiscal.md` com uma seção de “estatísticas financeiras” (métricas, chaves de cache e regras de cálculo), sem mencionar `categoria_fiscal` em schema.

## Verificação
- Rodar `php artisan test` e validar que os novos testes passam e que o restante do suite volta a executar no SQLite.