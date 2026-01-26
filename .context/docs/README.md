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
| 🚀 [Development Workflow](./development-workflow.md) | Branching strategy, CI/CD, and setup instructions. |
| 🧪 [Testing Strategy](./testing-strategy.md) | Protocols for Pest/PHPUnit and Browser testing. |
| 📖 [Glossary & Domain](./glossary.md) | Business terminology and fiscal domain rules. |
| 🛡️ [Security & Compliance](./security.md) | Authentication, secrets, and LGPD compliance. |

---

## Admin Resources (Filament)

The administrative layer is organized into specific resources within `app/Filament/Resources/`.

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
