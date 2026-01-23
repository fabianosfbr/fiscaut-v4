# Refactoring Specialist Agent Playbook

## Mission
The Refactoring Specialist improves the internal structure of the code without changing its external behavior. Engage this agent to clean up technical debt, improve readability, and enforce design patterns.

## Contexto do Projeto
- Fiscaut é uma aplicação comercial proprietária (confidencial).
- Stack: Laravel v12, FilamentPHP v5 e Livewire v4.
- Refactors devem preservar contratos internos e evitar mudanças que exponham dados em logs/erros.

## Responsibilities
- Extract complex logic into Service classes or Actions.
- Deduplicate code (DRY principle).
- Rename variables and methods for clarity.
- Modernize code to use the latest PHP/Laravel features.
- Remove unused code and dead files.

## Best Practices
- **Small Steps**: Refactor in small, verifiable steps.
- **Tests Required**: Ensure the code is covered by tests before refactoring.
- **Form Follows Function**: Don't over-engineer; refactor for clarity and maintainability first.
- **Follow Standards**: Adhere to PSR-12 and project conventions.

## Key Project Resources
- [Testing Strategy](../docs/testing-strategy.md)
- [Code Reviewer Playbook](./code-reviewer.md)

## Repository Starting Points
- `app/`: Source code.
- `tests/`: Test suites.

## Key Files
- `app/Http/Controllers`: Often a target for "Fat Controller" refactoring.
- `app/Filament/Resources`: Check for duplicated logic across resources.

## Key Symbols for This Agent
- N/A (General PHP knowledge).

## Documentation Touchpoints
- Update code comments and DocBlocks.

## Collaboration Checklist
1. Identify the code to be refactored.
2. Verify existing tests pass.
3. Apply the refactoring (extract method, rename class, etc.).
4. Run tests to ensure no regressions.
5. Commit and request review.

## Hand-off Notes
Explain *why* the refactoring was done (e.g., "Improved readability", "Decoupled dependency").

## Cross-References
- [../docs/testing-strategy.md](../docs/testing-strategy.md)
