# Middleware in Fiscaut v4.1

Middleware functions act as a bridge between a request and a response, primarily used for filtering and inspecting HTTP requests entering the application. In the Fiscaut v4.1 architecture (built on Laravel and Filament), middleware handles critical tasks such as authentication, authorization, localization, and state management for reactive components.

## Core Middleware Roles

In this project, middleware serves several primary functions:
1.  **Authentication & Security**: Ensuring users are logged in and protecting against CSRF attacks.
2.  **Panel Initialization**: Setting up the Filament environment and dispatching events that initialize the admin interface.
3.  **Data Consistency**: Trimming strings and converting empty fields to null to maintain database integrity.
4.  **Authorization**: Validating whether a user has the appropriate roles/permissions to access specific resources.

## Middleware Stacks

### 1. Global Middleware
These run on every request to the application. They handle low-level infrastructure such as proxy handling and maintenance mode.
- `TrustProxies`: Manages SSL termination and load balancer headers.
- `ValidatePostSize`: Ensures file uploads do not exceed server limits.
- `TrimStrings` / `ConvertEmptyStringsToNull`: Sanitizes input data before it reaches controllers or Livewire components.

### 2. Filament Admin Stack
Filament panels define their own middleware stack in the Panel Provider (e.g., `app/Providers/Filament/AdminPanelProvider.php`). This stack is essential for the dashboard to function correctly.

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
    DispatchServingFilamentEvent::class, // Vital for Filament UI components
])
```

### 3. Authentication Middleware
Defined separately to handle redirection and protected routes:
- `Authenticate`: Redirects guest users to the login page.
- `Verified`: (Optional) Ensures the user has verified their email address.

## Custom Middleware Implementation

### Creation
To create a middleware for specific business logic or QA validation:

```bash
php artisan make:middleware EnsureProfileIsComplete
```

### Implementation Example
In `app/Http/Middleware/EnsureProfileIsComplete.php`:

```php
public function handle(Request $request, Closure $next): Response
{
    if (auth()->check() && !auth()->user()->is_profile_complete) {
        return redirect()->route('profile.edit')
            ->with('warning', 'Please complete your profile first.');
    }

    return $next($request);
}
```

### Registration in Filament
To apply this to the admin panel, add it to the `authMiddleware` array in your Panel Provider:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->authMiddleware([
            Authenticate::class,
            \App\Http\Middleware\EnsureProfileIsComplete::class,
        ]);
}
```

## QA & Debugging Strategies

### Identifying Applied Middleware
To verify which middleware are active for a specific route during debugging:
```bash
php artisan route:list --path=admin
```
This identifies the full stack, including the `web` group and the Filament-specific layers.

### Bypassing Middleware in Feature Tests
When testing resource logic where middleware authentication is not the focus, use the `withoutMiddleware()` helper:

```php
public function test_can_view_dashboard_stats()
{
    $response = $this->withoutMiddleware()
                     ->get('/admin');

    $response->assertStatus(200);
}
```

### Testing Middleware Logic
Create dedicated tests to ensure middleware correctly blocks or permits access:

```php
public function test_unauthorized_user_is_blocked_from_qa_tools()
{
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
         ->get('/admin/qa-dashboard')
         ->assertStatus(403);
}
```

## Essential Project Middleware Reference

| Middleware | Purpose |
| :--- | :--- |
| `DispatchServingFilamentEvent` | Dispatches the event that Filament uses to register scripts, styles, and navigation items. |
| `SubstituteBindings` | Resolves Eloquent models from route parameters (e.g., `/users/{user}` becomes a `User` instance). |
| `VerifyCsrfToken` | Protects the application from Cross-Site Request Forgery; excludes specific API or webhook routes if configured. |
| `Authenticate` | The primary gatekeeper. In Fiscaut, it ensures that only users with the `canAccessPanel()` permission can enter the admin area. |
| `ShareErrorsFromSession` | Ensures `$errors` variable is available in Blade views, allowing Filament forms to show validation failures. |
