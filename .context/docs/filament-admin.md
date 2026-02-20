# Filament Admin (Fiscaut) — Resources, Pages, Actions e Extensões

Esta documentação descreve como o painel administrativo do Fiscaut é estruturado com **Filament**, onde ficam os principais artefatos (Resources, Pages e Actions) e como estender o admin com componentes e ações reutilizáveis.

---

## Visão geral

O admin do Fiscaut é implementado com Filament e organizado principalmente em:

- `app/Filament` — camada do painel (Resources, Pages, Actions e extensões)
- `public/js/filament` — artefatos JS relacionados a schemas/componentes (build do frontend do Filament)

A convenção adotada é separar cada CRUD em um **Resource**, e dentro dele manter pastas como `Pages`, `Schemas`, `Tables`, `RelationManagers` e `Actions`.

---

## Entry point do painel (Provider)

A configuração do painel é feita em:

- `app/Providers/Filament/AdminPanelProvider.php`

Pontos importantes:

- **Descoberta automática de Resources**
  - `discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')`
- **Descoberta automática de Pages**
  - `discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')`
- **Grupos de navegação (menu)**
  - `navigationGroups([...])`

### Implicação prática

Se você criar um novo Resource em `app/Filament/Resources/**`, ele tende a aparecer automaticamente no painel (desde que esteja dentro do namespace esperado e tenha as páginas/rotas padrão do Filament).

---

## Estrutura recomendada em `app/Filament`

### Resources (CRUDs)

`app/Filament/Resources/*` agrupa os CRUDs do Filament por domínio.

Estrutura típica:

- `*Resource.php`
  - Classe principal do resource: vincula **Model**, define **navegação**, **roteamento** e registra páginas.
- `Pages/*`
  - Páginas padrão do resource (ex.: `List*`, `Create*`, `Edit*`, `View*`).
- `Schemas/*`
  - “Blueprints”/configurações de **form** e **infolist**.
  - Exemplos de intenção: `...Form.php`, `...Infolist.php`.
- `Tables/*`
  - Configuração de tabela (colunas, filtros, ações, bulk actions etc.).
- `RelationManagers/*`
  - Gerenciamento de relacionamentos (sub-recursos exibidos dentro do resource).
- `Actions/*`
  - Actions específicas do resource (ex.: download/geração/cópia relacionada ao domínio).

> Observação: existe uma pasta `app/Filament/Resources/BulkActions/` (com `Schemas/Tables/Pages`), porém **sem** um `*Resource.php` no momento. Isso sugere que é um “esqueleto” ou um recurso em construção.

### Pages (fora de Resources)

Para telas avulsas (fora de CRUDs):

- `app/Filament/Pages/*`

### Actions reutilizáveis (globais)

Para ações reaproveitáveis em tabelas e páginas:

- `app/Filament/Actions/*`

### Componentes customizados (extensões)

Quando o Filament padrão não atende:

- `app/Filament/Forms/Components/*` — componentes customizados de formulário
- `app/Filament/Infolists/Components/*` — entries customizadas de infolist
- `app/Filament/Tables/Columns/*` — colunas customizadas de tabela

---

## Inventário de Resources

Abaixo estão os resources identificados no projeto (com model, grupo de navegação e arquivo principal):

