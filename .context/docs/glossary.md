# Glossary (Fiscaut v4.1)

This glossary defines the core terminology, domain concepts, and technical abstractions used in **Fiscaut v4.1** (a Brazilian fiscal management system). Its goal is to keep language consistent across product, engineering, and operations—especially where Brazilian tax concepts meet implementation details.

---

## Brazilian fiscal & business terminology

### CFOP (Código Fiscal de Operações e Prestações)
A standardized **4-digit** code defined by the Brazilian government that identifies the nature of a transaction (movement of goods or provision of services).

- **Why it matters:** CFOP affects tax rules, reporting, and validation flows.
- **Validation rule:** Must follow regulatory patterns; stored/handled as a strict 4-digit identifier.
- **Implementation reference:** Typically managed via `CfopResource` (Filament Resource).

---

### Simples Nacional
A simplified tax regime for small and medium-sized enterprises (SMEs) in Brazil. Taxes are calculated based on gross revenue and activity category (Anexo), using bracket tables (Faixas).

---

### Anexo (Simples Nacional)
The activity category (e.g., commerce, industry, services) that determines which table of revenue brackets applies.

- Each **Anexo** has multiple **Faixas** (brackets).
- Each **Faixa** has an **Alíquota Nominal**, **Valor a Deduzir**, and **Percentuais de Distribuição**.

---

### Faixa (Simples Nacional)
A revenue bracket within an Anexo, based on accumulated **gross revenue over the last 12 months**.

- **Core invariant:** Within the same Anexo, Faixas must be **disjoint** (no overlaps).
- **Range constraint:** `faixa_initial <= faixa_final`.

---

### Alíquota (Nominal)
The base percentage rate associated with a given Faixa before deductions and effective rate calculation.

---

### Valor a Deduzir
A fixed amount subtracted during the Simples Nacional formula to compute the **effective** tax burden for the bracket.

---

### Percentuais de Distribuição
The internal breakdown of the total Simples Nacional tax amount across specific taxes, commonly including:

- IRPJ, CSLL, COFINS, PIS, CPP, ICMS, ISS, IPI

These percentages vary by Faixa within an Anexo.

---

### NFe (Nota Fiscal Eletrônica)
Electronic invoice representing a commercial transaction in Brazil, issued as an XML document and authorized by SEFAZ.

---

### CTe (Conhecimento de Transporte Eletrônico)
Electronic document representing a freight/transport service transaction.

---

### Distribuição DF-e (DistDFe)
A SEFAZ distribution service that allows retrieval of authorized fiscal documents and events incrementally.

- Typically queried via SEFAZ’s `retDistDFeInt` responses.
- Supports incremental pagination using **NSU**.

---

### NSU (Número Sequencial Único)
A sequential identifier used by SEFAZ distribution APIs for:

- Pagination of document retrieval
- Checkpointing “last processed” position

---

### docZip
A payload field returned by SEFAZ distribution services:

- Base64-encoded
- GZip-compressed
- Contains XML (document/event)

---

### Manifestação do destinatário
SEFAZ events by which the recipient acknowledges a document (linked to the document key / *chave*), e.g.:

- “Ciência da Operação” (event code **210210**)

---

## System entities (business objects)

### Issuer (Empresa)
The company/entity being operated on within the system. It is the primary scope for fiscal data (documents, configurations, rules).

- **UI management:** commonly via `IssuerResource` (Filament Resource).

---

### Tenant (Assinante)
The top-level organization/client account in a **multi-tenant** architecture.

- **Data isolation:** resources are typically filtered by `tenant_id`.

---

### Category Tag (Categoria de Etiqueta)
A classification entity used to group “Tags” for:

- filtering
- reporting
- applying business rules in the administration panel

---

## System & architecture terms

Fiscaut is built on the **TALL Stack** (Tailwind, Alpine.js, Laravel, Livewire) and uses **FilamentPHP** for the admin/product UI.

### Resource (Filament)
A Filament concept that binds:

- an Eloquent model
- CRUD pages (list/create/edit/view)
- forms and tables
- actions, filters, and authorization boundaries

