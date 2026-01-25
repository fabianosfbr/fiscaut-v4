# Fiscaut Documentation Index

Welcome to the official documentation for **Fiscaut v4.1**. This repository serves as the central knowledge base for developers, maintainers, and stakeholders.

## Project Summary

**Fiscaut** is a proprietary commercial application designed for fiscal and administrative management. It provides a robust interface for handling complex tax rules, issuer management, and tenant-based configurations.

- **Status**: Active Development
- **Access**: Proprietary (Internal use/Licensed clients only)
- **Tech Stack**: Laravel v12, FilamentPHP v5 (Admin Panel), Livewire v4 (Reactive UI), and MySQL.

> [!IMPORTANT]
> **Confidencialidade**: Este é um projeto proprietário. Não compartilhe código, credenciais, dados de clientes ou detalhes de arquitetura fora dos canais autorizados da empresa.

---

## Documentation Guides

Explore specific areas of the system through the following guides:

| Guide | Description |
| :--- | :--- |
| 📘 [Project Overview](./project-overview.md) | High-level roadmap, business goals, and stakeholder notes. |
| 🏗️ [Architecture Notes](./architecture.md) | Service boundaries, dependency graphs, and ADRs (Architecture Decision Records). |
| 🚀 [Development Workflow](./development-workflow.md) | Branching strategy (GitFlow), CI/CD configurations, and setup instructions. |
| 🧪 [Testing Strategy](./testing-strategy.md) | Unit, Feature, and Browser testing protocols and CI gates. |
| 📖 [Glossary & Domain](./glossary.md) | Business terminology, user personas, and fiscal domain rules. |
| 🔄 [Data Flow & Integrations](./data-flow.md) | System diagrams, external API integrations, and queue management. |
| 🛡️ [Security & Compliance](./security.md) | Authentication models, secrets management, and LGPD/Compliance requirements. |
| 🛠️ [Tooling & Productivity](./tooling.md) | Custom CLI scripts, IDE configurations, and automation workflows. |

---

## Filament Admin Resources

The administrative interface is built on FilamentPHP v5. Key resources include:

### Core Management
- **Companies (Issuers)**: [`IssuerResource.php`](../app/Filament/Resources/Issuers/IssuerResource.php) - Management of tax-issuing entities.
- **Subscribers (Tenants)**: [`TenantResource.php`](../app/Filament/Resources/Tenants/TenantResource.php) - Multi-tenant account management.
- **Category Tags**: [`CategoryTagResource.php`](../app/Filament/Resources/CategoryTags/CategoryTagResource.php) - Organization and labeling system.

### Fiscal & Tax Configuration
- **CFOP**: [`CfopResource.php`](../app/Filament/Resources/Cfops/CfopResource.php) - Fiscal Operations and Installments codes.
- **CNAE**: [`CnaeResource.php`](../app/Filament/Resources/Cnaes/CnaeResource.php) - National Classification of Economic Activities.
- **Service Codes**: [`CodigoServicoResource.php`](../app/Filament/Resources/CodigosServicos/CodigoServicoResource.php) - Municipal service tax codes.
- **Accumulators**: [`AcumuladoresResource.php`](../app/Filament/Resources/Acumuladores/AcumuladoresResource.php) - Fiscal accumulation logic.

### Simples Nacional
- **Annexes**: [`SimplesNacionalAnexoResource.php`](../app/Filament/Resources/SimplesNacionalAnexos/SimplesNacionalAnexoResource.php)
- **Rates (Alíquotas)**: [`SimplesNacionalAliquotaResource.php`](../app/Filament/Resources/SimplesNacionalAliquotas/SimplesNacionalAliquotaResource.php)

---

## Repository Structure

```text
├── app/                # Core PHP application logic (Models, Resources, Actions)
├── bootstrap/          # Framework boilerplate
├── config/             # Application configuration files
├── database/           # Migrations, Seeders, and Factories
├── docs/               # Technical documentation (You are here)
├── public/             # Compiled assets and entry point
├── resources/          # Views (Blade), Lang files, and Raw assets (JS/CSS)
├── routes/             # Web, API, and Console route definitions
├── tests/              # Automated test suites (Pest/PHPUnit)
└── vendor/             # Composer dependencies
```

---

## Tech Stack Details

- **Backend**: PHP 8.2+ / Laravel 12
- **Admin UI**: Filament v5 (TALL Stack)
- **Frontend**: Livewire v4, Alpine.js, Tailwind CSS
- **Database**: MySQL 8.0
- **Dev Tools**: Docker (via `compose.yaml`), Vite, PHPStan, Laradumps

For detailed implementation patterns regarding JS components (Rich Editors, Selects, Modals), refer to the internal `vendor/filament` source analysis or the [Architecture Notes](./architecture.md).
