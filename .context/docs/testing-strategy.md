# Testing Strategy

This document defines the quality assurance framework and testing protocols for **fiscaut-v4.1**, a project built on **Laravel v12**, **FilamentPHP v5**, and **Livewire v4**.

## Overview

Quality assurance follows a multi-layered approach. Because the application relies heavily on FilamentPHP for its administrative interface, the strategy prioritizes **Feature Tests** to validate integrated workflows, supported by **Unit Tests** for isolated business logic and **Manual Validation** for UI/UX integrity.

### Current Implementation Status
> **Critical**: The automated testing environment is currently being standardized. Until the CI/CD pipeline and local runner configurations are finalized, **manual validation within the Filament Admin panel is the primary requirement** for all new features and bug fixes.

---

## Testing Layers

### 1. Feature Tests (`tests/Feature`)
Feature tests are the primary method for verifying system integrity. They focus on HTTP endpoints and the state of Livewire components within Filament Resources.

*   **Scope**: CRUD operations, Filament Resource actions, form validation logic, and multi-step wizards.
*   **Focus**: Ensuring that Filament Schemas (Forms and Tables) render correctly and process data as expected.
*   **Tools**: [Pest PHP](https://pestphp.com/) or PHPUnit.

**Example: Testing a Filament Resource**
```php
use App\Filament\Resources\UserResource;
use function Pest\Livewire\livewire;

it('can render the user list page', function () {
    $this->get(UserResource::getUrl('index'))->assertSuccessful();
});

it('can validate user creation', function () {
    livewire(UserResource\Pages\CreateUser::class)
        ->fillForm([
            'name' => '', // Required field
            'email' => 'not-an-email',
        ])
        ->call('create')
        ->assertHasFormErrors(['name', 'email']);
});
```

### 2. Unit Tests (`tests/Unit`)
Unit tests focus on isolated logic that does not require the database or the Laravel service container.

*   **Scope**: Calculation logic, string manipulations, and internal helper functions.
*   **Focus**: Data integrity and utility reliability.
*   **Reference**: Utility functions in `vendor/filament/support` and custom logic in `app/Helpers`.

### 3. Browser & Interaction Tests
For complex UI interactions that Livewire's internal state testing cannot capture (e.g., JavaScript-driven components or Rich Editor behavior), the project utilizes browser-level testing.

*   **Target Components**: `RichEditor`, `CodeEditor`, `Wizard` schemas, and complex `Select` interactions.
*   **Tools**: Laravel Dusk (optional, used for critical UI paths).

---

## Infrastructure & Configuration

### Database Management
Tests utilize the `RefreshDatabase` trait to ensure a clean state:
1.  Each test runs against a fresh database schema.
2.  Data is wrapped in a database transaction and rolled back after each test to prevent state pollution.

### Environment Setup
A `.env.testing` file must be present in the root directory. It is recommended to use an in-memory SQLite database for speed, or a dedicated MySQL/PostgreSQL testing database for feature parity with production.

```bash
# Execute the test suite
php artisan test
```

---

## Quality Gates

### Manual Validation (Mandatory)
Before any feature is merged, developers must manually verify the following:

1.  **CRUD Integrity**: Verify creation, reading, updating, and deletion of records via the Filament UI.
2.  **Notification Flow**: Ensure that `Notification` classes (found in `vendor/filament/notifications`) trigger the correct UI toasts and responses.
3.  **Unsaved Changes**: Verify that the `unsaved-changes-alert.js` triggers correctly when attempting to navigate away from a "dirty" form.
4.  **Responsiveness**: Check that new Table Columns or Form Fields render correctly on mobile and desktop views.

### Automation Gate (Upcoming)
Once the CI/CD pipeline is active, the following requirements will be enforced for all Pull Requests:
*   **Zero Failures**: 100% pass rate for the PHPUnit/Pest suite.
*   **Static Analysis**: No high-level errors reported by PHPStan/Larastan.
*   **Code Style**: Adherence to project-specific linting and formatting rules.

---

## Best Practices & Troubleshooting

### Testing Livewire State
When testing Filament components, focus on the internal state rather than raw HTML output:
*   Use `assertSet('data.field', 'value')` to check form state.
*   Use `assertHasErrors(['data.field' => 'required'])` for validation.
*   Use `assertActionExecuted()` for Filament header or table actions.

### Utilizing Fakes
Leverage Laravel's built-in fakes to avoid side effects and speed up tests:
*   `Notification::fake()`: Intercept Filament notifications.
*   `Event::fake()`: Prevent real events from firing.
*   `Mail::fake()`: Intercept outgoing emails.

### Real-time & WebSocket Testing
For features involving real-time updates or events, refer to `FakeEcho` and `FakeChannel` located in `vendor/livewire/livewire/src/Features/SupportEvents/fake-echo.js`. These allow you to simulate WebSocket events without requiring a running Pusher/Soketi server.

## Related Documentation
- [Development Workflow](./development-workflow.md)
- [FilamentPHP Official Documentation](https://filamentphp.com/docs)
- [Livewire Testing Guide](https://livewire.laravel.com/docs/testing)
