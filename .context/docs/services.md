# Service Layer Documentation

This document provides an overview of the dedicated service classes located in `app/Services`. These services encapsulate complex business logic, external API integrations, and system management tasks.

## Core Services

### CertificateService
Location: `App\Services\CertificateService`

Handles all operations related to digital certificates (PKCS#12).
*   **Validation**: Verifies if a certificate file is valid and its password is correct.
*   **Extraction**: Parses X.509 data to extract CNPJ, Company Name (Razão Social), and validity dates.
*   **Security**: Encrypts/Decrypts certificate content for storage using Laravel's `Crypt` facade.
*   **Legacy Support**: Includes a fallback mechanism using OpenSSL CLI to handle legacy algorithms (like RC2/3DES) often found in older .pfx files which may fail with newer OpenSSL 3+ libraries.

### CfopCacheService
Location: `App\Services\CfopCacheService`

Manages the caching strategy for CFOP (Código Fiscal de Operações e Prestações) configurations.
*   **Scope**: Caches data per-issuer (`issuer_id`) to ensure tenant isolation.
*   **Performance**: Stores "faturamento" (billing) status of CFOPs in memory/cache to avoid repeated database hits during tax calculations.
*   **TTL**: Default Time-To-Live is 1 hour.
*   **Operations**: `getCfopsFaturamento`, `isCfopFaturamento`, `warmCache`, `clearCache`.

### CnpjJaService
Location: `App\Services\CnpjJaService`

A client wrapper for the **CNPJ.já** API.
*   **Purpose**: Retreive up-to-date company data (Razão Social, Address, CNAEs) using a CNPJ.
*   **Usage**: Used mainly during onboarding or when registering new Issuers/Customers.
*   **Config**: Uses `admin.cnpj_ja_api_key` for authentication.

### CommandService
Location: `App\Services\CommandService`

Provides introspection capabilities for Artisan commands.
*   **Functionality**: Lists available system commands, including their signatures, descriptions, arguments, and options.
*   **Filtering**: Can filter commands based on configuration (`schedule.commands.supported` or `exclude`).
*   **Use Case**: Primarily used by the Task Scheduler UI to allow users to select which commands to schedule.

### ScheduleService & ScheduleHistoryService
Location: `App\Services\ScheduleService`

Manages the execution and history of scheduled tasks (Cron jobs).
*   **ScheduleService**: Retrieves active scheduled tasks, supporting caching to reduce database load.
*   **ScheduleHistoryService**: Prunes old execution logs based on a configured `max_history_count` to prevent database bloat.

## Fiscal Services (Sefaz)
Location: `App\Services\Sefaz`

These services manage direct communication with SEFAZ (Secretaria da Fazenda) web services.

### SefazNfeDownloadService
Handles the "DistribuicaoDFe" (Distribution) and "Manifestacao" (Manifestation) API for NFe (Nota Fiscal Eletrônica).
*   **Batch Download**: Automates the loop of fetching documents (NSU crawling) using NFePHP, handling SEFAZ limits (maxNSU, timeouts).
*   **Resiliency**: Handles SEFAZ hiccups (cStat 656/137), manages sleep intervals, and implements a Mock system for testing.
*   **State Management**: Automatically updates `ult_nsu_nfe` on the Issuer model to track progress.

### SefazCteDownloadService
Equivalent functionality to NFe service but for CTe (Conhecimento de Transporte Eletrônico).
*   **Implementation**: Uses `NFePHP\CTe\Tools` for direct communication.
*   **Tracking**: Manages `ult_nsu_cte` and `ultima_consulta_cte` state.

### SefazNfseDownloadService
Manages NFSe (Nota Fiscal de Serviço Eletrônica) downloads via the National ADN (Ambiente de Dados Nacional).
*   **Protocol**: Uses direct CURL/SSL calls with certificates to `adn.nfse.gov.br`.
*   **Processing**: Handles JSON responses from ADN, decodes base64/zipped XMLs, and identifies document types.
*   **DANFSE**: Provides functionality to retrieve the DANFSE PDF for specific access keys.

### CfeSatService
Specialized service for querying SAT batches (`CfeConsultarLotes`) via direct SOAP/CURL implementation. It also handles the parsing and persistence of CFe (Cupom Fiscal Eletrônico) into the `CupomFiscalEletronico` model.


## XML Services
Location: `App\Services\Xml`

> **Note**: For detailed documentation on XML Reading, see [`xml-reader-service.md`](./xml-reader-service.md).

### XmlIdentifierService
Location: `App\Services\Xml\XmlIdentifierService`

A utility to detect the type of an XML document string.
*   **Detection**: Scans XML content to identify if it is an NFe, CTe, Event, Inutilization, etc.
*   **Usage**: Used by Import Jobs to delegate the file to the correct processor.

---

**Tagging Services**
*   `Tagging\TagSuggestionService`: Suggests tags for new documents (NFe) by analyzing historical tag usage patterns for a specific issuer CNPJ (most frequently used tags).
