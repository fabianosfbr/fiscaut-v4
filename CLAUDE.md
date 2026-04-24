# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Environment Setup
```bash
# Using Laravel Sail (recommended)
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev

# Manual setup (if not using Sail)
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
# In two separate terminals:
php artisan serve   # Terminal 1
npm run dev         # Terminal 2
```

### Common Artisan Commands
```bash
# Database
./vendor/bin/sail artisan migrate                  # Run migrations
./vendor/bin/sail artisan migrate:refresh --seed   # Rebuild DB with seed data
./vendor/bin/sail artisan migrate:rollback         # Rollback last migration
./vendor/bin/sail artisan make:migration name      # Create new migration
./vendor/bin/sail artisan db:seed                  # Run seeders
./vendor/bin/sail artisan db:seed --class=ClassName # Run specific seeder

# Cache
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan view:clear
./vendor/bin/sail artisan route:clear

# Filament
./vendor/bin/sail artisan make:filament-user       # Create admin user
./vendor/bin/sail artisan filament:assets          # Republish Filament assets
./vendor/bin/sail artisan filament:optimize        # Optimize Filament components
./vendor/bin/sail artisan make:filament-resource ModelName # Generate resource

# Testing
./vendor/bin/sail artisan test                     # Run all tests
./vendor/bin/sail artisan test --filter=testName   # Run specific test
./vendor/bin/sail artisan test tests/Unit          # Run unit tests only
./vendor/bin/sail artisan test tests/Feature       # Run feature tests only
```

### NPM/Vite Commands
```bash
npm run dev    # Start Vite dev server with HMR
npm run build  # Build production assets
npm run prod   # Alias for build
```

### Code Quality
```bash
./vendor/bin/sail bin pint     # Run Laravel Pint (PSR-12 fixer)
./vendor/bin/sail bin pest     # Run Pest tests directly
```

## Code Architecture

### Core Structure
- **Modular Monolith** using Laravel and FilamentPHP
- **Multi-tenancy**: Data scoped by Tenant (Subscriber), each managing multiple Issuers (Companies)
- **TALL Stack**: Tailwind CSS, Alpine.js, Laravel, Livewire

### Key Directories
```
app/
├── Filament/        # Admin panel Resources, Pages, Actions
├── Models/          # Eloquent models (fiscal domain entities)
└── Actions/         # Reusable business logic classes

config/              # Application configuration
database/
├── migrations/      # Database schema (CFOP, CNAE, etc.)
└── seeders/         # Initial data for reference tables

resources/
├── views/           # Blade templates & Livewire components
└── lang/            # Localization (pt_BR)

public/js/filament/  # Compiled Filament JS assets
```

### Component System (Filament)
- **Forms**: `app/Filament/Resources/*/form.php` + `public/js/filament/forms/components/`
- **Tables**: `app/Filament/Resources/*/table.php` + `public/js/filament/tables/components/columns/`
- **Widgets**: `app/Filament/Resources/*/widgets/` + `public/js/filament/widgets/components/`

### Critical JavaScript Patterns
1. **Livewire Communication**: 
   - `Livewire.dispatch('event-name', data)` → PHP backend
   - `@this.on('event-name', callback)` ← PHP backend
2. **Alpine.js Data**: 
   - `Alpine.data('componentName', () => ({ ... }))`
3. **Finding Components**: 
   - `findClosestLivewireComponent(element)` for DOM-to-PHP bridging

### Multi-Tenancy Implementation
- Tenant identification via subdomain or request header
- Model scopes automatically apply tenant_id
- Separate database connections per tenant (when configured)

### Fiscal Domain Core
- **CFOP**: Fiscal Operation Codes (tax document types)
- **CNAE**: Economic Activity Classification
- **Simples Nacional**: Simplified tax regime with annexes and rates
- **ISS**: Municipal service taxation via Service Codes

## Development Workflow

### Git Branching
- `main`: Production-ready (stable)
- `develop`: Integration branch (beta/testing)
- `feature/*`: New features (branch from develop)
- `hotfix/*`: Urgent fixes (branch from main)
- **Flow**: feature/* → develop → main (via PRs)

### Pull Request Process
1. `git pull origin develop` (in feature branch)
2. `./vendor/bin/sail bin pint` (format code)
3. Open PR targeting `develop`
4. Include: summary, screenshots, migration notes
5. Address feedback, squash & merge when approved

### Testing Strategy
- **Unit Tests**: `tests/Unit/` (business logic, services)
- **Feature Tests**: `tests/Feature/` (Livewire components, Filament resources)
- Run: `./vendor/bin/sail artisan test`
- Manual validation required: Filament UI, responsiveness, JS console

### QA Focus Areas
1. **Form Validation**: RichEditor sanitization, Wizard state, FileUpload errors
2. **Livewire Reactivity**: Stats updates, unsaved-changes-alert, component synchronization
3. **Table Interactions**: Bulk actions, ToggleColumn AJAX, TextInputColumn validation
4. **Security**: Middleware authentication, Filament policies/roles, LGPD compliance

## File Patterns to Reuse
- **Form Components**: Check `public/js/filament/forms/components/` before creating new
- **Table Columns**: Check `public/js/filament/tables/components/columns/`
- **Widgets**: Check `public/js/filament/widgets/components/`
- **JS Utilities**: `vendor/filament/support/resources/js/utilities/` (Select, Notification, etc.)

When extending existing components, follow the established naming and modular structure patterns.