---
source: .github/copilot-instructions.md
type: github-copilot
---

# Project Rules and Guidelines

> Auto-generated from .context/docs on 2026-01-30T19:38:34.836Z

## README

# Fiscaut v4.1 Technical Documentation

Welcome to the technical documentation for **Fiscaut**, a proprietary fiscal and administrative management platform. This system is engineered to handle complex tax regulations, multi-tenant configurations, and fiscal automation using the TALL stack (Tailwind, Alpine.js, Laravel, Livewire).

## Project Overview

Fiscaut is designed to bridge the gap between complex Brazilian fiscal legislation and automated business management. It serves as a centralized hub for managing issuers (Companies), subscribers (Tenants), and the intricate web of tax rules (CFOP, CNAE, Simples Nacional).

- **Backend:** Laravel 12 / PHP 8.2+
- **Frontend:** Livewire 4 / Alpine.js / Tailwind CSS
- **Admin Panel:** FilamentPHP v5
- **Database:** MySQL 8.0

---

## Architecture & Tech Stack

### Framework & UI
The application follows a **Modular Monolith** approach powered by Laravel and Filament. The UI is highly reactive, utilizing Livewire for server-side state management and Alpine.js for client-side interactions.

### Component System
The repository includes a deep integration with Filament components. Custom behaviors are often extended via JavaScript utilities:
- **Rich Editors:** Custom handling for file uploads and validation messages (`vendor/filament/forms/resources/js/components/rich-editor.js`).
- **Selects:** Advanced querying and filtering logic (`vendor/filament/support/resources/js/utilities/select.js`).
- **Notifications:** A robust notification system for real-time user feedback (`vendor/filament/notifications/resources/js/Notification.js`).

### Multi-Tenancy
Fiscaut implements a multi-tenant architecture where data is scoped per **Tenant** (Subscriber). Each tenant can manage multiple **Issuers** (Companies).

---

## Core Documentation Guides

| Guide | Description |
| :--- | :--- |
| 📘 [Project Overview](./project-overview.md) | High-level roadmap and business goals. |
| 🏗️ [Architecture Notes](./architecture.md) | Service boundaries, dependency graphs, and ADRs. |
| 🧩 [Filament Admin](./filament-admin.md) | Inventário de Resources, Pages e Actions do painel. |
| 🚀 [Development Workflow](./development-workflow.md) | Branching strategy, CI/CD, and setup instructions. |
| 🧪 [Testing Strategy](./testing-strategy.md) | Protocols for Pest/PHPUnit and Browser testing. |
| 📖 [Glossary & Domain](./glossary.md) | Business terminology and fiscal domain rules. |
| 🛡️ [Security & Compliance](./security.md) | Authentication, secrets, and LGPD compliance. |
| 🛠️ [Application Services](./services.md) | Documentation for core business logic and external integrations. |
| ⚡ [Background Jobs](./jobs.md) | Guide to asynchronous queues, bulk actions, and SEFAZ pipelines. |
| 🧾 [XmlReaderService](./xml-reader-service.md) | XML parsing conventions and migration to array-based access. |

---

## Admin Resources (Filament)

The administrative layer is organized into specific resources within `app/Filament/Resources/`.
For an up-to-date inventory (resources/pages/actions), see [Filament Admin](./filament-admin.md).

### 1. Entity Management
- **Issuers (`IssuerResource`)**: Management of tax-issuing entities, their regimes, and metadata.
- **Tenants (`TenantResource`)**: Administration of subscriber accounts and access levels.
- **Category Tags (`CategoryTagResource`)**: Global tagging system for organizing records.

### 2. Fiscal & Tax Configuration
- **CFOP (`CfopResource`)**: Fiscal Operation and Installment codes used in tax documents.
- **CNAE (`CnaeResource`)**: National Classification of Economic Activities mapping.
- **Service Codes (`CodigoServicoResource`)**: Specific municipal codes for service taxation (ISS).
- **Accumulators (`AcumuladoresResource`)**: Logic for aggregating fiscal data over specific periods.

### 3. Simples Nacional Module
- **Annexes (`SimplesNacionalAnexoResource`)**: Management of the various tax "Annexes" of the Simples Nacional regime.
- **Rates (`SimplesNacionalAliquotaResource`)**: Maintenance of progressive tax rates and brackets.

---

## Repository Structure

```text
├── app/
│   ├── Filament/       # Admin panel resources, widgets, and pages
│   ├── Models/         # Eloquent models representing the fiscal domain
│   └── Actions/        # Reusable business logic classes
├── config/             # Application and third-party configurations
├── database/
│   ├── migrations/     # Database schema evolution
│   └── seeders/        # Initial data for fiscal codes (CFOP, CNAE)
├── docs/               # Technical documentation index
├── public/js/filament/ # Compiled assets for the admin interface
├── resources/
│   ├── views/          # Blade templates and Livewire components
│   └── lang/           # Localization (pt_BR)
└── tests/              # Automated test suites (Pest)
```

---

## Development Setup

To get started with development, ensure you have **Docker** and **PHP 8.2+** installed.

1.  **Clone the repository:**
    ```bash
    git clone [repository-url]
    cd fiscaut-v4.1
    ```

2.  **Environment Setup:**
    ```bash
    cp .env.example .env
    composer install
    npm install && npm run dev
    ```

