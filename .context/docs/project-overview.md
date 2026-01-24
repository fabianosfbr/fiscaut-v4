# Project Overview

## Project Overview
Fiscaut v4.1 is a robust fiscal automation and management system built on the Laravel framework. It is designed to streamline tax compliance and fiscal operations for businesses, providing a centralized platform for managing fiscal documents and obligations. The system leverages the power of Filament for a modern, responsive administrative interface.

## Status do Produto (Proprietário)
- **Aplicação comercial proprietária**: este repositório não é open-source.
- **Uso e distribuição**: somente por pessoas e ambientes autorizados/licenciados.
- **Sigilo**: evite copiar/colar código e detalhes de implementação para fora de canais aprovados.

## Codebase Reference
> **Detailed Analysis**: For complete symbol counts, architecture layers, and dependency graphs, see [`codebase-map.json`](./codebase-map.json).

## Quick Facts
- **Root**: `/root/projetos/fiscaut-v4.1`
- **Languages**: PHP (Laravel), JavaScript (Alpine.js/Livewire), CSS (Tailwind)
- **Frameworks**: Laravel 12, Filament 5, Livewire 4
- **Database**: MySQL
- **Full analysis**: [`codebase-map.json`](./codebase-map.json)

## Entry Points
- **Web Entry**: [`public/index.php`](../public/index.php) - The standard Laravel entry point.
- **Console Entry**: [`artisan`](../artisan) - The Laravel command-line interface.

## Key Exports
See `codebase-map.json` for a complete list of exported symbols and classes. Key resources include Filament Resources located in `app/Filament/Resources`.

## Principais recursos do Admin (Filament)
- Empresas (Issuer): [IssuerResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Issuers/IssuerResource.php)
- Assinantes (Tenant): [TenantResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Tenants/TenantResource.php)
- Categorias de Etiquetas: [CategoryTagResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CategoryTags/CategoryTagResource.php)
- CFOP: [CfopResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cfops/CfopResource.php)
- CNAE: [CnaeResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cnaes/CnaeResource.php)
- Códigos de Serviço: [CodigoServicoResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CodigosServicos/CodigoServicoResource.php)
- Acumuladores: [AcumuladoresResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Acumuladores/AcumuladoresResource.php)

## Multi-tenant e escopo por empresa
- O painel Filament registra automaticamente Resources via `discoverResources(...)`: [AdminPanelProvider.php](file:///root/projetos/fiscaut-v4.1/app/Providers/Filament/AdminPanelProvider.php)
- Alguns recursos escopam dados por `tenant_id` e/ou pela empresa atual (issuer) do usuário, geralmente com `modifyQueryUsing` nas Tables.

## File Structure & Code Organization
- `app/` — Core application code, including Models, Http Controllers, and Filament Resources.
- `bootstrap/` — Framework bootstrapping and configuration.
- `config/` — Application configuration files.
- `database/` — Database migrations, seeds, and factories.
- `public/` — Publicly accessible assets (images, JS, CSS).
- `resources/` — Views, raw assets (Sass, JS), and language files.
- `routes/` — Route definitions (web, api, console).
- `tests/` — Feature and Unit tests.
- `vendor/` — Composer dependencies.

## Technology Stack Summary
- **Backend**: PHP 8.4 running Laravel 12.48.
- **Frontend**: Blade templates augmented with Livewire 4 and Alpine.js.
- **Admin Panel**: Filament 5.0 providing a full-featured admin dashboard.
- **Styling**: Tailwind CSS 4.1.
- **Containerization**: Laravel Sail (Docker) for local development.

## Core Framework Stack
- **Backend Layer**: Laravel Framework (MVC architecture).
- **Frontend Layer**: Livewire for dynamic interfaces without writing extensive JavaScript.
- **Data Layer**: Eloquent ORM for database interactions.

## UI & Interaction Libraries
- **Filament**: The primary UI kit for the admin panel, providing tables, forms, and widgets.
- **Alpine.js**: Lightweight JavaScript framework used by Livewire and Filament for client-side interactivity.
- **Tailwind CSS**: Utility-first CSS framework for styling.

## Development Tools Overview
- **Artisan**: Powerful CLI for code generation, migrations, and system tasks.
- **Composer**: PHP dependency manager.
- **NPM/Vite**: Asset bundling and frontend package management.

## Getting Started Checklist
1. **Environment Setup**: Ensure Docker and Docker Compose are installed.
2. **Install Dependencies**: Run `composer install` and `npm install`.
3. **Environment Config**: Copy `.env.example` to `.env` and configure database credentials.
4. **Start Application**: Run `./vendor/bin/sail up -d` to start the containers.
5. **Key Generation**: Run `./vendor/bin/sail artisan key:generate`.
6. **Migrations**: Run `./vendor/bin/sail artisan migrate --seed` to set up the database.
7. **Access**: Visit `http://localhost` to view the application.
8. **Review**: Check [`development-workflow.md`](./development-workflow.md) for daily tasks.

## Next Steps
- Explore the **Architecture** guide to understand the system design.
- Review **Data Flow** to see how information moves through the system.
- Check **Tooling** for detailed command references.

## Cross-References
- [architecture.md](./architecture.md)
- [development-workflow.md](./development-workflow.md)
- [tooling.md](./tooling.md)
- [codebase-map.json](./codebase-map.json)
