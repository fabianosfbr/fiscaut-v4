# QA & Developer Q&A (Fiscaut-v4.1)

Central documentation hub for **Fiscaut-v4.1**. This section helps developers and QA engineers quickly understand the system structure, where UI behavior lives, and what to prioritize when testing.

Fiscaut-v4.1 is a web API + admin platform built on **Laravel**, using **Filament v3** for the admin UI and **Livewire** for reactive, server-synchronized interactions.

---

## Getting Started

Before contributing or running QA, align your local environment with production expectations:

- **Setup Guide**: [`./getting-started.md`](./getting-started.md) — environment requirements, PHP/Node dependencies, and how to run locally.
- Ensure you have:
  - **Composer**
  - **Node/NPM**
- Frontend/admin assets are primarily shipped/served from: `public/js/filament/`

---

## Project Architecture Overview

The application splits responsibilities between:

- **Laravel backend**: routing, auth, middleware, database (Eloquent), policies/permissions.
- **Filament + Livewire frontend**: admin screens, forms/tables/widgets, reactive behavior without building separate REST endpoints for most UI actions.

### Directory & Component Mapping

| Category | Primary Locations | What you’ll find there |
| --- | --- | --- |
| **Models / Schemas** | `public/js/filament/schemas` and `public/js/filament/schemas/components` | Client-side schema components used by Filament for complex UIs (e.g., wizards, tabs). |
| **Form Components** | `public/js/filament/forms/components` | Interactive form inputs (e.g., `RichEditor`, `Textarea`, `TagsInput`, `KeyValue`, `CheckboxList`). |
| **Table Columns** | `public/js/filament/tables/components/columns` | Column renderers and inline-edit components (e.g., `ToggleColumn`, `TextInputColumn`, `CheckboxColumn`). |
| **Widgets** | `public/js/filament/widgets/components` | Dashboard widgets and parts like Stats Overview. |
| **Utilities (vendor)** | `vendor/filament/support/resources/js/utilities` | Shared helpers used across Filament JS (DOM helpers, selection utilities, etc.). |

### Reactivity Model (Livewire)

Most “dynamic” admin behavior is Livewire-driven. While debugging, keep an eye on:

- **Linking DOM → Livewire component**
  - `findClosestLivewireComponent` (commonly used to bind events to the nearest component context)
- **Event-based communication**
  - `Livewire.dispatch(...)` (common pattern for notifying other components / backend state)

---

## Key Dependencies (What matters for QA & debugging)

- **Laravel Core**: request lifecycle, middleware, policies, queue/jobs, DB.
- **Filament v3**: admin resources, forms, tables, widgets, panels.
- **Livewire**: state synchronization, server-side validation, event lifecycle.
- **Shiki**: syntax highlighting in certain views (if present, verify rendering + theme consistency).

### Useful Internal APIs & Utilities

- **Select Utility**
  - Location: `vendor/filament/support/resources/js/utilities/select.js`
  - Use: complex dropdown behavior consistency (keyboard navigation, searching, etc.)

- **Notification System**
  - Location: `vendor/filament/notifications/resources/js/Notification.js`
  - Use: triggering UI alerts/toasts from JavaScript (or via Filament abstractions)

---

## QA Focus Areas (High-impact test coverage)

### 1) Form Validation & Persistence

Validate that Filament form schemas enforce constraints and that data persists correctly.

Checklist:
- **RichEditor**
  - Content sanitization/escaping
  - Persistence of formatted content
  - Copy/paste behavior
- **Wizard**
  - Step-to-step state retention
  - Validation per step vs. on final submit
  - Back/forward navigation behavior
- **File uploads (if enabled in resources)**
  - Upload failure states and error messaging
  - File type/size enforcement
  - Temporary upload cleanup (where applicable)

Related areas:
- `public/js/filament/forms/components/`
- `public/js/filament/schemas/components/` (wizard/tabs behavior)

---

### 2) Livewire Reactivity & State Sync

Confirm the UI remains synchronized with server-side state.

Checklist:
- Component updates after server-side changes (no stale UI)
- Loading states/disabled states behave correctly under slow network
- Events trigger expected server-side actions and update the right component

Targets:
- **Stats Overview widgets**
  - Ensure stats refresh when underlying data changes
- **Unsaved changes protection**
  - Validate navigation prevention and prompt behavior when forms are “dirty”

Reference:
- Unsaved changes logic is centralized in:
  - `vendor/filament/filament/resources/js/unsaved-changes-alert.js`

---

### 3) Table Interactions (Filament Tables)

Tables are critical admin workflows; focus on inline actions and bulk operations.

Checklist:
- **Bulk actions**
  - Selection model correctness (select page vs. select all)
  - Permissions respected (actions hidden/disabled when unauthorized)
- **ToggleColumn**
  - Immediate persistence behavior (AJAX/Livewire action)
  - Rollback/feedback on failure
- **TextInputColumn**
  - Validation before save
  - Correct formatting/parsing (numbers, decimals, locale if applicable)

Related code:
- `public/js/filament/tables/components/columns/`

---

### 4) Permissions & Security

Confirm unauthorized users cannot access or mutate protected resources.

Checklist:
- Backend enforcement via middleware and policies (UI hiding is not enough)
- Filament resource visibility matches role/policy rules
- Attempt direct route access and action calls as a low-privilege user

Reference:
- Middleware: `app/Http/Middleware`
- Filament policies: typically in `app/Policies` (and resource registration locations)

---

## Common Developer Q&A

### Where do I add custom JavaScript for a specific form field?

Add custom behavior under:

- `public/js/filament/forms/components`

Integrate using Filament’s/Livewire’s expected lifecycle hooks (commonly via Alpine component definitions and Livewire event hooks).

---

### How do I debug Livewire event listeners?

Practical steps:
- Use browser DevTools console to inspect:
  - `Livewire.on(...)` listeners
  - emitted/dispatched events
- Search the repository for the event name to find where it’s registered/handled.

Tip/reference example:
- `vendor/livewire/livewire/src/Features/SupportEvents/fake-echo.js` (useful to understand how events can be mocked/handled in tests)

---

### How is “Unsaved Changes” handled?

Centralized in:

- `vendor/filament/filament/resources/js/unsaved-changes-alert.js`

It monitors form state and blocks navigation when the form is dirty and changes haven’t been saved.

QA recommendations:
- Try browser back/forward navigation
- Try clicking sidebar links, breadcrumbs, and resource switches
- Verify behavior with both valid and invalid form states

---

## Related Documentation

- [`./getting-started.md`](./getting-started.md) — environment setup and running locally

---

_Last updated: 2026-01-23_
