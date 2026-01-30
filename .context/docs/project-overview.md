# Fiscaut v4.1 - Project Overview

## Introduction
Fiscaut v4.1 is a professional-grade fiscal automation and management system built on the **Laravel** framework. It serves as a centralized platform for businesses to streamline tax compliance, manage complex fiscal documents, and handle regulatory obligations through a modern, reactive interface.

The application is built using the **TALL stack** (Tailwind, Alpine.js, Laravel, Livewire) and leverages **Filament** to provide a highly productive administrative interface optimized for data-heavy operations.

---

## Technical Stack

| Layer | Technology | Version / Details |
| :--- | :--- | :--- |
| **Backend** | PHP | 8.4+ |
| **Framework** | Laravel | 12.x |
| **Frontend** | Livewire | 4.x |
| **Interactivity** | Alpine.js | Core of the TALL stack |
| **Admin UI** | Filament | 5.x |
| **CSS Framework** | Tailwind CSS | 4.1 |
| **Database** | MySQL | Persistent storage |
| **Asset Pipeline** | Vite | Module bundling |
| **Local Dev** | Laravel Sail | Docker-based environment |

---

## Core Architecture

### 1. Multi-tenant Structure
Fiscaut is designed for scalability across multiple organizations using a two-tier hierarchy:
- **Tenants (Assinantes)**: The top-level account or subscriber (e.g., an accounting firm).
- **Issuers (Empresas)**: Individual fiscal entities (companies) managed under a specific tenant.
- **Data Isolation**: Most resources are automatically scoped via `tenant_id` or the active `issuer` context to ensure strict data isolation and security.

### 2. Administrative Interface
The backend is powered by **Filament Resources**, which encapsulate logic for:
- **Issuer Management**: Handling fiscal entity configurations.
- **Fiscal Parameters**: Managing CFOP, CNAE, Service Codes, and Accumulators.
- **Tagging & Categories**: Dynamic labeling via the `CategoryTagResource`.

### 3. Reactive UI Components
The system bridges PHP and JavaScript to maintain a seamless user experience. It uses **Livewire** for server-side state management and **Alpine.js** for client-side interactivity. Complex UI elements like the notification system and form schemas are managed through a unified JavaScript bridge located in `vendor/filament/` and `public/js/filament/`.

### 4. Advanced XML Processing
The system utilizes a specialized array-based processing engine for fiscal documents, replacing legacy generic parsers.
- **Micro-services**: Dedicated `XmlNfeReaderService` and `XmlCteReaderService` for accurate schema handling.
- **Normalization**: Automatically converts complex XML trees into associative arrays with predictable structures (lists, attributes, content).
- **Compliance**: Adheres to strict SEFAZ schemas for NFe and CTe processing.


---

## Key System Components

### UI Component Library
The project features a suite of specialized JS-backed components. These are categorized into:

*   **Forms**: Advanced inputs including `selectFormComponent`, `richEditorFormComponent`, `fileUploadFormComponent`, and `dateTimePickerFormComponent`.
*   **Tables**: Interactive data grids utilizing `checkboxTableColumn`, `textInputTableColumn`, and `toggleTableColumn`.
*   **Widgets**: Data visualization tools such as `chart` components and `statsOverviewStatChart`.
*   **Schemas**: Structural components like `tabsSchemaComponent` and `wizardSchemaComponent` for multi-step data entry.

### Notifications & Actions
Fiscaut uses a centralized notification system allowing for real-time feedback:
- **Notification Class**: (`vendor/filament/notifications/resources/js/Notification.js`) Handles the creation and dispatch of user alerts.
- **Actions**: (`vendor/filament/actions/`) Encapsulates executable logic (e.g., "Export PDF", "Validate Document") that can be triggered from tables or forms.

### JavaScript Utilities
Common logic is extracted into utility modules for consistency:
- **Select Utility**: Handles dropdown logic and filtering (`vendor/filament/support/resources/js/utilities/select.js`).
- **Partials**: Helper functions like `findClosestLivewireComponent` to manage the DOM-to-Livewire relationship.
- **Pluralize**: Text manipulation for dynamic UI labels.

---

## Directory Organization

```text
├── app/
│   ├── Filament/          # Admin Panel resources, widgets, and custom pages
│   ├── Models/            # Eloquent models representing fiscal entities
│   └── Providers/         # Service providers, including Filament configuration
├── database/
│   └── migrations/        # Schema definitions for fiscal tables
├── public/js/filament/    # Compiled assets for Filament UI components
├── resources/
│   ├── views/             # Custom Blade and Livewire views
│   └── js/                # Application-level JavaScript (app.js)
├── vendor/filament/       # Core framework logic for forms, tables, and schemas
└── docs/                  # Project documentation (Architecture, Workflow)
```

---

## Getting Started

### Prerequisites
- Docker and Docker Compose
- Node.js & NPM
- Composer

### Installation Workflow
1.  **Clone & Install Dependencies**:
    ```bash
    composer install
    npm install
    ```
2.  **Environment Configuration**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
3.  **Local Environment (Sail)**:
    ```bash
    ./vendor/bin/sail up -d
    ./vendor/bin/sail artisan migrate --seed
    ```
4.  **Asset Compilation**:
    ```bash
    npm run dev
    ```

---

## Development Standards

- **Resource Discovery**: New management modules should be created as Filament Resources in `app/Filament/Resources/`.
- **Query Scoping**: Always implement global scopes or use `modifyQueryUsing` in Table definitions to respect the `tenant_id` and `issuer_id`.
- **UI Consistency**: Use the provided JS utilities and Filament components for form fields and table columns to ensure a unified user experience.
- **Component Communication**: Use Livewire events for server-side communication and Alpine.js `$dispatch` for client-side events.
- **Testing**: Utilize the `FakeEcho` and event fakes provided in the `vendor/livewire/livewire/src/Features/SupportEvents/` suite for real-time feature verification.

---

## Related Documentation
- [Architecture Details](./architecture.md) — Deep dive into the system design.
- [Development Workflow](./development-workflow.md) — Commands and daily routines.
- [Tooling Guide](./tooling.md) — Reference for CLI and helper tools.
