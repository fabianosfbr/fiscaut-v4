## Objetivo
- Fazer a página Filament [EntradaVsImposto.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/EntradaVsImposto.php) fornecer os dados esperados pela view [entrada-vs-imposto.blade.php](file:///root/projetos/fiscaut-v4.1/resources/views/filament/pages/relatorio/entrada-vs-imposto.blade.php), para o relatório renderizar sem erros.

## Implementação
- Seguir o mesmo padrão dos outros relatórios (ex.: [Faturamento.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/Faturamento.php)):
  - Adicionar propriedades públicas `?Issuer $issuer` e `array $faturamento`.
  - Implementar `mount()` pegando `Auth::user()?->currentIssuer` e chamando `generateData()`.
  - Implementar `generateData(?Issuer $issuer)` usando `StatisticData::entradaVsImpostoMensal($issuer)` para popular `$this->faturamento`.
- Robustez para “funcionar perfeitamente” com a view:
  - Garantir que cada mês tenha todas as chaves usadas no Blade (`faturamento`, `faturamento-nfse`, `icms`, `icmsST`, `ipi`, `pis`, `cofins`, `cprb`, `csll`, `irpj`, `faturamentoLiquido`), preenchendo ausentes com `0.0` e normalizando para `float`.

## Verificação
- Validar no browser acessando o menu Relatórios → “Entrada vs Imposto” e confirmar:
  - Sem “Undefined variable: faturamento”/“Undefined index”.
  - Tabela renderizando e totais/percentuais calculando.
- (Opcional, mas recomendado) Criar um teste unitário simples (Pest) que chama `StatisticData::entradaVsImpostoMensal($issuer)` e valida o “shape” dos dados retornados.
- Rodar a suíte relevante via Sail para garantir que nada quebre.