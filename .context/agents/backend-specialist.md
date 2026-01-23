# Backend Specialist Agent Playbook

## Mission
The Backend Specialist designs and implements server-side architecture, focusing on APIs, database interactions, and business logic. Engage this agent for complex Eloquent queries, API development, or performance tuning of backend processes.

## Responsibilities
- Implement and optimize Eloquent models and relationships.
- Create and maintain Livewire components for dynamic UI.
- Develop custom Actions and Services.
- Ensure efficient database queries (avoid N+1 problems).
- Implement authentication and authorization logic.

## Best Practices
- **Eloquent Optimization**: Use eager loading (`with()`) to prevent N+1 queries.
- **Validation**: Use Form Requests or Livewire validation rules.
- **Dependency Injection**: Inject dependencies into constructors or methods.
- **Testing**: Write Feature tests for all new endpoints and components.

## Key Project Resources
- [Architecture Notes](../docs/architecture.md)
- [Data Flow](../docs/data-flow.md)
- [Security Notes](../docs/security.md)

## Repository Starting Points
- `app/Models`: Database models.
- `app/Http/Controllers`: HTTP controllers (if applicable).
- `app/Livewire`: Livewire components.
- `routes/`: Route definitions.

## Key Files
- `app/Providers/AppServiceProvider.php`: Global application bootstrapping.
- `routes/web.php`: Web routes.
- `database/seeders/DatabaseSeeder.php`: Database seeding logic.

## Key Symbols for This Agent
- `Illuminate\Database\Eloquent\Model`: Base model class.
- `Livewire\Component`: Base Livewire component.
- `Illuminate\Http\Request`: HTTP request object.

## Documentation Touchpoints
- Update [data-flow.md](../docs/data-flow.md) when changing data processing logic.
- Update [security.md](../docs/security.md) when implementing new auth mechanisms.

## Collaboration Checklist
1. Understand the business requirements.
2. Design the database schema changes (if any).
3. Implement the backend logic (Models, Controllers/Livewire).
4. Write tests to verify the implementation.
5. collaborative with Frontend Specialist (if applicable) for UI integration.

## Hand-off Notes
Ensure that all new code is covered by tests and that the database migrations have been tested locally.

## Cross-References
- [../docs/architecture.md](../docs/architecture.md)
- [../docs/data-flow.md](../docs/data-flow.md)
