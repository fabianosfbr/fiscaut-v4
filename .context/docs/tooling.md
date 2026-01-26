# Tooling & Productivity Guide

This guide outlines the tools, scripts, and configurations used to streamline development for **Fiscaut v4.1**. Following these standards ensures a consistent environment and high code quality across the team.

---

## Tech Stack Overview

*   **Framework:** Laravel v12
*   **Admin Panel:** FilamentPHP v3+ (v5 branch)
*   **Frontend Logic:** Livewire v3+ (v4 branch) & Alpine.js
*   **Styling:** Tailwind CSS
*   **Development Environment:** Laravel Sail (Docker)

---

## Core Development Environment

### Laravel Sail
Fiscaut uses **Laravel Sail** as the primary Docker-based development environment. This ensures all developers work with identical versions of PHP, MySQL, and Redis.

*   **Start environment:** `./vendor/bin/sail up -d`
*   **Stop environment:** `./vendor/bin/sail stop`
*   **Run Artisan:** `./vendor/bin/sail artisan [command]`
*   **Run Composer:** `./vendor/bin/sail composer [command]`

> **Productivity Tip:** Add a shell alias to your `.zshrc` or `.bashrc`:
> `alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'`

### Asset Management
*   **PHP:** Managed via Composer.
*   **JavaScript:** Managed via NPM. Use `sail npm install` and `sail npm run dev` for local asset bundling.
*   **Filament Assets:** The project utilizes specialized JS components located in `public/js/filament/` and `vendor/filament/`.

---

## Code Quality & Automation

### Laravel Pint (Code Styling)
Fiscaut follows strict PHP styling conventions. Pint is used to automatically fix code style issues.

```bash
# Fix all files
sail bin pint

# Check for issues without fixing
sail bin pint --test
```

### Larastan (Static Analysis)
We use Larastan (a PHPStan wrapper) to catch potential bugs and type mismatches.

```bash
# Analyze the codebase
sail bin phpstan analyse
```

### IDE Helper
To improve autocompletion in IDEs (like VS Code or PhpStorm), generate helper files after creating new models or migrations:

```bash
sail artisan ide-helper:generate
sail artisan ide-helper:models -N
sail artisan ide-helper:meta
```

---

## Filament Frontend Tooling

The frontend is a hybrid of Livewire and complex JavaScript components. Understanding where these files reside is crucial for debugging.

### Component Architecture
The repository contains both vendor-published and local components:

| Component Type | Path |
| :--- | :--- |
| **Schemas** | `vendor/filament/schemas/resources/js/components` |
| **Forms/Tables** | `vendor/filament/forms/resources/js/components` |
| **Widgets** | `public/js/filament/widgets/components` |
| **Custom Columns** | `public/js/filament/tables/components/columns` |

### Key JavaScript Symbols & APIs
If you are writing custom Alpine.js logic or extending the UI, you can leverage these exported classes and functions:

*   **Notifications:** Use the `Notification` class in `vendor/filament/notifications/resources/js/Notification.js` to trigger UI alerts.
*   **DOM Utilities:** `findClosestLivewireComponent(el)` is available in `vendor/filament/support/resources/js/partials.js` to bridge the gap between Alpine and Livewire.
*   **Form Interaction:** 
    *   `Select` utility: `vendor/filament/support/resources/js/utilities/select.js`.
    *   `RichEditor` handlers: Found in `vendor/filament/forms/resources/js/components/rich-editor.js`.

---

## Testing Tools

### Real-time Event Testing
The project includes `FakeEcho`, `FakeChannel`, and `FakePresenceChannel` (located in `vendor/livewire/livewire/src/Features/SupportEvents/fake-echo.js`) which are useful for mocking broadcasting events in your test suites.

### Debugging Tools
*   **Whoops:** The error handler includes a custom JS interface (`vendor/filp/whoops/src/Whoops/Resources/js/whoops.base.js`) to provide interactive stack traces.
*   **Shiki PHP:** Used for syntax highlighting in the UI. Configuration for the Shiki binary is managed in `vendor/spatie/shiki-php/bin/shiki.js`.

---

## IDE Setup (Recommended)

### VS Code Extensions
1.  **PHP Intelephense:** Essential for advanced PHP support and symbol indexing.
2.  **Laravel Blade Snippets:** Syntax highlighting and snippets for `.blade.php`.
3.  **Tailwind CSS IntelliSense:** Autocomplete for utility classes.
4.  **Laravel Artisan:** Execute commands via the command palette.
5.  **EditorConfig for VS Code:** Ensures indentation matches the project's `.editorconfig`.

### Recommended Configuration
*   **Indentation:** 4 spaces for PHP, 2 spaces for JS/CSS.
*   **Line Endings:** LF (Unix).
*   **Encoding:** UTF-8.

---

## Productivity Workflows

### Artisan Tinker
Interacting with models and testing logic:
```bash
sail artisan tinker
```

### Log Monitoring
Monitor Laravel logs in real-time during development:
```bash
sail artisan tail
```

### Database Management
While Sail provides a database container, using a GUI like **TablePlus** or **DBeaver** is recommended.
*   **Host:** `127.0.0.1`
*   **Port:** `3306` (or the port defined in `.env`)
*   **User/Pass:** `sail` / `password`

---

## Cross-References
*   [Development Workflow](./development-workflow.md): Learn about branching and PR standards.
*   [Architecture Overview](./architecture.md): Understanding the structure of Fiscaut v4.1.
