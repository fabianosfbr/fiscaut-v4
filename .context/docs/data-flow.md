# Data Flow and Integrations

This document details the movement of data through the Fiscaut v4.1 application. It covers the path from user interaction in the Filament Admin Panel to database persistence and external service integrations.

## Architecture Overview

Fiscaut utilizes the **TALL stack** (Tailwind CSS, Alpine.js, Laravel, and Livewire). Data flow is primarily reactive, leveraging Livewire to synchronize the frontend state with backend logic.

### Core Components
- **Frontend (UI)**: Alpine.js and Filament components (found in `public/js/filament` and `vendor/filament`) manage the browser-side state and interactions.
- **Transport**: Livewire handles asynchronous AJAX requests to bridge the client and the server.
- **Business Logic**: Filament Resources (located in `app/Filament/Resources`) define schemas, validation, and authorization.
- **Persistence**: Eloquent Models (`app/Models`) interface with the MySQL database.

---

## Request Lifecycle

### 1. User Interaction
Users interact with components such as:
- **Form Fields**: `TextInput`, `Select`, `RichEditor`, or `DateTimePicker` (e.g., `vendor/filament/forms/resources/js/components/date-time-picker.js`).
- **Table Actions**: Sorting, filtering, or clicking actions like "Edit" or "Delete" in `FilamentTableColumnManager`.

### 2. Processing & Validation
- **State Synchronization**: As users type or select options, Livewire intercepts these changes and sends updates to the server.
- **Server-Side Validation**: Rules defined in Resource files (e.g., `CategoryTagForm.php`) are executed. If validation fails, a `ValidationException` is thrown, and errors are returned to the UI.
- **Authorization**: Laravel Policies (`app/Policies`) verify that the authenticated user has the necessary permissions (e.g., `view`, `create`, `update`) for the specific resource.

### 3. Persistence & Hooks
Validated data is persisted via Eloquent.
- **Standard Flow**: Filament's `CreateRecord` and `EditRecord` pages handle the saving process automatically.
- **Lifecycle Hooks**: Custom logic can be injected using hooks such as `mutateFormDataBeforeCreate` or `afterSave`. For example, in `CreateIssuer.php`, company data might be enriched before being stored.

### 4. UI Feedback
- **Notifications**: The system uses the `Notification` class (`vendor/filament/notifications/resources/js/Notification.js`) to send toast messages (Success, Warning, Danger) to the user.
- **DOM Updates**: Livewire performs DOM diffing to update only the modified parts of the page, ensuring a smooth SPA-like experience.

---

## Data Scoping & Multi-Tenancy

Fiscaut implements strict data isolation to ensure security and privacy between different entities.

### Tenant Isolation (`tenant_id`)
Most database tables include a `tenant_id` column. Global scopes are applied to Eloquent models to ensure that users only see data belonging to their specific organization or account.

### Issuer Context (`issuer_id`)
Many fiscal operations require a specific "Active Issuer" (Empresa Atual).
- **Session Context**: The current `issuer_id` is typically stored in the user's session.
- **Query Scoping**: Resources like `CategoryTagsTable.php` filter records based on both the active `tenant_id` and the selected `issuer_id`.

---

## Key Reference Flows

### Issuer (Company) Registration
The creation of an "Issuer" involves several integration points:
1. **CNPJ Lookup**: The system fetches public registration data via an external API.
2. **Encryption**: Sensitive data, such as digital certificate passwords, is encrypted before being stored in the database.
3. **Permission Granting**: Upon successful creation, the system automatically creates a permission record linking the creator to the new Issuer.
4. **Reference**: `app/Filament/Resources/Issuers/Pages/CreateIssuer.php`.

### Fiscal Document Processing
Handling fiscal documents (NF-e, NFC-e) involves specialized actions:
- **Certificate Management**: `DownloadCertificadoAction.php` handles the retrieval and streaming of stored digital certificates.
- **Service Configuration**: `GerenciarServicoAction.php` allows users to enable or disable specific communication services with SEFAZ (the Brazilian tax authority).

---

## Internal & Background Processes

### Events & Listeners
Fiscaut uses Laravel's Event system to decouple secondary tasks:
- **User Created**: Triggers default setting initialization.
- **Document Issued**: Triggers email notifications to customers.

### Background Jobs & Queues
Resource-intensive tasks are processed asynchronously:
- **Heavy Reporting**: Generation of complex fiscal summaries.
- **SEFAZ Synchronization**: Polling government web services for document status updates or synchronization.

---

## External Integrations

| Integration | Type | Purpose |
| :--- | :--- | :--- |
| **MySQL** | Database | Primary relational storage. |
| **Filesystem** | Local/S3 | Storage for encrypted `.pfx` certificates and generated XML/PDF documents. |
| **Fiscal APIs** | External | Integration with government services for document validation, signing, and transmission. |

---

## Observability & Debugging

### Logging
System events, integration errors, and exceptions are logged to `storage/logs/laravel.log`. For fiscal integrations, checking these logs is critical for identifying communication failures with external APIs.

### Error Handling
- **Frontend**: Alpine.js catches client-side UI errors.
- **Backend**: Configured in `bootstrap/app.php`, the application uses standard Laravel exception handling. In development, detailed stack traces are available via the Whoops handler.

---

## Cross-References
- [Architecture Overview](./architecture.md)
- **Model Definitions**: Located in `app/Models/`
- **Filament Configuration**: Located in `app/Filament/Resources/`
