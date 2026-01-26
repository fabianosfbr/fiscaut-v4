# Testing Strategy

This document outlines the quality assurance framework and testing protocols for the **fiscaut-v4.1** project. The application is built on **Laravel v12**, **FilamentPHP v5**, and **Livewire v4**, requiring a testing approach that emphasizes component state and administrative workflows.

## Testing Philosophy

Quality assurance in this project follows a multi-layered approach. Because the system relies heavily on FilamentPHP for its interface, we prioritize **Feature Tests** to validate integrated workflows, supported by **Unit Tests** for isolated logic and **Manual Validation** for UI/UX integrity.

### Implementation Status
> [!IMPORTANT]
> The automated testing environment is currently being standardized. Until CI/CD pipelines are finalized, **manual validation within the Filament Admin panel is the primary requirement** for all new features and bug fixes.

---

## Testing Layers

### 1. Feature Tests (`tests/Feature`)
Feature tests are the primary method for verifying system integrity. They focus on HTTP endpoints and the internal state of Livewire components within Filament Resources.

*   **Scope**: CRUD operations, Filament Resource actions, form validation, and multi-step wizards.
*   **Focus**: Ensuring Filament Schemas (Forms and Tables) render correctly and process data.
*   **Tools**: [Pest PHP](https://pestphp.com/) or PHPUnit.

#### Example: Testing a Filament Resource
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
*   **Key Utilities**: Functions in `vendor/filament/support/resources/js/utilities/` (like `Select`, `pluralize`, and `select.js`).

### 3. Browser & Interaction Tests
For complex UI interactions that Livewire state testing cannot capture (e.g., JavaScript-driven components or Rich Editor behavior), we utilize browser-level testing.

*   **Target Components**: `RichEditor`, `CodeEditor`, `Wizard` schemas, and complex `Select` interactions.
*   **Logic Verification**: Ensuring `unsaved-changes-alert.js` prevents accidental navigation and that `Notification` toasts appear correctly.

---

## Infrastructure & Configuration

### Database Management
Tests utilize the `RefreshDatabase` trait to ensure a clean state:
1.  Each test runs against a fresh database schema.
2.  Data is wrapped in a transaction and rolled back after each test to prevent state pollution.

### Environment Setup
A `.env.testing` file must be present in the root. We recommend an in-memory SQLite database for speed, or a dedicated MySQL/PostgreSQL instance for feature parity.

```bash
# Execute the test suite
php artisan test
```

---

## Quality Gates

### Manual Validation Checklist
Before merging, developers must manually verify:

1.  **CRUD Integrity**: Test creation, reading, updating, and deletion via the Filament UI.
2.  **Notification Flow**: Ensure `Notification` classes (found in `vendor/filament/notifications`) trigger the correct UI responses.
3.  **Unsaved Changes**: Verify that the `unsaved-changes-alert.js` triggers when attempting to navigate away from a "dirty" form.
4.  **Responsiveness**: Check that Table Columns (e.g., `toggle.js`, `text-input.js`) render correctly on mobile views.

### Automation Gate (Upcoming)
Once the CI/CD pipeline is active, the following will be enforced:
*   **100% Pass Rate**: No failures in the Pest/PHPUnit suite.
*   **Static Analysis**: No high-level errors reported by PHPStan/Larastan.
*   **Style Compliance**: Adherence to project-specific linting rules.

---

## Advanced Testing Techniques

### Utilizing Fakes
Leverage Laravel and Livewire fakes to avoid side effects and increase performance:

*   **Notifications**: Use `Notification::fake()` to intercept Filament notifications.
*   **WebSockets**: For features involving real-time updates, use `FakeEcho` and `FakeChannel` (located in `vendor/livewire/livewire/src/Features/SupportEvents/fake-echo.js`) to simulate events without a server.
*   **Storage**: Use `Storage::fake()` for testing file uploads in `FileUpload` components.

### Testing Livewire State
When testing Filament components, focus on the internal state rather than raw HTML:
*   Use `assertSet('data.field', 'value')` to check form state.
*   Use `assertHasErrors(['data.field' => 'required'])` for validation logic.
*   Use `assertActionExecuted()` for Filament header or table actions.

---

## Related Documentation
- [Development Workflow](./development-workflow.md)
- [FilamentPHP Official Documentation](https://filamentphp.com/docs)
- [Livewire Testing Guide](https://livewire.laravel.com/docs/testing)
