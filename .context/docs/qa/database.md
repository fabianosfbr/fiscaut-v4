# Database Documentation - Fiscaut-v4.1

This document provides a comprehensive overview of the database architecture, data access patterns, and management conventions for the Fiscaut-v4.1 project.

## 1. Core Architecture

The application follows a standard Laravel architecture, utilizing **Eloquent ORM** for data manipulation and **Filament v3** for the administrative management layer.

- **Framework**: Laravel 10.x/11.x
- **ORM**: Eloquent ORM (Object-Relational Mapper)
- **Database Driver**: Configurable via `.env` (PostgreSQL recommended for production).
- **Admin Interface**: Filament v3 (PHP, Livewire, and Alpine.js)

## 2. Layered Data Design

Data logic is segmented into three distinct layers to ensure separation of concerns:

### Persistence Layer (Migrations & Models)
- **Migrations (`database/migrations/`)**: The authoritative source for the database schema.
- **Models (`app/Models/`)**: Define business logic, attribute casting, and relationships.
- **Convention**: Use `$fillable` or `$guarded` for mass assignment protection and define explicit type hints for IDE support.

### Management Layer (Filament)
Filament acts as the bridge between the database and the user interface.
- **Resources**: Map Eloquent models to UI management modules.
- **Schemas**: Located in `public/js/filament/schemas` and `vendor/filament/schemas`, these define the structure of forms and tables.
- **Actions**: Server-side logic triggered by UI interactions (e.g., Bulk Delete, State Transitions).

### UI Mapping Layer
Specific JavaScript components handle specialized data types:
- **Standard Inputs**: `text-input.js`, `toggle.js`, `checkbox.js`.
- **Complex Metadata**: `key-value.js` handles JSON-based key-value pairs.
- **Rich Content**: `rich-editor.js` manages HTML/Markdown storage and file attachments.

## 3. Data Access Patterns

### Efficient Querying
To ensure high performance, the application implements the following patterns:
- **Eager Loading**: Always use `.with(['relationship'])` in Filament Resources or custom controllers to prevent N+1 query issues.
- **Livewire Integration**: Client-side filtering and searching are handled via Livewire, which intercepts UI events and performs targeted partial refreshes of the dataset.

### Search and Selection
The `Select` component (`vendor/filament/support/resources/js/utilities/select.js`) is the primary tool for related record selection.
- **Pattern**: Asynchronous searching is utilized for large datasets to keep the UI responsive.
- **Filtering**: Search queries are processed server-side through the `query()` method in the select utility.

## 4. Specialized Data Handling

### JSON Metadata
For dynamic configurations or flexible schemas, the system utilizes JSON columns:
- **Component**: `public/js/filament/forms/components/key-value.js`.
- **Implementation**: Data is stored as a `json` or `text` column in the database and cast to an `array` or `collection` in the Eloquent Model.

### File and Media Persistence
- **Rich Editor**: Managed via `vendor/filament/forms/resources/js/components/rich-editor.js`. It coordinates with Laravel's filesystem to handle uploads before saving the URL/path to the database.
- **File Uploads**: Uses Filament’s native file upload components which manage temporary storage and lifecycle events (uploading, uploaded, validation messages).

## 5. Development Workflow

### Schema Modifications
Directly modifying the database is strictly prohibited. Use Laravel Migrations:

```bash
# Create a new migration
php artisan make:migration add_field_to_table --table=target_table

# Execute migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback
```

### Seeding and Testing
Use seeders and factories to maintain consistent environments:
- **Seeders**: `database/seeders/DatabaseSeeder.php`
- **Factories**: `database/factories/` (used for generating dummy data during development/testing).

```bash
# Reset database and re-seed
php artisan migrate:fresh --seed
```

## 6. Best Practices

1.  **Atomic Transactions**: Wrap multi-table operations in `DB::transaction(function () { ... })` to ensure data integrity during failures.
2.  **Explicit Casting**: Define `$casts` in Models for dates, booleans, and JSON objects to ensure consistency between the DB and PHP/JS layers.
3.  **Soft Deletes**: Use the `SoftDeletes` trait on critical models (e.g., Users, Invoices) to allow for auditing and recovery.
4.  **Validation**: Rules should be defined at the Filament Resource level (`required`, `unique`, `exists`) to prevent corrupt data entry.
5.  **Relationship Protection**: Use `onDelete('restrict')` in migrations for critical foreign keys to prevent accidental data loss.
