# Getting Started with Fiscaut v4.1

Welcome to the development and testing environment for **Fiscaut v4.1**. This platform is a modern enterprise application built on the **Laravel** framework, utilizing the **TALL stack** (Tailwind CSS, Alpine.js, Laravel, and Livewire) with **Filament v3** as the primary administrative and data management engine.

---

## Prerequisites

Ensure your local machine satisfies the following version requirements:

*   **PHP:** 8.2 or higher
*   **Node.js:** v18.x (LTS) or higher
*   **Package Managers:** Composer (PHP) and NPM (JavaScript)
*   **Database:** MySQL 8.0+, PostgreSQL, or SQLite
*   **Tools:** Git

---

## Installation Guide

Follow these steps to set up your local development instance:

### 1. Repository Setup
```bash
git clone <repository-url>
cd fiscaut-v4.1
```

### 2. Dependency Management
Install the backend and frontend dependencies:
```bash
# Install PHP packages
composer install

# Install JavaScript packages
npm install
```

### 3. Environment Configuration
Create your local environment file and generate the application security key:
```bash
cp .env.example .env
php artisan key:generate
```

> **Configuration:** Open `.env` and update the `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` settings to match your local database server.

### 4. Database Initialization
Run the migrations to build the schema:
```bash
php artisan migrate
```

---

## Development Workflow

To run the application, you must execute the PHP server and the frontend asset compiler simultaneously.

### Running the Application
*   **Terminal 1 (Backend):** `php artisan serve` (Accessible at `http://127.0.0.1:8000`)
*   **Terminal 2 (Frontend/Vite):** `npm run dev` (Enables Hot Module Replacement)

### Building for Production
To compile and minify assets for testing in a production-like environment:
```bash
npm run build
```

---

## Architecture and File Structure

Fiscaut v4.1 follows a modular architecture where Filament handles the UI components and Livewire manages reactivity.

### Directory Map

| Path | Purpose |
| :--- | :--- |
| `app/Filament/Resources` | PHP definitions for CRUD interfaces, forms, and tables. |
| `vendor/filament/` | Core framework logic (Forms, Tables, Actions, Notifications). |
| `public/js/filament/schemas` | Compiled JavaScript components for Filament schemas. |
| `vendor/filament/support/resources/js/utilities` | Core JS utility functions (e.g., `Select`, `Pluralize`). |
| `resources/js/` | Custom application-level JavaScript and assets. |

### Core Components

The application utilizes several specialized components for data entry and display:

*   **Forms:** Located in `vendor/filament/forms/resources/js/components`. Key components include `RichEditor`, `Select`, `FileUpload`, and `TagsInput`.
*   **Tables:** Located in `vendor/filament/tables/resources/js/components`. Includes interactive columns like `ToggleColumn`, `TextInputColumn`, and `CheckboxColumn`.
*   **Notifications:** Managed via `vendor/filament/notifications`. Use `Notification::make()` in PHP to trigger real-time alerts.

---

## Public API and Utilities

For developers extending the frontend, the following utilities are frequently used:

*   **`Select` Utility:** Located at `vendor/filament/support/resources/js/utilities/select.js`. Provides advanced dropdown and selection logic.
*   **Livewire Integration:** The function `findClosestLivewireComponent` is essential for scripts interacting with Livewire's DOM structure.
*   **Rich Editor Extensions:** Custom extensions for the Trix-based editor are found in `vendor/filament/forms/resources/js/components/rich-editor/extensions.js`.

---

## Essential Artisan Commands

| Command | Description |
| :--- | :--- |
| `php artisan make:filament-resource` | Create a new model-based CRUD interface. |
| `php artisan filament:assets` | Republish Filament's frontend assets. |
| `php artisan filament:optimize` | Cache components for performance. |
| `php artisan test` | Run the test suite (Pest/PHPUnit). |
| `php artisan route:list` | View all registered application routes. |

---

## Troubleshooting

*   **Assets Not Loading:** Ensure `npm run dev` is active. If styles appear broken, run `php artisan filament:assets` to refresh vendor files.
*   **Database Connection Issues:** Double-check the `DB_PORT` and `DB_HOST` in your `.env`. If using Docker/Sail, ensure the containers are running.
*   **Permission Errors:** Ensure the web server has write access to `storage/` and `bootstrap/cache/`:
    ```bash
    chmod -R 775 storage bootstrap/cache
    ```
*   **Component Synchronization:** If Livewire components are not updating, verify that the `wire:key` attributes are unique within your Blade templates.
