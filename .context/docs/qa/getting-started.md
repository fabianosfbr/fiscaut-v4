# Getting Started with Fiscaut v4.1

Welcome to the Fiscaut v4.1 development environment. This project is built on the **Laravel** framework, leveraging **Filament v3** for the administrative interface and **Livewire** for reactive, real-time components.

## Prerequisites

Ensure your local environment meets the following requirements:

*   **PHP:** 8.2 or higher
*   **Node.js:** v18.x (LTS) or higher
*   **Composer:** Latest stable version
*   **NPM:** Included with Node.js
*   **Database:** MySQL 8.0+, PostgreSQL, or SQLite

## Installation

Follow these steps to initialize the project:

### 1. Clone the Repository
```bash
git clone <repository-url>
cd fiscaut-v4.1
```

### 2. Install Dependencies
Install both backend PHP packages and frontend JavaScript dependencies:
```bash
# Install PHP dependencies
composer install

# Install Frontend dependencies
npm install
```

### 3. Environment Configuration
Copy the template environment file and generate a unique application key:
```bash
cp .env.example .env
php artisan key:generate
```

> **Note:** Open the `.env` file and configure your local database connection:
> - `DB_CONNECTION=mysql`
> - `DB_DATABASE=fiscaut_db`
> - `DB_USERNAME=root`
> - `DB_PASSWORD=`

### 4. Database Setup
Run the migrations to create the required tables and schema:
```bash
php artisan migrate
```

### 5. Start Development Servers
You need to run both the Laravel server and the Vite development server simultaneously:

**Terminal 1 (PHP):**
```bash
php artisan serve
```

**Terminal 2 (Vite):**
```bash
npm run dev
```

The application will be accessible at `http://127.0.0.1:8000`.

---

## Core Architecture

Fiscaut v4.1 follows a modern Laravel TALL stack (Tailwind, Alpine.js, Laravel, Livewire) architecture, with a heavy emphasis on the Filament ecosystem.

### Key Directories

| Path | Description |
| :--- | :--- |
| `app/Filament/Resources` | Backend CRUD resource definitions. |
| `public/js/filament` | Compiled Filament assets and custom schema components. |
| `vendor/filament/` | Core framework logic for Forms, Tables, and Actions. |
| `resources/js/app.js` | Main entry point for custom JavaScript logic. |

### Filament Framework Integration

The project relies on Filament for its administrative UI. Key logic points include:

*   **Forms:** Located in `vendor/filament/forms`. Custom logic for components like `RichEditor` and `Select` is often augmented in `vendor/filament/support/resources/js/utilities`.
*   **Tables:** Located in `vendor/filament/tables`. Interactive columns (Toggle, Text, Checkbox) are handled via Livewire-synchronized components.
*   **Notifications:** Managed through `vendor/filament/notifications`. You can trigger these in PHP via `Notification::make()->send()`.

---

## Developer Tooling

### Useful Artisan Commands

| Command | Purpose |
| :--- | :--- |
| `php artisan make:filament-resource` | Generates a new CRUD resource (Model, Pages, Resource class). |
| `php artisan make:filament-widget` | Creates a new dashboard widget (e.g., Stats or Charts). |
| `php artisan filament:optimize` | Caches Filament components for better production performance. |
| `php artisan test` | Executes the PHPUnit/Pest test suite. |

### Frontend Build Pipeline
*   **Development:** `npm run dev` (Hot Module Replacement enabled).
*   **Production:** `npm run build` (Minifies assets and generates manifests).

---

## Troubleshooting

*   **Missing Styles/Scripts:** Ensure `npm run dev` is running. If icons are missing, try `php artisan filament:assets`.
*   **Permission Denied:** Ensure the `storage` and `bootstrap/cache` directories are writable by your system user (`chmod -R 775 storage bootstrap/cache`).
*   **White Screen / Class Not Found:** If you've recently updated code or installed packages, run `composer dump-autoload` and `php artisan view:clear`.
*   **Database Errors:** Verify your `.env` credentials and ensure the database specified in `DB_DATABASE` actually exists.
