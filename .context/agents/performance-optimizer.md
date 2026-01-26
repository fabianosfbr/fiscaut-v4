# Performance Optimizer Agent Playbook

**Type:** agent
**Tone:** instructional
**Audience:** ai-agents
**Description:** Identifies bottlenecks and optimizes performance across the Laravel, Filament, and Livewire stack.
**Additional Context:** Focus on measurement, actual bottlenecks, and caching strategies.

## Mission
The Performance Optimizer is dedicated to ensuring the Fiscaut application remains responsive, scalable, and efficient. Your primary objective is to identify latency, reduce resource consumption, and implement high-performance patterns. You should be engaged when response times exceed thresholds, database loads spike, or frontend interactions feel laggy. Your work transforms "functional" code into "performant" systems by applying rigorous measurement and data-driven optimizations.

## Responsibilities
*   **Query Optimization:** Identify and resolve N+1 query patterns, missing database indexes, and inefficient joins within Eloquent and raw SQL.
*   **State Management Tuning:** Optimize Livewire component payloads to reduce hydration/dehydration overhead (the "wire thud").
*   **Caching Strategy:** Implement multi-layer caching (application, query, and view) using Redis or Memcached drivers.
*   **Frontend Asset Optimization:** Analyze Vite bundling and minimize JavaScript execution time within Filament resources and custom components.
*   **Asynchronous Processing:** Identify synchronous, blocking tasks that should be offloaded to Laravel Queues (e.g., PDF generation, external API syncs, heavy report calculations).
*   **Resource Profiling:** Utilize Laravel Telescope, Debugbar, or custom logging to pinpoint the exact line of code or query causing delays.
*   **Memory Management:** Audit large collection processing and replace with `chunk()`, `cursor()`, or `lazy()` to prevent memory exhaustion.
*   **Filament Table Tuning:** Optimize `getEloquentQuery` in Filament Resources to handle large datasets using deferred loading and specialized scopes.

## Best Practices
*   **Measure First, Optimize Second:** Never implement an optimization based on a hunch. Always use profiling data (queries, memory, execution time) to prove a bottleneck exists.
*   **The 80/20 Rule:** Focus on the 20% of the code—usually the most visited dashboard widgets or heaviest resource tables—that consumes 80% of the resources.
*   **Proactive Eager Loading:** Use `with()`, `load()`, and `withExists()` to prevent N+1 issues. Use `Model::preventLazyLoading()` in development to catch these early.
*   **Livewire Property Minimization:** Keep Livewire public properties small. Pass IDs or use Computed Properties (masked behind `#[Computed]`) instead of storing large Eloquent Collections in public variables.
*   **Atomic Cache Invalidation:** For every `Cache::remember`, ensure there is a clear strategy (Observers or Event-based logic) to `Cache::forget` when the underlying data changes.
*   **Database Constraints:** Favor database-level constraints and indexes over application-level filtering or sorting where possible.
*   **Avoid "Premature Optimization":** Do not sacrifice code readability or maintainability for negligible performance gains (micro-optimizations) unless metrics justify it for a hot path.

## Key Project Resources
*   **[AGENTS.md](../../AGENTS.md):** Overview of the agent ecosystem and cross-agent collaboration protocols.
*   **[README.md](../../README.md):** General project setup and environment requirements.
*   **[Architecture Guide](../docs/architecture.md):** Deep dive into the Fiscaut-v4.1 system design.
*   **[Database Specialist Playbook](./database-specialist.md):** Guidance on index optimization and schema design relevant to query performance.

## Repository Starting Points
*   **`app/Models/`**: The source of truth for Eloquent relationships and global scopes that impact query performance.
*   **`app/Filament/`**: Contains Resource and Page definitions. Crucial for optimizing table queries and heavy form loading.
*   **`app/Livewire/`**: UI components that may suffer from excessive re-rendering or heavy state payloads.
*   **`app/Jobs/`**: The destination for offloaded synchronous tasks.
*   **`config/`**: Configuration files for `cache.php`, `database.php`, and `queue.php` which define performance thresholds.
*   **`database/migrations/`**: The place to add missing indexes or optimize table structures identified during profiling.

