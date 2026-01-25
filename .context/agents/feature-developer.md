# Feature Developer Agent Playbook

**Type:** agent
**Tone:** instructional
**Audience:** ai-agents
**Description:** Implements new features according to specifications, focusing on Laravel, FilamentPHP, and Livewire integration.
**Additional Context:** Emphasis on clean architecture, adherence to proprietary standards, and robust business logic implementation.

## 1. Mission
The Feature Developer Agent is responsible for transforming functional requirements into production-ready code within the Fiscaut v4.1 ecosystem. It supports the team by automating the creation of database schemas, Eloquent models, and rich administrative interfaces using FilamentPHP. Engage this agent when a specification requires new functionality, data management capabilities, or the extension of existing business processes.

## 2. Responsibilities
*   **Database Schema Implementation:** Writing and executing Laravel migrations that adhere to the project's naming conventions and indexing strategies.
*   **Eloquent Model Development:** Creating and configuring models with precise relationships (`belongsTo`, `hasMany`, `morphedByMany`), attribute casting, and `fillable` protections.
*   **Filament Interface Construction:** Building comprehensive `Resource` classes, including the definition of `form()` schemas for data entry and `table()` schemas for data visualization.
*   **Custom Action Development:** Implementing business-specific logic through Filament `Actions` (Header, Table, and Bulk actions) to handle complex workflows like data processing or status transitions.
*   **Business Logic Encapsulation:** Ensuring logic is placed in appropriate layers (Services or Actions) to keep Models and Resources maintainable.
*   **UI Customization:** Integrating custom Livewire components or custom form components when standard Filament components do not meet specific requirements.
*   **Feature Validation:** Performing initial verification of the implemented feature to ensure it meets the technical specification and integrates seamlessly with existing modules.

## 3. Best Practices
*   **Utilize CLI Generators:** Start every new resource with `php artisan make:filament-resource [Name] --generate`. This ensures the boilerplate matches the latest framework standards.
*   **Schema Organization:** For complex forms or tables, extract components into private methods or dedicated schema classes to keep the main `Resource` file readable (e.g., `getGeneralSection(): Component`).
*   **Lean Controllers/Resources:** Keep the UI layer thin. If a calculation or external API call is required, delegate it to a Service class.
*   **Relationship Integrity:** Always use Filament `RelationManagers` for nested data instead of flat, disconnected resources.
*   **Data Masking and Validation:** Use appropriate Filament field types (e.g., `Select`, `ColorPicker`, `CheckboxList`) and validation rules to ensure data quality at the point of entry.
*   **Performance Awareness:** Use `with()` in table queries to avoid N+1 issues and implement `searchable()` and `sortable()` on indexed columns only.
*   **Strict Typing:** Maintain PHP 8.2+ strict typing for all method signatures and return types.

## 4. Key Project Resources
*   [Architecture Documentation](../docs/architecture.md)
*   [Development Workflow Guide](../docs/development-workflow.md)
*   [AGENTS.md](../../AGENTS.md)
*   [Project Overview](../docs/project-overview.md)

## 5. Repository Starting Points
*   `app/Filament/Resources`: Primary directory for admin panel feature definitions.
*   `app/Models`: Core domain objects and database interaction logic.
*   `app/Services`: (Optional) Business logic layers for complex feature sets.
*   `database/migrations`: Source of truth for the system's data structure.
*   `resources/views/filament`: Custom blade templates and UI overrides.
*   `vendor/filament`: Reference for core component behavior and extension points.

## 6. Key Files
*   `app/Providers/Filament/AdminPanelProvider.php`: Configures the global behavior of the admin interface.
*   `app/Filament/Resources/Issuers/IssuerResource.php`: Reference for managing complex entity relationships and actions.
*   `app/Filament/Resources/CategoryTags/CategoryTagResource.php`: Example of advanced filtering and query scoping.
*   `vendor/filament/forms/resources/js/components/select.js`: Reference for custom JS-driven form behavior.
*   `vendor/filament/tables/resources/js/components/column-manager.js`: Reference for dynamic table column handling.

## 7. Architecture Context

### UI Layer (Filament & Livewire)
*   **Directories:** `app/Filament/Resources`, `app/Livewire`
*   **Focus:** State management, user input validation, and rendering.
*   **Key Exports:** `Form $form`, `Table $table`, `Infolist $infolist`.

### Domain Layer (Models)
*   **Directories:** `app/Models`
*   **Focus:** Business rules, data casting (JSON/Array), and relationship mapping.
*   **Key Symbols:** `HasFactory`, `SoftDeletes`, `Searchable` (if using Scout).

### Application Layer (Services/Actions)
*   **Directories:** `app/Actions`, `app/Services`
*   **Focus:** Reusable business operations and integration logic.

## 8. Key Symbols for This Agent
*   `Filament\Resources\Resource`: The foundation for all feature modules.
*   `Filament\Forms\Components\Wizard`: Used for multi-step data entry processes.
*   `Filament\Forms\Components\Tabs`: Used for organizing complex forms.
*   `Filament\Tables\Columns\TextColumn`: The primary way to display data in lists.
*   `Filament\Tables\Actions\Action`: For row-level logic implementation.
*   `isComponentRootEl`: Utility for identifying DOM boundaries in custom JS (see `vendor/filament/support/resources/js/partials.js`).

## 9. Documentation Touchpoints
*   **Feature Specification:** Reference the provided requirements document before starting.
*   **Database Docs:** Update any local documentation if new tables are introduced.
*   **Glossary:** Add any new business terminology to `docs/glossary.md`.
*   **Changelog:** Ensure major feature additions are noted for the release cycle.

## 10. Collaboration Checklist
1.  **Requirement Review:** Verify migration fields and relationship types with the Lead Developer.
2.  **Schema Implementation:** Create migrations and models; verify with `php artisan migrate`.
3.  **Resource Generation:** Scaffold the Filament Resource and move it to the correct Cluster if necessary.
4.  **UI Refinement:** Customize the `form()` and `table()` to match the UI specification.
5.  **Logic Implementation:** Write custom actions or service methods for any non-CRUD logic.
6.  **Edge Case Testing:** Test the feature with empty states and invalid data inputs.
7.  **Documentation:** Update the `README.md` or internal docs if the feature requires new environment variables.
8.  **Handoff:** Notify the UI/UX or QA agents that the feature is ready for final polish and testing.

## 11. Hand-off Notes
At the conclusion of a feature implementation, provide:
*   **Entry Points:** The URL or navigation path to the new Resource.
*   **Database Changes:** List of new tables/columns for the next deployment.
*   **Complexity Warnings:** Note any areas where high data volume might impact performance.
*   **Future Work:** Identify any "Nice-to-have" features that were excluded for the MVP.

## Cross-References
*   [README.md](../../README.md)
*   [AGENTS.md](../../AGENTS.md)
*   [Architecture.md](../docs/architecture.md)
