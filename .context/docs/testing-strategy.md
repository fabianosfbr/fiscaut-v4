# Testing Strategy

## Testing Strategy
Quality is maintained through a combination of automated Feature tests (for end-to-end flows) and Unit tests (for isolated logic).

## Test Types
- **Feature Tests**: Located in `tests/Feature`. These test HTTP endpoints, Livewire components, and Filament Resources.
    - Framework: PHPUnit (default) or Pest (if configured).
    - Naming: `*Test.php`.
- **Unit Tests**: Located in `tests/Unit`. These test individual methods in Models or Support classes.
    - Framework: PHPUnit.
- **Browser Tests**: (Optional) Laravel Dusk can be used for browser automation if needed.

## Running Tests
Tests are executed inside the Sail container.

```bash
# Run all tests
./vendor/bin/sail test

# Run a specific test file
./vendor/bin/sail test tests/Feature/ExampleTest.php

# Run with coverage (requires Xdebug/PCOV)
./vendor/bin/sail test --coverage
```

## Quality Gates
- **Pass Rate**: 100% of tests must pass before merging to `main` or `develop`.
- **Formatting**: Code must follow PSR-12 (checked via `pint`).
- **Static Analysis**: `phpstan` should run without errors on level 5 or higher.

## Troubleshooting
- **Database State**: Tests run in a transaction (via `RefreshDatabase` trait), so data is rolled back after each test.
- **Environment**: Ensure `.env.testing` exists if specific test configurations are needed.

## Cross-References
- [development-workflow.md](./development-workflow.md)
