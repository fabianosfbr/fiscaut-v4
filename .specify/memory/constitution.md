# Fiscaut Constitution

## Core Principles

### I. Code Quality (NON-NEGOTIABLE)

All code MUST comply with **PSR-12** coding standard. Type hints MUST be declared on all parameters and return types wherever PHP allows. Developers MUST run Laravel Pint before every commit:

```bash
./vendor/bin/sail bin pint
```

Rationale: Fiscaut is a commercial fiscal management platform. Consistent formatting and type safety reduce bugs in tax calculation, document generation, and SEFAZ integration pipelines. Custom logic MUST be kept modular, placed in service classes or domain methods rather than embedded in Blade templates or JavaScript assets.

---

### II. Testing Standards (NON-NEGOTIABLE)

The project employs a **multi-layered** testing strategy:

1. **Feature tests** (`tests/Feature`) are the primary automated validation for integrated workflows covering Filament Resources, Livewire state transitions, form validation, and authorization policies. Developers MUST use `Pest\Livewire\livewire()` helpers to assert Livewire component state (`assertHasFormErrors`, `assertSet`, `assertDatabaseHas`).

2. **Unit tests** (`tests/Unit`) cover isolated business logic: tax calculations, CFOP/CNAE mapping, XML parsing, and formatter utilities. Unit tests MUST NOT require a database connection unless the Eloquent model is the subject under test.

3. **Manual validation** inside the Filament Admin panel is a **mandatory gate** until CI is fully standardized. Every PR MUST include documented manual validation steps covering CRUD integrity, notification behavior, unsaved-changes protection, and role/permission sanity checks.

Rationale: The TALL stack (Laravel + Filament + Livewire + Alpine) mixes server-side state with client-side interactivity. State-based assertions on Livewire components catch regressions that HTML-scraping tests miss.

---

### III. User Experience Consistency (NON-NEGOTIABLE)

All admin UI implementations MUST follow these rules:

- **Filament-first**: Admin screens MUST be implemented as Filament Resources, Pages, or RelationManagers. Custom controllers are prohibited unless no Filament equivalent exists.
- **Notifications**: User feedback MUST use `Filament\Notifications\Notification`. Native `session()->flash()` or raw alerts MUST NOT appear in admin flows.
- **Responsive validation**: All custom form components and interactive columns MUST be tested on mobile breakpoints. The Filament UI is responsive by default; customizations MUST NOT break mobile layouts.
- **Consistent component reuse**: Before adding a new JS component in `public/js/filament/`, developers MUST verify no equivalent exists in `public/js/filament/forms/components`, `public/js/filament/widgets/components`, or `public/js/filament/tables/components/columns`.

Rationale: Users interact with Fiscaut exclusively through the Filament Admin panel. Consistent UX reduces training overhead and prevents data-entry errors in fiscal documents.

---

### IV. Performance Requirements (REQUIRED)

The system MUST meet these performance criteria:

- **Async-first for I/O-heavy work**: SEFAZ downloads, SIEG API calls, XML ingestion, and bulk imports MUST be implemented as queued Jobs (Redis/Horizon). Synchronous processing is prohibited for operations that touch external APIs or process files larger than 1 MB.
- **Database query efficiency**: All Filament Resource tables that display more than 500 records MUST define appropriate query scopes and pagination. N+1 queries are prohibited; use `with()` or `lazyLoad()` on relationships.
- **Queue throughput**: Background jobs MUST be idempotent and handle failures gracefully with retry logic. Dead-letter jobs MUST be logged and monitored.
- **Response time**: Synchronous HTTP responses for admin page loads MUST complete within 500 ms (p95) on the local Sail environment.

Rationale: Fiscaut processes high-volume fiscal document streams (NF-e, CT-e, events). Without async pipelines and query discipline, the admin panel becomes unusable under production load.

---

### V. Security & Data Integrity

The following are non-negotiable:

- **Multi-tenancy enforcement**: All Eloquent models MUST apply tenant/issuer global scopes. Queries that bypass scopes (e.g., `withoutGlobalScopes`) MUST be justified in a comment and reviewed.
- **Authorization**: Every Filament Resource action MUST have an explicit policy or ability check. Siloed data access between tenants is a legal and commercial requirement.
- **No secrets in code**: API keys, SEFAZ credentials, and database passwords MUST be injected via environment variables. `.env` MUST NOT be committed.
- **XML document audit trail**: Raw XML payloads MUST be stored in log tables (`LogSefazNfeContent`, `LogSefazCteContent`) before any processing. This provides a non-repudiation record for fiscal audits.

Rationale: Fiscaut manages confidential tax data subject to LGPD compliance. Multi-tenant data leakage or loss of audit trails creates legal liability.

---

## Quality Gates

All Pull Requests targeting `develop` MUST pass the following gates before merge:

| Gate | Tool | Command |
|------|------|---------|
| Formatting | Laravel Pint | `./vendor/bin/sail bin pint` |
| Static analysis | PHPStan | `./vendor/bin/sail bin phpstan` |
| Test suite | Pest/PHPUnit | `./vendor/bin/sail artisan test` |
| Manual validation | Reviewer checklist | Filament Admin UI walkthrough |

> Until CI pipelines are active, the author MUST document manual validation steps in the PR description and the reviewer MUST confirm completion.

---

## Architecture Constraints

- All database schema changes MUST go through Laravel migrations. Direct schema modifications are prohibited.
- Business logic MUST reside in service classes or model methods. Logic MUST NOT be placed in Blade templates, Filament Vue/Alpine assets, or anonymous route closures.
- New Filament Resources MUST be registered in the appropriate panel provider and include a policy class.
- External integrations (SEFAZ, SIEG) MUST be implemented as service classes with a corresponding Job for async dispatch.

---

## Development Workflow

The project follows a **Feature Branch** workflow: `feature/*` → `develop` → `main`. Squash-and-merge is the preferred strategy for keeping history readable.

- Direct commits to `main` or `develop` are prohibited.
- Feature branches MUST be small and focused (one feature or task per branch).
- `./vendor/bin/sail` commands are the standard development interface (Laravel Sail / Docker).

See [Development Workflow](./.context/docs/development-workflow.md) and [Testing Strategy](./.context/docs/testing-strategy.md) for full operational guidance.

---

## Governance

This Constitution supersedes all other development practices within the project. Amendments require:

1. A documented proposal with rationale and impact analysis.
2. Review and approval from at least one senior maintainer.
3. An updated version bump following semantic versioning rules:
   - **MAJOR**: Backward-incompatible removals or redefinitions of existing principles.
   - **MINOR**: New principles added or materially expanded guidance.
   - **PATCH**: Clarifications, wording fixes, non-semantic refinements.
4. Propagation of changes to affected templates (plan-template.md, spec-template.md, tasks-template.md) documented in the Sync Impact Report.
5. A migration or adoption plan for any principle that requires behavioral changes in active work.

All PRs and code reviews MUST verify compliance with the principles in this document. Complexity that violates a principle MUST be explicitly justified in the PR and tracked in the implementation plan.

**Version**: 1.1.0 | **Ratified**: 2026-01-23 | **Last Amended**: 2026-03-20
