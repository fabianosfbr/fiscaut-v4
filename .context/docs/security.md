# Security & Compliance (Fiscaut v4.1)

This document describes the security architecture, operational policies, and developer best practices for **Fiscaut v4.1**. The application is built on a security-hardened Laravel ecosystem with strong defaults and layered protections across authentication, authorization, data handling, and infrastructure.

---

## Security Stack

Fiscaut v4.1 relies on the following core technologies and their security features:

- **Framework**: **Laravel v12**  
  Provides authentication primitives, CSRF protection, encryption, signed URLs, secure cookies, middleware, and robust validation.

- **Admin Interface**: **FilamentPHP v5**  
  Provides authenticated admin access patterns and integrates with Laravel authorization (Policies/Gates). Filament forms/tables enforce server-side validation and can restrict actions by permission.

- **Frontend / UI State**: **Livewire v4**  
  Livewire requests include cryptographically signed payload checksums. If client-side component state is tampered with, the request is rejected.

---

## Authentication and Authorization

### Authentication

Authentication uses Laravel’s guard/session system, with Filament providing the admin login flow.

Key controls:

- **Session cookies**
  - Use **HTTP-only** cookies to reduce risk of session theft via JavaScript.
  - Use **SameSite** to reduce CSRF risk.
  - Enable **secure cookies** in HTTPS environments.

- **Session timeout**
  - Idle sessions expire according to `SESSION_LIFETIME` (configured in `config/session.php` and typically overridden via `.env`).

- **Rate limiting**
  - Login attempts should be throttled (Laravel’s rate limiter) to mitigate brute-force attacks.

**Operational checklist**
- Ensure production has:
  - `SESSION_SECURE_COOKIE=true`
  - HTTPS enforced end-to-end (including reverse proxy / load balancer)
  - Adequate throttling configured for login endpoints

---

### Authorization (ACL)

Fiscaut applies **granular access control** using Laravel’s Authorization layer.

- **Policies per Resource**
  - Each Filament Resource is mapped to a Laravel Policy.
  - Example: `CfopResource` is governed by `App\Policies\CfopPolicy`.

- **Method mapping**
  Access is validated using standard policy methods:
  - `viewAny` / `view` — list and view details
  - `create` — create new records
  - `update` — modify records
  - `delete` / `forceDelete` — remove records

- **Roles and permissions**
  - Roles/permissions are managed via `spatie/laravel-permission` (commonly integrated with Filament Shield).
  - Administrators can define fine-grained access to UI actions and (where applicable) API capabilities.

**Developer rules**
- Never rely on UI hiding alone (e.g., hiding buttons). Always enforce permission checks at the policy/gate level.
- Prefer policy checks in Resources and Controllers rather than ad-hoc role checks scattered throughout the code.

---

## Data Security

### Secrets & Environment Variables

- **Store secrets in `.env`**
  - Database credentials, API keys, SMTP credentials, S3 secrets, etc.
- **Never commit secrets**
  - `.env` must remain excluded via `.gitignore`.
- **Rotation policy**
  - Rotate `APP_KEY` and any long-lived API tokens at least annually, and immediately upon staff turnover or suspected compromise.

**Notes on `APP_KEY` rotation**
- Rotating `APP_KEY` invalidates encrypted data created with the old key (including some cookies and any application-encrypted fields). Plan rotations carefully:
  - schedule downtime or migration strategy
  - re-encrypt stored secrets if necessary

---

### Encryption & Hashing

- **Application encryption**
  - Laravel uses `APP_KEY` for symmetric encryption via `Crypt` (AES-256-CBC by default).
  - Use for sensitive values stored in the database (when appropriate).

- **Password hashing**
  - Passwords must never be stored in plain text.
  - Use Laravel hashing (Argon2id recommended; Bcrypt acceptable depending on environment).

---

### Database Protection

- **SQL injection protection**
  - Prefer Eloquent ORM and Query Builder (PDO parameter binding).
  - Avoid concatenating user input into raw SQL.