| Resource | Model | Navigation group | Arquivo |
|---|---|---|---|
| AcumuladoresResource | `Acumulador` | Configurações | `app/Filament/Resources/Acumuladores/AcumuladoresResource.php` |
| CategoryTagResource | `CategoryTag` | Configurações | `app/Filament/Resources/CategoryTags/CategoryTagResource.php` |
| CfopResource | `Cfop` | Configurações | `app/Filament/Resources/Cfops/CfopResource.php` |
| CnaeResource | `Cnae` | Configurações | `app/Filament/Resources/Cnaes/CnaeResource.php` |
| CodigoServicoResource | `CodigoServico` | Administração | `app/Filament/Resources/CodigosServicos/CodigoServicoResource.php` |
| IssuerResource | `Issuer` | Configurações | `app/Filament/Resources/Issuers/IssuerResource.php` |
| LogSefazCteContentResource | `LogSefazCteContent` | Administração | `app/Filament/Resources/LogSefazCteContents/LogSefazCteContentResource.php` |
| LogSefazNfeContentResource | `LogSefazNfeContent` | Administração | `app/Filament/Resources/LogSefazNfeContents/LogSefazNfeContentResource.php` |
| NfeEntradaResource | `NotaFiscalEletronica` | NFe | `app/Filament/Resources/NfeEntradas/NfeEntradaResource.php` |
| NfeSaidaResource | `NotaFiscalEletronica` | NFe | `app/Filament/Resources/NfeSaidas/NfeSaidaResource.php` |
| ScheduleResource | `Schedule` | Administração | `app/Filament/Resources/Schedules/ScheduleResource.php` |
| SimplesNacionalAliquotaResource | `SimplesNacionalAliquota` | Administração | `app/Filament/Resources/SimplesNacionalAliquotas/SimplesNacionalAliquotaResource.php` |
| SimplesNacionalAnexoResource | `SimplesNacionalAnexo` | Administração | `app/Filament/Resources/SimplesNacionalAnexos/SimplesNacionalAnexoResource.php` |
| TenantResource | `Tenant` | Administração | `app/Filament/Resources/Tenants/TenantResource.php` |
| UploadFileManagerResource | `UploadFile` | Demais docs. fiscais | `app/Filament/Resources/UploadFileManagers/UploadFileManagerResource.php` |
| UserResource | `User` | Administração | `app/Filament/Resources/Users/UserResource.php` |
| XmlImportJobResource | `XmlImportJob` | Administração | `app/Filament/Resources/XmlImportJobs/XmlImportJobResource.php` |

---

## Destaques de implementação

### XmlImportJobResource (histórico/status de importações)

O `XmlImportJobResource` gerencia a trilha de auditoria e o status das operações de importação de XML.

Principais características:

- Acompanha jobs de importação iniciados por usuários ou por processos do sistema
- Monitora progresso de processamento em lote
- Registra erros e estatísticas por job
- Usa relacionamento polimórfico `owner` para rastrear quem iniciou
- Exibe detalhamento de arquivos/documentos/eventos processados

Campos relevantes (conceitualmente):

- `import_type` — enum (ex.: USER/SYSTEM)
- `status` — pendente/processando/concluído/falhou
- contadores: `total_files`, `processed_files`, `imported_files`, `num_documents`, `num_events`
- `errors` — array de mensagens/estruturas de erro

Arquivos:

- `app/Filament/Resources/XmlImportJobs/XmlImportJobResource.php`

### CategoryTagResource + TagsRelationManager (categorias e etiquetas)

O `CategoryTagResource` gerencia **categorias de etiquetas** e permite administrar as tags relacionadas via `TagsRelationManager`.

Pontos importantes:

- Organização hierárquica/por categoria
- Gerenciamento aninhado das tags dentro da categoria (Relation Manager)
- Preenchimento automático de `tenant_id`, `issuer_id` e `category_id` para tags
- Invalidação de cache quando categorias/tags são alteradas
- Validação de unicidade de “code” dentro do contexto do issuer
- Filtros de segurança para isolamento por tenant

Arquivos:

- `app/Filament/Resources/CategoryTags/CategoryTagResource.php`
- `app/Filament/Resources/CategoryTags/RelationManagers/TagsRelationManager.php`

---

## Pages avulsas (fora de Resources)

Páginas registradas via `discoverPages(...)`:

- Importação:
  - `app/Filament/Pages/Importar/XmlImport.php`
  - `app/Filament/Pages/Importar/SiegImport.php`
- Configurações:
  - `app/Filament/Pages/Configuracoes/ConfiguracaoGeralPage.php`

