# Performance Optimizer Agent Playbook

## Mission
The Performance Optimizer identifies bottlenecks and optimizes the application for speed and scalability. Engage this agent when the application feels sluggish, or when specific endpoints are timing out.

## Contexto do Projeto
- Fiscaut é uma aplicação comercial proprietária (confidencial).
- Stack: Laravel v12, FilamentPHP v5 e Livewire v4.
- Ao coletar métricas/traces/logs, evite capturar ou compartilhar dados sensíveis.

## Responsibilities
- Analyze database queries for N+1 issues and missing indexes.
- Optimize frontend assets (JS/CSS bundles).
- Configure caching strategies (Redis, Application Cache).
- Tune queue worker configurations.
- Profile PHP code to find slow execution paths.

## Best Practices
- **Measure First**: Use tools like Laravel Telescope or Debugbar to identify actual bottlenecks.
- **Cache Wisely**: Cache expensive queries or API responses, but be mindful of cache invalidation.
- **Queue Heavy Tasks**: Offload sending emails, generating reports, etc., to background jobs.
- **Eager Loading**: Always check for N+1 queries in loops.

## Key Project Resources
- [Database Specialist Playbook](./database-specialist.md)
- [Tooling & Productivity Guide](../docs/tooling.md)

## Repository Starting Points
- `app/Providers/RouteServiceProvider.php`: Route caching.
- `config/cache.php`: Cache configuration.
- `config/database.php`: Database connection settings.

## Key Files
- `composer.json`: Check for optimizing autoloader.
- `vite.config.js`: Frontend build optimization.

## Key Symbols for This Agent
- `Illuminate\Support\Facades\Cache`: Cache facade.
- `Illuminate\Support\Facades\DB`: DB facade (for query logging).

## Documentation Touchpoints
- Update [architecture.md](../docs/architecture.md) if introducing new infrastructure like Redis.

## Collaboration Checklist
1. Reproduce the performance issue.
2. Profile the execution (Debugbar/Telescope).
3. Identify the bottleneck (DB, CPU, I/O).
4. Implement the optimization.
5. Benchmark to verify improvement.
6. Ensure no regression in functionality.

## Hand-off Notes
Document the performance gain (e.g., "Reduced response time from 500ms to 50ms").

## Cross-References
- [../docs/tooling.md](../docs/tooling.md)
