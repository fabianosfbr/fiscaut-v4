# Getting Started (Dev & QA) — Fiscaut v4.1

This guide helps you set up **Fiscaut v4.1** locally for development and QA testing. Fiscaut is a Laravel application built with the **TALL stack** (Tailwind, Alpine, Laravel, Livewire) and uses **Filament v3** for admin/data management UI.

---

## Prerequisites

Make sure your workstation meets these minimum versions:

- **PHP**: 8.2+
- **Composer**: latest stable
- **Node.js**: 18.x LTS+
- **NPM**: comes with Node (or use pnpm/yarn if the project supports it)
- **Database**: MySQL 8+, PostgreSQL, or SQLite
- **Git**

Recommended (not required): Redis, Mailpit, Docker/Sail (if your team uses containers).

---

## Repository Setup

Clone the repository and enter the project directory:

```bash
git clone <repository-url>
cd fiscaut-v4.1
```

---

## Install Dependencies

Install backend (PHP) and frontend (JS) dependencies:

```bash
composer install
npm install
```

If you encounter memory issues during install:
- For Composer: consider `COMPOSER_MEMORY_LIMIT=-1 composer install`
- For Node: ensure Node 18+ is in use

---

## Environment Configuration

Create the environment file and generate the application key:

```bash
cp .env.example .env
php artisan key:generate
```

### Configure the database

Open `.env` and set the database connection variables:

- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

Example (MySQL):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fiscaut
DB_USERNAME=root
DB_PASSWORD=
```

---

## Database Initialization

Run migrations to create the schema:

```bash
php artisan migrate
```

If your project includes seeders and you need test data:

```bash
php artisan db:seed
# or
php artisan migrate --seed
```

---

## Run the Application (Local Dev)

You typically run **two processes** in parallel:

### Terminal 1 — Backend (Laravel)

```bash
php artisan serve
```

App will be available at:

- `http://127.0.0.1:8000`

### Terminal 2 — Frontend (Vite)

```bash
npm run dev
```

This enables hot module replacement (HMR) for frontend assets.

---

## Build Assets for Production-like Testing

To compile and minify assets (useful for QA verification of production builds):

```bash
npm run build
```

---

## Project Structure (What to Know)

Fiscaut is organized around Laravel conventions, with Filament providing most admin UI building blocks.

### Key directories

| Path | Purpose |
| --- | --- |
| `app/Filament/Resources` | Filament Resources defining CRUD pages, forms, and tables. |
| `vendor/filament/` | Filament framework core (Forms, Tables, Actions, Notifications). |
| `public/js/filament/schemas` | Compiled Filament schema JS (built assets used by the UI). |
| `public/js/filament/schemas/components` | Compiled JS components (e.g., Tabs/Wizard). |
| `public/js/filament/forms/components` | Compiled JS for form inputs (Textarea, Rich Editor, Tags Input, etc.). |
| `public/js/filament/tables/components/columns` | Compiled JS for table column interactivity (Toggle/TextInput/Checkbox). |
| `resources/js/` | Application-level frontend code (if the project adds custom JS). |

### How Filament fits in

- **Filament Resources** (PHP) define:
  - Form schemas (fields/components)
  - Table schemas (columns/filters/actions)
  - CRUD pages
- **Livewire** powers reactivity (server-driven UI updates).
- **Filament JS assets** (compiled into `public/js/filament/...`) provide client-side behavior for complex components like rich editors, tags input, wizards, and table column controls.

---

## Common Developer & QA Commands

### Artisan (Laravel)

| Command | Description |
| --- | --- |
| `php artisan test` | Run test suite (Pest/PHPUnit depending on project setup). |
| `php artisan route:list` | Inspect available routes. |
| `php artisan migrate:fresh --seed` | Rebuild DB from scratch and seed (useful in QA). |
| `php artisan config:clear` | Clear config cache (after `.env` edits, etc.). |
| `php artisan cache:clear` | Clear application cache. |
| `php artisan view:clear` | Clear compiled Blade views. |
| `php artisan filament:assets` | Republish Filament assets (useful when UI assets look stale). |
| `php artisan filament:optimize` | Optimize/cache Filament components for performance. |
| `php artisan make:filament-resource ModelName` | Generate a new Filament Resource scaffold. |

### Node/Vite

| Command | Description |
| --- | --- |
| `npm run dev` | Start Vite dev server (HMR). |
| `npm run build` | Build production assets. |

---

## Troubleshooting

### Assets not loading / broken UI styles

1. Ensure Vite is running:
   ```bash
   npm run dev
   ```
2. If Filament assets appear outdated or missing:
   ```bash
   php artisan filament:assets
   ```
3. Hard refresh your browser (cache can mask asset updates).

### Database connection errors

- Recheck `.env` values (`DB_HOST`, `DB_PORT`, credentials).
- Ensure your DB server is running.
- If using Docker/Sail, confirm containers are up and ports match.

### Permissions issues (Linux/macOS)

Make sure Laravel can write to `storage/` and `bootstrap/cache/`:

```bash
chmod -R 775 storage bootstrap/cache
```

If you’re using Docker, fix permissions via container user/group strategy (team-specific).

### Livewire components not updating

- Ensure unique `wire:key` values where lists/loops render dynamic components.
- Clear caches:
  ```bash
  php artisan view:clear
  php artisan cache:clear
  ```

---

## Related Documentation / References

- Filament Resources and UI behavior: `app/Filament/Resources`
- Compiled Filament frontend assets:
  - `public/js/filament/schemas`
  - `public/js/filament/forms/components`
  - `public/js/filament/tables/components/columns`

If you need onboarding for a specific module (resources, roles/permissions, domain rules), document it as a separate page and link it from here.