Uso típico: telas que não se encaixam como CRUD clássico (ex.: “wizard” de importação, páginas de configuração geral, dashboards específicos etc.).

---

## Actions

### Actions globais (reutilizáveis)

Estas actions ficam em `app/Filament/Actions/` e são pensadas para uso em múltiplos resources/páginas (ex.: ações de documento fiscal, download, manifestação, classificação, toggle de escrituração).

Lista:

- `ClassificarDocumentoAction.php`
- `ClassificarDocumentoNfeAvancadoAction.php`
- `DownloadPdfNfeAction.php`
- `DownloadXmlAction.php`
- `DownloadXmlPdfCteEmLoteAction.php`
- `DownloadXmlPdfNfeEmLoteAction.php`
- `ManifestarNfeAction.php`
- `RemoverClassificaoNfeAction.php`
- `SugerirEtiquetaAction.php`
- `ToggleEscrituracaoAction.php`

Quando usar:
- Se a ação for aplicada em **vários** resources/tabelas, mantenha como global.
- Se a ação depender fortemente de regras de um domínio específico, prefira action “do resource”.

### Actions específicas de Resources

Alguns resources possuem actions “locais” em `app/Filament/Resources/<Domínio>/Actions/`, por exemplo:

- UploadFileManagers:
  - `app/Filament/Resources/UploadFileManagers/Actions/DownloadFileAction.php`
- Issuers:
  - `app/Filament/Resources/Issuers/Actions/DownloadCertificadoAction.php`
  - `app/Filament/Resources/Issuers/Actions/GerenciarServicoAction.php`
- CategoryTags:
  - `app/Filament/Resources/CategoryTags/Actions/CopiarEtiquetaAction.php`
  - `app/Filament/Resources/CategoryTags/Actions/GerarEtiquetaAction.php`

---

## Componentes JS (schemas/widgets/forms/tables)

Além do PHP, há artefatos JS do Filament em:

- `public/js/filament/schemas`
- `public/js/filament/schemas/components`
- `public/js/filament/widgets/components`
- `public/js/filament/forms/components`
- `public/js/filament/tables/components/columns`

Esses arquivos incluem componentes como `wizard`, `tabs`, `textarea`, `tags-input`, `rich-editor`, `key-value`, `checkbox-list` e colunas como `toggle`, `text-input`, `checkbox`.

Uso prático:
- Tipicamente são arquivos gerados/compilados (dependendo do pipeline), mas ajudam a entender o que está sendo usado no frontend do admin.
- Se for necessário customizar UI via JS, estes diretórios indicam a organização atual.

Arquivos exemplares:
- `public/js/filament/schemas/components/wizard.js`
- `public/js/filament/schemas/components/tabs.js`
- `public/js/filament/forms/components/rich-editor.js`
- `public/js/filament/tables/components/columns/toggle.js`

---

## Como adicionar um novo Resource (checklist rápido)

1. Criar a pasta do domínio em `app/Filament/Resources/<Domínio>/`
2. Criar `*Resource.php` apontando para o Model
3. Criar `Pages/` (List/Create/Edit/View conforme necessário)
4. Opcional: extrair `Schemas/` e `Tables/` para manter o `*Resource.php` enxuto
5. Se houver relacionamento interno, criar `RelationManagers/`
6. Se houver ações específicas, criar `Actions/`
7. Validar se aparece no menu (navigation group) e se as permissões/escopos (tenant/issuer) estão aplicados

---

## Referências internas (arquivos úteis)

- Provider do painel:
  - `app/Providers/Filament/AdminPanelProvider.php`
- Raiz do admin:
  - `app/Filament/`
- Resources:
  - `app/Filament/Resources/`
- Pages avulsas:
  - `app/Filament/Pages/`
- Actions globais:
  - `app/Filament/Actions/`
- Extensões:
  - `app/Filament/Forms/Components/`
  - `app/Filament/Infolists/Components/`
  - `app/Filament/Tables/Columns/`
- JS do Filament:
  - `public/js/filament/`

---
