# Development Workflow (Fiscaut v4.1)

This document defines the standard development process, environment setup, and coding expectations for **Fiscaut v4.1**. Following this workflow ensures consistency across the team and helps preserve the integrity of the project’s architecture and delivery pipeline.

---

## 1) Confidentiality & Core Stack

Fiscaut is a proprietary commercial application. All source code, database schemas, and business logic are strictly confidential and must not be shared outside authorized channels.

**Core stack**

- **Backend**: Laravel v12
- **Admin panel**: FilamentPHP v5
- **Frontend runtime**: Livewire v4, Alpine.js
- **Environment**: Laravel Sail (Docker)
- **UI/Styling**: Tailwind CSS + Filament Blade components

---

## 2) Branching Strategy

The project uses a **Feature Branch** workflow: all changes must be developed in dedicated branches and integrated via Pull Requests.

| Branch | Purpose | Stability |
|---|---|---|
| `main` | Production-ready code | Stable |
| `develop` | Integration branch for in-progress features | Beta / Testing |
| `feature/*` | New features, components, tasks | Experimental |
| `hotfix/*` | Urgent production fixes | Critical |

**Flow:** `feature/*` → `develop` → `main`

**Rules**
- Do not commit directly to `main` or `develop`.
- Keep feature branches small and focused (one feature/task per branch).
- Prefer **Squash and Merge** to keep history readable.

---

## 3) Local Environment Setup (Laravel Sail)

Development is containerized with **Laravel Sail** to ensure parity between development, staging, and production.

### Prerequisites

- Docker Desktop (or Docker Engine)
- Git
- PHP 8.4+ *(only required locally if you need to bootstrap Composer without the container method; recommended approach below avoids local PHP)*

### Initial installation

```bash
# 1) Clone
git clone <repo-url>
cd fiscaut-v4.1

# 2) Install PHP dependencies using a temporary container
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php84-composer:latest \
  composer install --ignore-platform-reqs

# 3) Environment file
cp .env.example .env

# 4) Start Sail
./vendor/bin/sail up -d

# 5) Laravel setup
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed

# 6) Frontend dependencies & build
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

**Common URLs**
- App: `http://localhost`
- Filament Admin: `http://localhost/admin`

---

## 4) Daily Operations

### Sail commands

- Start: `./vendor/bin/sail up -d`
- Stop: `./vendor/bin/sail down`
- Shell inside container: `./vendor/bin/sail shell`
- View logs: `./vendor/bin/sail logs -f`

### Database management

Never modify schema manually. All changes must go through Laravel migrations.

- Create migration:
  ```bash
  ./vendor/bin/sail artisan make:migration create_name_table
  ```
- Rebuild database (destructive):
  ```bash
  ./vendor/bin/sail artisan migrate:refresh --seed
  ```

---

## 5) Coding Standards & Quality

### PHP standards

- **PSR-12** compliance is required.
- Use **type hints** for parameters and return types whenever possible.
- Run **Laravel Pint** before every commit:
  ```bash
  ./vendor/bin/sail bin pint
  ```

### Architecture conventions (Filament + JS)

Respect existing structure and reuse what exists before creating new implementations.

**Project-provided Filament JS assets (compiled/distributed)**
- **Schemas**: `public/js/filament/schemas`
  - Schema components: `public/js/filament/schemas/components`
- **Components**
  - Widgets: `public/js/filament/widgets/components`
  - Form components: `public/js/filament/forms/components`
  - Table column components: `public/js/filament/tables/components/columns`

**Vendor references (upstream Filament)**
- Filament schemas/resources: `vendor/filament/schemas/resources/js`
- Shared JS utilities (example: Select utilities): `vendor/filament/support/resources/js/utilities/`

**Guidelines**
- **Before adding a new JS component**, check whether an equivalent already exists in:
  - `public/js/filament/forms/components`
  - `public/js/filament/widgets/components`
  - `public/js/filament/tables/components/columns`
- Prefer **Filament schema builder** patterns for forms/tables; only add custom Alpine/JS behavior where necessary.
- Keep custom logic modular and consistent with existing components (naming and structure).

### Filament / Livewire practices

- Register new Filament Resources in the appropriate Provider (per project conventions).
- For user feedback, use `Filament\Notifications\Notification`.
- Validate changes in both desktop and mobile layouts (Filament UI is responsive, but customizations may not be).

---

## 6) Testing & Validation

### Manual validation (required)

Until automated coverage is complete, manual verification is mandatory for every feature:

1. Verify behavior inside the **Filament Admin** panel.
2. Test responsiveness (mobile/tablet).
3. Check browser console for JS errors (especially when modifying Alpine.js/Livewire behavior).

### Automated testing

- **Unit tests**: `tests/Unit` (business logic)
- **Feature tests**: `tests/Feature` (Livewire components, Filament Resources)
- Run test suite:
  ```bash
  ./vendor/bin/sail artisan test
  ```

---

## 7) Pull Request (PR) Process

1. Sync with `develop`:
   ```bash
   git pull origin develop
   ```
   *(Resolve conflicts in your feature branch, not in `develop`.)*
2. Format code:
   ```bash
   ./vendor/bin/sail bin pint
   ```
3. Open a PR targeting **`develop`**.
4. PR description must include:
   - Summary of changes
   - Screenshots/recordings (especially for Filament UI changes)
   - Any migration/seed considerations
5. Address review comments promptly.
6. Merge via **Squash and Merge** once approved.

---

## 8) Onboarding Checklist

Use this checklist to confirm your environment and workflow are correctly set up:

- [ ] `./vendor/bin/sail up -d` works and `http://localhost` loads
- [ ] Can log into Filament Admin at `/admin`
- [ ] Can open the **CFOP** resource and create/update an entry
- [ ] Modify a CSS/JS file and confirm Vite HMR reloads
- [ ] `./vendor/bin/sail bin pint` runs successfully

---

## Related Documentation

- [Testing Strategy](./testing-strategy.md)
- [Tooling Guide](./tooling.md)
