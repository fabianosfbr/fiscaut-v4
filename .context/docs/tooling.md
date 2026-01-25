# Tooling & Productivity Guide

This guide outlines the tools, scripts, and configurations used to streamline development for **Fiscaut v4.1**. Following these standards ensures a consistent environment and high code quality across the team.

## Tech Stack Overview

*   **Framework:** Laravel v12
*   **Admin Panel:** FilamentPHP v3+ (v5 branch)
*   **Frontend Logic:** Livewire v3+ (v4 branch)
*   **Styling:** Tailwind CSS
*   **Development Environment:** Laravel Sail (Docker)

---

## Core Development Tools

### Laravel Sail
Fiscaut uses **Laravel Sail** as the primary Docker-based development environment. This ensures all developers work with identical versions of PHP, MySQL, and Redis.

*   **Start environment:** `./vendor/bin/sail up -d`
*   **Stop environment:** `./vendor/bin/sail stop`
*   **Run Artisan:** `./vendor/bin/sail artisan [command]`

> **Productivity Tip:** Add a shell alias to your `.zshrc` or `.bashrc` to run commands faster:
> `alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'`

### Dependency Management
*   **PHP:** Managed via Composer. Always run `sail composer install` after pulling changes or updating `composer.json`.
*   **JavaScript:** Managed via NPM. Use `sail npm install` and `sail npm run dev` for local asset bundling.

---

## Code Quality & Automation

To maintain the integrity of the codebase, the following tools are integrated and should be run frequently.

### Laravel Pint (Code Styling)
Fiscaut follows strict PHP styling conventions. Pint is used to automatically fix code style issues to match the project standards.

```bash
# Fix all files
sail bin pint

# Check for issues without fixing
sail bin pint --test
```

### Larastan (Static Analysis)
We use Larastan (a PHPStan wrapper) to catch potential bugs and type mismatches before they reach production.

```bash
# Analyze the codebase
sail bin phpstan analyse
```

### IDE Helper
To improve autocompletion in IDEs (like VS Code or PhpStorm), generate the helper files periodically, especially after creating new models or migrations:

```bash
sail artisan ide-helper:generate
sail artisan ide-helper:models -N
sail artisan ide-helper:meta
```

---

## IDE Setup (Recommended)

### VS Code Extensions
For the best experience with Laravel 12 and Filament, we recommend the following extensions:

1.  **PHP Intelephense:** Essential for advanced PHP support and symbol indexing.
2.  **Laravel Blade Snippets:** Syntax highlighting and snippets for `.blade.php` files.
3.  **Tailwind CSS IntelliSense:** Autocomplete for utility classes.
4.  **Laravel Artisan:** Execute Artisan commands directly from the VS Code command palette.
5.  **EditorConfig for VS Code:** Ensures that indentation and line endings match the project's `.editorconfig`.

### Configuration
Ensure your editor is set to:
*   **Indentation:** 4 spaces for PHP, 2 spaces for JS/CSS.
*   **Line Endings:** LF (Unix).
*   **Encoding:** UTF-8.

---

## Filament & Frontend Tooling

Fiscaut is heavily built on **Filament**, and developers should be familiar with how its assets and components are structured.

### Component Architecture
The repository contains both vendor-published and local components:
*   **Schemas:** Logic for layout components like `Tabs` and `Wizard` (`vendor/filament/schemas/resources/js/components`).
*   **Forms & Tables:** Interactive JS components for fields (e.g., `Select`, `DateTimePicker`, `RichEditor`) and columns.
*   **Widgets:** Dashboard components located under `public/js/filament/widgets/components`.

### Useful Public APIs
When building custom frontend logic or interacting with Filament components via JavaScript, the following exported functions are available:

| Feature | Exported Function | Location |
| :--- | :--- | :--- |
| **Notifications** | `Notification` class | `vendor/filament/notifications/...` |
| **Rich Editor** | `richEditorFormComponent` | `vendor/filament/forms/...` |
| **Selects** | `selectFormComponent` | `vendor/filament/forms/...` |
| **Stats** | `statsOverviewStatChart` | `vendor/filament/widgets/...` |

To rebuild Filament-related assets if you are customizing core components:
```bash
sail npm run build
```

---

## Productivity Tips

### Artisan Tinker
Use Tinker to interact with the database and test logic in a REPL environment. This is highly effective for debugging Models and Service classes.
```bash
sail artisan tinker
```

### Database Management
While Sail provides a database container, using a GUI like **TablePlus** or **DBeaver** is recommended for data exploration.
*   **Host:** `127.0.0.1`
*   **Port:** `3306` (or the port defined in your `.env`)
*   **User:** `sail`
*   **Password:** `password`

### Monitoring Logs
Keep a terminal window open to monitor Laravel logs in real-time during development:
```bash
sail artisan tail
```

---

## Cross-References
*   [Development Workflow](./development-workflow.md): Learn about branching and PR standards.
*   [Architecture Overview](./architecture.md): Understanding the structure of Fiscaut v4.1.
