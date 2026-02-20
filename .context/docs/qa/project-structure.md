# Project Structure (Fiscaut v4.1)

This document describes how the Fiscaut v4.1 repository is organized, how major layers relate to each other, and where to place new code. The application is built with **Laravel** and uses the **Filament TALL stack** (Tailwind CSS, Alpine.js, Laravel, Livewire) for administrative and operational interfaces.

---

## High-level directory layout

At the root level, the project largely follows standard Laravel conventions, with some Filament- and product-specific additions:

```text
/
├── app/                # Core application code (Business logic, Models, Providers)
├── config/             # Application configuration files
├── database/           # Migrations, Seeders, and Factories
├── docker/             # Containerization and environment setup
├── docs/               # Documentation (this file lives here)
├── lang/               # Translation and localization files
├── public/             # Public assets: compiled JS/CSS, images, etc.
├── resources/          # Blade views, raw assets (pre-build), frontend source
├── routes/             # Web, API, and console routes
├── specs/              # Technical specifications and design docs
└── tests/              # Automated tests (Feature and Unit)
```

---

## Application layer (`app/`)

The `app/` directory contains the PHP source-of-truth for domain rules, data modeling, and Filament UI definitions.

### Filament UI (`app/Filament/`)

This is the primary UI layer of the system. Filament is class-driven: most CRUD and dashboard behavior is expressed in PHP rather than handcrafted Blade templates.

Typical structure and responsibilities:

- **Resources**  
  Encapsulate CRUD for an Eloquent model and define:
  - Forms (fields, validation, behavior)
  - Tables (columns, filters, actions)
  - Pages (list/create/edit/view flows)

  Example naming pattern:
  - `app/Filament/Resources/EmpresaResource.php`
  - `app/Filament/Resources/EmpresaResource/Pages/*`

- **Clusters**  
  Navigation-level grouping for related Resources/Pages (e.g., “Settings”, “User Management”), impacting sidebar organization and breadcrumbs.

- **Pages & Widgets**  
  Used for dashboards or custom screens that are not standard CRUD. Widgets may include stats overviews, counters, charts, and custom blocks.

**Rule of thumb:** Resource classes should orchestrate UI behavior; *complex domain logic belongs in `app/Services`*.

---

### Data models (`app/Models/`)

Eloquent models represent the data layer and define:

- **Relationships** (`belongsTo`, `hasMany`, etc.)
- **Query scopes** for reusable filters (e.g., `active()`, `byCompany()`)
- **Casting** for JSON, dates, and Enums (recommended to reduce “stringly-typed” state)

Use models as the central point for data representation, but avoid packing complex multi-step workflows here—delegate those to Services.

---

### Business services (`app/Services/`)

Service classes contain multi-step business workflows and integration logic, such as:

- Tax calculations and fiscal rules
- Third-party API integrations
- Report generation
- Batch operations and orchestrations used by both UI and backend entry points

**Why this exists:** it supports the “lean UI layer” principle: Filament Resources call into services, services operate on models/repositories/integrations, and results are returned to the UI.

---

### Enums (`app/Enums/`)

The codebase uses native PHP Enums to standardize state and eliminate repeated literals.

Examples of typical enum roles:

- Business activity types (e.g., `AtividadesEmpresariaisEnum.php`)
- Tax/corporate regimes (e.g., `RegimesEmpresariaisEnum.php`)
- Global configuration keys (e.g., `ConfiguracoesGeraisEnum.php`)

**Convention:** do not hardcode status/type strings. Prefer enum cases in:
- Model casts
- Form options
- Table badges/labels
- Business logic branching

---

## Frontend assets & interactivity

Fiscaut uses a hybrid approach:
- **Livewire + Filament** for server-rendered UI with reactive updates
- **Alpine.js** for lightweight client-side state and interactions

### Public, compiled JS (`public/js/filament/`)

Interactive Filament components are shipped as compiled modules under `public/js/filament/`. This includes logic for schemas, forms, tables, and widgets.

Key areas:

