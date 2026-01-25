# Architecture Documentation

Fiscaut v4.1 is a robust commercial application built on the **TALL stack** (Tailwind CSS, Alpine.js, Laravel, and Livewire). It follows a **Modular Monolith** architecture, leveraging the FilamentPHP ecosystem to provide a highly extensible administrative interface and data management system.

## Technology Stack

| Layer | Technology |
|-------|------------|
| **Backend Framework** | Laravel v12 |
| **Admin Panel** | FilamentPHP v5 |
| **Frontend Reactivity** | Livewire v4 & Alpine.js |
| **Styling** | Tailwind CSS |
| **Database** | MySQL |
| **Caching/Queues** | Redis (Configurable) |
| **Runtime** | PHP-FPM / Nginx (Docker/Sail) |

---

## Architectural Layers

The application follows the standard Laravel MVC pattern, enhanced by Filament's resource-based architecture.

### 1. Domain Layer (`app/Models`)
Represents the business entities and logic. 
- **Models**: High-level entities such as `Cfop`, `Cnae`, `User`, and `SimplesNacionalAnexo`.
- **Relationships**: Standard Eloquent relationships (HasMany, BelongsTo) define the data graph.
- **Scoping**: Heavy use of Global Scopes or `modifyQueryUsing` for multi-tenancy and issuer-based filtering.

### 2. Application Layer (`app/Filament` & `app/Livewire`)
This layer handles the orchestration of business logic and user interactions.
- **Filament Resources**: Located in `app/Filament/Resources`, these act as the primary controllers for CRUD operations.
- **Schemas & Tables**: Configuration for forms and tables is often extracted into dedicated classes within the Resource directories to promote reusability.
- **Actions**: Custom logic for data processing (e.g., exports, downloads, status changes) is encapsulated in `Action` classes.
- **Relation Managers**: Manage sub-resources (e.g., managing `Users` within an `Issuer` context) directly within the parent view.

### 3. Presentation Layer (`resources/views` & JS Components)
- **Blade Templates**: Used for structural layouts.
- **Filament Components**: Pre-built UI components for forms, tables, and notifications.
- **Alpine.js**: Handles client-side reactivity for complex UI elements like `RichEditor`, `Wizard`, and `Select` utilities.
- **Assets**: Core frontend logic for Filament components is located in `public/js/filament/`.

### 4. Infrastructure Layer
- **Service Providers**: (`app/Providers`) Bootstrap application services, register Filament panels, and configure authentication guards.
- **Migrations**: (`database/migrations`) Define the relational schema for MySQL.

---

## Key Design Patterns

| Pattern | Implementation in Fiscaut |
|---------|---------------------------|
| **MVC** | Core Laravel structure (Model-View-Controller). |
| **Resource-Based Routing** | Filament resources automatically map URL structures to specific database entities. |
| **Tenant/Issuer Scoping** | Systematic filtering of queries based on `tenant_id` or `currentIssuer` to ensure data isolation. |
| **Facade** | Static interfaces to internal services (e.g., `Notification::make()`). |
| **Action Pattern** | Encapsulating specific business tasks (like "Download Report") into discrete, testable classes. |
| **Repository (Implicit)** | Filament Resources abstract the data access layer, providing a unified interface for fetching and saving models. |

---

## Frontend Component Architecture

The application utilizes a sophisticated JavaScript bridge between PHP and the browser, primarily managed by Alpine.js and Livewire.

### Core Utilities (`vendor/filament/support/resources/js/utilities`)
- **`Select`**: A robust utility for managing dropdown logic and search.
- **`Pluralize`**: String manipulation for UI labels.
- **`Modal`**: Logic for managing overlay states and keyboard interactions.

### Component Logic
Interactive UI elements are broken down into specialized scripts:
- **Forms**: Components like `rich-editor.js`, `tags-input.js`, and `file-upload.js` handle client-side validation and async processing.
- **Tables**: Logic in `table.js` handles row selection, bulk actions, and polling.
- **Notifications**: Managed by `Notification.js`, providing real-time feedback to users via the `Notification` class.

---

## System Entry Points

- **Web Entry**: `public/index.php` routes all HTTP traffic.
- **Admin Panel**: Accessible via the path defined in `AdminPanelProvider.php` (typically `/admin`).
- **CLI**: `artisan` serves as the entry point for scheduled tasks, migrations, and code generation.

---

## Data Flow & Security

1. **Request**: A user interacts with a Filament Table or Form.
2. **Middleware**: Laravel and Filament middleware verify authentication and tenant access.
3. **Processing**: Livewire intercepts the action, executing logic in the corresponding Resource or RelationManager.
4. **Data Access**: The Model applies scopes (e.g., `tenant_id`) before querying MySQL.
5. **Response**: Livewire updates only the affected DOM elements, or Filament triggers a `Notification` toast.

---

## Related Documentation
- [Project Overview](./project-overview.md)
- [Data Flow](./data-flow.md)
- [Codebase Map](./codebase-map.json)
