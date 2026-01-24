# Development Workflow

## Development Workflow
The development process for Fiscaut v4.1 is centered around Laravel Sail for a consistent local environment and standard Laravel practices.

## Confidencialidade e Stack
- **Produto**: aplicação comercial proprietária; evite compartilhar informações fora de canais autorizados.
- **Stack**: Laravel v12, FilamentPHP v5 e Livewire v4.

## Branching & Releases
- **Strategy**: Git Flow or Feature Branch workflow.
- **Main Branch**: `main` (Production-ready).
- **Develop Branch**: `develop` (Integration branch).
- **Feature Branches**: `feature/feature-name` (Created from develop).

## Local Development
All commands should be run via Sail to ensure they execute within the Docker container.

### Prerequisites
- Docker Desktop / Docker Engine
- Git

### Setup
```bash
# Clone repository
git clone <repo-url>
cd fiscaut-v4.1

# Install Dependencies
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# Start Environment
./vendor/bin/sail up -d

# Setup App
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

### Daily Commands
```bash
# Start server
./vendor/bin/sail up -d

# Stop server
./vendor/bin/sail down

# Access shell
./vendor/bin/sail shell
```

## Code Review Expectations
- **Style**: Follow PSR-12 coding standards.
- **Testing**: enquanto o ambiente de testes não estiver pronto, priorizar validação manual no Filament e registrar evidências; adicionar testes assim que o setup estiver disponível.
- **Static Analysis**: Code should pass `phpstan` (if configured) and `pint` styling.
- **Filament**: Ensure Resources are properly registered and navigation labels are consistent.

## Onboarding Tasks
1. Set up the local environment.
2. Log in to the admin panel (`/admin` or `/app`).
3. Create a new `CFOP` entry to verify database connectivity.
4. (Opcional) Executar a suíte de testes quando o ambiente estiver configurado.

## Cross-References
- [testing-strategy.md](./testing-strategy.md)
- [tooling.md](./tooling.md)
