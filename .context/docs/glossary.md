# Glossary & Domain Concepts

## Glossary & Domain Concepts
This document defines the core terminology used within the Fiscaut system.

- **CFOP** (Código Fiscal de Operações e Prestações): A Brazilian tax code used to identify the nature of circulation of goods or provision of services. It is a critical entity in this system.
- **Filament**: The admin panel framework used to build the UI.
- **Resource**: A Filament concept representing a CRUD interface for a specific Eloquent model.
- **NFe** (Nota Fiscal Eletrônica): Electronic Invoice (likely a related concept in this domain).

## Type Definitions
- **Model**: Represents a database table row (e.g., `App\Models\Cfop`).
- **Policy**: Authorization logic class (e.g., `App\Policies\CfopPolicy`).

## Enumerations
(To be populated as enums are discovered in `app/Enums`)

## Core Terms
- **Tenant**: If the application is multi-tenant, this refers to the organization using the software.
- **Fiscal Document**: Generic term for invoices, receipts, etc.

## Acronyms & Abbreviations
- **CRUD**: Create, Read, Update, Delete.
- **MVC**: Model View Controller.
- **ORM**: Object-Relational Mapping (Eloquent).
- **TALL**: Tailwind, Alpine, Laravel, Livewire.

## Personas / Actors
- **Administrator**: Has full access to all system settings and resources.
- **Fiscal Analyst**: Manages fiscal documents, CFOPs, and tax rules.
- **Viewer**: Read-only access to reports.

## Domain Rules & Invariants
- **CFOP Validation**: CFOP codes must follow a specific 4-digit format defined by the government.
- **Uniqueness**: Certain fiscal codes must be unique per operation type.

## Cross-References
- [project-overview.md](./project-overview.md)
