# Implementation Plan: FiscautConnector Sync Service

**Branch**: `001-fiscaconnector-sync-service` | **Date**: 2026-03-20 | **Spec**: specs/001-fiscaconnector-sync-service/spec.md
**Input**: Feature specification from `/specs/001-fiscaconnector-sync-service/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Criar o serviço `FiscautConnectorService` que envia uma requisição POST para o FiscautConnector com as credenciais de API do tenant, passando `cgc_emp` (CNPJ do issuer atual via construtor) e `sync = true`, e verificando se a resposta é OK.

## Technical Context

**Language/Version**: PHP 8.2+ (Laravel 12)  
**Primary Dependencies**: Laravel Http facade, Filament Notifications  
**Storage**: N/A  
**Testing**: Pest/PHPUnit (`tests/Unit/FiscautConnectorServiceTest.php`)  
**Target Platform**: Linux server (Laravel Sail / Docker)  
**Project Type**: Laravel service class (stateful, constructor-injected)  
**Performance Goals**: Resposta síncrona < 5s (chamada de API externa)  
**Constraints**: API key não deve ser logada; multi-tenancy deve ser respeitada  
**Scale/Scope**: Chamada pontual via ação de menu ou botão

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Code Quality (PSR-12, type hints, Pint) | ✅ PASS | Service class follows PSR-12; type hints on all params/returns |
| II. Testing Standards (unit tests) | ✅ PASS | Unit tests with Http fake for isolated logic |
| III. UX Consistency | N/A | No UI component — pure service |
| IV. Performance (async-first) | ✅ PASS | Esta é uma chamada de sincronização sob demanda, não um pipeline de alto volume — processamento síncrono é aceitável aqui |
| V. Security (no secrets in code) | ✅ PASS | API key comes from env/config; no hardcoding |

**No violations requiring justification.**

## Project Structure

### Documentation (this feature)

```text
specs/001-fiscaconnector-sync-service/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 output
└── tasks.md             # Phase 2 output (/speckit.tasks)
```

### Source Code (repository root)

```text
app/
├── Exceptions/
│   └── FiscautConnectorException.php    # Custom exception
└── Services/
    └── FiscautConnectorService.php      # Main service

tests/
└── Unit/
    └── FiscautConnectorServiceTest.php   # Unit tests
```

**Structure Decision**: Seguindo a convenção existente em `app/Services/` (ex.: `CnpjJaService.php`). O serviço é stateful com `cgc_emp` injetado via construtor. Exceção customizada em `app/Exceptions/` seguindo convenção Laravel. Autenticação via `Http::withToken()`.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

Nenhuma violação — todos os princípios são satisfeitos com a estrutura proposta.

## Technical Notes

### Config Keys Needed

Adicionar em `config/admin.php`:
```php
'fiscaconnector_url' => env('FISCAUTCONNECTOR_URL'),
'fiscaconnector_api_key' => env('FISCAUTCONNECTOR_API_KEY'),
```

### Expected API Contract

- **Endpoint**: `POST {fiscaconnector_url}/sync`
- **Headers**: `Authorization: Bearer {api_key}` (via `Http::withToken()`), `Content-Type: application/json`, `Accept: application/json`
- **Body**:
  ```json
  {
    "cgc_emp": "12345678000199",
    "sync": true
  }
  ```
- **Success Response**: HTTP 200, `{"status": "OK"}` ou `{"status": "OK", "data": {...}}`
- **Error Response**: HTTP 4xx/5xx ou `{"status": "ERROR", "message": "..."}`

## Implementation Steps

1. Criar `FiscautConnectorException` em `app/Exceptions/`
2. Criar `FiscautConnectorService` em `app/Services/` — `__construct(string $cgcEmp)` + `sync(): bool`
3. Adicionar config keys em `config/admin.php`
4. Adicionar entries no `.env.example`
5. Criar unit tests em `tests/Unit/FiscautConnectorServiceTest.php`
6. Executar Pint para formatar
