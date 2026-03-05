## Objetivo

Centralizar o acesso ao issuer atual do usuário (hoje espalhado como `Auth::user()->currentIssuer`) em uma função helper única, com cache, e substituir as ocorrências no codebase para padronização e facilidade de manutenção.

## Contexto técnico

- `currentIssuer` é um relacionamento `belongsTo` em `App\Models\User`, via coluna `issuer_id`, com eager-load global em `$with = ['currentIssuer']`.
- Apesar do eager-load, o codebase repete muitas vezes `Auth::user()->currentIssuer` (incluindo acesso a `->id`, `->cnpj`, `->razao_social`, e checagens de null), o que dificulta manutenção e padronização.

## Levantamento de ocorrências

### Onde `currentIssuer` é definido

- User model: [User.php](file:///root/projetos/fiscaut-v4.1/app/Models/User.php#L15-L74)
  - `$with = ['currentIssuer'];`
  - `currentIssuer(): belongsTo(Issuer::class, 'issuer_id')`

### Ocorrências encontradas (Auth::user()->currentIssuer e variações)

#### Views / Blade
- [sieg-import.blade.php](file:///root/projetos/fiscaut-v4.1/resources/views/filament/pages/importar/sieg-import.blade.php#L6)
- [issuer-switcher.blade.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/%E2%9A%A1issuer-switcher/issuer-switcher.blade.php#L27)
- [configuracao-geral.blade.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1configuracao-geral/configuracao-geral.blade.php#L13)

#### Issuer Switcher (Livewire component em View)
- [issuer-switcher.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/%E2%9A%A1issuer-switcher/issuer-switcher.php#L1-L97) (altera `issuer_id` do usuário no `afterStateUpdated`)

#### Widgets / Pages / Actions (Filament)
- Widgets
  - [FiscalDashboardProdutoVendivoChart.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Widgets/FiscalDashboardProdutoVendivoChart.php#L16)
  - [FiscalDashboardFaturamentoCompraChart.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Widgets/FiscalDashboardFaturamentoCompraChart.php#L16)
  - [RelatorioFaturamentoMensalChart.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Widgets/RelatorioFaturamentoMensalChart.php#L16)
  - [RelatorioEntradaSaidaChart.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Widgets/RelatorioEntradaSaidaChart.php#L16)
  - [MinMaxNsuOverview.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/LogSefazNfeContents/Widgets/MinMaxNsuOverview.php#L15)
  - [MinMaxNsuCteOverview.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/LogSefazCteContents/Widgets/MinMaxNsuCteOverview.php#L15)
- Pages (relatórios)
  - [RelatorioResumoEtiquetaNfe.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/RelatorioResumoEtiquetaNfe.php#L46)
  - [RelatorioResumoEtiquetaNfse.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/RelatorioResumoEtiquetaNfse.php#L42)
  - [Faturamento.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/Faturamento.php#L36)
  - [EntradaSaida.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/EntradaSaida.php#L35)
  - [EntradaVsImposto.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/EntradaVsImposto.php#L29)
  - [ListagemFornecedor.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/ListagemFornecedor.php#L39)
  - [ListagemProduto.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/ListagemProduto.php#L49)
  - [ListagemCliente.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/ListagemCliente.php#L39)
- Actions (amostra)
  - [ClassificarDocumentoAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/ClassificarDocumentoAction.php#L40)
  - [ClassificarDocumentoEmLoteAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/ClassificarDocumentoEmLoteAction.php#L37)
  - [ClassificarDocumentoNfeAvancadoAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/ClassificarDocumentoNfeAvancadoAction.php#L104)
  - [RemoverClassificaoNfeAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/RemoverClassificaoNfeAction.php#L34)
  - [ManifestarNfeAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/ManifestarNfeAction.php#L61)
  - [ToggleEscrituracaoAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/ToggleEscrituracaoAction.php#L36)
  - [ToggleEscrituacaoEmLoteAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/ToggleEscrituacaoEmLoteAction.php#L25)
  - [GerarTxtIntegracaoDominioSistema.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/GerarTxtIntegracaoDominioSistema.php#L36)
  - [GerarArquivoTxtLancamentoContabilGeral.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/GerarArquivoTxtLancamentoContabilGeral.php#L55)
  - [ImportarLancamentoContabilGeralAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/ImportarLancamentoContabilGeralAction.php#L112)

#### Resources / Tables / Schemas (Filament)
- [NfeEntradasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/NfeEntradas/Tables/NfeEntradasTable.php#L107)
- [NfeEntradasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/NfeEntradas/Tables/NfeEntradasTable.php#L388)
- [NfseEntradasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/NfseEntradas/Tables/NfseEntradasTable.php#L34)
- [NfseEntradasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/NfseEntradas/Tables/NfseEntradasTable.php#L84)
- [NfeSaidasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/NfeSaidas/Tables/NfeSaidasTable.php#L39)
- [NfeSaidasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/NfeSaidas/Tables/NfeSaidasTable.php#L272)
- [CteTomadasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CteTomadas/Tables/CteTomadasTable.php#L41)
- [CteTomadasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CteTomadas/Tables/CteTomadasTable.php#L117)
- [CteEntradasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CteEntradas/Tables/CteEntradasTable.php#L30)
- [CteSaidasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CteSaidas/Tables/CteSaidasTable.php#L30)
- [LayoutsTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Layouts/Tables/LayoutsTable.php#L23)
- [LayoutRuleSchema.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Layouts/Schemas/LayoutRuleSchema.php#L210)
- [UploadFileManagersTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/UploadFileManagers/Tables/UploadFileManagersTable.php#L38)
- [UploadFileManagersTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/UploadFileManagers/Tables/UploadFileManagersTable.php#L69)
- [UploadFileManagerForm.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/UploadFileManagers/Schemas/UploadFileManagerForm.php#L33)
- [UploadFileManagerForm.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/UploadFileManagers/Schemas/UploadFileManagerForm.php#L94)
- [LogSefazNfeContentsTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/LogSefazNfeContents/Tables/LogSefazNfeContentsTable.php#L16)
- [LogSefazCteContentsTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/LogSefazCteContents/Tables/LogSefazCteContentsTable.php#L16)
- [TagsRelationManager.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CategoryTags/RelationManagers/TagsRelationManager.php#L48)
- [TagsRelationManager.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CategoryTags/RelationManagers/TagsRelationManager.php#L131)
- [CreateUploadFileManager.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/UploadFileManagers/Pages/CreateUploadFileManager.php#L52)
- [CreateFornecedor.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Fornecedors/Pages/CreateFornecedor.php#L15)
- [CreateCliente.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Clientes/Pages/CreateCliente.php#L15)
- [CreateBanco.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Bancos/Pages/CreateBanco.php#L15)
- [CreateHistoricoContabil.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/HistoricoContabils/Pages/CreateHistoricoContabil.php#L15)
- [HistoricoContabilsTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/HistoricoContabils/Tables/HistoricoContabilsTable.php#L18)
- [HistoricoContabilForm.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/HistoricoContabils/Schemas/HistoricoContabilForm.php#L23)
- [ParametroSuperLogicasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/ParametroSuperLogicas/Tables/ParametroSuperLogicasTable.php#L22)
- [EditParametroSuperLogica.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/ParametroSuperLogicas/Pages/EditParametroSuperLogica.php#L21)
- [CreateParametroSuperLogica.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/ParametroSuperLogicas/Pages/CreateParametroSuperLogica.php#L17)
- [ParametroSuperLogicaForm.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/ParametroSuperLogicas/Schemas/ParametroSuperLogicaForm.php#L49)
- [EditParametroGeral.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/ParametroGerals/Pages/EditParametroGeral.php#L32)
- [CreateParametroGeral.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/ParametroGerals/Pages/CreateParametroGeral.php#L17)
- [ParametroGeralForm.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/ParametroGerals/Schemas/ParametroGeralForm.php#L49)
- [ImportarLancamentoContabilSuperLogicasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/ImportarLancamentoContabilSuperLogicas/Tables/ImportarLancamentoContabilSuperLogicasTable.php#L17)
- [FornecedorsTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Fornecedors/Tables/FornecedorsTable.php#L20)
- [BancosTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Bancos/Tables/BancosTable.php#L20)
- [PlanoDeContasTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/PlanoDeContas/Tables/PlanoDeContasTable.php#L20)
- [ClientesTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Clientes/Tables/ClientesTable.php#L20)

#### Models / Controllers / Outros
- [NotaFiscalEletronica.php](file:///root/projetos/fiscaut-v4.1/app/Models/NotaFiscalEletronica.php#L85)
- [NotaFiscalServico.php](file:///root/projetos/fiscaut-v4.1/app/Models/NotaFiscalServico.php#L58)
- [Tag.php](file:///root/projetos/fiscaut-v4.1/app/Models/Tag.php#L31)
- [PlanoDeContaSelectController.php](file:///root/projetos/fiscaut-v4.1/app/Http/Controllers/PlanoDeContaSelectController.php#L15)
- [UploadFileController.php](file:///root/projetos/fiscaut-v4.1/app/Http/Controllers/UploadFileController.php#L16)

#### Components (PHP view-components em resources/views/components/configuracao)
- [acumulador-nfe-nota-propria.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1acumulador-nfe-nota-propria/acumulador-nfe-nota-propria.php#L39)
- [acumulador-nfe-nota-terceiro.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1acumulador-nfe-nota-terceiro/acumulador-nfe-nota-terceiro.php#L40)
- [cfop-nfe-entrada-terceiro.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1cfop-nfe-entrada-terceiro/cfop-nfe-entrada-terceiro.php#L43)
- [cfop-nfe-entrada-propria.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1cfop-nfe-entrada-propria/cfop-nfe-entrada-propria.php#L43)
- [acumulador-cte-nota-entrada.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1acumulador-cte-nota-entrada/acumulador-cte-nota-entrada.php#L39)
- [acumulador-cte-nota-saida.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1acumulador-cte-nota-saida/acumulador-cte-nota-saida.php#L39)
- [cfop-cte-nota-entrada.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1cfop-cte-nota-entrada/cfop-cte-nota-entrada.php#L42)
- [cfop-cte-nota-saida.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1cfop-cte-nota-saida/cfop-cte-nota-saida.php#L42)
- [produto-generico.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1produto-generico/produto-generico.php#L45)
- [imposto-equivalente.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1imposto-equivalente/imposto-equivalente.php#L35)
- [configuracao-geral.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/configuracao/%E2%9A%A1configuracao-geral/configuracao-geral.php#L41)

Obs.: o levantamento completo inclui também acessos a `->id`, `->cnpj`, `->razao_social`, nullsafe (`?->`) e usos em `Cache::forget(...)`.

## Mudança proposta (técnica)

### 1) Criar helper centralizado com cache

Adicionar em `app/Helpers/helper.php` uma função única `currentIssuer()` para retornar o issuer atual do usuário autenticado:

- Assinatura: `function currentIssuer(?\App\Models\User $user = null): ?\App\Models\Issuer`
- Estratégia:
  - Se `$user` não for passado, usa `Auth::user()`.
  - Se não houver usuário ou `issuer_id`, retorna `null`.
  - Se a relação `currentIssuer` estiver carregada, retorna a relação (sem consulta extra).
  - Caso contrário, usa `Cache::remember()` com chave derivada de `user_id` + `issuer_id` e TTL curto (ex.: 10 min) para buscar `Issuer::find($issuer_id)`.
  - Também usa memoização em memória (variável `static`) para evitar múltiplos acessos ao cache na mesma requisição.

### 1.1) Limpeza do cache ao alternar issuer (issuer-switcher)

Ao alternar a empresa atual no componente [issuer-switcher.php](file:///root/projetos/fiscaut-v4.1/resources/views/components/%E2%9A%A1issuer-switcher/issuer-switcher.php#L1-L97), limpar explicitamente o cache relacionado ao issuer atual:

- Capturar `$oldIssuerId = $user->issuer_id` antes do update.
- Após atualizar `issuer_id`, executar `Cache::forget(...)` para:
  - a chave do issuer antigo (evita acúmulo de cache “órfão” e garante que não seja reaproveitado indevidamente)
  - a chave do issuer novo (garante que a próxima tela leia o issuer atualizado, mesmo que exista valor antigo no cache)
- A implementação deve reutilizar a mesma estratégia de chave definida pelo helper (idealmente via função auxiliar, ex.: `currentIssuerCacheKey($userId, $issuerId)` ou `forgetCurrentIssuerCache($userId, $issuerId)`).

### 2) Refatorar o codebase para usar o helper

Substituir `Auth::user()->currentIssuer` (e variações) por `currentIssuer()`:

- Onde hoje há `Auth::user()->currentIssuer->id`, trocar por `currentIssuer()->id` (mantém o mesmo comportamento em caso de null, ou seja, erro como antes).
- Onde hoje há nullsafe (`Auth::user()?->currentIssuer?->id`), trocar por `currentIssuer()?->id`.
- Onde hoje há guardas como `if (! Auth::user()->currentIssuer)`, trocar por `if (! currentIssuer())`.
- Onde hoje há acesso a campos: `currentIssuer()->cnpj`, `currentIssuer()->razao_social`, etc.
- Onde hoje é montada chave de cache com issuer id, trocar para `currentIssuer()->id` para padronização.

### 3) Verificação

- Rodar `composer dump-autoload` (indiretamente via Sail) se necessário (o arquivo helper já é carregado via `autoload.files`).
- Executar uma navegação manual nas principais páginas afetadas (tabelas de NF-e/NFS-e/CT-e, relatórios e configurações) para garantir que não houve regressão de null e que o issuer ainda é resolvido corretamente.
- Se a suíte de testes estiver estável no ambiente, rodar `./vendor/bin/sail php artisan test`.

## Assumptions / Riscos

- A troca mantém o comportamento atual quanto a issuer ausente (em vários pontos o código assume que existe). Onde o código atual já faz `if (! Auth::user()->currentIssuer)`, manteremos a checagem equivalente com o helper.
- O cache cross-request é TTL curto e com chave incluindo `issuer_id`, evitando “issuer errado” quando o usuário alterna de issuer.
