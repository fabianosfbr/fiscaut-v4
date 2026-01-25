# Development Workflow

This document outlines the standard development process, environment setup, and coding expectations for the Fiscaut v4.1 project.

## Confidentiality & Core Stack

Fiscaut is a proprietary commercial application. All source code, database schemas, and business logic are confidential.

*   **Framework**: Laravel v12
*   **Admin Panel**: FilamentPHP v5
*   **Frontend**: Livewire v4, Alpine.js
*   **Environment**: Laravel Sail (Docker)

## Branching Strategy

The project follows a standard **Feature Branch** workflow (similar to Git Flow).

*   **`main`**: Production-ready code. Only merged via Pull Requests from `develop`.
*   **`develop`**: Integration branch for features currently in progress.
*   **`feature/feature-name`**: New features or tasks. Created from `develop`.
*   **`hotfix/issue-name`**: Urgent production fixes. Created from `main`.

## Local Environment Setup

The development environment is containerized using Laravel Sail to ensure consistency across all developer machines.

### Prerequisites
- Docker Desktop or Docker Engine
- Git

### Initial Installation
Run the following commands to bootstrap the project:

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

# 3. Create environment file
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

## Daily Operations

### Managing the Environment
```bash
# Start the containers
./vendor/bin/sail up -d

# Stop the containers
./vendor/bin/sail down

# Access the application container shell
./vendor/bin/sail shell
```

### Database Management
Always use migrations for schema changes. If you modify a migration that hasn't been pushed to `develop`, you can refresh:
```bash
./vendor/bin/sail artisan migrate:refresh --seed
```

## Coding Standards & Quality

### PHP Standards
- Follow **PSR-12** coding standards.
- Use **Laravel Pint** to format code before committing:
  ```bash
  ./vendor/bin/sail bin pint
  ```
- Aim for strict typing in function signatures and return types where possible.

### Filament and Livewire Patterns
- **Resources**: Ensure Filament Resources are properly registered in the Provider.
- **Labels**: Use consistent navigation labels and breadcrumbs.
- **Components**: Reuse existing components in `public/js/filament/schemas/components` or `vendor/filament/...` where applicable.

### Testing & Validation
- **Manual Validation**: Until the full automated testing suite is finalized, developers must manually validate features within the Filament panel.
- **Evidence**: Attach screenshots or screen recordings of successful manual tests to Pull Requests.
- **Future-proofing**: Write tests for complex business logic in `tests/Feature` or `tests/Unit` as the environment matures.

## Pull Request Process

1.  **Sync**: Ensure your feature branch is up-to-date with `develop`.
2.  **Lint**: Run `pint` to fix styling issues.
3.  **Submit**: Create a Pull Request targeting the `develop` branch.
4.  **Review**: At least one other developer must approve the PR.
5.  **Merge**: Once approved and CI (if applicable) passes, merge using "Squash and Merge".

## Onboarding Checklist

Complete these tasks to ensure your environment is correctly configured:

1.  [ ] Successfully run `sail up -d` and access the app at `http://localhost`.
2.  [ ] Log in to the Filament Admin panel (usually `/admin`).
3.  [ ] Navigate to the **CFOP** resource and create a test entry to verify DB connectivity.
4.  [ ] Verify that Vite is correctly hot-reloading changes by modifying a CSS or JS file.

## Related Documentation
- [Testing Strategy](./testing-strategy.md)
- [Tooling Guide](./tooling.md)
