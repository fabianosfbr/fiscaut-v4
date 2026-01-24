# Testing Strategy

## Testing Strategy
Quality is maintained through a combination of automated Feature tests (for end-to-end flows) and Unit tests (for isolated logic).

## Contexto do Projeto
- **Produto**: aplicação comercial proprietária.
- **Stack**: Laravel v12, FilamentPHP v5 e Livewire v4.
- **Status atual do ambiente**: a execução automatizada de testes ainda não está padronizada/estável no ambiente de desenvolvimento. Priorizar validação manual no admin até o setup ser concluído.

## Test Types
- **Feature Tests**: Located in `tests/Feature`. These test HTTP endpoints, Livewire components, and Filament Resources.
    - Framework: PHPUnit (default) or Pest (if configured).
    - Naming: `*Test.php`.
- **Unit Tests**: Located in `tests/Unit`. These test individual methods in Models or Support classes.
    - Framework: PHPUnit.
- **Browser Tests**: (Optional) Laravel Dusk can be used for browser automation if needed.

## Running Tests
Quando o ambiente estiver pronto, documentar aqui o comando padrão (Sail/CLI) para execução do PHPUnit/Pest.

## Quality Gates
- **Antes de automatizar**: exigir validação manual do fluxo no Filament (CRUD + regras críticas).
- **Depois de automatizar**: definir gate de CI (pass rate, lint e análise estática) conforme pipeline do projeto.

## Troubleshooting
- **Database State**: Tests run in a transaction (via `RefreshDatabase` trait), so data is rolled back after each test.
- **Environment**: Ensure `.env.testing` exists if specific test configurations are needed.

## Cross-References
- [development-workflow.md](./development-workflow.md)
