# Middleware in Fiscaut v4.1

Middleware functions act as a bridge between a request and a response. They are primarily used for filtering HTTP requests entering your application. In the Fiscaut v4.1 architecture (Laravel + Filament), middleware handles critical tasks such as authentication, authorization, localization, and state management.

## Purpose

In this project, middleware is used to:
1.  **Verify Authentication**: Ensure users are logged in before accessing protected routes.
2.  **Role & Permission Check**: Validate if a user has the necessary permissions to access specific Filament resources.
3.  **Data Sanitization**: Trim strings and convert empty strings to null.
4.  **Security**: Prevent Cross-Site Request Forgery (CSRF) and handle CORS.
5.  **State Management**: Initialize Livewire and Filament state for the dashboard.

## Global vs. Route Middleware

### Global Middleware
These run on every HTTP request to the application. They are defined in `app/Http/Kernel.php` (or `bootstrap/app.php` in newer Laravel versions).
- `TrustProxies`: Handles SSL termination behind load balancers.
- `VerifyCsrfToken`: Protects against CSRF attacks.
- `ShareErrorsFromSession`: Makes validation errors available to views.

### Filament/Route Middleware
Filament uses a specific stack to manage the administrative panel state. These are often defined in the Panel Provider (e.g., `app/Providers/Filament/AdminPanelProvider.php`):

```php
->middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    AuthenticateSession::class,
    ShareErrorsFromSession::class,
    VerifyCsrfToken::class,
    SubstituteBindings::class,
    DisableBladeIconComponents::class,
    DispatchServingFilamentEvent::class,
])
->authMiddleware([
    Authenticate::class,
]);
```

## Creating Custom Middleware

To create a new middleware for QA or specific business logic:

1.  **Generate the Middleware**:
    ```bash
    php artisan make:middleware EnsureUserHasAccess
    ```

2.  **Implement Logic**:
    In `app/Http/Middleware/EnsureUserHasAccess.php`:
    ```php
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->can_access_qa_tools) {
            abort(403);
        }

        return $next($request);
    }
    ```

## Testing & QA Middleware

When debugging or testing, middleware can be used to simulate specific environments or bypass certain checks in a controlled way.

### Debugging Middleware Execution
If a request is failing or redirecting unexpectedly, you can debug the middleware stack using:
```bash
php artisan route:list
```
This command shows exactly which middleware are applied to specific routes, including the Filament admin routes.

### Bypassing Middleware in Tests
In the context of QA and automated testing (Feature tests), you can bypass middleware using the `withoutMiddleware()` method:

```php
public function test_admin_page_can_be_rendered()
{
    $response = $this->withoutMiddleware()
                     ->get('/admin');

    $response->assertStatus(200);
}
```

## Common Project Middleware

| Middleware | Description |
| :--- | :--- |
| `Authenticate` | Redirects unauthenticated users to the login page. |
| `VerifyCsrfToken` | Validates CSRF tokens for POST, PUT, PATCH, and DELETE requests. |
| `DispatchServingFilamentEvent` | Essential for Filament components to initialize correctly. |
| `SubstituteBindings` | Automatically injects model instances into routes based on IDs. |

## Localization Middleware
The project may use middleware to set the locale based on user preferences or session data. This ensures that Filament labels, notifications, and form components are displayed in the correct language.

```php
public function handle($request, Closure $next)
{
    App::setLocale($request->user()->locale ?? config('app.locale'));
    return $next($request);
}
```
