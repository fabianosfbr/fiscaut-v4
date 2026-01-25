# Database Documentation - Fiscaut-v4.1

This document outlines the database architecture, data access patterns, and management conventions for the Fiscaut-v4.1 project. The application leverages the Laravel ecosystem, specifically using Eloquent ORM and Filament for administrative interfaces and data management.

## 1. Engine & Core Technologies

- **Framework**: Laravel 10.x/11.x
- **ORM**: Eloquent ORM (Object-Relational Mapper)
- **Database Driver**: Configurable via `.env` (typically PostgreSQL or MySQL).
- **Admin Interface**: Filament v3 (PHP & Livewire)
- **Frontend Interactivity**: Alpine.js and custom JavaScript components for real-time data binding.

## 2. Architecture & Data Flow

The system separates data logic into three distinct layers:

### A. Persistence Layer (Migrations & Models)
- **Migrations**: Located in `database/migrations/`. These define the source of truth for the schema.
- **Models**: Located in `app/Models/`. These define business logic, attribute casting (e.g., JSON to array), and relationships.
- **Conventions**: Use `$fillable` for mass assignment protection and define type hints for IDE support.

### B. Management Layer (Filament Resources)
Filament serves as the primary interface for database interaction.
- **Schemas**: Definition of how data is structured for forms and tables.
- **Resources**: Map Eloquent models to the UI.
- **Actions**: Server-side logic triggered by UI buttons (Delete, Edit, Bulk Actions).

### C. UI Component Mapping
Data fields are mapped to specific JavaScript components for optimized rendering:
- **Text/Boolean**: `text-input.js`, `toggle.js`, and `checkbox.js`.
- **Complex Data**: `key-value.js` (for metadata) and `rich-editor.js` (for HTML/Markdown).

## 3. Data Access Patterns

### Querying & Filtering
The application uses a hybrid approach for data retrieval:
- **Eloquent Queries**: Primary method for server-side logic.
- **Eager Loading**: To prevent N+1 issues, always use `.with()` when fetching related data for tables.
- **Client-Side Filtering**: Handled via Livewire components that intercept UI interactions and refresh the dataset without a full page reload.

### Select & Search Logic
Select components utilize a dedicated utility for searching and filtering:
- **Utility**: `vendor/filament/support/resources/js/utilities/select.js`
- **Pattern**: Asynchronous searching is preferred for large datasets to maintain UI responsiveness.

## 4. Specialized Data Handling

### JSON and Metadata
For flexible data structures (like dynamic configurations), the system uses the **Key-Value Form Component**:
- **Location**: `public/js/filament/forms/components/key-value.js`
- **Storage**: Typically stored in a `json` or `text` column in the database, cast to an `array` in the Eloquent Model.

### Rich Content & Files
- **Rich Editor**: Managed via `vendor/filament/forms/resources/js/components/rich-editor.js`. Handles file uploads and content formatting.
- **File Uploads**: Uses Filament's file upload component which handles temporary storage before persisting the path to the database.

## 5. Maintenance and Development

### Schema Changes
Never modify the database structure directly. Use migrations:
```bash
# Create a new table or modify existing
php artisan make:migration add_status_to_users_table --table=users

# Apply changes
php artisan migrate

# Rollback last change
php artisan migrate:rollback
```

### Seeding and Testing
Use seeders to maintain a consistent development environment:
- **Seeders**: `database/seeders/DatabaseSeeder.php`
- **Factories**: `database/factories/` for generating dummy data.

```bash
# Refresh database and run all seeders
php artisan migrate:fresh --seed
```

## 6. Best Practices

1.  **Atomic Transactions**: Wrap multi-row updates in `DB::transaction()` to ensure data integrity.
2.  **Validation**: Define rules in Filament Resource forms (`required`, `unique`, `max`, etc.) to prevent invalid data from reaching the database.
3.  **Soft Deletes**: Use the `SoftDeletes` trait in Models where data retention is required for auditing purposes.
4.  **Attribute Casting**: Explicitly cast dates, booleans, and JSON objects in the Model to ensure type consistency between the database and the PHP/JS layers.
