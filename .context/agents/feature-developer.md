# Feature Developer Agent Playbook

## Mission
The Feature Developer implements new features, integrating them into the existing architecture. Engage this agent for the majority of development tasks, such as creating new Filament Resources, Livewire components, or business logic.

## Contexto do Projeto
- Fiscaut é uma aplicação comercial proprietária (confidencial).
- Stack: Laravel v12, FilamentPHP v5 e Livewire v4.
- Ao propor mudanças, preserve sigilo e evite expor dados/segredos em exemplos e logs.

## Responsibilities
- Implement end-to-end features (DB -> Model -> UI).
- Create Filament Resources and Pages.
- Write Feature tests.
- Ensure code follows project standards.

## Best Practices
- **Filament Resources**: Use `php artisan make:filament-resource` to scaffold.
- **TDD**: Write tests before or during implementation.
- **Small Commits**: Break down large features into smaller, reviewable chunks.
- **Reuse**: Reuse existing components and logic where possible.

## Key Project Resources
- [Development Workflow](../docs/development-workflow.md)
- [Architecture Notes](../docs/architecture.md)

## Repository Starting Points
- `app/Filament`: Admin UI.
- `app/Models`: Data layer.
- `tests/Feature`: Testing.

## Key Files
- `app/Filament/Resources/CfopResource.php`: Example resource.
- `routes/web.php`: Web routes.

## Key Symbols for This Agent
- `Filament\Resources\Resource`: Base resource class.
- `Filament\Forms\Form`: Form builder.
- `Filament\Tables\Table`: Table builder.

## Documentation Touchpoints
- Update [project-overview.md](../docs/project-overview.md) if a major feature is added.
- Update [glossary.md](../docs/glossary.md) with new terms.

## Collaboration Checklist
1. Receive requirements from Architect or User.
2. Plan the implementation steps (Migration -> Model -> Resource -> Test).
3. Implement the feature.
4. Write and pass tests.
5. Verify in the browser.

## Hand-off Notes
Point to the main entry point of the new feature (e.g., the URL or Menu item).

## Cross-References
- [../docs/development-workflow.md](../docs/development-workflow.md)
- [../docs/architecture.md](../docs/architecture.md)
