# Architecture (Fiscaut v4.1)

Fiscaut v4.1 is a commercial-grade tax and fiscal management application built on the **TALL stack** (Tailwind CSS, Alpine.js, Laravel, Livewire). The system follows a **Modular Monolith** approach: business capabilities are organized into cohesive modules (models, resources, services, jobs), but deployed as a single Laravel application.

This document explains the high-level architecture, key layers, data flow, and the frontend component model used by Filament/Livewire.

Related docs:
- [Project Overview](./project-overview.md)
- [Data Flow](./data-flow.md)
- [Filament Admin](./filament-admin.md)
- [Codebase Map](./codebase-map.json)

---

## Technology Stack

| Layer | Technology |
|------:|------------|
| Backend Framework | Laravel v12 |
| Admin Panel | FilamentPHP v5 |
| Frontend Reactivity | Livewire v4 + Alpine.js |
| Styling | Tailwind CSS |
| Database | MySQL |
| Cache/Queues | Redis (Laravel Horizon) |
| Runtime | PHP-FPM / Nginx (Docker/Sail) |

---

## Architectural Style: Modular Monolith on Laravel + Filament

The application uses the Laravel MVC baseline, but most “admin app” use-cases are implemented as **Filament Resources** rather than traditional controllers. This produces a practical structure:

- **Models** define domain entities and persistence rules.
- **Resources/Pages/RelationManagers** define admin screens, forms, tables, actions, and authorization.
- **Services/Jobs** implement integration-heavy and asynchronous workflows (e.g., XML ingestion, SEFAZ download pipelines).
- **Livewire + Alpine** deliver interactive UIs with server-side state and client-side enhancements.

---

## Layered Architecture

### 1) Domain Layer (`app/Models`)
Responsible for business entities and persistence rules.

**Responsibilities**
- Eloquent models for core entities (e.g., `Cfop`, `Cnae`, `User`, `SimplesNacionalAnexo`, fiscal documents).
- Relationships using Eloquent relations (`hasMany`, `belongsTo`, etc.).
- Query scoping and constraints (global scopes and `modifyQueryUsing`) to enforce:
  - multi-tenancy
  - issuer/empresa boundaries
  - consistent filtering across the admin

**Typical patterns**
- Global scopes for tenant isolation
- Accessors/mutators for domain-specific formatting
- Shared query scopes for repeated filters

---

### 2) Application Layer (`app/Filament`, `app/Livewire`, Services, Jobs)
Orchestrates user interactions, application workflows, and integration pipelines.

#### Filament (`app/Filament`)
Filament acts as the primary “application layer” for CRUD and administrative workflows.

**Key building blocks**
- **Resources** (`app/Filament/Resources`): Define CRUD screens, routing, forms, tables, and policies.
- **Pages**: Custom screens beyond CRUD (dashboards, wizards, reports).
- **Relation Managers**: Sub-resource management within a parent context (e.g., fiscal details under an Issuer).
- **Actions / ActionGroups**: Encapsulate operations like exports, downloads, status transitions, and batch operations.

> See [Filament Admin](./filament-admin.md) for the catalog of resources/pages and how they map to business areas.

#### Livewire (`app/Livewire`)
Livewire components cover interactive experiences that benefit from server-driven state without writing a SPA.

**Typical usage**
- dynamic forms
- inline interactions
- step-based or reactive UIs where Filament components are extended

#### Services & Jobs (integration and async work)
The application uses service classes for integration logic and jobs for queued processing (especially for high-volume XML processing and external APIs).

Common characteristics:
- jobs are queued (Redis/Horizon) for throughput and resilience
- services encapsulate parsing, normalization, identification, and persistence logic

---

### 3) Presentation Layer (`resources/views`, `public/js/filament`)
Presentation is a combination of Blade + Filament + Livewire, with Alpine-powered component behaviors.

#### Blade (`resources/views`)
- Layout structure and rendering scaffolding
- Embeds Livewire components and Filament views

#### Filament/Livewire client-side assets (`public/js/filament`)
Filament’s interactive components rely on JavaScript shipped to the browser. In this repository, key scripts live in:

- **Models / schemas**
  - `public/js/filament/schemas`
  - `public/js/filament/schemas/components`

- **Components**
  - `public/js/filament/widgets/components`
  - `public/js/filament/forms/components`
  - `public/js/filament/tables/components/columns`
  - `public/js/filament/widgets/components/stats-overview/stat`

These scripts are primarily Alpine-powered component implementations that bridge server-rendered configuration (schemas) with client-side interactions (wizards, tabs, editors, tag inputs, etc.).

**Notable component scripts (examples)**
- Schemas:
  - `public/js/filament/schemas/components/wizard.js` (Wizard behavior)
  - `public/js/filament/schemas/components/tabs.js` (Tabs behavior)
- Forms:
  - `public/js/filament/forms/components/textarea.js`
  - `public/js/filament/forms/components/tags-input.js`
  - `public/js/filament/forms/components/rich-editor.js`
  - `public/js/filament/forms/components/key-value.js`
  - `public/js/filament/forms/components/checkbox-list.js`
- Tables (columns):
  - `public/js/filament/tables/components/columns/toggle.js`
  - `public/js/filament/tables/components/columns/text-input.js`
  - `public/js/filament/tables/components/columns/checkbox.js`

