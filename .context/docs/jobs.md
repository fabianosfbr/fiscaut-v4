# Background Jobs

The application processes certain tasks in the background using Laravel's Queue system to prevent blocking the UI and to handle resource-intensive operations efficiently.

## Core Jobs

### 1. ProcessXmlFile
**Location:** `App\Jobs\ProcessXmlFile`
-   **Purpose:** Handles the processing of a single imported XML file (NFe, CTe, or Event) from the file system.
-   **Workflow:**
    1.  Validates file existence.
    2.  Extracts XML content using `XmlExtractorService`.
    3.  Identifies the XML type (NFe, Resumo, Event, CTe) using `XmlIdentifierService`.
    4.  Delegates parsing to `XmlNfeReaderService` or `XmlCteReaderService`.
    5.  Persists the document to the database.
    6.  Updates the `XmlImportJob` progress (processed counts).
-   **Usage:** Triggered when users manually upload XML files via the interface.
-   **Retry Logic:** Configured with 3 attempts and 60-second backoff interval to handle temporary file system issues.

### 2. ProcessXmlFileBatch
**Location:** `App\Jobs\ProcessXmlFileBatch`
-   **Purpose:** Orchestrates the processing of multiple XML files as part of a batch operation.
-   **Workflow:**
    1.  Accepts a collection of XML file paths and associated import job/issuer.
    2.  Dispatches individual `ProcessXmlFile` jobs for each file into a Laravel Bus batch.
    3.  Monitors the batch completion to update the overall import job status.
-   **Usage:** Used for bulk imports of XML files through the admin interface.

## Bulk Actions

### 1. DownloadXmlPdfNfeEmLoteActionJob
**Location:** `App\Jobs\BulkAction\DownloadXmlPdfNfeEmLoteActionJob`
-   **Purpose:** Generates a ZIP archive containing XMLs and/or PDFs (DANFE) for a selected list of NFe records.
-   **Key Features:**
    -   Generates PDFs on-the-fly using `NFePHP\DA\NFe\Danfe`.
    -   Includes credit footer if configured.
    -   Notifies the user via database notification when the ZIP is ready.

### 2. DownloadXmlPdfCteEmLoteActionJob
**Location:** `App\Jobs\BulkAction\DownloadXmlPdfCteEmLoteActionJob`
-   **Purpose:** Generates a ZIP archive containing XMLs and/or PDFs (DACTE) for a selected list of CTe records.
-   **Key Features:**
    -   Generates PDFs on-the-fly using `NFePHP\DA\CTe\Dacte`.
    -   Includes credit footer if configured.
    -   Notifies the user via database notification when the ZIP is ready.

### 3. DownloadUploadFileBulkActionJob
**Location:** `App\Jobs\BulkAction\DownloadUploadFileBulkActionJob`
-   **Purpose:** Creates a ZIP download for generic file uploads (documents managed in the `UploadFileManager`).
-   **Key Features:**
    -   Can organize files into folders based on tags or document types.
    -   Handles multi-tagged files by placing them in a special folder.
    -   Notifies the user upon completion.

## Fiscal (SEFAZ) Jobs

These jobs manage the asynchronous downloading and processing of documents from the SEFAZ national environment. They are designed to work in batches to handle large volumes of data.

### 1. NFe Download Pipeline

#### SefazNfeDownloadAndProcessBatchJob
**Location:** `App\Jobs\Sefaz\SefazNfeDownloadAndProcessBatchJob`
-   **Role:** The entry point (Coordinator).
-   **Logic:**
    1.  Calls `SefazNfeDownloadService` to fetch a batch of distinct documents using the last known NSU.
    2.  Creates a tracking record (`XmlImportJob`).
    3.  Dispatches a `SefazNfeDownloadBatchJob` to process the found documents.

#### SefazNfeDownloadBatchJob
**Location:** `App\Jobs\Sefaz\SefazNfeDownloadBatchJob`
-   **Role:** The Batch Manager.
-   **Logic:**
    -   Takes the list of raw documents found by the coordinator.
    -   Spawns individual `SefazNfeProcessDocumentJob` for each document into a bus batch.
    -   Monitors the batch completion to update the status of the `XmlImportJob` to 'COMPLETED'.

#### SefazNfeProcessDocumentJob
**Location:** `App\Jobs\Sefaz\SefazNfeProcessDocumentJob`
-   **Role:** The Worker.
-   **Logic:**
    -   Receives a single raw document array (NSU, content, type).
    -   Reads the XML using `XmlNfeReaderService`.
    -   Persists the NFe, Summary, or Event to the database.
    -   Logs the raw content in `LogSefazNfeContent`.

### 2. CTe Download Pipeline

Follows the same Coordinator -> Batch Manager -> Worker pattern as NFe.

-   **Coordinator:** `SefazCteDownloadAndProcessBatchJob`
-   **Batch Manager:** `SefazCteDownloadBatchJob`
-   **Worker:** `SefazCteProcessDocumentJob`
    -   Uses `XmlCteReaderService` for parsing CTe documents.
    -   Logs content to `LogSefazCteContent`.

### 3. Sieg Integration Pipeline

Integration with the SIEG API for bulk downloading of fiscal documents.

#### SiegConnect
**Location:** `App\Jobs\Sieg\SiegConnect`
-   **Role:** The Connector / Coordinator.
-   **Logic:**
    -   Connects to the SIEG API (`BaixarXmlsV2`) using the tenant's API key.
    -   Iterates through pages of results using `Skip` and `Take`.
    -   creates a tracking `XmlImportJob`.
    -   Dispatches `ProcessXmlSiegBatch` to process downloaded XMLs.
    -   Handles API polling limits (sleeping 300ms between requests).

#### ProcessXmlSiegBatch
**Location:** `App\Jobs\Sieg\ProcessXmlSiegBatch`
-   **Role:** The Batch Manager.
-   **Logic:**
    -   Receives a list of base64 encoded XMLs.
    -   Chunks them into smaller groups (batch size 50).
    -   Decodes base64 content.
    -   Dispatches individual `ProcessXmlSieg` jobs into a bus batch.
    -   Monitors completion to update `XmlImportJob` status and notify the user.

#### ProcessXmlSieg
**Location:** `App\Jobs\Sieg\ProcessXmlSieg`
-   **Role:** The Worker.
-   **Logic:**
    -   Identifies the XML type using `XmlIdentifierService`.
    -   Delegates parsing to `XmlNfeReaderService` (for NFe, Events, Summaries) or `XmlCteReaderService` (for CTe, Events).
    -   Sets the origin as 'SIEG'.
    -   Persists data to the database.

### 4. Cross-Referencing

#### CheckNfeData
**Location:** `App\Jobs\Sefaz\CheckNfeData`
-   **Purpose:** Link CTe documents to their related NFe documents.
-   **Logic:**
    -   Parses the `nfe_chave` field from a CTe.
    -   Looks up the referenced NFe in the database.
    -   **Tag Inheritance:** If found, copies tags from the NFe to the CTe.
    -   **Metadata sync:** Copies values like issuer/recipient and values from NFe to CTe metadata for reporting.