- **Schemas**  
  Dynamic schema/wizard helpers:
  - `public/js/filament/schemas`
  - `public/js/filament/schemas/components`

  Examples (compiled modules):
  - `public/js/filament/schemas/components/wizard.js`
  - `public/js/filament/schemas/components/tabs.js`

- **Form components**  
  Field-specific client behavior for richer inputs:
  - `public/js/filament/forms/components/*`
  - Examples: `textarea.js`, `tags-input.js`, `rich-editor.js`, `key-value.js`, `checkbox-list.js`

- **Table column components**  
  Inline-editing and interactive table behaviors:
  - `public/js/filament/tables/components/columns/*`
  - Examples: `toggle.js`, `text-input.js`, `checkbox.js`

- **Widget components**  
  Client behavior for widgets and stats blocks:
  - `public/js/filament/widgets/components/*`
  - Stats overview patterns:
    - `public/js/filament/widgets/components/stats-overview/stat`

> Note: These files are compiled/minified and may contain short symbol names. When changing frontend behavior, prefer editing the source in `resources/` (if present) and rebuilding assets, rather than patching compiled files directly.

---

### Source views and assets (`resources/`)

- **Blade templates** live in `resources/views`.  
  Filament covers most screens, but overrides and custom layouts/components reside here.

- **Raw frontend assets** (CSS/JS) are typically authored here and built via Vite/Tailwind into `public/`.

---

## Configuration, database, routes

### Configuration (`config/`)
Laravel configuration files. Use environment variables for secrets and deployment-specific values.

### Database (`database/`)
- **Migrations**: versioned schema evolution
- **Seeders**: reproducible baseline data for dev/test
- **Factories**: test data generation

**Convention:** every schema change must be in a migration (no manual DB edits).

### Routes (`routes/`)
- `web.php`: browser routes
- `api.php`: API routes
- console routes as applicable

Filament routes are largely managed by the Filament package; custom endpoints should be clearly separated by purpose.

---

## Tests (`tests/`)

Automated tests should cover:
- Services (unit tests)
- Authorization and business flows (feature tests)
- Critical Filament actions and policies where appropriate

When adding new service logic, add/extend unit tests in `tests/Unit` and verify integration flows in `tests/Feature`.

---

## Specs and documentation (`specs/`, `docs/`)

- `specs/` contains technical specifications and design notes.
- `docs/` contains developer documentation.  
  Keep architectural decisions, onboarding steps, and operational instructions here.

---

## Architectural conventions (project rules)

1. **Domain isolation**  
   Keep Filament Resources focused on UI definition and orchestration. Put multi-step logic in `app/Services`.

2. **Enum-first state**  
   Prefer Enums over strings for statuses, types, categories, and config keys.

3. **UI consistency**  
   Use Filament components (`Forms\Components\*`, `Tables\Columns\*`) wherever possible. Create custom Blade components only when Filament doesn’t support the needed behavior.

4. **Database integrity**  
   All schema changes via migrations; seeders should keep environments predictable.

5. **Type safety**  
   Prefer strict typing and explicit return types in `app/` to support static analysis and reduce runtime errors.

6. **Reuse existing JS utilities**  
   Common JS helpers often live under Filament support utilities (e.g., `vendor/filament/support/resources/js/utilities/`). Reuse patterns instead of re-implementing.

---

## “Where do I put…?” (quick mapping)

- **New CRUD for a model** → `app/Filament/Resources/*`
- **Custom dashboard screen** → `app/Filament/Pages/*`
- **Custom dashboard widget** → `app/Filament/Widgets/*`
- **Complex business workflow** → `app/Services/*`
- **Status/type constants** → `app/Enums/*`
- **Schema changes** → `database/migrations/*`
- **Baseline/test data** → `database/seeders/*`, `database/factories/*`
- **Custom Blade UI** → `resources/views/*`
- **Frontend source assets** → `resources/*` (built to `public/`)
- **Compiled runtime assets** → `public/*`
- **Docs/specs** → `docs/*`, `specs/*`

---

## Related areas

- **Filament compiled components**: `public/js/filament/**`
- **Domain logic entry points**: `app/Services/**`
- **UI definitions**: `app/Filament/**`
- **Data layer**: `app/Models/**`
- **Specifications**: `specs/**`
