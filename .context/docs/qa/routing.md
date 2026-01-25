# Routing and Navigation System

This document outlines the routing architecture of the application, which utilizes a hybrid approach combining standard Laravel routing, Filament's resource-based routing, and Livewire's SPA navigation features.

## Overview

The routing system is divided into three main layers:
1.  **Laravel Web Routes**: Standard routes for public pages and authentication.
2.  **Filament Admin Routes**: Automated routing for resources and administration dashboards.
3.  **Livewire SPA Navigation**: Intercepted navigation for a seamless user experience.

---

## 1. Standard Laravel Routes

Defined in `routes/web.php` and `routes/api.php`. These routes handle functionality outside the administrative panel.

### Web Routes (`routes/web.php`)
Standard entry points for the application. Typical uses include:
- Redirecting the root URL (`/`) to the admin login.
- Publicly accessible landing pages.
- Custom authentication flows.

### Console Routes (`routes/console.php`)
Closure-based Artisan commands. These are used for CLI interactions and scheduled tasks.
```php
Artisan::command('app:sync-data', function () {
    $this->info('Syncing data...');
})->purpose('Synchronize external data sources');
```

---

## 2. Filament Admin Routing

Filament automatically manages routes for Resources and Pages. These are registered via the `AdminPanelProvider`.

### Resource Routing
Every Resource class in `app/Filament/Resources` automatically generates a set of named routes:
- **List**: `/admin/resources`
- **Create**: `/admin/resources/create`
- **Edit**: `/admin/resources/{record}/edit`
- **View**: `/admin/resources/{record}`

### Custom Page Routing
Custom pages are defined in `app/Filament/Pages`. You can customize the URL by setting the `$slug` property.

```php
namespace App\Filament\Pages;

use Filament\Pages\Page;

class ConfiguracaoGeralPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.configuracoes.configuracao-geral-page';
    
    // Accessible at /admin/configuracoes/geral
    protected static ?string $slug = 'configuracoes/geral';
}
```

### Route Middleware
Admin routes are protected by default via the Filament middleware stack, which includes:
- `Authenticate`: Ensures the user is logged in.
- `DispatchServingFilamentEvent`: Initializes Filament-specific client-side assets.

---

## 3. SPA Navigation (Livewire)

The application uses Livewire's `wire:navigate` to provide a Single Page Application (SPA) experience. This speeds up transitions by only updating the page body.

### Unsaved Changes Protection
To prevent data loss during SPA transitions, the system monitors form states. If a user attempts to navigate away with dirty form fields, the system intercepts the event.

**Key Logic:**
- **Script**: `unsaved-changes-alert.js`
- **Behavior**: Listens for Livewire navigation events. If `shouldPreventNavigation()` returns true, a confirmation dialog is displayed.

### State Persistence
Because the page does not fully reload:
- **Notifications**: Filament notifications persist across transitions.
- **Filters/Search**: Table states (sorting, filtering) are maintained via URL query parameters (e.g., `?tableFilters[status][value]=active`).

---

## 4. Routing Best Practices & Patterns

| Route Category | Location | Recommendation |
| :--- | :--- | :--- |
| **CRUD Entities** | `app/Filament/Resources` | Use standard Filament Resources for database management. |
| **Complex Tools** | `app/Filament/Pages` | Use custom Pages for dashboards or multi-step processes. |
| **Public API** | `routes/api.php` | Use for external integrations or mobile app endpoints. |
| **Legacy/Custom** | `routes/web.php` | Only for routes that cannot be handled by Filament. |

---

## 5. QA Verification Checklist

When testing routing or navigation changes, verify the following:

1.  **Slug Uniqueness**: Ensure custom slugs in `app/Filament/Pages` do not conflict with Resource names.
2.  **Navigation Links**: Check that sidebar links correctly reflect the defined `$slug`.
3.  **Back Button Behavior**: Verify that Livewire navigation correctly updates the browser history and that the "Back" button functions as expected.
4.  **Dirty State Alert**:
    - Open a resource edit form.
    - Modify a field.
    - Click a sidebar link.
    - **Expected**: A browser alert should block navigation until confirmed.
5.  **Breadcrumb Accuracy**: Ensure breadcrumbs correctly reflect the hierarchy defined in the Filament Resource `getPages()` method.
6.  **Route Cache**: After adding new routes in `web.php`, clear the cache to ensure visibility:
    ```bash
    php artisan route:clear
    ```

## 6. Related Components

- **AdminPanelProvider**: `app/Providers/Filament/AdminPanelProvider.php` (Central configuration).
- **Navigation Utilities**: Located in `vendor/filament/support/resources/js` and `resources/js/app.js`.
- **Notification Manager**: `vendor/filament/notifications/resources/js/Notification.js`.
