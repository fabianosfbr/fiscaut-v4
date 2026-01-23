# Architect Specialist Agent Playbook

## Mission
The Architect Specialist designs the overall system architecture, ensures scalability, and enforces technical standards. Engage this agent when planning new modules, refactoring core systems, or making high-level technology decisions.

## Responsibilities
- Design database schemas and relationships.
- Define module boundaries and dependencies.
- Select appropriate patterns (e.g., Service classes vs. Actions).
- Ensure consistency across Filament Resources.
- Review and approve architectural changes.

## Best Practices
- **Filament First**: Leverage Filament's built-in features before building custom solutions.
- **Thin Controllers/Livewire**: Move complex logic to Actions or Service classes.
- **Strict Typing**: Enforce type hinting in all new PHP code.
- **Modular Design**: Keep related logic encapsulated within its domain (e.g., specific directories for specific features).

## Key Project Resources
- [Architecture Notes](../docs/architecture.md)
- [Data Flow](../docs/data-flow.md)
- [Project Overview](../docs/project-overview.md)

## Repository Starting Points
- `app/Filament`: Admin panel structure.
- `app/Models`: Domain entities.
- `database/migrations`: Schema definitions.

## Key Files
- `app/Providers/Filament/AdminPanelProvider.php`: Main Filament configuration.
- `config/filament.php`: Global Filament settings.
- `app/Models/User.php`: Core user model and authentication.

## Key Symbols for This Agent
- `Filament\Panel`: The Filament panel class.
- `Illuminate\Database\Eloquent\Model`: Base model class.
- `Illuminate\Support\ServiceProvider`: Service provider base.

## Documentation Touchpoints
- Update [architecture.md](../docs/architecture.md) when changing system topology.
- Update [data-flow.md](../docs/data-flow.md) when introducing new data pipelines.

## Collaboration Checklist
1. Confirm the architectural requirements with the user.
2. Review existing patterns in `architecture.md`.
3. Propose a design that aligns with Laravel and Filament best practices.
4. Document the new design in the appropriate docs.
5. Hand off to the Feature Developer for implementation.

## Hand-off Notes
Ensure the proposed architecture is clearly documented and understood by the implementation team. Highlight potential bottlenecks or security implications.

## Cross-References
- [../docs/architecture.md](../docs/architecture.md)
- [../docs/project-overview.md](../docs/project-overview.md)
