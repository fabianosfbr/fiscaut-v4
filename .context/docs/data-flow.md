# Data Flow and Integrations

This document describes how data moves through the Fiscaut v4.1 ecosystem. It covers the reactive cycle between the frontend and backend, state management within the TALL stack, and specific fiscal data handling.

## Architecture Overview

Fiscaut follows the **TALL stack** architecture, which defines a specific flow for data synchronization:

1.  **Tailwind CSS**: Utility-first styling for the UI.
2.  **Alpine.js**: Handles local browser state and lightweight client-side interactions.
3.  **Laravel**: The backend core providing business logic, security, and persistence.
4.  **Livewire**: Acts as the bridge, synchronizing the Alpine.js state with the Laravel backend via AJAX.

## The Reactive Data Loop

The primary data flow mechanism in Fiscaut is the **Livewire Request/Response cycle**.

### 1. Client-Side Capture
When a user interacts with a Filament component (e.g., entering data into a `TextInput` or selecting a date in `vendor/filament/forms/resources/js/components/date-time-picker.js`), Alpine.js captures the input event.

### 2. State Synchronization
Livewire intercepts these changes. For complex components, utility functions like `findClosestLivewireComponent` (defined in `vendor/filament/support/resources/js/partials.js`) are used to locate the relevant backend component and dispatch updates.

### 3. Server-Side Processing
The backend receives the updated state and performs:
-   **Validation**: Executes rules defined in the Resource (e.g., `CategoryTagForm.php`).
-   **Authorization**: Checks Laravel Policies to ensure the user has the `update` or `create` capability.
-   **Lifecycle Hooks**: Functions like `mutateFormDataBeforeCreate()` are called to transform data before it hits the database.

### 4. DOM Diffing & UI Feedback
The server sends back a JSON payload containing the new HTML and state. Livewire performs a "DOM diff," updating only the modified elements. Notifications are often triggered at this stage using the `Notification` class:

```javascript
// Example of a notification being triggered from the JS side
new Notification()
    .title('Saved successfully')
    .success()
    .send();
```

---

## Data Scoping & Isolation

Fiscaut uses a multi-layered approach to ensure data security and organization.

### Multi-Tenancy (`tenant_id`)
Most models are scoped by a `tenant_id`. This is typically handled via a Global Scope in Eloquent, ensuring that a user in "Company A" cannot accidentally query or modify records belonging to "Company B."

### Issuer Context (`issuer_id`)
In a fiscal context, a single user or tenant may manage multiple companies (Issuers).
-   **Active Issuer**: The application maintains an "Active Issuer" context, usually stored in the session.
-   **Filtering**: Resources like `CategoryTagsTable.php` filter results based on the current `issuer_id`.

---

## Fiscal Document Lifecycle

Processing fiscal documents (NF-e, NFC-e) involves specific integration patterns:

### Certificate Management
Digital certificates (`.pfx` files) are required for signing XML documents.
1.  **Storage**: Certificates and passwords are stored in the `issuers` table (encrypted at rest) and decrypted only at runtime.
2.  **Retrieval**: Services load `Issuer->certificado_content` and `Issuer->senha_certificado` and build a `NFePHP\Common\Certificate` instance in memory.
3.  **Communication**: The system interacts with SEFAZ web services (Distribuição DF-e / Manifestação) using the certificate for authentication.

### Background Synchronization
Since SEFAZ integrations can be slow or intermittent, heavy operations are offloaded to background queues:
-   **Job Dispatch**: When a user clicks "Synchronize," a background job is dispatched.
-   **Polling**: The UI may poll for the status of these background jobs or receive an update via a WebSocket event.

---

## Leitura de documentos via SEFAZ (Distribuição DF-e)

O fluxo de “leitura” (distribuição de DF-e via NSU) é dividido em 2 etapas: **consulta do lote** e **processamento por docZip**.

### NF-e (modelo 55)

- Serviço: `app/Services/Sefaz/NfeService.php`
- Checkpoint por empresa (Issuer):
  - `issuers.ult_nsu_nfe` (último NSU processado)
  - `issuers.ultima_consulta_nfe` (timestamp de consulta)
- Execução (alto nível):
  - Monta config (tpAmb, UF, CNPJ, schemes/versão) e chama `NFePHP\NFe\Tools->sefazDistDFe($ultNSU, 0)`.
  - Quando há docZip no retorno, despacha `ProcessResponseNfeSefazJob` (fila `high`) e cada docZip é processado em `ProcessXmlResponseNfeSefazJob` (fila `low`).
  - Os docZip são decodificados como `base64` + `gzdecode` (XML original).
- Persistência:
  - Resumo (`resNFe`): `LogSefazResumoNfe::updateOrCreate(...)` guarda metadados e XML (string).
  - Documento completo (`nfeProc`): `NotaFiscalEletronica::updateOrCreate(...)` guarda XML comprimido (`gzcompress`) e metadados extraídos do `nfeProc`.
