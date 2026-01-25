# Project Rules and Guidelines

> Auto-generated from .context/docs on 2026-01-25T15:01:03.051Z

## README

# Documentation Index

Welcome to the repository knowledge base. Start with the project overview, then dive into specific guides as needed.

## Contexto do Projeto
- **Produto**: Fiscaut é uma aplicação comercial proprietária (uso interno e/ou por clientes licenciados).
- **Confidencialidade**: não compartilhar código, dados, credenciais ou detalhes de arquitetura fora dos canais autorizados.
- **Stack principal**: Laravel v12, FilamentPHP v5 (admin), Livewire v4 (UI reativa) e MySQL.

## Core Guides
- [Project Overview](./project-overview.md)
- [Architecture Notes](./architecture.md)
- [Development Workflow](./development-workflow.md)
- [Testing Strategy](./testing-strategy.md)
- [Glossary & Domain Concepts](./glossary.md)
- [Data Flow & Integrations](./data-flow.md)
- [Security & Compliance Notes](./security.md)
- [Tooling & Productivity Guide](./tooling.md)

## Recursos Filament (Admin)
- **Configurações**
  - Empresas (Issuer): [IssuerResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Issuers/IssuerResource.php)
  - Assinantes (Tenant): [TenantResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Tenants/TenantResource.php)
  - Categorias de Etiquetas: [CategoryTagResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CategoryTags/CategoryTagResource.php)
  - CFOP: [CfopResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cfops/CfopResource.php)
  - CNAE: [CnaeResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cnaes/CnaeResource.php)
  - Códigos de Serviço: [CodigoServicoResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/CodigosServicos/CodigoServicoResource.php)
  - Acumuladores: [AcumuladoresResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Acumuladores/AcumuladoresResource.php)
  - Anexos do Simples Nacional: [SimplesNacionalAnexoResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/SimplesNacionalAnexos/SimplesNacionalAnexoResource.php)
  - Alíquotas do Simples Nacional: [SimplesNacionalAliquotaResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/SimplesNacionalAliquotas/SimplesNacionalAliquotaResource.php)

## Planos (AI Context)
- [Plans Index](../plans/README.md)

## Repository Snapshot
- `app/`
- `artisan/`
- `bootstrap/`
- `compose.yaml`
- `composer.json`
- `composer.lock`
- `config/`
- `database/`
- `docker/`
- `lang/`
- `laradumps.yaml`
- `package-lock.json`
- `package.json`
- `phpunit.xml`
- `public/`
- `README.md`
- `resources/`
- `routes/`
- `specs/`
- `storage/`
- `tests/` — Automated tests and fixtures.
- `vendor/`
- `vite.config.js`

## Document Map
| Guide | File | Primary Inputs |
| --- | --- | --- |
| Project Overview | `project-overview.md` | Roadmap, README, stakeholder notes |
| Architecture Notes | `architecture.md` | ADRs, service boundaries, dependency graphs |
| Development Workflow | `development-workflow.md` | Branching rules, CI config, contributing guide |
| Testing Strategy | `testing-strategy.md` | Test configs, CI gates, known flaky suites |
| Glossary & Domain Concepts | `glossary.md` | Business terminology, user personas, domain rules |
| Data Flow & Integrations | `data-flow.md` | System diagrams, integration specs, queue topics |
| Security & Compliance Notes | `security.md` | Auth model, secrets management, compliance requirements |
| Tooling & Productivity Guide | `tooling.md` | CLI scripts, IDE configs, automation workflows |


## README

# Q&A Index

Project type: **web-api**

Generated: 2026-01-23T17:58:26.752Z

## Getting-started

- [How do I set up and run this project?](./getting-started.md)

## Architecture

- [How is the codebase organized?](./project-structure.md)
- [How does routing work?](./routing.md)
- [How does middleware work?](./middleware.md)

## Features

- [How is data stored and accessed?](./database.md)

