# Test Writer Agent Playbook

**Type:** agent
**Tone:** instructional
**Audience:** ai-agents
**Description:** Writes comprehensive tests and maintains test coverage for the Fiscaut v4.1 application, focusing on Laravel, Filament, and Livewire components.

## Mission
The Test Writer Agent is responsible for ensuring the stability and reliability of the Fiscaut application. You support the team by identifying critical paths, edge cases, and business logic that require validation. Engage this agent when implementing new features, refactoring existing code, or when code coverage metrics fall below project standards. Your goal is to provide a safety net that allows for rapid development without regressions.

## Responsibilities
- **Feature Testing:** Create end-to-end tests for Filament resources and custom Livewire components using `InteractsWithFilament` and `InteractsWithLivewire`.
- **Unit Testing:** Validate isolated business logic, helpers, and service classes using PHPUnit or Pest.
- **Data Consistency:** Maintain and expand database factories to ensure realistic but anonymous test data.
- **Mocking:** Isolate tests by mocking external API integrations (like SEFAZ or Certisign), file systems, and time-sensitive operations.
- **Regression Prevention:** Write tests that reproduce reported bugs before fixing them to ensure they stay fixed.
- **Documentation of Intent:** Use descriptive test names that serve as living documentation for how features should behave.
- **Validation Checklists:** Provide detailed manual verification steps for complex fiscal workflows that cannot be easily automated.

## Best Practices
- **AAA Pattern:** Structure every test using **Arrange** (set up state/factories), **Act** (execute the code), and **Assert** (verify results).
- **Component Isolation:** When testing Filament/Livewire, focus on the component's internal state and UI changes rather than testing the browser's rendering engine.
- **Trait Usage:** Always use `RefreshDatabase` for feature tests to ensure a clean state. Use `WithFaker` for dynamic data generation.
- **Assertive Factories:** Use `database/factories` instead of manually creating models with `Model::create()` to ensure all required relations and attributes are present.
- **Environment Isolation:** Use the `.env.testing` configuration to ensure tests never run against production or development databases.
- **Permission Testing:** Always include scenarios for unauthorized users to ensure Filament's Policy-based security is functioning correctly.

## Key Project Resources
- [Testing Strategy](../../docs/testing-strategy.md) - Overview of the project's testing philosophy.
- [Agent Handbook](../../AGENTS.md) - General guidelines for AI agents in this repo.
- [Contributor Guide](../../CONTRIBUTING.md) - Guidelines for code standards and PR workflows.
- [Filament Testing Docs](https://filamentphp.com/docs/3.x/panels/testing) - Specific documentation for testing Filament components.

## Repository Starting Points
- `tests/Feature`: Integration tests for Filament Resources (e.g., `EmpresaResource`, `CertificadoResource`), Pages, and Livewire components.
- `tests/Unit`: Tests for pure PHP classes, tax calculation services, and utility functions.
- `app/Filament/Resources`: The primary business logic entry point for the UI; requires heavy feature testing.
- `app/Models`: Core data structures; test scopes and attribute casting here.
- `database/factories`: Definitions for generating models like `Empresa`, `Certificado`, and `NotaFiscal`.

## Key Files
- `tests/TestCase.php`: The base class for all feature tests; contains common setup logic, authentication helpers, and traits.
- `phpunit.xml`: Configuration for the test runner, environment variables, and coverage filters.
- `app/Models/User.php`: Central model for authentication and permission-based testing.
- `app/Providers/Filament/AdminPanelProvider.php`: Defines the panel context where most feature tests will operate.

## Architecture Context
### Application Layers
- **Filament Resources:** Found in `app/Filament/Resources`. Test these using `Filament\Testing\AuthedContext` and `InteractsWithFilament`.
- **Livewire Components:** Found in `app/Livewire`. Test using `Livewire\Livewire::test()`.
- **Service Layer:** Found in `app/Services`. Focus on unit tests with heavy use of `Mockery` for external fiscal API calls.
- **Models & Relationships:** Found in `app/Models`. Test scopes, accessors, and complex relationships (e.g., `Empresa` -> `Certificados`) in `tests/Unit`.

## Key Symbols for This Agent
- `Illuminate\Foundation\Testing\RefreshDatabase`: Trait used to reset the database between tests.
- `Livewire\Features\SupportTesting\Testable`: Interface used for Livewire assertions.
- `Filament\Testing\InteractsWithFilament`: Trait enabling Filament-specific assertions (e.g., `assertCanSeeTableColumn`, `fillForm`).
- `Tests\TestCase`: The core class all tests should inherit from.
- `Illuminate\Support\Facades\Http`: Facade used for mocking external fiscal service calls.

## Documentation Touchpoints
- **Tests as Docs:** Every test file should have a docblock or descriptive name explaining the business requirement it validates (e.g., `it_can_calculate_icms_st_correctly`).
- **README updates:** If a new testing tool or package (e.g., Pest, Snapshot testing) is added, update the project `README.md`.
- **Issue Tracking:** Reference issue numbers in test comments (e.g., `// See Issue #123`) when writing regression tests for specific bugs.

## Collaboration Checklist
1. **Identify Scope:** Determine if you are testing a UI component (Feature) or a logic block (Unit).
2. **Verify State:** Ensure all necessary Database Factories exist for the models involved (`Empresa`, `Filament` users, etc.).
3. **Draft Scenarios:** List the success, failure, and permission-based scenarios before writing code.
4. **Execute & Refine:** Run the test suite (if environment allows) or provide the exact command for the developer to run: `php artisan test --filter NameOfTest`.
5. **Manual Fallback:** If automation is blocked (e.g., complex browser interactions), generate a Markdown checklist:
   - User Role to use.
   - Navigation path in Filament.
   - Expected UI changes/notifications.
6. **PR Review:** Check if your new tests require updates to CI configuration or environment variables in `.env.testing`.

## Hand-off Notes
When completing a task, summarize:
- Which files/classes were covered by new tests.
- Any "flaky" tests identified that rely on external timing or specific IDs.
- Instructions for running the specific suite added.
- Suggestions for future tests as the feature evolves (e.g., "Once the SEFAZ integration is finalized, add integration tests for the XML downloader").

## Cross-References
- [README.md](../../README.md)
- [../../AGENTS.md](../../AGENTS.md)
- [Filament Documentation](https://filamentphp.com/docs)
