# Routing & Navigation (QA)

This document describes the application’s routing and navigation architecture and provides a QA checklist for validating routing-related changes. The app uses a hybrid model:

1. **Laravel routing** for public/non-admin endpoints.
2. **Filament routing** for the admin panel (Resources and Pages).
3. **Livewire SPA navigation** (`wire:navigate`) to avoid full page reloads in the admin UI.

---

## Mental Model: 3 Routing Layers

| Layer | What it covers | Where it lives | Typical URLs |
|---|---|---|---|
| Laravel Web/API | Public pages, auth, integrations, API endpoints | `routes/web.php`, `routes/api.php` | `/`, `/login`, `/api/...` |
| Filament Admin | Admin CRUD + admin tools | `app/Providers/Filament/AdminPanelProvider.php`, `app/Filament/...` | `/admin/...` |
| Livewire SPA Navigation | Client-side interception of admin navigation | Filament/Livewire JS (vendor) | Same URLs, but transitions are SPA-like |

---

## 1) Standard Laravel Routes

### Web routes (`routes/web.php`)
Use standard Laravel routes for anything **outside** the Filament admin panel, such as:

- Root redirects (e.g., `/` → `/admin/login`)
- Public landing pages
- Custom authentication flows (if any)
- Legacy routes that don’t belong in Filament

### API routes (`routes/api.php`)
Use for programmatic access (integrations/mobile). Keep admin UI endpoints in Filament.

### Console routes (`routes/console.php`)
Closure-based Artisan commands; helpful for scheduled tasks and maintenance:

```php
Artisan::command('app:sync-data', function () {
    $this->info('Syncing data...');
})->purpose('Synchronize external data sources');
```

---

## 2) Filament Admin Routing

Filament registers admin routes automatically through the **Admin Panel Provider**.

### Central configuration
- **File:** `app/Providers/Filament/AdminPanelProvider.php`
- **Role:** Defines the admin panel path/prefix (commonly `/admin`), middleware stack, auth behavior, and panel features.

### Resource routing (CRUD)
Every Filament Resource under:

- **Directory:** `app/Filament/Resources`

generates a conventional set of routes (names and URLs) for CRUD pages. For example, `UserResource` typically maps to:

- List: `/admin/users`
- Create: `/admin/users/create`
- Edit: `/admin/users/{record}/edit`
- View (if enabled): `/admin/users/{record}`

**Where the hierarchy is defined:** inside the Resource’s `getPages()` method (this also affects breadcrumbs and URL patterns).

### Custom Page routing (tools, dashboards, workflows)
Custom admin pages live in:

- **Directory:** `app/Filament/Pages`

You can control the URL via the `$slug` property:

```php
namespace App\Filament\Pages;

use Filament\Pages\Page;

class ConfiguracaoGeralPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.configuracoes.configuracao-geral-page';

    // Accessible at: /admin/configuracoes/geral
    protected static ?string $slug = 'configuracoes/geral';
}
```

#### Slug conventions
- Treat `$slug` as a URL path fragment relative to the admin prefix.
- Keep slugs unique to avoid collisions with Resources or other Pages.

### Middleware and access control
Filament admin routes are protected by Filament’s middleware stack, configured in the panel provider. Common responsibilities include:

- **Authentication**: ensuring the user is logged in
- **Filament bootstrapping**: dispatching “serving Filament” events and loading panel assets

If admin routes “work locally but fail in staging/production”, verify:
- middleware configuration
- auth guard/session configuration
- route caching (see below)

---

## 3) Livewire SPA Navigation (Admin UX)

The admin UI uses **Livewire’s SPA navigation** (via `wire:navigate`) to make transitions feel instantaneous:

- The browser does **not** fully reload the page.
- Only the relevant page content updates.
- History/back-forward behave like a SPA (when configured correctly).

### Unsaved changes protection (“dirty form” guard)
Because navigation can be intercepted, the app must prevent data loss when leaving a modified form.