3.  **Database Migration:**
    ```bash
    php artisan migrate --seed
    ```

4.  **Admin Access:**
    Create a super-admin user to access the Filament dashboard:
    ```bash
    php artisan make:filament-user
    ```

---

## Security & Confidentiality

> [!WARNING]
> **Confidentiality Notice:** Fiscaut is a proprietary commercial application. All source code, database schemas, and documentation are strictly confidential. Unauthorized sharing of credentials, client data, or architectural details is prohibited.

For security vulnerabilities or compliance issues (LGPD), please refer to the [Security & Compliance](./security.md) guide.


## README

# Quality Assurance and Developer Q&A

Welcome to the central documentation hub for **Fiscaut-v4.1**. This guide provides developers and QA engineers with a technical overview of the system architecture, component structures, and testing priorities.

Fiscaut-v4.1 is a robust web-api and administrative platform built on the **Laravel** framework, leveraging **Filament v3** for its UI/UX and **Livewire** for real-time reactivity.

---

## 🚀 Getting Started

Before contributing or testing, ensure your environment matches the production requirements:

*   **Setup Guide**: [How do I set up and run this project?](./getting-started.md) — Covers environment requirements, PHP/Node dependencies, and local server configuration.
*   **Dependencies**: Ensure `Composer` and `NPM` are installed. The project utilizes high-performance UI assets located in `public/js/filament/`.

---

## 🏗️ Project Architecture

The application follows a structured approach where logic is distributed between Laravel's backend and Filament's reactive frontend.

### Directory & Component Mapping

| Category | Primary Locations | Purpose |
| :--- | :--- | :--- |
| **Models/Schemas** | `vendor/filament/schemas/resources/js` | Defines the data structure and field definitions for forms/wizards. |
| **Form Components** | `public/js/filament/forms/components` | Logic for interactive inputs like `RichEditor`, `Select`, and `TagsInput`. |
| **Table Columns** | `public/js/filament/tables/components/columns` | Rendering logic for data grids (e.g., `ToggleColumn`, `TextInputColumn`). |
| **Widgets** | `public/js/filament/widgets/components` | Dashboard elements like `StatsOverview` and charting tools. |
| **Utilities** | `vendor/filament/support/resources/js/utilities` | Core helper functions for selection, pluralization, and DOM manipulation. |

### Component Reactivity
The frontend heavily utilizes **Livewire**. For debugging, pay attention to:
*   `findClosestLivewireComponent`: Used to link JS events to PHP backend state.
*   `Livewire.dispatch()`: Common pattern for inter-component communication.

---

## 🛠️ Technical Context

### Key Dependencies
*   **Laravel Core**: Handles routing, middleware, and database ORM (Eloquent).
*   **Filament v3**: Powers the Administrative Panel, Form Builder, and Table Builder.
*   **Livewire**: Provides the bridge between PHP and JavaScript without writing full REST APIs for every interaction.
*   **Shiki**: Used for high-fidelity code syntax highlighting in specific views.

### Public APIs & Utilities
Developers can leverage internal utilities for consistency:
*   **Select Utility**: Located at `vendor/filament/support/resources/js/utilities/select.js`. Use the `Select` class for handling complex dropdown logic.
*   **Notification System**: Use the `Notification` class in `vendor/filament/notifications/resources/js/Notification.js` to trigger UI alerts from JS.

---

## 🧪 QA Focus Areas

When performing quality assurance, focus on these critical paths:

### 1. Form Validation & Persistence
Verify that Filament form schemas correctly enforce constraints.
*   Check that `RichEditor` content is correctly sanitized.
*   Ensure `Wizard` components maintain state across steps.
*   Test `FileUpload` handlers for proper error reporting.

### 2. Component Reactivity (Livewire)
Ensure the UI remains synchronized with the server state.
*   **Stats Overview**: Verify that "Stat" widgets update when underlying data changes.
*   **Unsaved Changes Alert**: Test that the `unsaved-changes-alert.js` triggers correctly when a user attempts to navigate away from a dirty form.

### 3. Table Interactions
*   Test **Bulk Actions** in data tables.
*   Verify that `ToggleColumn` updates the database immediately via AJAX.
*   Check that `TextInputColumn` validates input before saving.

### 4. Permissions & Security
*   Validate that **Middleware** (located in `app/Http/Middleware`) correctly intercepts unauthorized requests.
*   Ensure that administrative resources are restricted to users with the appropriate roles defined in Filament policies.

---

## ❓ Common Developer Q&A

**Q: Where do I add custom JavaScript for a specific form field?**
A: Custom logic should be placed in `public/js/filament/forms/components`. Ensure you hook into the `Alpine.data()` or `Livewire` lifecycle.

**Q: How do I debug Livewire event listeners?**
A: Use the browser console to monitor `Livewire.on` events. You can also refer to `vendor/livewire/livewire/src/Features/SupportEvents/fake-echo.js` for examples of how events are mocked in tests.

**Q: How is the "Unsaved Changes" logic handled?**
A: The logic is centralized in `vendor/filament/filament/resources/js/unsaved-changes-alert.js`. It monitors the state of form fields and prevents navigation if changes are detected but not saved.

---
*Last Updated: 2026-01-23*

