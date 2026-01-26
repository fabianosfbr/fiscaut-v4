# Development Workflow

This document outlines the standard development process, environment setup, and coding expectations for the **Fiscaut v4.1** project. This guide ensures consistency across the development team and maintains the integrity of the application's architecture.

## 1. Confidentiality & Core Stack

Fiscaut is a proprietary commercial application. All source code, database schemas, and business logic are strictly confidential.

*   **Framework**: Laravel v12
*   **Admin Panel**: FilamentPHP v5
*   **Frontend**: Livewire v4, Alpine.js
*   **Environment**: Laravel Sail (Docker)
*   **Styling/Components**: Tailwind CSS & Filament Blade Components

## 2. Branching Strategy

The project follows a **Feature Branch** workflow. All development must occur in dedicated branches before being integrated into the main line.

| Branch | Purpose | Stability |
| :--- | :--- | :--- |
| `main` | Production-ready code. | Stable |
| `develop` | Integration branch for features in progress. | Beta / Testing |
| `feature/*` | New features, components, or tasks. | Experimental |
| `hotfix/*` | Urgent production fixes. | Critical |

**Workflow Flow**: `feature/*` → `develop` → `main`.

## 3. Local Environment Setup

The development environment is containerized using **Laravel Sail** to ensure parity between development, staging, and production.

### Prerequisites
- Docker Desktop or Docker Engine
- Git
- PHP 8.4+ (local install only needed for initial bootstrap if not using the container method)

### Initial Installation

```bash
# 1. Clone the repository
git clone <repo-url>
cd fiscaut-v4.1

# 2. Install PHP dependencies via a temporary container
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# 3. Setup environment
cp .env.example .env

# 4. Start the Sail environment
./vendor/bin/sail up -d

# 5. Finalize Laravel setup
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed

# 6. Install and compile assets
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

## 4. Daily Operations

### Managing the Environment
- **Start**: `./vendor/bin/sail up -d`
- **Stop**: `./vendor/bin/sail down`
- **Shell Access**: `./vendor/bin/sail shell`
- **Logs**: `./vendor/bin/sail logs -f`

### Database Management
Never modify the database schema manually. Always use migrations.
- **New Migration**: `./vendor/bin/sail artisan make:migration create_name_table`
- **Refresh Environment**: `./vendor/bin/sail artisan migrate:refresh --seed`

## 5. Coding Standards & Quality

### PHP Standards
- **PSR-12 Compliance**: All PHP code must adhere to PSR-12.
- **Strict Typing**: Use type hints for function arguments and return types.
- **Linting**: Run Laravel Pint before every commit:
  ```bash
  ./vendor/bin/sail bin pint
  ```

### Architecture Patterns
Developers should respect the established directory structure for custom Filament logic:
- **Custom Schemas**: Located in `public/js/filament/schemas` and `vendor/filament/schemas/resources/js`.
- **Custom Components**: Check `public/js/filament/forms/components` or `public/js/filament/widgets/components` before creating new ones.
- **Utilities**: Common JS utilities (like `Select`) are found in `vendor/filament/support/resources/js/utilities/`.

### Filament and Livewire
- **Resources**: Register all new Filament Resources in the appropriate Provider.
- **Forms/Tables**: Utilize Filament's schema builder. For complex custom interactions, use the existing Alpine.js components located in the `resources/js` or `public/js/filament` directories.
- **Notifications**: Use the `Filament\Notifications\Notification` class for user feedback.

## 6. Testing & Validation

### Manual Validation
Until the automated testing suite reaches full coverage, manual validation is mandatory:
1.  Verify the feature in the Filament Admin panel.
2.  Test responsive behavior (mobile/tablet views).
3.  Check browser console for JS errors (especially when working with Alpine.js/Livewire).

### Automated Testing
- **Unit Tests**: For isolated business logic in `tests/Unit`.
- **Feature Tests**: For Livewire components and Filament Resources in `tests/Feature`.
- **Execution**: `./vendor/bin/sail artisan test`

## 7. Pull Request Process

1.  **Sync**: `git pull origin develop` into your feature branch.
2.  **Lint**: Run `./vendor/bin/sail bin pint`.
3.  **Submit**: Open a PR against the `develop` branch.
4.  **Description**: Include a summary of changes and screenshots/recordings of the feature working.
5.  **Review**: Address comments from peer reviewers.
6.  **Merge**: Once approved, use **Squash and Merge** to keep a clean history.

## 8. Onboarding Checklist

Complete these tasks to verify your setup:

- [ ] Successfully run `sail up -d` and access `http://localhost`.
- [ ] Log in to the Filament Admin panel at `/admin`.
- [ ] Navigate to the **CFOP** resource and create/update an entry.
- [ ] Modify a CSS/JS file and verify Vite's hot-reload (HMR) triggers.
- [ ] Run `./vendor/bin/sail bin pint` to ensure the linter is working.

---

**Related Documentation:**
- [Testing Strategy](./testing-strategy.md)
- [Tooling Guide](./tooling.md)
