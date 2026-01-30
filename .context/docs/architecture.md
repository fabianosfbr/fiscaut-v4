# Architecture Documentation

Fiscaut v4.1 is a commercial-grade tax and fiscal management application built on the **TALL stack** (Tailwind CSS, Alpine.js, Laravel, and Livewire). It employs a **Modular Monolith** architecture, leveraging the FilamentPHP ecosystem to provide a highly extensible administrative interface and sophisticated data management system.

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

The application follows the standard Laravel MVC pattern, enhanced by Filament's resource-based architecture for rapid administrative development.

### 1. Domain Layer (`app/Models`)
The domain layer encapsulates business entities and persistence logic.
- **Models**: High-level entities such as `Cfop`, `Cnae`, `User`, and `SimplesNacionalAnexo`.
- **Relationships**: Defined via standard Eloquent relationships (HasMany, BelongsTo) to maintain data integrity and the fiscal data graph.
- **Query Scoping**: Heavy utilization of Global Scopes and `modifyQueryUsing` hooks to enforce multi-tenancy and issuer-based filtering.

### 2. Application Layer (`app/Filament` & `app/Livewire`)
This layer orchestrates business logic and user interactions.
- **Filament Resources**: Located in `app/Filament/Resources`, these act as the primary controllers for CRUD operations.
- **Schemas & Tables**: Configuration for forms and tables is often extracted into dedicated classes or methods within the Resource directories to promote reusability and clean code.
- **Actions**: Custom logic for data processing (e.g., exports, status changes, downloads) is encapsulated in reusable `Action` and `ActionGroup` classes.
- **Relation Managers**: Handle sub-resources (e.g., managing specific fiscal details within an `Issuer` context) directly within the parent view.
  - See [Filament Admin](./filament-admin.md) for a concrete inventory of resources/pages/actions in `app/Filament`.

### 3. Presentation Layer (`resources/views` & JS Components)
- **Blade Templates**: Provide the structural layouts and integrate Livewire components.
- **Filament UI Components**: Standardized UI components for forms (Inputs, Selects, Rich Editors) and tables (Columns, Filters).
- **Alpine.js Components**: Client-side logic for complex UI elements like `Wizard`, `Tabs`, and `RichEditor`. These are located in `public/js/filament/` and `vendor/filament/`.
- **Assets**: Core frontend logic for Filament components is bundled and served from `public/js/filament/`.

### 4. Infrastructure Layer
- **Service Providers**: (`app/Providers`) Responsible for bootstrapping application services, registering Filament panels, and configuring security guards.
- **Migrations**: (`database/migrations`) Define the relational schema for the MySQL database.

---

## Key Design Patterns

| Pattern | Implementation in Fiscaut |
|---------|---------------------------|
| **MVC** | Fundamental Laravel structure separating data, UI, and logic. |
| **Resource-Based Routing** | Filament resources automatically map URL structures to specific database entities. |
| **Multi-Tenancy Scoping** | Systematic filtering of queries based on `tenant_id` or `currentIssuer` to ensure data isolation between different clients. |
| **Facade/Singleton** | Static interfaces to internal services, such as the `Notification::make()` utility. |
| **Action Pattern** | Encapsulating specific tasks (like "Download Report") into discrete, testable classes rather than bloated controllers. |

---

## Frontend Component Architecture

The application utilizes a sophisticated JavaScript bridge between the PHP backend and the browser, primarily managed by Alpine.js and Livewire.

### Core Utilities
Found in `vendor/filament/support/resources/js/utilities/`:
- **`Select`**: A robust utility class for managing dropdown logic, searching, and item selection.
- **`Pluralize`**: String manipulation utility for generating dynamic UI labels.
- **`Modal`**: Logic for managing overlay states, keyboard interactions (ESC to close), and accessibility.

### Component Logic
Interactive UI elements are broken down into specialized scripts:
- **Forms**: Components like `rich-editor.js`, `tags-input.js`, and `file-upload.js` handle client-side validation, asynchronous file processing, and rich text manipulation.
- **Tables**: Logic in `table.js` handles row selection, bulk actions, and real-time polling updates.
- **Notifications**: Managed by the `Notification` class in `vendor/filament/notifications/resources/js/Notification.js`, providing real-time feedback via toast messages.

---

## System Entry Points

- **Web Entry**: `public/index.php` routes all incoming HTTP traffic through the Laravel middleware stack.
- **Admin Panel**: Accessible via the path defined in `AdminPanelProvider.php` (defaults to `/admin`), serving as the main interface for fiscal management.
- **CLI**: The `artisan` command-line interface serves as the entry point for background tasks, migrations, and scheduled fiscal processing jobs.

---

## Data Flow & Security

1. **Request**: A user interacts with a Filament Table or Form (e.g., updating a tax rate).
2. **Middleware**: Laravel and Filament middleware verify session authentication and tenant access rights.
3. **Processing**: Livewire intercepts the action via AJAX, executing logic in the corresponding Resource or RelationManager on the server.
4. **Data Access**: The Eloquent Model applies necessary scopes (e.g., `tenant_id`) before executing the query against MySQL.
5. **Response**: Livewire updates only the affected DOM elements using Alpine.js for smooth transitions, or triggers a `Notification` toast to confirm success.

---

## Related Documentation
- [Project Overview](./project-overview.md)
- [Data Flow](./data-flow.md)
- [Filament Admin](./filament-admin.md)
- [Codebase Map](./codebase-map.json)