Resources centralize how a domain model is managed in the UI.

---

### Schema
A definition describing the structure and behavior of UI components (forms, tables, widgets).

- In this codebase, schemas are present under `public/js/filament/schemas` and `public/js/filament/schemas/components`.
- Common schema component patterns include **tabs** and **wizard** layouts (e.g., exports like `tabsSchemaComponent` / `wizardSchemaComponent` in the Filament JS bundle).

---

### Action / ActionGroup
UI-triggered operations tied to business logic (e.g., run a fiscal calculation, sync documents, send notifications).

- **ActionGroup** composes multiple actions into a grouped UI control.

---

### Notification
A feedback mechanism shown to the user for:

- success/failure messages
- process completion
- warnings and validation hints

---

## UI component glossary (Filament front-end bundles)

> The following terms map to Filament UI building blocks and are often represented in the repository under:
>
> - `public/js/filament/forms/components`
> - `public/js/filament/tables/components/columns`
> - `public/js/filament/widgets/components`
> - `public/js/filament/schemas/components`

### Forms
Components used for data input:

- **Select**: dropdown selection controls (commonly referenced as `selectFormComponent` conceptually).
- **Textarea**: multi-line text input (`public/js/filament/forms/components/textarea.js`).
- **Tags Input**: manages a set/collection of tags (`public/js/filament/forms/components/tags-input.js`).
- **Rich Editor**: WYSIWYG editing (`public/js/filament/forms/components/rich-editor.js`).
- **Key-Value**: editor for structured pairs (`public/js/filament/forms/components/key-value.js`).
- **Checkbox List**: list-based multi-select (`public/js/filament/forms/components/checkbox-list.js`).

---

### Tables
Components for listing and managing datasets:

- **Toggle Column**: inline boolean switch (`public/js/filament/tables/components/columns/toggle.js`).
- **Text Input Column**: editable table cell input (`public/js/filament/tables/components/columns/text-input.js`).
- **Checkbox Column**: boolean display/selection (`public/js/filament/tables/components/columns/checkbox.js`).

---

### Widgets
Dashboard/KPI components:

- **Stats Overview / Stat Chart**: small KPI cards and trends (see `public/js/filament/widgets/components/stats-overview/stat`).

---

## Personas & roles

### Administrator
Superuser with global access:

- tenant management
- system configuration
- global fiscal rules

---

### Fiscal Analyst
Domain specialist responsible for:

- CFOP maintenance
- tax rules
- validating fiscal documents and operational accuracy

---

### Viewer
Read-only role used for:

- audits
- reporting
- monitoring without modification privileges

---

## Domain rules & invariants (data integrity)

These are non-negotiable constraints enforced across the application:

1. **CFOP integrity**  
   CFOP must be a strictly valid 4-digit code according to regulatory formatting rules.

2. **Simples Nacional continuity**  
   Within a single Anexo, Faixas must not overlap; ranges must be valid (`faixa_initial <= faixa_final`).

3. **Tenant isolation**  
   Data from one `tenant_id` must never be accessible to users from another tenant. This is expected to be enforced at query boundaries and in authorization logic.

4. **Fiscal uniqueness**  
   Certain fiscal identifiers (e.g., NFe number under a specific Issuer and operation type) must be unique to avoid duplicate filings and inconsistent reporting.

---

## Acronyms & abbreviations

| Acronym | Meaning |
| --- | --- |
| API | Application Programming Interface |
| CRUD | Create, Read, Update, Delete |
| MVC | Model-View-Controller |
| ORM | Object-Relational Mapping (Eloquent) |
| SPA | Single Page Application (simulated via Livewire/Filament) |
| TALL | Tailwind, Alpine.js, Laravel, Livewire |
| UI/UX | User Interface / User Experience |

---

## Cross-references

- **Architecture overview:** `docs/project-overview.md`
- **Data models:** `app/Models`
- **Frontend/Filament bundles:** `public/js/filament/` (and vendor Filament assets where applicable)
- **Authorization & policies:** Laravel Policies (e.g., `CfopPolicy`)
