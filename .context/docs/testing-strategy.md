# Testing Strategy (fiscaut-v4.1)

This document defines the quality assurance framework and testing protocols for **fiscaut-v4.1**. The application is built on **Laravel v12**, **FilamentPHP v5**, and **Livewire v4**, so testing must cover both traditional HTTP flows and Livewire-driven component state inside the Filament Admin panel.

> [!IMPORTANT]
> The automated testing environment is currently being standardized. Until CI/CD pipelines are finalized, **manual validation within the Filament Admin panel is the primary requirement** for all new features and bug fixes.

---

## Goals

- Ensure **admin workflows** (Filament Resources, Pages, Actions) behave correctly end-to-end.
- Prevent regressions in **Livewire component state**, validation, and multi-step schemas (e.g., Wizards/Tabs).
- Maintain confidence in **data integrity** (CRUD, business rules, permissions).
- Verify **UI/UX safety nets**, especially navigation guards and notifications.

---

## Testing Philosophy

We use a **multi-layered** strategy:

1. **Feature tests** as the primary automated validation for integrated workflows (HTTP + Livewire/Filament).
2. **Unit tests** for isolated logic and pure utilities.
3. **Browser/interaction tests** for JavaScript-heavy or editor-like components that are hard to validate by state assertions alone.
4. **Manual validation** as the current required gate until CI is fully standardized.

This mirrors how users interact with the system: through Filament screens backed by Livewire state, not just static HTML.

---

## Testing Layers

### 1) Feature Tests (`tests/Feature`) — Primary

Feature tests validate complete behaviors: routes, middleware, policies, database interactions, and Livewire state transitions inside Filament pages/resources.

**Scope**
- CRUD operations through Filament Resources
- Resource actions (header/table actions)
- Form validation and error handling
- Multi-step flows (e.g., Wizards/Tabs)
- Table behaviors and column interactions

**Focus**
- Filament schemas render and process data correctly
- Livewire state mutations are correct (`data.*` structure, form state, actions)
- Authorization is enforced (who can see/do what)

**Tooling**
- Pest or PHPUnit (project supports either; examples below use Pest + Livewire helpers)

#### Example: Render a Filament Resource page

```php
use App\Filament\Resources\UserResource;

it('can render the user list page', function () {
    $this->get(UserResource::getUrl('index'))->assertSuccessful();
});
```

#### Example: Validate a Filament Create page (Livewire state)

```php
use App\Filament\Resources\UserResource;
use function Pest\Livewire\livewire;

it('validates user creation fields', function () {
    livewire(UserResource\Pages\CreateUser::class)
        ->fillForm([
            'name' => '',             // required
            'email' => 'not-an-email' // invalid
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'email']);
});
```

#### Recommended patterns for Filament/Livewire feature tests

- Prefer **state assertions** over HTML assertions:
  - `assertSet('data.field', 'value')`
  - `assertHasErrors(['data.field' => 'required'])`
- Verify actions:
  - `assertActionExecuted()` for Filament header/table actions (where applicable)
- Keep tests user-centered:
  - “A user with role X can do Y”
  - “A user without permission cannot see/execute Z”

---

### 2) Unit Tests (`tests/Unit`) — Isolated Logic

Unit tests target pure logic that should not require full HTTP stack, Livewire, or database state (when possible).

**Scope**
- Business rules implemented as pure functions/classes
- Calculations, formatting, mapping, parsing
- Helper functions and utilities

**Focus**
- Deterministic behavior
- Fast execution
- Clear, small test cases

**Notes on front-end utilities**
Some UI behavior depends on JavaScript utilities (notably within Filament’s ecosystem). When the project includes custom JS logic (or relies on certain behaviors), consider unit-testing JS utilities separately (if a JS test runner is added later). For now, treat them as integration concerns verified by browser/manual checks.

---

### 3) Browser & Interaction Tests — Complex UI

Use browser-level testing for interactions that are difficult to reliably validate via Livewire state alone, especially when JavaScript controls the UX.

**Typical targets**
- Rich text editors and code editors (selection, toolbars, serialization)
- Complex `Select` behaviors (async searching, keyboard navigation)
- Multi-step schemas (Wizard/Tabs) with client-side transitions
- Navigation guards (unsaved changes alerts)
- Toast notifications timing/visibility

