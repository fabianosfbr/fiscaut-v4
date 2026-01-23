# Test Writer Agent Playbook

## Mission
The Test Writer ensures the application is robust by creating and maintaining a comprehensive test suite. Engage this agent to increase code coverage or when adding new features.

## Responsibilities
- Write Feature tests for end-to-end flows.
- Write Unit tests for isolated logic.
- Mock external dependencies and services.
- Ensure tests are reliable (not flaky) and fast.
- Maintain test data factories.

## Best Practices
- **AAA Pattern**: Arrange, Act, Assert.
- **Isolation**: Tests should not depend on each other.
- **Coverage**: Aim for high coverage in critical business logic.
- **Readable Names**: Test method names should describe the scenario being tested.

## Key Project Resources
- [Testing Strategy](../docs/testing-strategy.md)
- [Bug Fixer Playbook](./bug-fixer.md)

## Repository Starting Points
- `tests/Feature`: Integration/Feature tests.
- `tests/Unit`: Unit tests.
- `database/factories`: Data factories.

## Key Files
- `tests/TestCase.php`: Base test configuration.
- `phpunit.xml`: Test runner config.

## Key Symbols for This Agent
- `Illuminate\Foundation\Testing\RefreshDatabase`: Trait to reset DB.
- `Mockery`: Mocking library.

## Documentation Touchpoints
- Update [testing-strategy.md](../docs/testing-strategy.md) if adopting new testing tools.

## Collaboration Checklist
1. Identify the functionality to test.
2. Determine the type of test (Feature vs. Unit).
3. Set up the test state (Factories).
4. Execute the action.
5. Assert the expected outcome.
6. Run the test suite.

## Hand-off Notes
Note any complex setup required for the tests.

## Cross-References
- [../docs/testing-strategy.md](../docs/testing-strategy.md)
