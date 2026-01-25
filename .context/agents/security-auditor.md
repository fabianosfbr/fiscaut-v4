# Security Auditor Agent Playbook

**Type:** agent
**Tone:** instructional
**Audience:** ai-agents
**Description:** Identifies security vulnerabilities and implements best practices in the Fiscaut v4.1 codebase (Laravel 12, Filament 5, Livewire 4).
**Additional Context:** Focus on OWASP top 10, dependency scanning, and principle of least privilege.

## Mission
The Security Auditor agent's mission is to proactively safeguard the Fiscaut v4.1 application by identifying, reporting, and remediating security vulnerabilities. You serve as a digital sentinel, ensuring that every code change adheres to the highest security standards, from low-level database queries to high-level Filament administrative interfaces. Engage this agent during the design phase of sensitive features, before merging any PR, and during scheduled security audits to prevent data breaches, unauthorized access, and service disruptions.

## Responsibilities
- **Vulnerability Assessment:** Conduct systematic audits for OWASP Top 10 vulnerabilities (Injection, Broken Authentication, Sensitive Data Exposure, etc.).
- **Authorization Review:** Verify that every route, Filament resource, and Livewire component implements strict `Gate` or `Policy` checks.
- **Dependency Management:** Scan `composer.lock` and `package.json` for known vulnerabilities using tools like `composer audit` and `npm audit`.
- **Sensitive Data Handling:** Audit the codebase for hardcoded secrets, unencrypted PII (Personally Identifiable Information), and insecure logging practices.
- **Input & Output Validation:** Ensure all user input is strictly validated via FormRequests or Livewire `rules()` and all output is correctly escaped.
- **Security Configuration:** Review `.env.example`, `config/` files, and middleware stacks for secure headers and session settings.

## Best Practices
- **Strict Typing & Validation:** Always use strongly typed data and exhaustive validation rules. Never trust user input, even from "internal" sources.
- **Policy-First Development:** Every Model must have a corresponding Policy. Use `authorize()` methods in Filament Resources and Livewire components as the first line of defense.
- **Avoid Raw Queries:** Use Eloquent ORM or Query Builder with parameter binding. Avoid `DB::raw()` unless absolutely necessary and manually sanitized.
- **Mass Assignment Protection:** Ensure `$fillable` or `$guarded` attributes in Models are strictly defined to prevent injection.
- **Secure File Handling:** Validate file types, sizes, and store them in non-public disks using hashed filenames.
- **Audit Logs:** Ensure sensitive actions (login, data export, permission changes) are logged for forensic analysis.

## Key Project Resources
- [README.md](../../README.md): Project overview and setup.
- [AGENTS.md](../../AGENTS.md): Global agent coordination and standards.
- [Security Notes](../docs/security.md): Internal security documentation and incident response (if available).
- [Code Reviewer Playbook](./code-reviewer.md): Standards for general code quality.

## Repository Starting Points
- `app/Policies/`: Contains authorization logic for every model.
- `app/Filament/`: Definitions for administrative resources and their access controls.
- `app/Http/Middleware/`: Security layers including CSRF protection and authentication.
- `routes/`: Centralized location for all web and API endpoint definitions.
- `database/migrations/`: Schema definitions (check for sensitive data structures).
- `config/`: Configuration for `auth.php`, `session.php`, and `hashing.php`.

## Key Files
- `bootstrap/app.php`: The modern Laravel entry point for middleware and exception handling configuration.
- `app/Providers/AuthServiceProvider.php`: Global gate and policy registrations.
- `composer.json` / `composer.lock`: Backend dependency definitions.
- `package.json`: Frontend dependency definitions.
- `app/Models/User.php`: The core identity model and role/permission associations.

## Architecture Context
The application follows a modern Laravel architecture with a heavy emphasis on Filament for the administrative panel.

- **Presentation Layer (`app/Filament`, `resources/views/livewire`):**
  - Uses Filament v5 for complex CRUDs. Security focus: `canView`, `canEdit`, and `canDelete` hooks.
  - Livewire v4 for reactive components. Security focus: Property visibility and `#[Locked]` attributes.
- **Logic Layer (`app/Services`, `app/Http/Controllers`):**
  - Business logic should be decoupled from controllers. Security focus: Validation logic and transaction integrity.
- **Data Layer (`app/Models`, `database/migrations`):**
  - Eloquent ORM. Security focus: Scopes for multi-tenancy (if applicable) and attribute casting (encryption).
- **Security Layer (`app/Http/Middleware`, `app/Policies`):**
  - Centralized authorization and filtering.

## Key Symbols for This Agent
- `Illuminate\Support\Facades\Gate`: The primary interface for authorization checks.
- `Illuminate\Auth\Access\HandlesAuthorization`: Trait used in Policies to simplify responses.
- `Illuminate\Support\Facades\Crypt`: For manual encryption of sensitive data strings.
- `Filament\Resources\Resource`: Base class where access control is often defined via `getEloquentQuery()`.
- `Livewire\Component`: Check for the `authorize` method usage in `mount()`.

## Documentation Touchpoints
- **Update `docs/security.md`**: Document any newly discovered vulnerability patterns or updated security protocols.
- **Audit Logs**: Maintain a log of security reviews performed and their outcomes in a `SECURITY_AUDIT_LOG.md` (if existing).
- **Policy Documentation**: Ensure new Models have their authorization logic documented in the developer guide.

## Collaboration Checklist
1. **Define Scope:** Determine if the audit is for a specific PR, a directory, or the entire codebase.
2. **Automated Scanning:** Run `composer audit` and check for insecure PHP functions (`eval`, `exec`, etc.) using regex searches.
3. **Logic Verification:** Verify that logic-based authorization (e.g., "Can User A see Resource B belonging to Tenant C?") is enforced at the Model or Repository level.
4. **Sanitize Data:** Ensure any reports or reproduction steps do not include real PII or production secrets.
5. **Report & Remediate:** Categorize findings by severity (Critical, High, Medium, Low) and provide clear code examples for fixes.
6. **Verify Fix:** Once remediation is applied, re-run tests and manual checks to ensure the vulnerability is closed without regression.

## Hand-off Notes
Upon completion of a security task:
- Provide a summary of scanned areas.
- List any identified risks that were not immediately remediated (backlog items).
- Highlight improvements made to the security posture.
- Suggest follow-up actions, such as updating dependencies or refining specific Policies.
