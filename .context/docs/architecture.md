# Architecture Notes

## Architecture Notes
Fiscaut v4.1 follows the standard Laravel architecture, extended by the Filament ecosystem for the administrative interface. It adheres to the Model-View-Controller (MVC) pattern, although the "View" layer is heavily managed by Livewire components and Filament Resources.

## Contexto e Stack
- **Produto**: aplicação comercial proprietária (confidencial).
- **Frameworks**: Laravel v12, FilamentPHP v5 e Livewire v4.

## System Architecture Overview
The application is a **Modular Monolith** built on Laravel.
- **Web Server**: Nginx (via Sail/Docker) handles incoming HTTP requests.
- **Application Server**: PHP-FPM executes the Laravel application.
- **Database**: MySQL stores relational data.
- **Queue Worker**: Redis (optional/configurable) for background jobs.

Requests flow through the public index, are routed by Laravel's router, processed by Controllers or Livewire Components, and responses are rendered via Blade templates.

## Architectural Layers
- **Domain Layer**: Located primarily in `app/Models`. Represents the business entities (e.g., `Cfop`, `User`).
- **Application Layer**:
    - `app/Http/Controllers`: Standard HTTP controllers (less used in favor of Livewire/Filament).
    - `app/Filament/Resources`: Filament resources acting as the admin logic layer (CRUD operations).
    - `app/Livewire`: Custom Livewire components for dynamic UI elements.
- **Infrastructure Layer**: `database/migrations`, `config/`, and `app/Providers`.
- **Presentation Layer**: `resources/views` (Blade files) and Filament generated views.

### Filament Resources (exemplos)
- Configurações: [CfopResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cfops/CfopResource.php), [CnaeResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cnaes/CnaeResource.php), [SimplesNacionalAliquotaResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/SimplesNacionalAliquotas/SimplesNacionalAliquotaResource.php)
- Registro automático no painel: [AdminPanelProvider.php](file:///root/projetos/fiscaut-v4.1/app/Providers/Filament/AdminPanelProvider.php) usa `discoverResources(...)` para carregar recursos em `app/Filament/Resources`.

> See [`codebase-map.json`](./codebase-map.json) for complete symbol counts and dependency graphs.

## Detected Design Patterns
| Pattern | Confidence | Locations | Description |
|---------|------------|-----------|-------------|
| MVC | 100% | `app/Http/Controllers`, `app/Models`, `resources/views` | Core Laravel Architecture |
| Repository (Implicit) | 90% | `app/Filament/Resources` | Filament Resources abstract data access logic |
| Service Provider | 100% | `app/Providers` | Bootstrapping application services |
| Facade | 100% | `Illuminate\Support\Facades` | Static interface to classes available in the service container |

## Entry Points
- **Web**: [`public/index.php`](../public/index.php)
- **Console**: [`artisan`](../artisan)

## Public API
(Currently, the application is primarily a web interface. API endpoints would be defined in `routes/api.php` if exposed.)

| Symbol | Type | Location |
|--------|------|----------|
| `api/*` | Route | `routes/api.php` |

## Internal System Boundaries
- **Admin Panel vs. Public Front**: The `app/Filament` directory encapsulates the administrative domain, while `app/Http/Controllers` (if used) would handle public-facing pages.
- **Authentication**: Managed by Laravel's auth system, likely integrated with Filament's auth guards.

## External Service Dependencies
- **MySQL**: Primary data store.
- **Redis** (Optional): Cache and Queue driver.
- **Mail Server**: SMTP configuration for sending emails (e.g., Mailpit in local dev).

## Key Decisions & Trade-offs
- **Filament vs. Custom Admin**: Choosing Filament accelerates development of CRUD interfaces but ties the UI to the TALL stack (Tailwind, Alpine, Laravel, Livewire).
- **Livewire vs. Vue/React**: Livewire allows keeping logic in PHP, reducing context switching and complexity for a PHP-focused team.

## Top Directories Snapshot
- `app/` (Core Logic)
- `database/` (Schema & Data)
- `resources/` (Views & Assets)
- `routes/` (Routing)
- `tests/` (Testing)

## Related Resources
- [project-overview.md](./project-overview.md)
- [data-flow.md](./data-flow.md)

## Cross-References
- [project-overview.md](./project-overview.md)
- [data-flow.md](./data-flow.md)
- [codebase-map.json](./codebase-map.json)
