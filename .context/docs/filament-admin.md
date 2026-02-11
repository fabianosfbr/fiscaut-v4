# Filament (Admin) — Resources, Pages e Actions

O painel administrativo do Fiscaut usa Filament e organiza sua camada de UI/admin principalmente em `app/Filament`.

## Entry point do painel

O painel é configurado pelo provider:

- [AdminPanelProvider.php](file:///root/projetos/fiscaut-v4.1/app/Providers/Filament/AdminPanelProvider.php)

Pontos importantes desse provider:

- Descoberta automática de Resources: `discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')`
- Descoberta automática de Pages: `discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')`
- Grupos de navegação (menu): `navigationGroups([...])`

## Estrutura de diretórios em `app/Filament`

- `app/Filament/Resources/*`: CRUDs do Filament, normalmente separados por domínio.
  - `*Resource.php`: classe principal do resource (modelo, navegação e roteamento das páginas).
  - `Pages/*`: páginas (List/Create/Edit/View) do resource.
  - `Schemas/*`: configurações de formulário/infolist (ex.: `...Form.php`, `...Infolist.php`).
  - `Tables/*`: configuração de tabela (ex.: `...Table.php`).
  - `RelationManagers/*`: relacionamentos (sub-recursos) dentro do resource.
  - `Actions/*`: actions específicas do resource.
- `app/Filament/Pages/*`: páginas avulsas (fora de resources).
- `app/Filament/Actions/*`: actions reutilizáveis (normalmente usadas em Table/Pages).
- `app/Filament/Forms/Components/*`: componentes customizados de formulário.
- `app/Filament/Infolists/Components/*`: entries customizadas de infolist.
- `app/Filament/Tables/Columns/*`: colunas customizadas de tabela.

## Resources (inventário)

| Resource | Model | Navigation group | Arquivo |
|---|---|---|---|
| AcumuladoresResource | `Acumulador` | Configurações | [AcumuladoresResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Acumuladores/AcumuladoresResource.php) |
| CategoryTagResource | `CategoryTag` | Configurações | [CategoryTagResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CategoryTags/CategoryTagResource.php) |
| - Includes | `TagsRelationManager` | Nested tag management | [TagsRelationManager.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CategoryTags/RelationManagers/TagsRelationManager.php) |
| CfopResource | `Cfop` | Configurações | [CfopResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cfops/CfopResource.php) |
| CnaeResource | `Cnae` | Configurações | [CnaeResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cnaes/CnaeResource.php) |
| CodigoServicoResource | `CodigoServico` | Administração | [CodigoServicoResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CodigosServicos/CodigoServicoResource.php) |
| IssuerResource | `Issuer` | Configurações | [IssuerResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Issuers/IssuerResource.php) |
| LogSefazCteContentResource | `LogSefazCteContent` | Administração | [LogSefazCteContentResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/LogSefazCteContents/LogSefazCteContentResource.php) |
| LogSefazNfeContentResource | `LogSefazNfeContent` | Administração | [LogSefazNfeContentResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/LogSefazNfeContents/LogSefazNfeContentResource.php) |
| NfeEntradaResource | `NotaFiscalEletronica` | NFe | [NfeEntradaResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/NfeEntradas/NfeEntradaResource.php) |
| NfeSaidaResource | `NotaFiscalEletronica` | NFe | [NfeSaidaResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/NfeSaidas/NfeSaidaResource.php) |
| ScheduleResource | `Schedule` | Administração | [ScheduleResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Schedules/ScheduleResource.php) |
| SimplesNacionalAliquotaResource | `SimplesNacionalAliquota` | Administração | [SimplesNacionalAliquotaResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/SimplesNacionalAliquotas/SimplesNacionalAliquotaResource.php) |
| SimplesNacionalAnexoResource | `SimplesNacionalAnexo` | Administração | [SimplesNacionalAnexoResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/SimplesNacionalAnexos/SimplesNacionalAnexoResource.php) |
| TenantResource | `Tenant` | Administração | [TenantResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Tenants/TenantResource.php) |
| UploadFileManagerResource | `UploadFile` | Demais docs. fiscais | [UploadFileManagerResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/UploadFileManagers/UploadFileManagerResource.php) |
| UserResource | `User` | Administração | [UserResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Users/UserResource.php) |
| XmlImportJobResource | `XmlImportJob` | Administração | [XmlImportJobResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/XmlImportJobs/XmlImportJobResource.php) |

### XmlImportJobResource

The XmlImportJobResource manages the history and status of XML import operations in the system. It tracks batch import jobs for various types of fiscal documents (NF-e, CT-e, etc.).

**Key Features:**
- Tracks import jobs initiated by users or system processes
- Monitors progress of batch XML processing operations
- Records errors and statistics for each import job
- Supports polymorphic relationships to track which entity initiated the import
- Provides detailed information about processed files, documents, and events

**Model Properties:**
- `import_type`: Enum indicating if import was initiated by USER or SYSTEM
- `status`: Current status (pending, processing, completed, failed)
- `total_files` / `processed_files` / `imported_files`: Progress tracking
- `num_documents` / `num_events`: Count of processed fiscal documents and events
- `errors`: Array of error messages encountered during processing
- Polymorphic `owner` relationship to track which entity initiated the import

### CategoryTagResource and TagsRelationManager

The CategoryTagResource manages "Categorias das etiquetas" (Categories of tags) allowing users to organize tags into logical groups. Each category can contain multiple tags managed through the TagsRelationManager.

**Key Features:**
- Hierarchical organization of tags for better classification
- Nested management of individual tags within each category
- Automatic assignment of tenant and issuer IDs to tags
- Cache invalidation when categories or tags are modified
- Unique code validation within each issuer context

**TagsRelationManager includes:**
- Code and name fields for tag identification
- Active/inactive toggle for tag management
- Automatic population of tenant_id, issuer_id, and category_id
- Security filters to ensure data isolation between tenants

Observação: existe uma pasta `app/Filament/Resources/BulkActions/` (Schemas/Tables/Pages), porém sem um `*Resource.php` no momento.

## Pages (fora de Resources)

- [XmlImport.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Importar/XmlImport.php)
- [SiegImport.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Importar/SiegImport.php)
- [ConfiguracaoGeralPage.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Configuracoes/ConfiguracaoGeralPage.php)


## Actions

### Actions globais (reutilizáveis)

- [ClassificarDocumentoAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/ClassificarDocumentoAction.php)
- [ClassificarDocumentoNfeAvancadoAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/ClassificarDocumentoNfeAvancadoAction.php)
- [DownloadPdfNfeAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/DownloadPdfNfeAction.php)
- [DownloadXmlAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/DownloadXmlAction.php)
- [DownloadXmlPdfCteEmLoteAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/DownloadXmlPdfCteEmLoteAction.php)
- [DownloadXmlPdfNfeEmLoteAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/DownloadXmlPdfNfeEmLoteAction.php)
- [ManifestarNfeAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/ManifestarNfeAction.php)
- [RemoverClassificaoNfeAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/RemoverClassificaoNfeAction.php)
- [SugerirEtiquetaAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/SugerirEtiquetaAction.php)
- [ToggleEscrituracaoAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Actions/ToggleEscrituracaoAction.php)

### Actions específicas de Resources

- UploadFileManagers: [DownloadFileAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/UploadFileManagers/Actions/DownloadFileAction.php)
- Issuers: [DownloadCertificadoAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Issuers/Actions/DownloadCertificadoAction.php), [GerenciarServicoAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Issuers/Actions/GerenciarServicoAction.php)
- CategoryTags: [CopiarEtiquetaAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CategoryTags/Actions/CopiarEtiquetaAction.php), [GerarEtiquetaAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CategoryTags/Actions/GerarEtiquetaAction.php)

## Componentes customizados (apoio)

- Columns (Tables): [app/Filament/Tables/Columns](file:///root/projetos/fiscaut-v4.1/app/Filament/Tables/Columns)
- Form components: [app/Filament/Forms/Components](file:///root/projetos/fiscaut-v4.1/app/Filament/Forms/Components)
- Infolist components: [app/Filament/Infolists/Components](file:///root/projetos/fiscaut-v4.1/app/Filament/Infolists/Components)
