## Objetivo
- Parar de depender da tabela `nfe_products` e calcular “produtos mais vendidos” a partir do XML armazenado em `nfes.xml` (via accessor `NotaFiscalEletronica::produtos`).
- Retornar os 15 produtos mais vendidos dos últimos 12 meses e exibir no widget de gráfico do dashboard (FilamentPHP v5).

## Diagnóstico (estado atual)
- `StatisticData::produtosMaisVendidos()` faz `leftJoin` em `nfe_products` e ainda agrupa por `quantidade`, o que fragmenta o ranking: [StatisticData.php:L582-L616](file:///root/projetos/fiscaut-v4.1/app/Filament/Widgets/StatisticData.php#L582-L616).
- Os itens da NF-e já existem no XML e são expostos pelo accessor `getProdutosAttribute()` no model: [NotaFiscalEletronica.php:L108-L156](file:///root/projetos/fiscaut-v4.1/app/Models/NotaFiscalEletronica.php#L108-L156).
- O widget `FiscalDashboardProdutoVendivoChart` chama a função, mas não monta `labels/datasets`: [FiscalDashboardProdutoVendivoChart.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Widgets/FiscalDashboardProdutoVendivoChart.php).

## Alterações planejadas
### 1) Reescrever `StatisticData::produtosMaisVendidos($issuer)` para usar XML
- Consultar NF-es de saída do emitente (issuer) no período:
  - filtros: `tenant_id`, `emitente_cnpj = $issuer->cnpj`, `status_nota = 100`, `data_emissao >= now()->subMonths(12)->startOfDay()`.
  - selecionar colunas mínimas (`id`, `xml`) e iterar com `chunkById()` para não estourar memória.
- Para cada NF-e, iterar `$nfe->produtos` (derivado do XML) e acumular por produto:
  - chave do agrupamento: preferir `cProd` (código) e fallback para `xProd` normalizado.
  - somatórios: `amount` (soma de `qCom`) e `total` (soma de `vProd`).
  - normalização numérica: reutilizar `StatisticData::normalizeMoney()` para lidar com vírgula/ponto.
- Após o scan:
  - ordenar por `amount` desc (e desempate por `total`), pegar top 15.
  - retornar no mesmo formato já esperado pela UI: `[{ label, amount, total }]`.

### 2) Implementar o gráfico no widget `FiscalDashboardProdutoVendivoChart`
- Montar `labels` e `datasets` no padrão do `ChartWidget` do Filament v5, seguindo o estilo do widget existente: [FiscalDashboardFaturamentoCompraChart.php:L17-L59](file:///root/projetos/fiscaut-v4.1/app/Filament/Widgets/FiscalDashboardFaturamentoCompraChart.php#L17-L59).
- Trocar `getType()` para `bar` (ranking funciona melhor que linha).
- Usar barras horizontais (`indexAxis: 'y'`) via `getOptions()` para melhorar legibilidade de nomes longos.
- Dataset principal: “Quantidade” (dados de `amount`).
- (Opcional, se você quiser já de primeira) adicionar segundo dataset “Total (R$)” com eixo separado — deixo pronto se fizer sentido visualmente.

## Verificação
- Criar um teste automatizado simples (Pest/PHPUnit) com 2 NF-es de exemplo (XML mínimo com `det/prod`), garantindo que:
  - agrega corretamente por `cProd/xProd`;
  - respeita o filtro de 12 meses;
  - retorna no máximo 15 itens ordenados.
- Rodar a suíte de testes via Sail (padrão do projeto).<mccoremem id="01KG5PCBPH5FWYH8HYAJV5GKSQ" />

## Impacto esperado
- Dashboard volta a funcionar mesmo sem a tabela de itens.
- Ranking e gráfico passam a refletir “últimos 12 meses” e não “histórico inteiro”.
- Elimina o bug de agrupamento por `quantidade` que distorcia o top 15.