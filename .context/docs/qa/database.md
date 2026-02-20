# Database (QA) — Fiscaut v4.1

This document describes how the Fiscaut v4.1 application persists and manages data, with a focus on **schema ownership**, **data access patterns**, and **admin (Filament) interactions** that affect the database. It is intended for developers working on features, migrations, performance, or QA validation.

---

## 1) Stack & Responsibilities

### Core technologies
- **Laravel** (10/11): application framework
- **Eloquent ORM**: models, relationships, casting, query building
- **Database**: configured via `.env` (PostgreSQL recommended for production)
- **Filament v3**: admin/management UI built on Livewire + Alpine.js

### “Source of truth” for the schema
- **Migrations (`database/migrations/`)** are the authoritative schema definition.
- Direct/manual database changes are **not** accepted in the project workflow—always codify schema changes in migrations.

---

## 2) Layered Data Design

The project follows a clear separation between how data is stored, how it is manipulated in PHP, and how it is presented/edited via Filament.

### 2.1 Persistence layer (Migrations & Models)
**Where**
- `database/migrations/`: tables, columns, indexes, constraints
- `app/Models/`: Eloquent models (casts, relationships, business rules)

**Conventions**
- Use `$fillable` or `$guarded` to control mass assignment.
- Define `$casts` for all non-trivial types (dates, booleans, JSON).
- Prefer explicit relationship methods and type-safe conventions.

**Example: JSON + boolean casting**
```php
class ExampleModel extends Model
{
    protected $casts = [
        'is_active' => 'boolean',
        'metadata'  => 'array',   // JSON column
        'created_at'=> 'datetime',
    ];
}
```

### 2.2 Management layer (Filament resources/forms/tables)
Filament resources map models to CRUD UIs. This layer is where:
- validation rules commonly live (required/unique/exists)
- searching/filtering is performed through Livewire interactions
- bulk actions can generate high-impact database operations

**Note on JS UI schemas/components**
The repository includes compiled/packaged Filament UI components under:
- `public/js/filament/schemas`
- `public/js/filament/schemas/components`
- `public/js/filament/forms/components`
- `public/js/filament/tables/components/columns`

These components influence **how** values are entered/edited (e.g., JSON key-value editor, rich text editor), and therefore have direct implications on **column types** and **casts**.

### 2.3 UI mapping layer (specialized input types)
Certain inputs are notable because they drive storage format and validation needs:

- **Key/Value editor**: `public/js/filament/forms/components/key-value.js`  
  Typically implies a `json` or `text` column plus `$casts` to `array`.

- **Rich editor**: `public/js/filament/forms/components/rich-editor.js`  
  Stores HTML/serialized content; may also store file references (paths/URLs).

- **Tags input**: `public/js/filament/forms/components/tags-input.js`  
  Often stored as JSON array or a normalized relation depending on domain needs.

- **Toggle / Checkbox columns**:
  - `public/js/filament/tables/components/columns/toggle.js`
  - `public/js/filament/tables/components/columns/checkbox.js`  
  These should map cleanly to boolean columns and be cast as `boolean`.

---

## 3) Data Access Patterns (Performance & Correctness)

### 3.1 Avoid N+1 queries (eager load relationships)
When building lists (especially in Filament tables), always eager load required relations:

```php
// Example pattern: ensure relationships used in table columns are eager loaded
$query = Model::query()->with(['relationshipA', 'relationshipB']);
```

**QA checks**
- Validate that list screens do not trigger dozens/hundreds of queries.
- Use Laravel Debugbar/Telescope to confirm eager loading is effective.

### 3.2 Livewire-driven filtering/searching
Filament uses Livewire to perform server-side searching/filtering while keeping UI reactive.
Practical implications:
- Add appropriate indexes for frequently searched columns.
- Ensure filters do not force full table scans on large datasets.

### 3.3 Transactions for multi-step writes
Wrap multi-table or multi-step operations in `DB::transaction()` to ensure atomicity:

```php
DB::transaction(function () use ($data) {
    $parent = ParentModel::create($data['parent']);
    $parent->children()->createMany($data['children']);
});
```

**QA checks**
- Simulate failures mid-operation and confirm no partial data remains.
- Verify that unique constraints + transactions behave as expected under concurrency.

---

## 4) Column Types & Storage Conventions

### 4.1 JSON metadata
Use JSON when the shape is dynamic and not worth normalizing.

**Database**
- Prefer `json`/`jsonb` (PostgreSQL) when possible.

