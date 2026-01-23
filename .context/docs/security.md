# Security & Compliance Notes

## Security & Compliance Notes
Fiscaut v4.1 relies on the robust security features provided by the Laravel framework and the Filament ecosystem to protect data and ensure compliance.

## Escopo e Confidencialidade
- **Produto**: aplicação comercial proprietária; trate este documento como confidencial.
- **Stack**: Laravel v12, FilamentPHP v5 e Livewire v4.
- **Regra prática**: nunca registrar, versionar ou compartilhar segredos (tokens, chaves, credenciais) em texto puro.

## Authentication & Authorization
- **Authentication**: Handled by Laravel's default authentication guards. Filament provides its own login page and auth flow, typically using the `web` guard.
- **Authorization**:
    - **Policies**: Every Filament Resource (e.g., `CfopResource`) is backed by a Policy (e.g., `CfopPolicy`) to control `viewAny`, `create`, `update`, `delete`, etc.
    - **Roles & Permissions**: Typically managed via packages like `spatie/laravel-permission` or Filament Shield (if installed).
    - **Session Management**: Secure, HTTP-only cookies are used for session management.

## Secrets & Sensitive Data
- **Environment Variables**: Sensitive credentials (DB passwords, API keys) are stored in `.env` and **never** committed to version control.
- **Encryption**: Laravel's `APP_KEY` is used for symmetric encryption of sensitive data via `Crypt` facade.
- **Passwords**: Hashed using Bcrypt or Argon2 before storage.

## Compliance & Policies
- **CSRF Protection**: All forms are protected against Cross-Site Request Forgery.
- **XSS Protection**: Blade templates automatically escape output.
- **SQL Injection**: Eloquent ORM uses PDO parameter binding to prevent SQL injection.

## Incident Response
- **Logging**: Application errors are logged to `storage/logs`. In production, these should be shipped to a monitoring service (e.g., Sentry, CloudWatch).
- **Debug Mode**: `APP_DEBUG` must be set to `false` in production to prevent leaking stack traces.

## Cross-References
- [architecture.md](./architecture.md)
