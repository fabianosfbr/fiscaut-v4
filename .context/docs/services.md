# Service Layer (`app/Services`)

This document describes the dedicated service classes located in `app/Services`. Services encapsulate:

- **Complex business logic** (rules, calculations, workflows)
- **External integrations** (SEFAZ, CNPJ.já, NFSe ADN)
- **System management concerns** (scheduling, command introspection, caching)
- **Cross-cutting helpers** (certificate handling, XML identification)

## Conventions & How Services Are Used

### Typical responsibilities

A service in this repository usually:

- Exposes a small set of **public methods** representing business operations
- Depends on **framework components** (Cache, Crypt, Http, DB, logging)
- Keeps controllers/jobs thin by centralizing:
  - validation and preconditions
  - retries and resilience logic
  - caching strategies
  - parsing/normalization of external responses

### Common call sites

Services are commonly invoked from:

- **Jobs** (imports, background downloads, scheduled tasks)
- **Controllers** (onboarding flows, user actions)
- **Console/Artisan commands** (manual operations, system tasks)
- **Other services** (composition)

### Related docs

- XML reading/processing details: [`xml-reader-service.md`](./xml-reader-service.md)

---

## Core Services

### `CertificateService`
**Namespace / Location**: `App\Services\CertificateService`

Handles operations related to **digital certificates (PKCS#12 / .pfx)** used by fiscal integrations.

#### Responsibilities

- **Validation**
  - Checks if a PFX file is valid
  - Verifies whether the provided password decrypts the certificate
- **Extraction**
  - Parses X.509 data to extract:
    - **CNPJ**
    - **Razão Social** (Company Name)
    - **Validity period** (start/end)
- **Secure storage**
  - Encrypts/decrypts certificate contents for persistence using Laravel’s `Crypt` facade
- **Legacy algorithm support**
  - Includes a fallback mechanism using the **OpenSSL CLI** to support older PFX algorithms (e.g., RC2/3DES) that may fail under OpenSSL 3+

#### When to use

- Uploading or rotating issuer certificates
- Validating certificates during onboarding
- Preparing certificate material to be used by SEFAZ/NFSe tools

#### Notes / pitfalls

- Older `.pfx` files may fail with the PHP/OpenSSL bindings; the service’s CLI fallback is essential for compatibility.
- Treat decrypted certificate content as sensitive data; avoid logging.

---

### `CfopCacheService`
**Namespace / Location**: `App\Services\CfopCacheService`

Manages caching of CFOP configuration, especially the “faturamento” (billing) status used frequently during fiscal/tax calculations.

#### Responsibilities

- **Tenant isolation**
  - Cache is scoped per issuer via `issuer_id`
- **Performance**
  - Avoids repeated DB hits during calculations by caching CFOP “faturamento” flags
- **TTL**
  - Default TTL: **1 hour**
- **Operations**
  - `getCfopsFaturamento`
  - `isCfopFaturamento`
  - `warmCache`
  - `clearCache`

#### When to use

- Any hot path that repeatedly checks whether a CFOP is treated as “faturamento”
- Pre-warming caches after config changes

#### Example usage (conceptual)

```php
$cache = app(\App\Services\CfopCacheService::class);

$cache->warmCache($issuerId);

if ($cache->isCfopFaturamento($issuerId, $cfopCode)) {
    // Apply billing rules...
}
```

---

### `CnpjJaService`
**Namespace / Location**: `App\Services\CnpjJaService`

Client wrapper for the **CNPJ.já** API, used to fetch authoritative company data by CNPJ.

#### Responsibilities

- Retrieves up-to-date company information:
  - Razão Social
  - Address
  - CNAEs
  - Other registration details (as provided by the API)
- Normalizes data for use in onboarding flows

#### Configuration

- Authentication key: `admin.cnpj_ja_api_key`

#### When to use

- Issuer/customer onboarding
- Auto-filling company registration forms
- Refreshing company data

---

### `CommandService`
**Namespace / Location**: `App\Services\CommandService`

Provides introspection utilities for Artisan commands—used primarily by the scheduling UI.

#### Responsibilities

- Lists available Artisan commands with:
  - signature
  - description
  - arguments
  - options
- Filters commands:
  - allowlist via `schedule.commands.supported`
  - or exclusion list (`exclude`)

#### Primary use case

- A Task Scheduler UI where a user picks a supported command to schedule.

---

### `ScheduleService` & `ScheduleHistoryService`
**Namespace / Location**: `App\Services\ScheduleService` (and related history service)

Manages execution and lifecycle of scheduled tasks.

#### `ScheduleService`

- Retrieves active scheduled tasks
- Supports caching to reduce database load

#### `ScheduleHistoryService`

- Prunes old execution logs to avoid database growth
- Uses configurable `max_history_count`

#### When to use

- Task scheduler UI
- Background scheduling infrastructure (cron-driven execution)
- Maintenance routines to keep history tables small

---

## Fiscal Services (SEFAZ & Related)

**Namespace / Location**: `App\Services\Sefaz`

These services handle direct communication with government fiscal web services and require:

- A valid issuer certificate (often from `CertificateService`)
- Correct environment configuration (production vs homologation, endpoints)
- Careful handling of rate limits, intermittent failures, and state (NSU progress)

### `SefazNfeDownloadService`
Handles NFe “DistribuicaoDFe” and “Manifestacao” flows.

#### Responsibilities

- **Batch downloads / NSU crawling**
  - Repeatedly queries SEFAZ for new documents using NSU iteration
  - Manages SEFAZ constraints (timeouts, max NSU behavior)
- **Resiliency**
  - Handles transient SEFAZ statuses (e.g. `cStat` 656/137)
  - Sleep/retry patterns to avoid hammering endpoints
- **Testing support**
  - Implements a mock mechanism for deterministic test runs
- **State management**
  - Updates issuer progress state:
    - `ult_nsu_nfe`

#### When to use

- Periodic downloads of incoming/outgoing NFe documents
- Automation of distribution retrieval and manifestation workflows

---

### `SefazCteDownloadService`
CTe equivalent of the NFe downloader.

#### Responsibilities

- Uses `NFePHP\CTe\Tools` for CTe communication
- Tracks issuer state:
  - `ult_nsu_cte`
  - `ultima_consulta_cte`

#### When to use

- Periodic retrieval of CTe documents via distribution services

---

### `SefazNfseDownloadService`
NFSe download integration via **ADN (Ambiente de Dados Nacional)**.

#### Responsibilities

- Communicates with `adn.nfse.gov.br` using **direct CURL/SSL** with certificates
- Processes JSON responses:
  - decodes base64 payloads
  - unzips/decodes XMLs when necessary
  - identifies document types
- Provides DANFSE retrieval:
  - fetches the DANFSE PDF for a given access key

#### When to use

- Download and processing of NFSe documents through ADN
- Obtaining DANFSE PDFs for presentation/storage

---

### `CfeSatService`
Service for querying SAT batches and persisting CFe documents.

#### Responsibilities

- Implements `CfeConsultarLotes` via direct SOAP/CURL
- Parses CFe responses and persists data into:
  - `CupomFiscalEletronico` model

#### When to use

- SAT batch consultation flows
- Importing and persisting CFe documents

---

## XML Services

**Namespace / Location**: `App\Services\Xml`

> Detailed XML reading and parsing logic is documented in: [`xml-reader-service.md`](./xml-reader-service.md)

### `XmlIdentifierService`
**Namespace / Location**: `App\Services\Xml\XmlIdentifierService`

Detects the type of an XML document from its content.

#### Responsibilities

- Scans an XML string and identifies whether it is:
  - NFe
  - CTe
  - Event
  - Inutilization
  - and other supported fiscal document types
- Enables import flows to route a given XML to the correct processor

#### When to use

- Upload/import pipelines that accept mixed XML types
- Pre-validation steps before attempting full parsing

#### Example usage (conceptual)

```php
$identifier = app(\App\Services\Xml\XmlIdentifierService::class);

$type = $identifier->identify($xmlString);

switch ($type) {
    case 'nfe':
        // dispatch NFe processor/job
        break;
    case 'cte':
        // dispatch CTe processor/job
        break;
}
```

---

## Tagging Services

### `TagSuggestionService`
**Namespace / Location**: `App\Services\Tagging\TagSuggestionService`

Suggests tags for newly imported documents (notably NFe) based on historical usage for a given issuer CNPJ.

#### Responsibilities

- Analyzes previous tag assignments
- Suggests the most frequent/likely tags for similar documents within the same issuer context

#### When to use

- Enhancing import UX with automatic tag suggestions
- Standardizing tagging across repeated vendors/operations

---

## Implementation Notes (Practical Guidance)

- Prefer injecting services via Laravel’s container:
  - constructor injection in controllers/jobs
  - or `app(Service::class)` in small scripts
- For integrations (SEFAZ/NFSe/SAT), ensure:
  - certificate availability and validity
  - robust error handling and retry strategies
  - state tracking fields are updated transactionally where needed
- For caching (`CfopCacheService`):
  - warm caches after configuration changes
  - clear cache on updates to CFOP configuration

---

## Cross-References

- XML processing details: [`xml-reader-service.md`](./xml-reader-service.md)
- Service implementations: `app/Services/**` (browse by namespace: `Sefaz`, `Xml`, `Tagging`, etc.)
