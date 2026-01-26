# Project Structure and Architectural Overview

This document provides a technical overview of the Fiscaut v4.1 codebase organization. The project is built on the Laravel framework and leverages the **Filament TALL stack** (Tailwind CSS, Alpine.js, Laravel, Livewire) for its administrative and operational interfaces.

## High-Level Directory Structure

The repository follows a standard Laravel directory structure with specific patterns for Filament resources and business logic separation.

```text
/
├── app/                # Core application code (Business logic, Models, Providers)
├── config/             # Application configuration files
├── database/           # Migrations, Seeders, and Factories
├── docker/             # Containerization and environment setup
├── lang/               # Translation and localization files
├── public/             # Compiled JS/CSS and public assets
├── resources/          # Raw assets (CSS/JS), Blade templates, and views
├── routes/             # Web, API, and Console route definitions
├── specs/              # Technical specifications and design docs
└── tests/              # Automated tests (Feature and Unit)
```

---

## Core Application Logic (`app/`)

The `app/` directory contains the primary business intelligence and UI definitions.

### 1. Filament UI Layer (`app/Filament/`)
As a Filament-driven application, the user interface is defined through PHP classes rather than manual templates:
- **Resources**: Encapsulate the CRUD logic for database models. They define the forms, tables, and pages associated with a specific model (e.g., `EmpresaResource.php`).
- **Clusters**: Logical groupings of resources (e.g., "Settings" or "User Management") used to organize the navigation sidebar and breadcrumbs.
- **Pages & Widgets**: Custom standalone dashboard pages and visual components like `StatsOverview` charts or custom status counters.

### 2. Data Layer (`app/Models/`)
Eloquent models serve as the source of truth for the database schema. They define:
- **Relationships**: Database associations (e.g., `BelongsTo`, `HasMany`).
- **Scopes**: Reusable query logic for filtering data (e.g., `active()`, `byCompany()`).
- **Casts**: Mapping database types to PHP types, especially useful for casting JSON or Enums.

### 3. Business Logic (`app/Services/`)
To maintain the "Lean Controller/Model" pattern, complex logic—such as tax calculations, third-party API integrations, or complex report generation—is encapsulated in **Service Classes**. This ensures code reusability across Filament Resources, API Controllers, and Console Commands.

### 4. State Management (`app/Enums/`)
The project makes heavy use of Native PHP Enums to ensure type safety and consistent status handling:
- `AtividadesEmpresariaisEnum.php`: Standardizes business activity types.
- `RegimesEmpresariaisEnum.php`: Manages tax and corporate regimes.
- `ConfiguracoesGeraisEnum.php`: Centralizes keys for global application settings.

---

## Frontend and Client-Side Assets

Fiscaut uses a hybrid approach: server-side rendering with Livewire and client-side reactivity with Alpine.js.

### JavaScript Components (`public/js/filament/`)
Interactive UI elements are powered by compiled JavaScript modules. These often act as Alpine.js "magics" or components:
- **Schemas**: Logic for dynamic form schemas and wizards (`public/js/filament/schemas`).
- **Form Components**: Specialized field handlers for `richEditor`, `colorPicker`, `tagsInput`, and `fileUpload`.
- **Table Components**: Interactive column logic such as `toggleTableColumn` and `textInputTableColumn` that allow inline editing.

### Resources (`resources/`)
- **Views**: Contains Blade templates. While Filament handles the majority of the UI, custom layouts and component overrides reside in `resources/views`.
- **CSS/JS Source**: The raw source files processed by Vite/Tailwind before being published to the `public/` directory.

---

## Technical Dependencies

The project relies on a modern PHP ecosystem:

- **Filament (v3+)**: The core framework providing the admin panel, form builder, and table builder.
- **Livewire**: Orchestrates reactive UI updates without manual AJAX handling, bridging the gap between PHP and the browser.
- **Spatie Packages**: Used for common requirements such as Permissions (Roles), Media Library (file attachments), and Activity Logging.
- **Alpine.js**: Handles lightweight client-side state, dropdowns, and modal transitions within the browser.

---

## Architectural Conventions

To ensure maintainability and scalability, the following conventions are enforced:

1.  **Domain Isolation**: Avoid putting complex business logic inside Filament Resource classes. If a calculation requires multiple steps or external data, delegate it to a class in `app/Services`.
2.  **Enum Usage**: Never hardcode strings for statuses, types, or categories. Always refer to a case in an `app/Enums` file.
3.  **UI Consistency**: Utilize Filament's native components (`Forms\Components\*` and `Tables\Columns\*`) to maintain visual consistency. Only build custom Blade components when the framework's native options are exhausted.
4.  **Database Integrity**: All schema changes must be versioned via migrations. Seeders should be maintained to provide a predictable environment for development and testing.
5.  **Type Hinting**: Strict typing is encouraged across all methods in the `app/` directory to facilitate static analysis and prevent runtime errors.
6.  **Utility Organization**: Common JavaScript utilities (like selection logic or string manipulation) are located in `vendor/filament/support/resources/js/utilities/` and should be reused where possible.
