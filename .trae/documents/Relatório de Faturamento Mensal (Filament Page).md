## Objetivo
- Entregar a tela "Relatório Faturamento" como na imagem: listagem mensal (mês/ano + valor) + total e um gráfico de barras.
- Usar como fonte **obrigatória** a série mensal de [StatisticData::faturamentoMensal](file:///root/projetos/fiscaut-v4.1/app/Filament/Widgets/StatisticData.php) e a Page existente [Faturamento](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/Faturamento.php).

## Diagnóstico do estado atual
- A Page [Faturamento](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/Faturamento.php) existe, mas `generateData()` está vazio e só define issuer no `mount()`.
- A view [faturamento.blade.php](file:///root/projetos/fiscaut-v4.1/resources/views/filament/pages/relatorio/faturamento.blade.php) está placeholder.
- Já existe um ChartWidget de exemplo que consome `StatisticData::faturamentoMensal()` ([FiscalDashboardFaturamentoCompraChart](file:///root/projetos/fiscaut-v4.1/app/Filament/Widgets/FiscalDashboardFaturamentoCompraChart.php)).

## Implementação (código)
### 1) Popular dados na Page
- Implementar `generateData(Issuer $issuer)` em [Faturamento.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/Faturamento.php):
  - Buscar `StatisticData::faturamentoMensal($issuer)`.
  - Normalizar em uma lista de linhas (ex.: `[['data_ref' => '02-2026', 'valor' => 136250.61], ...]`).
  - Calcular `total` (soma do `income`).
  - Garantir ordem para a listagem (mais recente primeiro) e disponibilizar também uma versão cronológica (para o gráfico, se necessário).
  - Tratar `Auth::user()`/`currentIssuer` nulos de forma segura (sem fatal error, mostrando valores 0 e/ou estado vazio).

### 2) Gráfico (barras) reutilizando a mesma fonte
- Criar um **novo ChartWidget** (ex.: `RelatorioFaturamentoMensalChart`) em `app/Filament/Widgets/`:
  - `getData()` chama `StatisticData::faturamentoMensal(Auth::user()->currentIssuer)`.
  - Monta dataset único com `income`.
  - `getType(): 'bar'`.
  - Usar paleta próxima do exemplo já existente (azul translúcido).

### 3) Layout da view (listagem + total + gráfico)
- Atualizar [faturamento.blade.php](file:///root/projetos/fiscaut-v4.1/resources/views/filament/pages/relatorio/faturamento.blade.php) para montar um grid responsivo:
  - Coluna esquerda: título/ação "Gerar Declaração de Faturamento Mensal" (botão) + tabela simples com `DATA REF.` e `FATURAMENTO` e linha de **Total**.
    - Formatação monetária: `R$ {{ formatar_moeda($valor) }}` usando o helper já existente em [helper.php](file:///root/projetos/fiscaut-v4.1/app/Helpers/helper.php).
  - Coluna direita: renderizar o widget do gráfico via Livewire (`@livewire(Widget::class)`), encaixado em um card/section do Filament.

### 4) Botão "Gerar Declaração"
- Implementar inicialmente como ação simples (chama método Livewire na própria Page e dispara uma Notification “em desenvolvimento” ou exporta CSV).
- Se você já tiver o formato esperado (PDF/CSV e filtros), eu adapto nessa mesma etapa.

## Verificação
- Smoke test no painel Filament:
  - Acessar `/admin/faturamento`.
  - Validar: lista aparece com meses, valores em BRL, total correto, e gráfico renderiza.
- Rodar testes automatizados existentes (via Sail, conforme padrão do projeto) e garantir que não quebre o build.

## Arquivos que serão alterados/criados
- Alterar: [Faturamento.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/Faturamento.php)
- Alterar: [faturamento.blade.php](file:///root/projetos/fiscaut-v4.1/resources/views/filament/pages/relatorio/faturamento.blade.php)
- Criar: `app/Filament/Widgets/RelatorioFaturamentoMensalChart.php` (novo ChartWidget)

## Observações de comportamento
- A série mensal usada é a de `StatisticData::faturamentoMensal()`, que hoje mede **saída NFe** (`income`) e **entrada NFe** (`expense`). Para este relatório, a listagem/gráfico usará somente `income` (faturamento), como na imagem.