**When to choose browser tests**
- The bug involves DOM behavior, focus management, scrolling, drag-and-drop, or editor toolbars.
- State assertions pass but the UI still breaks (or vice versa).

> If browser automation is not yet wired (e.g., Dusk/Playwright), capture required steps in the **Manual Validation Checklist** (below) and add automated coverage later.

---

## Infrastructure & Configuration

### Database management

Use a clean database state per test to avoid cross-test pollution.

- Prefer Laravel’s `RefreshDatabase` trait (common default for Feature tests)
- For unit tests, avoid DB unless required

General expectations:
1. Each test starts from a known schema state.
2. Data mutations do not leak across tests.

### Environment setup

Create a `.env.testing` in the repository root.

**Recommended**
- SQLite in-memory for speed when compatible
- Dedicated MySQL/PostgreSQL for parity if SQLite differences affect behavior

Run the suite:

```bash
php artisan test
```

---

## Quality Gates

### Current Gate: Manual validation (required)

Before merging, developers must validate the change in the Filament Admin UI.

#### Manual Validation Checklist

1. **CRUD Integrity**
   - Create, view, edit, delete affected records via Filament screens
   - Verify validation messages and required fields behave as expected

2. **Notifications**
   - Confirm Filament notifications appear at the correct time with correct content
   - Validate success/error branches (e.g., action failure shows error notification)

3. **Unsaved Changes Protection**
   - Confirm the “unsaved changes” alert triggers when navigating away from a dirty form
   - Confirm it does **not** trigger when the form is clean or after a successful save

4. **Responsiveness**
   - Validate key pages and tables on mobile breakpoints
   - Ensure interactive columns (e.g., toggle/text input style interactions) behave correctly

5. **Role/Permission sanity checks**
   - Validate the minimum expected roles can access screens/actions
   - Validate restricted roles cannot access or execute protected actions

Document any manual steps in the PR description for reviewer reproducibility.

---

### Upcoming Gate: Automation (CI/CD)

Once CI is active, expect enforcement of:

- **Test suite must pass** (Pest/PHPUnit)
- **Static analysis** (e.g., PHPStan/Larastan) with no high-severity issues
- **Style/lint compliance** per project rules

Until that point, treat manual validation as non-negotiable and prioritize adding feature tests for regression-prone areas.

---

## Advanced Testing Techniques

### Using fakes to avoid side effects

Use Laravel/Livewire fakes to keep tests fast and deterministic:

- **Notifications**
  - `Notification::fake()` to assert notifications without rendering UI
- **Storage**
  - `Storage::fake()` to test uploads without writing to disk
- **Events/WebSockets (when applicable)**
  - Use fake channels/echo implementations to simulate events without a running server

These techniques reduce flakiness and improve test performance.

---

## Livewire/Filament Testing Guidelines (Practical)

- Prefer **testing outcomes** (state, DB, authorization) over internal implementation.
- In Filament Create/Edit pages:
  - use `fillForm()` to populate fields
  - use `call('create')` / `call('save')` to trigger standard flows
  - assert:
    - validation errors (`assertHasFormErrors`)
    - state (`assertSet`)
    - DB changes (`assertDatabaseHas`, `assertDatabaseMissing`)
- For actions:
  - verify action execution and side effects (DB, notifications, redirects)

---

## Related Documentation

- [Development Workflow](./development-workflow.md)
- [FilamentPHP Documentation](https://filamentphp.com/docs)
- [Livewire Testing Guide](https://livewire.laravel.com/docs/testing)

--- 

## Suggested Next Steps (for the repository)

- Standardize a baseline test harness:
  - Ensure `.env.testing` defaults are documented and committed as `.env.testing.example`
  - Decide on Pest vs PHPUnit conventions and apply consistently
- Add a small set of “smoke” feature tests for critical admin workflows:
  - authentication + access control
  - one representative resource CRUD
  - one multi-step form flow (wizard/tabs)
- Introduce browser automation once CI is stable for editor-heavy and JS-driven interactions
