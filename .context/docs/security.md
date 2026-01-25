# Security & Compliance

This document outlines the security architecture, policies, and best practices for Fiscaut v4.1. As a proprietary commercial application, security is a core pillar of the development lifecycle.

## Scope and Confidentiality

*   **Product Status**: Proprietary commercial application.
*   **Confidentiality**: This document and the related security configurations are confidential.
*   **Core Rule**: Never commit, version, or share secrets (tokens, API keys, database credentials) in plain text or via version control systems.

## Security Stack

Fiscaut v4.1 leverages the modern PHP security ecosystem:
*   **Framework**: Laravel v12 (Security-hardened foundation).
*   **Admin Interface**: FilamentPHP v5 (Integrated ACL and form security).
*   **Frontend**: Livewire v4 (Secure state management and communication).

---

## Authentication and Authorization

### Authentication
User authentication is managed through Laravel's native guards. Filament provides a secure entry point via its custom login flow, utilizing the `web` guard by default.
*   **Session Management**: Sessions are stored using secure, HTTP-only cookies to mitigate session hijacking.
*   **Session Timeout**: Configured via `config/session.php` to ensure idle sessions are invalidated.

### Authorization (ACL)
Fiscaut uses a granular authorization model to ensure users only access data they are permitted to see.
*   **Policies**: Every Filament Resource is mapped to a Laravel Policy. For example, `CfopResource` is governed by `CfopPolicy`. Access is checked for:
    *   `viewAny` / `view`
    *   `create`
    *   `update`
    *   `delete` / `forceDelete`
*   **Role Management**: Roles and permissions are typically managed via `spatie/laravel-permission` or Filament Shield, allowing for fine-grained UI and API access control.

---

## Data Security

### Secrets & Environment Variables
*   **Environment Storage**: All sensitive credentials (database passwords, mailer keys, third-party API tokens) are stored in the `.env` file.
*   **Exclusion**: The `.env` file is explicitly ignored by Git (`.gitignore`) to prevent accidental leaks.

### Encryption
*   **Application Key**: The `APP_KEY` is used for symmetric encryption of sensitive data via the `Crypt` facade.
*   **Password Hashing**: User passwords are never stored in plain text. They are hashed using **Bcrypt** or **Argon2** with a secure work factor.

### Database Protection
*   **SQL Injection**: Fiscaut utilizes Eloquent ORM and the Query Builder, which use PDO parameter binding to prevent SQL injection attacks.
*   **Mass Assignment**: Models use `$fillable` or `$guarded` properties to prevent malicious data injection during creation or updates.

---

## Web Vulnerability Protections

### CSRF (Cross-Site Request Forgery)
All state-changing requests (POST, PUT, PATCH, DELETE) are protected by CSRF tokens. Laravel automatically verifies these tokens for all web routes.

### XSS (Cross-Site Scripting)
*   **Blade Templating**: The Blade engine automatically escapes all output using `{{ $var }}`.
*   **Livewire Security**: Component properties are sanitized, and state is checksummed to prevent client-side tampering.
*   **Rich Text**: Fields using `rich-editor` or `markdown-editor` are sanitized on the server-side before storage if they allow HTML.

---

## Infrastructure and Incident Response

### Production Hardening
*   **Debug Mode**: `APP_DEBUG` must be set to `false` in production. This prevents the exposure of sensitive stack traces, environment variables, and database queries to end-users.
*   **HTTPS**: The application must be served exclusively over HTTPS.

### Logging and Monitoring
*   **Application Logs**: Errors and security events are logged to `storage/logs`.
*   **External Monitoring**: In production environments, logs should be forwarded to services like Sentry, Logstash, or CloudWatch for real-time incident detection.

### Incident Response Steps
1.  **Identify**: Detect the leak or vulnerability via logs or monitoring.
2.  **Isolate**: Rotate compromised keys (e.g., `APP_KEY`, API tokens) immediately.
3.  **Remediate**: Patch the code or configuration vulnerability.
4.  **Audit**: Review access logs to determine the extent of the impact.

---

## Developer Guidelines

*   **Validation**: Always use Laravel Request Validation or Filament Form validation to ensure data integrity.
*   **Dependencies**: Regularly run `composer update` and `npm update` to patch vulnerabilities in third-party packages.
*   **Least Privilege**: Grant the database user only the permissions required for the application to function.

## Cross-References
- [Architecture Documentation](./architecture.md)
