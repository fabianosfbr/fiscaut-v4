# Tooling & Productivity Guide (Fiscaut v4.1)

This document describes the tooling, scripts, and conventions used to develop **Fiscaut v4.1**. Following these standards helps keep environments consistent and improves code quality and developer productivity.

---

## Tech Stack Overview

- **Backend:** Laravel v12
- **Admin Panel:** FilamentPHP v3+ (project tracks Filament “v5 branch”)
- **Frontend Runtime:** Livewire v3+ (project tracks Livewire “v4 branch”) + Alpine.js
- **Styling:** Tailwind CSS
- **Local Dev Environment:** Laravel Sail (Docker)

---

## Core Development Environment

### Laravel Sail (Docker)

Fiscaut uses **Laravel Sail** to standardize PHP/MySQL/Redis versions across the team.

Common commands:

```bash
# Start containers (detached)
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail stop

# Run Artisan commands
./vendor/bin/sail artisan [command]

# Run Composer commands
./vendor/bin/sail composer [command]
```

#### Quality-of-life alias

Add a `sail` alias to your shell profile (`~/.zshrc`, `~/.bashrc`):

```bash
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```

With that in place, you can run:

```bash
sail up -d
sail artisan migrate
sail composer install
```

---

## Asset Management

### PHP dependencies (Composer)

Install/update backend dependencies via Sail:

```bash
sail composer install
sail composer update
```

### JavaScript dependencies (NPM)

Install and build frontend assets via Sail:

```bash
sail npm install
sail npm run dev
```

### Filament frontend assets (where they live)

This project uses Filament’s JS components along with locally stored/customized JS.

Key locations:

- Local Filament-related JS: `public/js/filament/`
- Vendor Filament packages: `vendor/filament/*`

> Tip: When debugging UI behavior, always confirm whether the code is coming from `vendor/` (package) or `public/js/filament/` (local).

---

## Code Quality & Automation

### Laravel Pint (code style)

Pint enforces consistent PHP formatting.

```bash
# Fix style issues
sail bin pint

# Check only (do not modify files)
sail bin pint --test
```

When to run:
- Before pushing a branch
- Before opening a PR
- After large refactors

### Larastan / PHPStan (static analysis)

Larastan (PHPStan for Laravel) helps detect type issues and potential bugs early.

```bash
sail bin phpstan analyse
```

Recommended workflow:
- Run after introducing new service classes, complex queries, or refactoring domain logic.
- Treat new warnings as blockers unless explicitly justified.

### IDE Helper (autocomplete, model metadata)

To improve IDE completion and Laravel “magic” visibility:

```bash
sail artisan ide-helper:generate
sail artisan ide-helper:models -N
sail artisan ide-helper:meta
```

Run after:
- Creating/updating Models
- Adding relations, scopes, accessors, casts
- Adding new macros or helper functions

---

## Filament & Frontend Tooling

Fiscaut’s UI is primarily Filament + Livewire, augmented by Alpine.js and richer JS components.

### Component architecture (paths)

The repository contains both vendor-provided and local component code.

| Component Type | Path |
| --- | --- |
| Schemas (vendor) | `vendor/filament/schemas/resources/js/components` |
| Forms/Tables (vendor) | `vendor/filament/forms/resources/js/components` |
| Widgets (local) | `public/js/filament/widgets/components` |
| Custom Table Columns (local) | `public/js/filament/tables/components/columns` |

Local “schemas” and “components” are also present under:

- `public/js/filament/schemas`
- `public/js/filament/schemas/components`
- `public/js/filament/forms/components`
- `public/js/filament/tables/components/columns`

These directories typically contain built/bundled JavaScript artifacts used by the Filament UI at runtime.

### Notable internal JS entry points (local)

The codebase includes several locally stored Filament component modules (often minified/bundled). Examples include:

- `public/js/filament/schemas/components/wizard.js`
- `public/js/filament/schemas/components/tabs.js`
- `public/js/filament/forms/components/textarea.js`
- `public/js/filament/forms/components/tags-input.js`
- `public/js/filament/forms/components/rich-editor.js`
- `public/js/filament/forms/components/key-value.js`
- `public/js/filament/forms/components/checkbox-list.js`
- `public/js/filament/tables/components/columns/toggle.js`
- `public/js/filament/tables/components/columns/text-input.js`
- `public/js/filament/tables/components/columns/checkbox.js`

> Practical note: These files may not be designed for hand-editing (they can be compiled artifacts). Prefer modifying the source that produces them (if present in your build pipeline) or overriding behavior via Filament/Livewire configuration where possible.

### Useful vendor APIs & utilities (when extending UI)

If you’re writing custom Alpine logic or bridging Alpine ↔ Livewire behavior, these vendor utilities are commonly useful:

- **Notifications**
  - `vendor/filament/notifications/resources/js/Notification.js`
  - Use it to trigger UI notifications/alerts from JS.

- **DOM / Livewire bridge utilities**
  - `findClosestLivewireComponent(el)` in:
    - `vendor/filament/support/resources/js/partials.js`
  - Helpful when you have a DOM node and need the associated Livewire component instance.

- **Form / UI utilities**
  - Select utility:
    - `vendor/filament/support/resources/js/utilities/select.js`
  - Rich editor handlers:
    - `vendor/filament/forms/resources/js/components/rich-editor.js`

---

## Testing & Debugging Tools

### Livewire event testing (Fake Echo)

For tests that involve broadcasting/events, Livewire provides fake Echo/channel implementations:

- `vendor/livewire/livewire/src/Features/SupportEvents/fake-echo.js`

Includes utilities such as:
- `FakeEcho`
- `FakeChannel`
- `FakePresenceChannel`

Use these to mock and assert event behavior without needing a real websocket backend.

### Error handling UI (Whoops)

Interactive error pages and stack traces are powered by Whoops:

- `vendor/filp/whoops/src/Whoops/Resources/js/whoops.base.js`

### Syntax highlighting (Shiki)

Syntax highlighting is provided via Shiki PHP:

- `vendor/spatie/shiki-php/bin/shiki.js`

If highlighting behaves unexpectedly (missing theme, failing binary), this is a good starting point for investigation.

---

## IDE Setup (Recommended)

### VS Code extensions

Recommended baseline for a productive setup:

1. **PHP Intelephense** – PHP indexing, completion, navigation
2. **Laravel Blade Snippets** – Blade snippets + syntax support
3. **Tailwind CSS IntelliSense** – class autocomplete + linting
4. **Laravel Artisan** – run Artisan from command palette
5. **EditorConfig** – enforce `.editorconfig` rules

### Editor conventions

- **Indentation**
  - PHP: 4 spaces
  - JS/CSS: 2 spaces
- **Line endings:** LF
- **Encoding:** UTF-8

---

## Productivity Workflows

### Artisan Tinker

Use Tinker for quickly testing queries, policies, casts, helpers, etc.:

```bash
sail artisan tinker
```

### Log monitoring

Tail logs while reproducing bugs or validating Livewire requests:

```bash
sail artisan tail
```

### Database access (GUI clients)

Sail provides a database container; a GUI client is often faster for exploration:

- Recommended clients: **TablePlus**, **DBeaver**
- Typical connection values:
  - Host: `127.0.0.1`
  - Port: `3306` (or whatever is defined in `.env`)
  - User/Pass: `sail` / `password`

> If your `.env` maps DB ports differently, always prefer the local port defined in Docker/Sail config.

---

## Cross-References

- [Development Workflow](./development-workflow.md) — branching, PRs, and team standards
- [Architecture Overview](./architecture.md) — high-level structure of Fiscaut v4.1