**How to think about these files**
- They are **client-side “adapters”**: Filament/Livewire generate HTML + metadata; these scripts add interactive behavior (keyboard handling, state sync, editor integration, etc.).
- They are not the source of truth for business rules; instead, they implement UX and state handling.

---

### 4) Infrastructure Layer (`app/Providers`, `database/migrations`, runtime)
Infrastructure bootstraps the application and defines persistence schema.

**Key parts**
- **Service Providers** (`app/Providers`):
  - register bindings and services
  - configure Filament panels
  - define guards/security integration
- **Migrations** (`database/migrations`):
  - MySQL schema and constraints
- **Queues/Cache**:
  - Redis + Horizon for background processing and throughput
- **Runtime**:
  - typical Laravel deployment using PHP-FPM and Nginx (often via Docker/Sail in development)

---

## Key Design Patterns Used

| Pattern | How it appears in Fiscaut |
|---|---|
| MVC | Laravel foundation for request lifecycle and data access. |
| Resource-based UI | Filament Resources map entities to CRUD UI and routes. |
| Multi-tenancy scoping | Systematic query filtering by tenant/issuer context. |
| Action Pattern | Discrete, reusable operations (export, download, change status) instead of bloated controllers. |
| Services + Jobs pipeline | Integration-heavy tasks implemented as services and queued jobs for scale and resilience. |

---

## System Entry Points

- **Web**: `public/index.php` → Laravel HTTP kernel → middleware → routes → Filament/Livewire.
- **Admin Panel**: configured by the Filament panel provider (commonly defaulting to `/admin`).
- **CLI**: `artisan` for migrations, scheduled tasks, and queue workers.

---

## Request/Data Flow (Typical Admin Interaction)

Example: a user edits a tax configuration in a Filament form.

1. **User action**: User changes a field or clicks “Save” in a Filament resource.
2. **Middleware/auth**: Laravel + Filament middleware validate session, permissions, and tenant/issuer access.
3. **Livewire request**: The action is sent via AJAX to the Livewire component that backs the resource/page.
4. **Server execution**:
   - Resource/Page logic runs (validation, authorization, transformations).
   - Eloquent models apply scopes (tenant/issuer) automatically.
5. **Persistence**: MySQL write occurs (often wrapped in transactions for consistency).
6. **UI update**:
   - Livewire returns a DOM diff.
   - Alpine enhances transitions and component behavior.
   - Notifications may be triggered (toast feedback) via Filament notifications.

---

## Frontend Component Architecture (Filament + Alpine)

The interactive UI is largely driven by:
- **Filament configuration (PHP)**: defines “what the UI is”
- **Livewire state (PHP)**: defines “how it behaves on the server”
- **Alpine component logic (JS)**: defines “how it behaves in the browser”

### Core utilities (upstream Filament)
In Filament’s upstream packages (commonly under `vendor/filament/.../resources/js/utilities/`), you typically find foundational utilities such as:
- Select/dropdown orchestration
- modal behavior and accessibility
- string utilities (pluralization, labels)

These utilities underpin the components shipped into `public/js/filament/`.

---

## XML Processing Architecture

A major subsystem in Fiscaut is ingestion and processing of fiscal XML documents (NF-e, CT-e, and events). This is designed as a set of **identification + parsing + routing + persistence** pipelines.

### Core Services (conceptual)
- **`XmlReaderService`**: parses XML into structured data (arrays/DTO-like structures).
- **`XmlIdentifierService`**: detects document type (NF-e, CT-e, event) and routes to the right processor.
- **`XmlNfeReaderService`**: NF-e document/event interpretation.
- **`XmlCteReaderService`**: CT-e document/event interpretation.

### Ingestion Methods (pipelines)
1. **Manual upload**
   - User uploads XML files through the admin UI.
   - Processing is typically queued (e.g., `ProcessXmlFile` job).
2. **SEFAZ integration**
   - Automated retrieval of documents/events.
   - Coordinated job pipeline (download batch → process documents).
3. **SIEG integration**
   - Bulk import via SIEG API and corresponding jobs.
4. **Bulk import**
   - Batch processing multiple XML files (e.g., `ProcessXmlFileBatch`).

### Persistence Targets (examples)
Processed data is stored in dedicated models/tables, e.g.:
- `NotaFiscalEletronica` for NF-e
- `ConhecimentoTransporteEletronico` for CT-e
- raw XML and event logs:
  - `LogSefazNfeContent`, `LogSefazCteContent`
  - `LogSefazNfeEvent`, `LogSefazCteEvent`

**Why this design**
- Supports high throughput with queues.
- Separates raw storage (audit/debug) from normalized domain tables.
- Enables re-processing and traceability of events and source payloads.

---

## Conventions and Practical Notes for Developers

- Prefer implementing admin CRUD via **Filament Resources** rather than custom controllers.
- Keep business logic in **services** or **domain methods**, not in Blade/JS.
- Use **jobs** for IO-heavy work (SEFAZ/SIEG, XML parsing, large batch imports).
- Treat `public/js/filament/**` as **UI behavior**: changes here affect component interactivity and can impact many screens.

---

## Cross-References

- Filament resources, pages, actions inventory: **[Filament Admin](./filament-admin.md)**
- System-level flow diagrams and interaction sequences: **[Data Flow](./data-flow.md)**
- General orientation: **[Project Overview](./project-overview.md)**
- Repository structure map: **[Codebase Map](./codebase-map.json)**
