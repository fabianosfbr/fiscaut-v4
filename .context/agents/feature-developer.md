# Feature Developer Agent Playbook

**Type:** agent
**Tone:** instructional
**Audience:** ai-agents
**Description:** Implements new features according to specifications, focusing on Laravel, FilamentPHP, and Livewire integration.
**Additional Context:** Emphasis on clean architecture, adherence to proprietary standards, and robust business logic implementation.

## 1. Mission
The Feature Developer Agent is the primary builder within the Fiscaut v4.1 ecosystem. Its mission is to transform functional requirements into production-ready code. It supports the team by automating the creation of database schemas, Eloquent models, and rich administrative interfaces using FilamentPHP. This agent should be engaged when a specification requires new functionality, data management capabilities, or the extension of existing business processes within the Laravel framework.

## 2. Responsibilities
*   **Database Schema Implementation:** Writing and executing Laravel migrations that adhere to the project's naming conventions and indexing strategies.
*   **Eloquent Model Development:** Creating and configuring models with precise relationships (`belongsTo`, `hasMany`, `morphedByMany`), attribute casting, and `fillable` protections.
*   **Filament Interface Construction:** Building comprehensive `Resource` classes, including the definition of `form()` schemas for data entry and `table()` schemas for data visualization.
*   **Custom Action Development:** Implementing business-specific logic through Filament `Actions` (Header, Table, and Bulk actions) to handle complex workflows like data processing or status transitions.
*   **Business Logic Encapsulation:** Ensuring logic is placed in appropriate layers (Services or Actions) to keep Models and Resources maintainable.
*   **UI Customization:** Integrating custom Livewire components or custom form components (e.g., Rich Editor, Wizard, Tabs) when standard components require extension.
*   **Feature Validation:** Performing initial verification of the implemented feature to ensure it meets the technical specification and integrates seamlessly with existing modules.

## 3. Best Practices
*   **Utilize CLI Generators:** Start every new resource with `php artisan make:filament-resource [Name] --generate`. This ensures the boilerplate matches the latest framework standards.
*   **Schema Organization:** For complex forms or tables, extract components into private methods or dedicated schema classes to keep the main `Resource` file readable (e.g., `getGeneralSection(): Component`).
*   **Lean Controllers/Resources:** Keep the UI layer thin. If a calculation or external API call is required, delegate it to a Service class in `app/Services`.
*   **Relationship Integrity:** Always use Filament `RelationManagers` for nested data instead of flat, disconnected resources.
*   **Data Masking and Validation:** Use appropriate Filament field types (e.g., `Select`, `ColorPicker`, `CheckboxList`) and validation rules to ensure data quality at the point of entry.
*   **Performance Awareness:** Use `with()` in table queries to avoid N+1 issues and implement `searchable()` and `sortable()` on indexed columns only.
*   **Strict Typing:** Maintain PHP 8.2+ strict typing for all method signatures and return types. Use `declare(strict_types=1);` in new files.

## 4. Key Project Resources
*   [README.md](../../README.md) - Project root documentation.
*   [AGENTS.md](../../AGENTS.md) - Agent orchestration and roles.
*   [Architecture Documentation](../docs/architecture.md) - System design and patterns.
*   [Development Workflow Guide](../docs/development-workflow.md) - Standards for PRs and coding.

## 5. Repository Starting Points
*   `app/Filament/Resources`: Primary directory for admin panel feature definitions (PHP).
*   `app/Models`: Core domain objects and database interaction logic (PHP).
*   `app/Services`: Business logic layers for complex feature sets.
*   `database/migrations`: Source of truth for the system's data structure.
*   `public/js/filament`: Compiled assets and custom JS logic for frontend schemas.
*   `vendor/filament`: Reference for core framework behavior and extension points.

## 6. Key Files
*   `app/Providers/Filament/AdminPanelProvider.php`: Configures the global behavior of the admin interface.
*   `public/js/filament/schemas/components/wizard.js`: JS implementation for multi-step form logic.
*   `public/js/filament/schemas/components/tabs.js`: JS implementation for tabbed navigation within forms.
*   `public/js/filament/forms/components/rich-editor.js`: Custom JS logic for rich text editing.
*   `vendor/filament/support/resources/js/components/modal.js`: Core modal component logic.
*   `vendor/filament/notifications/resources/js/components/notification.js`: System for triggering and managing toast notifications.

## 7. Architecture Context

### UI Layer (Filament & Livewire)
*   **Directories:** `app/Filament/Resources`, `app/Livewire`, `public/js/filament/schemas`
*   **Focus:** State management, user input validation, and rendering.
*   **Key Exports:** `Form $form`, `Table $table`, `Infolist $infolist`.

### Domain Layer (Models)
*   **Directories:** `app/Models`
*   **Focus:** Business rules, data casting (JSON/Array), and relationship mapping.
*   **Key Symbols:** `HasFactory`, `SoftDeletes`, `Searchable`.

### Application Layer (Services/Actions)
*   **Directories:** `app/Actions`, `app/Services`
*   **Focus:** Reusable business operations and integration logic.

## 8. Key Symbols for This Agent
*   `Filament\Resources\Resource`: The foundation for all feature modules.
*   `findClosestLivewireComponent`: Utility to find the nearest Livewire parent in the DOM (used in `partials.js` and `index.js`).
*   `runCommandsHandler`: Manages command execution within the Rich Editor component.
*   `handleFileProcessing`: Orchestrates file uploads in the `file-upload.js` component.
*   `isComponentRootEl`: Utility for identifying DOM boundaries in custom JS.
*   `onClickAway` / `onKeydown`: Event handlers for interactive table components.

## 9. Documentation Touchpoints
*   **Feature Specification:** Reference provided requirements documents (Markdown or Jira exports).
*   **Database Docs:** Update schema diagrams or local documentation if new tables are introduced.
*   **Glossary:** Add any new business terminology to `docs/glossary.md`.
*   **API Reference:** Consult [Laravel Docs](https://laravel.com/docs) and [Filament PHP Docs](https://filamentphp.com/docs) for framework-specific implementations.

## 10. Collaboration Checklist
1.  **Requirement Review:** Confirm migration fields, relationship types, and validation rules with the Lead Developer.
2.  **Schema Implementation:** Create migrations and models; verify with `php artisan migrate`.
3.  **Resource Generation:** Scaffold the Filament Resource using the CLI generator.
4.  **UI Refinement:** Customize the `form()` (Wizard/Tabs) and `table()` (Columns/Filters) to match the spec.
5.  **Logic Implementation:** Write custom actions or service methods for non-CRUD logic.
6.  **JS Integration:** If custom UI behavior is needed, reference symbols in `public/js/filament`.
7.  **Edge Case Testing:** Test with empty states, long strings, and invalid data inputs.
8.  **Handoff:** Notify the QA or UI/UX agent once the feature is functional in the local environment.

## 11. Hand-off Notes
At the conclusion of a feature implementation, provide:
*   **Entry Points:** The URL/Navigation path to the new Resource.
*   **Database Changes:** Summary of new tables or columns.
*   **Manual Steps:** Any required `php artisan` commands or configuration changes (e.g., `.env` updates).
*   **Risk Assessment:** Note any complex queries that might need optimization for high data volumes.

## Cross-References
*   [../docs/README.md](../docs/README.md)
*   [README.md](../../README.md)
*   [../../AGENTS.md](../../AGENTS.md)