**Model**
- Cast to `array` (or `collection` if you consistently use Laravel collections).

**Filament**
- The KeyValue component is the typical UI for editing this structure.

**QA checks**
- Confirm invalid JSON cannot be persisted.
- Confirm default values are handled consistently (e.g., `[]` vs `null`).

### 4.2 Rich text / HTML content
Rich editor fields often store HTML and may embed/upload files.

**Recommendations**
- Use `text`/`longText` columns.
- Validate length constraints if the DB engine has limits.
- Ensure sanitization/allowed tags rules are defined if content can be user-generated.

### 4.3 Booleans
Anything edited via toggle/checkbox should be:
- stored as `boolean`
- cast as `boolean` in the model

**QA checks**
- Confirm `null` vs `false` behavior is intentional (and consistent across UI + API).

---

## 5) Migrations Workflow (Required)

### 5.1 Creating and applying migrations
```bash
php artisan make:migration add_field_to_table --table=target_table
php artisan migrate
```

### 5.2 Rollbacks
```bash
php artisan migrate:rollback
```

### 5.3 Reset database (local/dev/testing)
```bash
php artisan migrate:fresh --seed
```

**Rules**
- Do not patch production manually.
- Every schema change must be reproducible from a fresh database.

---

## 6) Seeding, Factories, and Test Data

**Where**
- Seeders: `database/seeders/DatabaseSeeder.php`
- Factories: `database/factories/`

**Recommended practice**
- Seed “reference” data deterministically (stable IDs where needed).
- Use factories for randomized data in tests and QA load checks.

**QA checks**
- Ensure seeders can run repeatedly on a clean DB.
- Ensure tests do not depend on implicit DB state.

---

## 7) Constraints, Deletes, and Integrity

### 7.1 Foreign keys & delete behavior
For critical data, prefer restrictive deletes:

- `onDelete('restrict')` to prevent accidental cascading deletions
- alternatively `cascade` only when the child is meaningless without the parent

**QA checks**
- Attempt to delete a parent record and confirm correct behavior.
- Confirm application surfaces meaningful errors when restrict blocks a delete.

### 7.2 Soft deletes
For auditability and recovery, use soft deletes on critical models:
- `use SoftDeletes;`
- ensure Filament resources explicitly handle “trashed” scopes when needed

**QA checks**
- Confirm soft-deleted records are hidden from default queries.
- Confirm restore actions behave correctly (including relations and uniqueness).

---

## 8) Validation (Database + Application)

### 8.1 Application-level validation (Filament/Forms)
Prefer defining rules close to the input definition:
- `required`
- `unique`
- `exists`
- numeric/date constraints

### 8.2 Database-level enforcement
Back up validation with:
- unique indexes
- foreign keys
- not-null constraints when appropriate

**QA checks**
- Verify invalid states cannot be persisted by bypassing UI (e.g., direct requests).
- Verify meaningful error messages bubble up to the user/admin interface.

---

## 9) Practical QA Checklist (Database-Focused)

Use this list when reviewing new features or changes:

1. **Migrations**
   - [ ] New columns have correct types and defaults
   - [ ] Indexes added for frequently filtered/searched columns
   - [ ] Down migrations correctly reverse schema changes

2. **Models**
   - [ ] `$casts` defined for booleans/dates/JSON
   - [ ] Relationships are explicit and used with eager loading where needed

3. **Filament screens**
   - [ ] No N+1 queries on list/detail pages
   - [ ] Bulk actions are safe (confirm prompts, transactions where needed)

4. **Integrity**
   - [ ] Foreign keys and delete behavior match business rules
   - [ ] Soft delete behavior matches visibility expectations

5. **Performance**
   - [ ] Search/filter operations scale (indexes, avoid full scans)
   - [ ] Large relations use async selects/search (where applicable)

---

## Related Locations in This Repository

- Schema & persistence:
  - `database/migrations/`
  - `app/Models/`
  - `database/seeders/`
  - `database/factories/`

- Filament UI components influencing storage formats:
  - `public/js/filament/forms/components/key-value.js`
  - `public/js/filament/forms/components/rich-editor.js`
  - `public/js/filament/forms/components/tags-input.js`
  - `public/js/filament/tables/components/columns/toggle.js`
  - `public/js/filament/tables/components/columns/checkbox.js`
  - `public/js/filament/tables/components/columns/text-input.js`

If you add a new database-backed feature, update this document when it introduces new conventions (new JSON structures, new delete rules, new indexing strategy, or a new “source of truth” for any schema-related behavior).