- **Script:** `vendor/filament/filament/resources/js/unsaved-changes-alert.js`
- **Key behavior:**
  - Detects whether a form is “dirty”
  - Intercepts navigation
  - Shows a browser confirmation dialog

**What QA should see:**
- On an edit/create form, change a field
- Click a sidebar link / breadcrumb / back button
- A confirmation dialog appears and blocks navigation unless confirmed

### State persistence during SPA navigation
Since the page isn’t fully reloaded, some UI state naturally persists across transitions:

- **Notifications** remain visible across navigation
  - Related vendor code: `vendor/filament/notifications/resources/js/Notification.js`
- **Tables** preserve sort/filter/search state via URL query strings, e.g.:
  - `?tableFilters[status][value]=active`

This is expected and should be considered during QA validation (especially when using the back button).

---

## 4) Patterns: When to Use What

| Need | Use | Location |
|---|---|---|
| Standard CRUD for an entity | **Filament Resource** | `app/Filament/Resources` |
| Admin tool / dashboard / multi-step flow | **Filament Page** | `app/Filament/Pages` |
| Public site pages, redirects, bespoke endpoints | **Laravel web routes** | `routes/web.php` |
| External integration endpoints | **Laravel API routes** | `routes/api.php` |

---

## 5) QA Verification Checklist (Routing & Navigation)

Use this checklist whenever:
- a Resource/Page is added/renamed
- slugs change
- navigation items change
- Livewire/Filament upgrades occur
- middleware/auth changes occur

### A) Route & slug correctness
1. **Slug uniqueness**
   - Ensure Page `$slug` values don’t conflict with other Pages or Resource base paths.
2. **Expected URLs**
   - Confirm the admin prefix + slug matches the intended URL (e.g., `/admin/configuracoes/geral`).
3. **Breadcrumb accuracy**
   - Ensure breadcrumbs reflect the hierarchy defined by Resource `getPages()` and the Page slug.

### B) Navigation behavior (SPA vs full reload)
4. **SPA navigation active**
   - Clicking sidebar links should not cause a full “flash” reload.
   - Browser history should update correctly.
5. **Back/Forward buttons**
   - Back returns to the previous page **and** restores relevant state where applicable (filters in URL, etc.).

### C) Unsaved changes guard
6. **Dirty state confirmation**
   - Steps:
     1. Open a Resource create/edit form
     2. Modify a field (do not save)
     3. Navigate away (sidebar link / breadcrumb / browser back)
   - Expected: confirmation dialog appears; cancel keeps you on the page.

### D) Access control & middleware
7. **Auth protection**
   - Logged-out user should be redirected to login when hitting admin URLs directly.
8. **Role/permission checks (if applicable)**
   - Validate the correct access restrictions for sensitive resources/pages.

### E) Route caching and deployment sanity
9. **Route cache**
   - If new/changed routes aren’t visible, clear route cache:
     ```bash
     php artisan route:clear
     ```
   - In environments that cache config/routes aggressively, ensure deployment steps include cache rebuilds as needed.

---

## 6) Related Files / References

- **Admin panel configuration**
  - `app/Providers/Filament/AdminPanelProvider.php`
- **Filament Resources (CRUD routing)**
  - `app/Filament/Resources/*`
- **Filament Pages (custom slugs)**
  - `app/Filament/Pages/*`
- **Unsaved changes navigation guard (vendor)**
  - `vendor/filament/filament/resources/js/unsaved-changes-alert.js`
- **Notifications persistence (vendor)**
  - `vendor/filament/notifications/resources/js/Notification.js`

---

## Common QA Failures and Likely Causes

- **404 on a new admin page**
  - Slug conflict, page not registered, or route cache not cleared
- **Navigation causes full reload**
  - `wire:navigate` not applied / Livewire SPA disabled / JS asset issues
- **No unsaved changes prompt**
  - Dirty tracking not triggered, custom form components bypassing Filament form handling, or vendor JS not loaded
- **Back button doesn’t restore expected state**
  - State not encoded in URL (filters/sort), or custom page state not persisted