- **Mass assignment protection**
  - All models should define `$fillable` or `$guarded`.
  - Treat any `Model::create($request->all())` pattern as suspicious unless carefully validated and constrained.

**Developer checklist**
- Use Form Requests / Filament validation for every write operation.
- Keep model assignment strict and explicit.

---

## Web Vulnerability Protections

### CSRF (Cross-Site Request Forgery)

- All state-changing requests (POST/PUT/PATCH/DELETE) are protected by CSRF tokens.
- Laravel’s `VerifyCsrfToken` middleware validates tokens for all web routes by default.

**Developer rules**
- Do not disable CSRF protection globally.
- If an endpoint must be excluded (rare), document the reason and ensure alternative protection exists (e.g., signed requests, tokens, origin checks).

---

### XSS (Cross-Site Scripting)

Controls:

- **Blade templating**
  - Use escaped output: `{{ $value }}`
  - Avoid raw output: `{!! $value !!}` unless content is *trusted and sanitized*

- **Livewire**
  - Component state is signed/validated per request.
  - Tampering triggers checksum failure and request rejection.

- **Rich text**
  - Inputs from rich editors (e.g., `rich-editor` / markdown) must be sanitized **server-side** prior to storage to prevent stored XSS.

**Example**
```php
// Safe (escaped)
<div>{{ $user->name }}</div>

// Dangerous (only if sanitized and trusted)
<div>{!! $htmlFromUser !!}</div>
```

---

## Production Hardening

### Required production settings

- **Disable debug output**
  - `APP_DEBUG=false` in production to prevent leakage of stack traces and environment values.

- **HTTPS only**
  - Serve the application exclusively over TLS.
  - Ensure secure cookies are enabled.

- **Security headers**
  Configure at the web server (Nginx/Apache) or trusted proxy:

- `Strict-Transport-Security` (HSTS)
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`

**Recommended additional headers (context-dependent)**
- `Content-Security-Policy` (CSP) — best protection against XSS when properly configured
- `Referrer-Policy`
- `Permissions-Policy`

---

## Logging, Monitoring, and Auditability

### Logging

- Security-relevant events should be logged (e.g., failed logins, permission/role changes).
- Default Laravel logs are typically stored at:
  - `storage/logs/laravel.log`

### Monitoring / alerting

- Use an error tracking system (e.g., Sentry) in production to capture exceptions and provide real-time alerts.
- Monitor authentication anomalies:
  - spikes in failed login attempts
  - unusual geographic access patterns
  - repeated authorization failures

---

## Incident Response

Minimum response procedure:

1. **Identify**
   - Confirm the issue using logs/monitoring (what happened, when, and scope).
2. **Isolate**
   - Revoke/rotate compromised credentials immediately (`APP_KEY`, API tokens, passwords).
   - Disable affected accounts/sessions when appropriate.
3. **Remediate**
   - Patch the vulnerability, add regression tests, and deploy.
4. **Audit**
   - Review access and application logs to determine exposure.
   - Document root cause, impacted systems, and corrective actions.

---

## Developer Guidelines (Required Practices)

1. **Validate all input**
   - Use Laravel Form Requests or Filament form validation.
   - Reject unexpected fields and enforce strict types/formats.

2. **Audit dependencies**
   - Run regularly:
     - `composer audit`
     - `npm audit`
   - Patch promptly; document exceptions (e.g., false positives or deferred upgrades).

3. **Principle of least privilege**
   - DB users should have only the permissions required for the app database.
   - Restrict filesystem permissions:
     - `storage/` and `bootstrap/cache/` writable only by the web server user
   - Avoid running PHP-FPM / web server as a privileged user.

4. **No secrets in code**
   - Never hardcode credentials or tokens.
   - Access configuration via `config()` (backed by `env()` in runtime configuration).

---

## Cross-References

- [Architecture Documentation](./architecture.md)
