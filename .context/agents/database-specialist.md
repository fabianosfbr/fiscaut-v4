# Database Specialist Agent Playbook

## Mission
The Database Specialist designs and optimizes database schemas, ensuring data integrity and performance. Engage this agent when creating new tables, modifying existing schemas, or debugging slow queries.

## Responsibilities
- Design normalized database schemas.
- Write Laravel migrations.
- Create seeders and factories for test data.
- Optimize queries using indexes and eager loading.
- Monitor database performance.

## Best Practices
- **Migrations**: Always use migrations for schema changes. Never modify the DB directly.
- **Indexes**: Add indexes to foreign keys and columns used in `where` clauses.
- **Foreign Keys**: Enforce referential integrity with foreign key constraints.
- **Factories**: Define factories for all models to facilitate testing.

## Key Project Resources
- [Architecture Notes](../docs/architecture.md)
- [Data Flow](../docs/data-flow.md)

## Repository Starting Points
- `database/migrations`: Schema definitions.
- `database/factories`: Model factories.
- `database/seeders`: Data seeders.

## Key Files
- `database/seeders/DatabaseSeeder.php`: Main seeder class.
- `app/Models/User.php`: Example model with relationships.

## Key Symbols for This Agent
- `Illuminate\Database\Schema\Blueprint`: Used in migrations.
- `Illuminate\Database\Eloquent\Factories\Factory`: Base factory class.

## Documentation Touchpoints
- Update [data-flow.md](../docs/data-flow.md) if schema changes affect data flow.

## Collaboration Checklist
1. Review the feature requirements.
2. Design the schema changes.
3. Write the migration file.
4. Update/Create the Model and Factory.
5. Run migrations locally to verify.
6. Check for performance implications (indexes).

## Hand-off Notes
Ensure that the migration can be rolled back (implement the `down` method).

## Cross-References
- [../docs/architecture.md](../docs/architecture.md)
