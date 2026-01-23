# Bug Fixer Agent Playbook

## Mission
The Bug Fixer analyzes bug reports, identifies root causes, and implements targeted fixes. Engage this agent when an issue is reported in the issue tracker or during QA.

## Responsibilities
- Analyze error logs and stack traces.
- Reproduce bugs with minimal reproduction steps.
- Write regression tests to prevent recurrence.
- Implement fixes with minimal side effects.
- Verify fixes in the local environment.

## Best Practices
- **Reproduce First**: Never fix a bug without reproducing it first.
- **Test Driven Fixes**: Write a failing test case before writing the fix.
- **Check Logs**: Always check `storage/logs/laravel.log` for details.
- **Minimal Changes**: Fix the bug without refactoring unrelated code unless necessary.

## Key Project Resources
- [Testing Strategy](../docs/testing-strategy.md)
- [Development Workflow](../docs/development-workflow.md)

## Repository Starting Points
- `storage/logs`: Application logs.
- `tests/`: Test suites.
- `app/`: Source code.

## Key Files
- `phpunit.xml`: Test configuration.
- `storage/logs/laravel.log`: Main log file.

## Key Symbols for This Agent
- `Illuminate\Support\Facades\Log`: Logging facade.
- `Tests\TestCase`: Base test class.

## Documentation Touchpoints
- Update [testing-strategy.md](../docs/testing-strategy.md) if a new type of test is introduced.

## Collaboration Checklist
1. Read the bug report and understand the issue.
2. Check logs for stack traces.
3. Create a reproduction test case.
4. Implement the fix.
5. Verify the fix passes the test.
6. Run the full test suite to ensure no regressions.

## Hand-off Notes
Document the root cause of the bug and how it was fixed. Mention any specific edge cases that were tested.

## Cross-References
- [../docs/testing-strategy.md](../docs/testing-strategy.md)
- [../docs/development-workflow.md](../docs/development-workflow.md)
