# Glossary & Domain Concepts

This document defines the core terminology, domain concepts, and technical abstractions used within the Fiscaut v4.1 system. It serves as a single source of truth for developers and stakeholders to ensure consistent communication and implementation across the codebase.

## Domain Terminology (Fiscal & Business)

Fiscaut is a fiscal management system tailored for the Brazilian market. Understanding these domain-specific terms is critical for working with the business logic.

### Brazilian Tax Concepts

*   **CFOP (Código Fiscal de Operações e Prestações):** A standardized 4-digit code used by the Brazilian government to identify the nature of the circulation of goods or the provision of services. 
    *   *Validation:* Must follow specific government patterns.
    *   *Implementation:* Managed via `CfopResource`.
*   **Simples Nacional:** A simplified tax regime for small and medium-sized enterprises (SMEs) in Brazil.
*   **Anexo (Simples Nacional):** A specific category of business activity (e.g., commerce, industry, services) that dictates which tax table (Faixas) applies to a company's revenue.
*   **Faixa (Simples Nacional):** A revenue bracket. Each "Anexo" has multiple brackets based on the accumulated gross revenue over the last 12 months.
*   **Alíquota (Nominal):** The base percentage rate applied to a specific revenue bracket (Faixa).
*   **Valor a Deduzir:** A fixed value subtracted from the tax calculation formula to determine the effective tax rate.
*   **Percentuais de Distribuição:** The internal breakdown of the total tax paid, distributed among different taxes (IRPJ, CSLL, COFINS, PIS, CPP, ICMS, ISS, IPI) based on the current Faixa.
*   **NFe (Nota Fiscal Eletrônica):** The digital document representing a commercial transaction in Brazil.
*   **CTe (Conhecimento de Transporte Eletrônico):** The digital document representing a freight/transport service transaction in Brazil.
*   **Distribuição DF-e (DistDFe):** SEFAZ service used to retrieve authorized documents and events by incremental NSU.
*   **NSU:** "Número Sequencial Único" used by SEFAZ distribution services to paginate and checkpoint retrieval of documents/events.
*   **docZip:** Base64-encoded, GZip-compressed XML payload returned by SEFAZ distribution services within `retDistDFeInt`.
*   **Manifestação do destinatário:** SEFAZ events for recipient acknowledgement (ex.: "Ciência da Operação" 210210) usually tied to a document key (chave).

### System Entities

*   **Issuer (Empresa):** The entity or company being operated within the system. In the UI, this is managed via `IssuerResource`. It serves as the primary scope for fiscal data.
*   **Tenant (Assinante):** The top-level organization or client account. In this multi-tenant architecture, most resources are filtered by `tenant_id` to ensure data isolation.
*   **Category Tag (Categoria de Etiqueta):** A classification system used to group "Tags". These are used for filtering, reporting, and applying business rules within the administration panel.

## System & Architecture Terms

Fiscaut is built on the **TALL Stack** (Tailwind, Alpine.js, Laravel, Livewire) and utilizes **FilamentPHP**.

### Core Abstractions

*   **Resource:** A Filament-specific concept that bundles the Eloquent model with the UI logic for CRUD (Create, Read, Update, Delete) operations (e.g., `vendor/filament/schemas/resources/js`).
*   **Schema:** Definitions that describe the structure and behavior of UI components, forms, and tables. 
    *   Key exports include `tabsSchemaComponent` and `wizardSchemaComponent`.
*   **Action:** Logic-driven buttons or triggers that perform specific tasks, such as running a fiscal calculation or sending a notification. Managed by the `Action` and `ActionGroup` classes.
*   **Notification:** A real-time feedback system (managed via the `Notification` class) that informs users of process completions, errors, or alerts.

### UI Components

*   **Forms:** Components used for data entry.
    *   `selectFormComponent`: Handles dropdown selections.
    *   `richEditorFormComponent`: Provides WYSIWYG capabilities for documentation or notes.
    *   `tagsInputFormComponent`: Manages collections of tags.
*   **Tables:** Components for data listing and management.
    *   `filamentTableColumnManager`: Handles visibility and ordering of columns.
    *   `toggleTableColumn`: Provides a quick boolean switch within a list view.
*   **Widgets:** Dashboard elements used to display KPIs.
    *   `statsOverviewStatChart`: Used for displaying trends (e.g., revenue growth).

## Personas & Actors

*   **Administrator:** A superuser with unrestricted access to system configurations, tenant management, and global fiscal rules.
*   **Fiscal Analyst:** A domain expert responsible for managing CFOPs, tax rules, and validating fiscal documents. They handle the day-to-day accuracy of the tax engine.
*   **Viewer:** A restricted role with read-only access, typically used for auditing or generating reports without the ability to modify data.

## Domain Rules & Invariants

To ensure data integrity, the following rules are enforced throughout the application:

1.  **CFOP Integrity:** Every CFOP code must strictly follow the 4-digit regulatory format.
2.  **Simples Nacional Continuity:** Within the same "Anexo," revenue brackets ("Faixas") must be disjoint (no overlaps). The condition `faixa_initial <= faixa_final` must always be true.
3.  **Tenant Isolation:** No data associated with a `tenant_id` should ever be accessible to a user belonging to a different tenant, regardless of their role. This is enforced at the query level.
4.  **Fiscal Uniqueness:** Certain fiscal identifiers (like NFe numbers for a specific Issuer) must be unique per operation type to prevent duplicate tax filings.

## Acronyms & Abbreviations

| Acronym | Definition |
| :--- | :--- |
| **API** | Application Programming Interface |
| **CRUD** | Create, Read, Update, Delete |
| **MVC** | Model-View-Controller |
| **ORM** | Object-Relational Mapping (Eloquent) |
| **SPA** | Single Page Application (Simulated via Livewire/Filament) |
| **TALL** | Tailwind, Alpine.js, Laravel, Livewire |
| **UI/UX** | User Interface / User Experience |

## Cross-References

*   **Technical Implementation:** See `docs/project-overview.md` for architectural details.
*   **Data Models:** Refer to the Eloquent models located in `app/Models`.
*   **Frontend Logic:** See the resources in `public/js/filament/` and `vendor/filament/`.
*   **Authorization:** See Laravel Policies (e.g., `CfopPolicy`) for permission logic.
