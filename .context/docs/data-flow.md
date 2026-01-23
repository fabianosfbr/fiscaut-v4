# Data Flow & Integrations

## Data Flow & Integrations
Data in Fiscaut v4.1 primarily flows from user inputs in the Filament Admin Panel to the MySQL database via Eloquent Models. External integrations are minimal in the core structure but may exist for specific fiscal services.

## Module Dependencies
- **app/Filament/** → Depends on `app/Models` and `Filament` vendor packages.
- **app/Http/** → Depends on `app/Models`.
- **app/Models/** → Depends on `Illuminate\Database\Eloquent`.
- **database/** → Depends on Schema definitions.

## Service Layer
While a strict Service Layer is not enforced by default in Laravel, logic often resides in:
- **Filament Resources**: `app/Filament/Resources/*Resource.php` (Handling UI & Persistence logic).
- **Actions**: `app/Actions` (if present, for reusable business logic).
- **Models**: `app/Models` (Business logic related to data).

## High-level Flow
1. **Input**: User interacts with a Filament Form or Table.
2. **Processing**:
   - Livewire intercepts the interaction.
   - Validation rules in the Resource/Form are applied.
   - Authorization policies (`app/Policies`) are checked.
3. **Persistence**: Validated data is saved to MySQL via Eloquent.
4. **Feedback**: UI updates via Livewire DOM diffing or Flash notifications.

## Internal Movement
- **Events & Listeners**: Used for side effects (e.g., `UserRegistered` -> `SendWelcomeEmail`).
- **Jobs**: Long-running tasks offloaded to the queue (e.g., generating fiscal reports).

## External Integrations
- **Database**: MySQL (Connection via PDO).
- **Filesystem**: Local or S3 (for document storage).

## Observability & Failure Modes
- **Logs**: Laravel writes to `storage/logs/laravel.log`.
- **Exceptions**: Handled by `bootstrap/app.php` and rendered to the user (or debug page in local).
- **Validation Errors**: Automatically displayed in Filament forms.

## Cross-References
- [architecture.md](./architecture.md)
