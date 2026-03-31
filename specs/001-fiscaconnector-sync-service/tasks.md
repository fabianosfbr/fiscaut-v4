---

description: "Task list for FiscautConnector Sync Service"
---

# Tasks: FiscautConnector Sync Service

**Input**: Design documents from `/specs/001-fiscaconnector-sync-service/`
**Prerequisites**: plan.md (required), spec.md (required), research.md
**Testing**: Pest (PHPUnit-compatible) + Laravel Sail
**Dev Environment**: Laravel Sail (Docker)

**Note**: Tests are requested (SC-004 in spec.md requires unit test coverage).

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g. US1)
- Include exact file paths in descriptions

## Path Conventions

- Exception: `app/Exceptions/`
- Service: `app/Services/`
- Config: `config/admin.php`
- Env: `.env.example`
- Tests: `tests/Unit/`
- Sail: `./vendor/bin/sail`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Configuration and exception infrastructure needed by the service

- [X] T001 Add `fiscaconnector_url` and `fiscaconnector_api_key` config keys in `config/admin.php` following existing pattern (e.g., `cnpj_ja_api_key` on line 30)
- [X] T002 Add `FISCAUTCONNECTOR_URL=` and `FISCAUTCONNECTOR_API_KEY=` entries in `.env.example` following existing API key pattern

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before the user story can be implemented

- [X] T003 Create `FiscautConnectorException` in `app/Exceptions/FiscautConnectorException.php` extending `Exception`, with constructor accepting `string $message` and optional `?string $previous`

**Checkpoint**: Exception ready — service implementation can now begin

---

## Phase 3: User Story 1 - FiscautConnector Sync Trigger (Priority: P1)

**Goal**: `FiscautConnectorService` sends a POST request to FiscautConnector with `cgc_emp` and `sync=true`, returns `true`/`false` based on API response, throws `FiscautConnectorException` on HTTP errors.

**Independent Test**: All scenarios tested via `tests/Unit/FiscautConnectorServiceTest.php` using Pest + `Http::fake()` — no network or database required.

### Tests for User Story 1 ⚠️

> **NOTE: Write these tests FIRST using Pest syntax, ensure they FAIL before implementation**

- [X] T004 [P] [US1] Create `tests/Unit/FiscautConnectorServiceTest.php` using Pest with `@group fiscaconnector`:
  ```php
  <?php
  use App\Services\FiscautConnectorService;
  use App\Exceptions\FiscautConnectorException;

  describe('FiscautConnectorService', function () {
      // tests go here
  });
  ```
- [X] T005 [P] [US1] Add Pest test: `it_sends_post_with_cgc_emp_and_sync_true` — use `Http::fake()` with a callback to assert body contains `cgc_emp` and `sync === true`
- [X] T006 [P] [US1] Add Pest test: `it_returns_true_when_status_is_ok` — `Http::fake([... => Http::response(['status' => 'OK'], 200)])`, expect service returns `true`
- [X] T007 [P] [US1] Add Pest test: `it_returns_false_when_status_is_not_ok` — `Http::fake([... => Http::response(['status' => 'ERROR'], 200)])`, expect service returns `false`
- [X] T008 [P] [US1] Add Pest test: `it_throws_exception_on_http_4xx` — `Http::fake([... => Http::response([], 404)])`, expect `FiscautConnectorException`
- [X] T009 [P] [US1] Add Pest test: `it_throws_exception_when_api_key_is_missing` — use `Config::shouldReceive('get')->with('admin.fiscaconnector_api_key')->andReturn(null)` to simulate missing API key, expect `FiscautConnectorException`
- [X] T010 [P] [US1] Add Pest test: `it_uses_bearer_token_authentication` — use `Http::fake()` + `Http::assertSent(function ($request) use ($apiKey) { return str_contains($request->header('Authorization')[0] ?? '', $apiKey); })`

### Implementation for User Story 1

- [X] T011 [US1] Create `app/Services/FiscautConnectorService.php`:
  - `__construct(string $cgcEmp)` — stores `$this->cgcEmp`
  - `sync(): bool` — reads `config('admin.fiscaconnector_url')` + `config('admin.fiscaconnector_api_key')`, builds payload `['cgc_emp' => $this->cgcEmp, 'sync' => true]`, sends POST via `Http::withToken($apiKey)->post($url, $payload)`, returns `true`/`false` based on `status === 'OK'`, throws `FiscautConnectorException` on HTTP failure or missing API key, logs errors via `Log::error()`
  - Use `type` declarations on all parameters and return types

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: Polish & Cross-Cutting Concerns

**Purpose**: Final validation and formatting

- [X] T012 Run `./vendor/bin/sail bin pint` to format all modified files
- [X] T013 Run `./vendor/bin/sail artisan test --filter=fiscaconnector` to verify all Pest tests pass

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: No dependencies — can start immediately, but must complete before service implementation
- **User Story 1 (Phase 3)**: Depends on Phase 2 (exception exists) — tests must be written and fail before T011
- **Polish (Phase 4)**: Depends on Phase 3 completion

### Within User Story 1

- All tests (T004–T010) can run in parallel — they are in the same file but use different Pest scenarios
- Service implementation (T011) MUST complete before tests can pass
- Tests (T004–T010) MUST be written before T011

### Parallel Opportunities

- All tests (T004–T010) can be written in parallel
- Config additions (T001, T002) can run in parallel with each other and with T003
- T003 (exception) can run in parallel with T001/T002

---

## Parallel Example: Phase 1 + Phase 2 (all parallel)

```bash
# All of these can run simultaneously:
Task: "T001 Add fiscaconnector config keys in config/admin.php"
Task: "T002 Add env entries in .env.example"
Task: "T003 Create FiscautConnectorException in app/Exceptions/"
```

---

## Implementation Strategy

### MVP First (User Story 1 only)

1. Complete Phase 1: Config + Env
2. Complete Phase 2: Exception
3. Write all tests (Phase 3 — tests) using Pest — ensure they FAIL
4. Implement service (Phase 3 — implementation) — tests should now PASS
5. Run Pint + test suite (Phase 4)

---

## Notes

- All tasks include exact file paths for direct execution
- Service must follow PSR-12 and have return type declarations (Constitution §I)
- **Testing: Pest framework** — use `describe/it` blocks, `expect()` assertions, and `Http::fake()` for isolated tests (Constitution §II)
- **Dev environment: Laravel Sail** — all artisan/npm commands wrapped with `./vendor/bin/sail`
- No database or network required for tests — use `Http::fake()` (Constitution §II)
- API key must NOT be hardcoded — use `config()` (Constitution §V)
- Bearer token auth via `Http::withToken()` as confirmed in research
