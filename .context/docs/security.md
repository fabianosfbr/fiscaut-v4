# Security & Compliance

This document outlines the security architecture, policies, and best practices for **Fiscaut v4.1**. As a proprietary commercial application, security is a core pillar of the development lifecycle, ensuring data integrity and protection against common web vulnerabilities.

## Security Stack

Fiscaut v4.1 is built on a modern, security-hardened PHP ecosystem:

*   **Framework**: [Laravel v12](https://laravel.com) - Provides the foundational security layer (Auth, CSRF, Encryption).
*   **Admin Interface**: [FilamentPHP v5](https://filamentphp.com) - Handles secure administrative access and granular form security.
*   **Frontend**: [Livewire v4](https://livewire.laravel.com) - Manages secure state communication via cryptographically signed checksums.

---

## Authentication and Authorization

### Authentication
User authentication is handled via Laravel's native guard system. Filament provides a secure entry point using its custom login flow.

*   **Session Management**: Sessions use secure, HTTP-only, and SameSite cookies to mitigate session hijacking and CSRF.
*   **Session Timeout**: Idle sessions are automatically invalidated based on the `SESSION_LIFETIME` value in `config/session.php`.
*   **Rate Limiting**: Login attempts are throttled to prevent brute-force attacks.

### Authorization (ACL)
The application employs a granular Access Control List (ACL) model.

*   **Policies**: Every Filament Resource is mapped to a Laravel Policy. For example, the `CfopResource` is governed by `App\Policies\CfopPolicy`.
*   **Method Mapping**: Access is checked at the gate level for:
    *   `viewAny` / `view`: Permission to list or see record details.
    *   `create`: Permission to initialize new records.
    *   `update`: Permission to modify existing data.
    *   `delete` / `forceDelete`: Permission to remove records.
*   **Role Management**: Roles and permissions are managed via `spatie/laravel-permission` (often integrated with Filament Shield), allowing administrators to define fine-grained UI and API access.

---

## Data Security

### Secrets & Environment Variables
*   **Environment Storage**: Sensitive credentials (DB passwords, API keys, S3 secrets) are stored in the `.env` file.
*   **Git Security**: The `.env` file is explicitly ignored by version control (`.gitignore`).
*   **Secret Rotation**: It is recommended to rotate `APP_KEY` and API tokens annually or upon staff turnover.

### Encryption & Hashing
*   **Application Key**: The `APP_KEY` is used for symmetric encryption (AES-256-CBC) via the `Crypt` facade for any sensitive database fields.
*   **Password Hashing**: Passwords are never stored in plain text. Fiscaut uses **Argon2id** (or Bcrypt) with a secure work factor, meeting modern cryptographic standards.

### Database Protection
*   **SQL Injection**: The application utilizes Eloquent ORM and Query Builder, which use PDO parameter binding natively to prevent SQL injection.
*   **Mass Assignment**: All models define `$fillable` or `$guarded` attributes to prevent malicious data injection during `create()` or `update()` calls.

---

## Web Vulnerability Protections

### CSRF (Cross-Site Request Forgery)
All state-changing requests (POST, PUT, PATCH, DELETE) are protected by CSRF tokens. Laravel's `VerifyCsrfToken` middleware automatically validates these tokens for all web routes.

### XSS (Cross-Site Scripting)
*   **Blade Templating**: The Blade engine automatically escapes output using `{{ $var }}`. Raw output `{!! $var !!}` is strictly prohibited unless the source is trusted and pre-sanitized.
*   **Livewire Security**: Component properties are sanitized on every request. State is checksummed on the server; if the client alters the state, the checksum fails and the request is rejected.
*   **Rich Text Sanitization**: For fields using `rich-editor` or `markdown-editor`, Fiscaut sanitizes HTML on the server-side before storage to prevent stored XSS attacks.

---

## Infrastructure and Incident Response

### Production Hardening
*   **Debug Mode**: `APP_DEBUG` **must** be set to `false` in production. This prevents the exposure of stack traces and environment variables to users.
*   **HTTPS Only**: The application must be served exclusively over TLS (HTTPS). Secure cookies (`SESSION_SECURE_COOKIE=true`) must be enabled.
*   **Security Headers**: Use a web server (Nginx/Apache) to inject security headers:
    *   `Strict-Transport-Security` (HSTS)
    *   `X-Content-Type-Options: nosniff`
    *   `X-Frame-Options: SAMEORIGIN`

### Logging and Monitoring
*   **Audit Logs**: Security-critical events (failed logins, permission changes) are logged to `storage/logs/laravel.log`.
*   **Error Tracking**: In production, services like Sentry should be used to capture and alert on exceptions in real-time.

### Incident Response Steps
1.  **Identify**: Detect the leak or vulnerability via logs.
2.  **Isolate**: Rotate compromised keys (`APP_KEY`, API tokens) immediately.
3.  **Remediate**: Deploy a patch for the vulnerability.
4.  **Audit**: Review access logs to determine if data was accessed.

---

## Developer Guidelines

To maintain the security posture of Fiscaut, developers must adhere to:

1.  **Validation First**: Never trust user input. Use Laravel `Request` validation or Filament `form()` validation for every input.
2.  **Dependency Audits**: Regularly run `composer audit` and `npm audit` to identify and patch vulnerabilities in third-party packages.
3.  **Principle of Least Privilege**:
    *   The database user should only have permissions for the specific application database.
    *   File permissions for `storage` and `bootstrap/cache` should be restricted to the web server user (`www-data`).
4.  **No Secrets in Code**: Never hardcode API keys or credentials. Always use `config()` which pulls from `env()`.

## Cross-References
- [Architecture Documentation](./architecture.md)
