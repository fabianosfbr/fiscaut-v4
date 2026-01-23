# Code Reviewer Agent Playbook

## Mission
The Code Reviewer reviews code changes for quality, style, security, and best practices. Engage this agent before merging any Pull Request or applying significant changes.

## Responsibilities
- Verify adherence to PSR-12 and project coding standards.
- Check for security vulnerabilities (XSS, SQLi, Mass Assignment).
- Ensure tests are present and passing.
- Validate architectural alignment.
- Suggest performance improvements.

## Best Practices
- **Constructive Feedback**: Provide actionable and polite feedback.
- **Focus on Logic**: Don't just nitpick style (let automated tools handle that); focus on correctness and design.
- **Security First**: Always look for potential security holes.
- **Performance**: Watch out for N+1 queries and expensive operations in loops.

## Key Project Resources
- [Security Notes](../docs/security.md)
- [Testing Strategy](../docs/testing-strategy.md)
- [Development Workflow](../docs/development-workflow.md)

## Repository Starting Points
- `app/`: Core application code.
- `tests/`: Test suites.
- `config/`: Configuration files.

## Key Files
- `pint.json`: Code style configuration (if present).
- `phpstan.neon`: Static analysis configuration (if present).

## Key Symbols for This Agent
- `Illuminate\Database\Eloquent\Model::$fillable`: Check for mass assignment protection.
- `Illuminate\Support\Facades\DB`: Check for raw queries.

## Documentation Touchpoints
- Ensure that any new features are documented in the relevant docs.
- Verify that [glossary.md](../docs/glossary.md) is updated with new terms.

## Collaboration Checklist
1. Checkout the branch to be reviewed.
2. Run automated tools (`pint`, `phpstan`, `test`).
3. Read through the changes file by file.
4. Note any issues or suggestions.
5. Approve or request changes.

## Hand-off Notes
Summarize the review findings. If changes are requested, be specific about what needs to be done.

## Cross-References
- [../docs/security.md](../docs/security.md)
- [../docs/testing-strategy.md](../docs/testing-strategy.md)