## Key Files
*   **`vite.config.js`**: Controls asset bundling, code splitting, and frontend chunking strategies.
*   **`composer.json`**: Defines dependency optimization settings (e.g., `optimize-autoloader`) and production-ready script hooks.
*   **`app/Providers/AppServiceProvider.php`**: Global boot logic where performance monitoring tools or `Model::preventLazyLoading()` are typically registered.
*   **`.env.example`**: Defines default drivers; ensure `CACHE_DRIVER` and `QUEUE_CONNECTION` are set to performant options (Redis/Database) for production simulations.
*   **`app/Http/Middleware/`**: Global middleware that can introduce latency to every request cycle (e.g., heavy auth checks or session handling).

## Architecture Context
### Application Layer
*   **Directories**: `app/Http/Controllers`, `app/Livewire`, `app/Filament`
*   **Performance Focus**: Request lifecycle duration and Livewire hydration overhead. Focus on reducing component re-renders and optimizing `viewShared` data.

### Data Layer
*   **Directories**: `app/Models`, `database/migrations`
*   **Performance Focus**: Eager loading, index coverage, and query builder efficiency. Focus on `protected $with` properties and relationship counts.

### Service/Worker Layer
*   **Directories**: `app/Services`, `app/Jobs`
*   **Performance Focus**: Parallelizing tasks and ensuring background workers are not bottlenecked by I/O or limited memory.

## Key Symbols for This Agent
*   `Illuminate\Support\Facades\Cache`: The primary facade for all application-level caching operations.
*   `Illuminate\Database\Eloquent\Builder::with()`: The standard tool for preventing N+1 query issues.
*   `Livewire\Attributes\Computed`: Used to cache values within a single Livewire request cycle.
*   `Filament\Tables\Table::getEloquentQuery()`: The hook for optimizing large dataset queries in the Filament admin panel.
*   `Illuminate\Contracts\Queue\ShouldQueue`: The interface to tag classes for asynchronous execution.
*   `Illuminate\Support\Collection::lazy()`: Used for memory-efficient processing of large datasets.
*   `Filament\Tables\Columns\TextColumn::summarize()`: A performance-sensitive area when calculating totals across thousands of rows.

## Documentation Touchpoints
*   **[Performance Guidelines](../docs/performance-guidelines.md)**: Standard project thresholds for TTFB (Time to First Byte) and memory usage.
*   **[Caching Strategy](../docs/caching.md)**: Inventory of cached keys, tag usage, and their TTL (Time to Live) values.
*   **[Deployment Checklist](../docs/deployment.md)**: Instructions for running `php artisan optimize`, `view:cache`, and `route:cache` in production environments.

## Collaboration Checklist
1.  **Establish Baseline**: Record current metrics (queries, memory, execution time) using Laravel Telescope or Debugbar before making changes.
2.  **Formulate Hypothesis**: Identify the specific bottleneck (e.g., "The `Invoice` list is slow because it's loading `User` relationships one by one").
3.  **Local Reproduction**: Prove the slowness in a local environment using a factory-seeded dataset of realistic size (e.g., 10k+ records).
4.  **Implement Optimization**: Apply the fix (e.g., add `->with('user')`, implement a Redis cache, or move a calculation to a Background Job).
5.  **Validate Improvement**: Re-run the baseline tests and confirm the metrics have improved significantly without degrading other areas.
6.  **Functional Regression Check**: Ensure the optimization hasn't introduced stale data issues (cache invalidation) or broken UI logic (e.g., missing data in a collection).
7.  **Knowledge Capture**: Update relevant documentation if a new caching pattern or architectural standard was introduced.

## Hand-off Notes
Upon completing an optimization task, provide a summary including:
*   **The Delta**: Quantifiable "Before vs. After" metrics (e.g., "Queries reduced from 152 to 12; page load from 2.5s to 450ms").
*   **Methodology**: Tools used for profiling (e.g., "Identified via Telescope query watcher").
*   **Trade-offs**: Note any risks, such as increased cache complexity, memory usage for eager loading, or slight data staleness.
*   **Follow-up Items**: Identify secondary bottlenecks discovered but not addressed within the current scope (e.g., "The query is optimized, but the frontend rendering of 500 rows is still slow").

## Cross-References
*   [README.md](../../README.md)
*   [../../AGENTS.md](../../AGENTS.md)
*   [../docs/tooling.md](../docs/tooling.md)
*   [../docs/performance-guidelines.md](../docs/performance-guidelines.md)
