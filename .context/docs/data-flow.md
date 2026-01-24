# Data Flow & Integrations

## Data Flow & Integrations
Data in Fiscaut v4.1 primarily flows from user inputs in the Filament Admin Panel to the MySQL database via Eloquent Models. External integrations are minimal in the core structure but may exist for specific fiscal services.

## Nota de Confidencialidade
Fiscaut é um produto comercial proprietário. Documente integrações e fluxos sem expor segredos (tokens, chaves, URLs internas) ou dados sensíveis.

## Module Dependencies
- **app/Filament/** → Depends on `app/Models` and `Filament` vendor packages.
- **app/Http/** → Depends on `app/Models`.
- **app/Models/** → Depends on `Illuminate\Database\Eloquent`.
- **database/** → Depends on Schema definitions.

## Service Layer
While a strict Service Layer is not enforced by default in Laravel, logic often resides in:
- **Filament Resources**: `app/Filament/Resources/*Resource.php` (Handling UI & Persistence logic).
- **Actions**: `app/Actions` (if present, for reusable business logic).
- **Models**: `app/Models` (Business logic related to data).

## High-level Flow
1. **Input**: User interacts with a Filament Form or Table.
2. **Processing**:
   - Livewire intercepts the interaction.
   - Validation rules in the Resource/Form are applied.
   - Authorization policies (`app/Policies`) are checked.
3. **Persistence**: Validated data is saved to MySQL via Eloquent.
4. **Feedback**: UI updates via Livewire DOM diffing or Flash notifications.

## Escopo de dados (Tenant / Empresa)
Boa parte das telas do admin opera com escopo por:
- **tenant_id**: definido no usuário autenticado.
- **empresa atual (issuer)**: em alguns recursos, o usuário também precisa ter `currentIssuer` definido para a query retornar dados.

Exemplos implementados:
- `IssuerResource` lista empresas por `tenant_id` e oferece ações como download de certificado e gerenciamento de serviços: [IssuersTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Issuers/Tables/IssuersTable.php)
- `CategoryTagResource` filtra por `tenant_id` + `issuer_id` e usa filtros avançados com `whereHas(tags)`: [CategoryTagsTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CategoryTags/Tables/CategoryTagsTable.php)

## Fluxos de referência (Filament)
- **Cadastro de Empresa (Issuer)**
  - `CreateIssuer` injeta `tenant_id`, criptografa a senha do certificado, consulta dados do CNPJ e cria permissão do usuário para a empresa recém-criada: [CreateIssuer.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Issuers/Pages/CreateIssuer.php)
  - Ações adicionais no List: download de certificado e gerenciamento de serviços: [DownloadCertificadoAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Issuers/Actions/DownloadCertificadoAction.php), [GerenciarServicoAction.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Issuers/Actions/GerenciarServicoAction.php)
- **Categorias de Etiquetas (CategoryTag)**
  - Form com campos de classificação/flags e cor: [CategoryTagForm.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CategoryTags/Schemas/CategoryTagForm.php)
  - Table com contagem de etiquetas, filtros (ternary/select) e busca composta: [CategoryTagsTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CategoryTags/Tables/CategoryTagsTable.php)

## Internal Movement
- **Events & Listeners**: Used for side effects (e.g., `UserRegistered` -> `SendWelcomeEmail`).
- **Jobs**: Long-running tasks offloaded to the queue (e.g., generating fiscal reports).

## External Integrations
- **Database**: MySQL (Connection via PDO).
- **Filesystem**: Local or S3 (for document storage).

## Observability & Failure Modes
- **Logs**: Laravel writes to `storage/logs/laravel.log`.
- **Exceptions**: Handled by `bootstrap/app.php` and rendered to the user (or debug page in local).
- **Validation Errors**: Automatically displayed in Filament forms.

## Cross-References
- [architecture.md](./architecture.md)
