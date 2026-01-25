# Fiscaut v4.1 - Project Overview

## Introduction
Fiscaut v4.1 is a professional-grade fiscal automation and management system built on the **Laravel** framework. It serves as a centralized platform for businesses to streamline tax compliance, manage complex fiscal documents, and handle regulatory obligations.

The application is built using the **TALL stack** (Tailwind, Alpine.js, Laravel, Livewire) and leverages **Filament** to provide a highly productive, reactive administrative interface.

---

## Technical Stack

| Layer | Technology |
| :--- | :--- |
| **Backend** | PHP 8.4+ / Laravel 12 |
| **Frontend** | Livewire 4, Alpine.js, Blade |
| **Admin UI** | Filament 5 |
| **CSS Framework** | Tailwind CSS 4.1 |
| **Database** | MySQL |
| **Asset Pipeline** | Vite |
| **Local Dev** | Laravel Sail (Docker) |

---

## Core Architecture

### 1. Multi-tenant Structure
Fiscaut is designed for scalability across multiple organizations. It utilizes a two-tier hierarchy:
- **Tenants (Assinantes)**: The top-level account or subscriber (e.g., an accounting firm).
- **Issuers (Empresas)**: Individual fiscal entities (companies) managed under a specific tenant.
- **Data Isolation**: Most resources are automatically scoped via `tenant_id` or the active `issuer` context to ensure strict data isolation and security.

### 2. Administrative Interface
The backend is powered by **Filament Resources**, which encapsulate logic for:
- **Issuer Management**: Handling fiscal entity configurations.
- **Fiscal Parameters**: Managing CFOP, CNAE, Service Codes, and Accumulators.
- **Tagging & Categories**: Dynamic labeling via the `CategoryTagResource`.

### 3. Reactive UI Components
The system avoids full page reloads by combining **Livewire** for server-side state management with **Alpine.js** for client-side interactivity. Complex UI elements like the notification system and form schemas are managed through a unified JavaScript bridge.

---

## Key System Components

### UI Component Library
The project features a suite of specialized JS-backed components located in `vendor/filament/` and `public/js/filament/`. Key components include:

- **Forms**: `selectFormComponent`, `richEditorFormComponent`, `fileUploadFormComponent`, `dateTimePickerFormComponent`.
- **Tables**: `checkboxTableColumn`, `textInputTableColumn`, `toggleTableColumn`, and a comprehensive `filamentTableColumnManager`.
- **Widgets**: `chart` components and `statsOverviewStatChart` for data visualization.
- **Schemas**: `tabsSchemaComponent` and `wizardSchemaComponent` for multi-step data entry.

### Notifications & Actions
Fiscaut uses a centralized notification system (`vendor/filament/notifications/`) allowing for real-time feedback.
- **Notification Class**: Handles the creation and dispatch of user alerts.
- **Actions**: Encapsulates executable logic (e.g., "Export PDF", "Validate Document") that can be triggered from tables or forms.

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
└── vendor/filament/       # Core framework logic for forms, tables, and schemas
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
- **Testing**: Use the `FakeEcho` and event fakes provided in the testing suite for real-time feature verification.

---

## Related Documentation
- [Architecture Details](./architecture.md) — Deep dive into the system design.
- [Development Workflow](./development-workflow.md) — Commands and daily routines.
- [Tooling Guide](./tooling.md) — Reference for CLI and helper tools.