- Eventos:
  - `resEvento`, `procEventoNFe` e `evento` são direcionados para rotinas de log e não alteram o status do documento diretamente.
- Manifestação:
  - `NfeService::manifestaCienciaDaOperacao()` usa `Tools->sefazManifesta()` e registra `LogSefazManifestoEvent`, além de marcar `LogSefazResumoNfe.is_ciente_operacao`.

### CT-e (Distribuição DF-e)

- Serviço: `app/Services/Sefaz/CteService.php`
- Checkpoint por empresa (Issuer):
  - `issuers.ult_nsu_cte`
  - `issuers.ultima_consulta_cte`
- Execução (alto nível):
  - Monta config (tpAmb, UF, CNPJ, schemes/versão) e chama `NFePHP\CTe\Tools->sefazDistDFe($ultNSU, 0)`.
  - Despacha `ProcessResponseCteSefazJob` (fila `high`) e processa docZip em `ProcessXmlResponseCteSefazJob` (fila `low`), também via `base64` + `gzdecode`.
- Persistência:
  - Documento completo (`cteProc`): `ConhecimentoTransporteEletronico::updateOrCreate(...)` guarda XML comprimido (`gzcompress`) e metadados do `cteProc`.
  - Se o CT-e contiver chaves de NF-e vinculadas, despacha `CheckNfeData` (fila `low`) para enriquecer/associar dados.
- Eventos:
  - `procEventoCTe`, `eventoCTe` e `evento` são direcionados para rotinas de log.

### Restrições e throttling

- Ambos os serviços limitam o loop de consulta (até 50 iterações) e aplicam `sleep(8)` entre consultas.
- O processamento para quando `cStat` indicar “sem documentos” ou “consumo indevido” (`137`/`656`) e grava o checkpoint no Issuer quando `ultNSU` está disponível.

---

## External Integrations

| Integration | File/Component Reference | Purpose |
| :--- | :--- | :--- |
| **MySQL** | `app/Models/` | Relational data persistence. |
| **SEFAZ APIs** | `app/Services/Sefaz/NfeService.php`, `app/Services/Sefaz/CteService.php` | Distribuição DF-e (NSU/docZip) e manifestação. |
| **CNPJ API** | `CreateIssuer.php` | Automated lookup of company registration data. |
| **Filesystem** | `config/filesystems.php` | Storage of uploaded XML/ZIP files and generated artifacts (ex.: PDFs). |

---

## Importação manual de XML/ZIP (NF-e e CT-e)

Além da leitura por NSU, existe um fluxo de importação manual (upload) para processar XMLs e ZIPs com documentos/eventos:

- UI (Filament): `app/Filament/Pages/Importar/XmlImport.php`
  - Salva o arquivo no disk `local` (diretório `xml-imports`) e cria um batch de processamento.
- Batch/job:
  - `app/Jobs/ProcessXmlFileBatch.php` cria `Bus::batch([ProcessXmlFile...])` e acompanha status.
  - `app/Jobs/ProcessXmlFile.php` lê o arquivo do Storage, extrai XML(s), identifica o tipo e chama:
    - `NfeService->exec($xmlReader, $xml, 'Importacao')` ou
    - `CteService->exec($xmlReader, $xml, 'Importacao')`
- Extração/identificação:
  - `app/Services/Xml/XmlExtractorService.php` extrai `.xml` e varre `.zip`.
  - `app/Services/Xml/XmlIdentifierService.php` infere o tipo pelo elemento raiz (`nfeProc`, `resNFe`, `cteProc`, eventos, etc.).

---

## Error Handling & Debugging

### Frontend Errors
Client-side issues in Alpine.js or Filament components are visible in the browser console. The `vendor/filament/support/resources/js/utilities/select.js` and other utilities include internal checks to prevent common data binding errors.

### Backend Exceptions
-   **Validation Errors**: Automatically caught by Filament/Livewire and displayed as inline form errors.
-   **System Errors**: Logged to `storage/logs/laravel.log`.
-   **Fiscal Communication Errors**: Specific errors from government web services are typically parsed and displayed to the user via "Danger" notifications to facilitate troubleshooting.

### Observação de implementação (logs SEFAZ)
Os serviços e jobs de leitura chamam métodos como `registerLogNfeContent` e `registerLogCteEvent` via o trait `App\Services\Sefaz\Traits\HasLogSefaz`. Se esse trait não estiver presente no projeto, o processamento de leitura pode falhar com erro de classe/trait não encontrado e/ou métodos inexistentes.

---

## Related Documentation
-   **Models**: See `app/Models` for schema definitions.
-   **Resources**: See `app/Filament/Resources` for UI logic and form schemas.
-   **Policies**: See `app/Policies` for data access rules.
