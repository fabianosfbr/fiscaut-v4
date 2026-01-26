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
1.  **Storage**: Encrypted certificates are stored on the filesystem (Local or S3).
2.  **Retrieval**: The `DownloadCertificadoAction.php` retrieves and decrypts the certificate into memory for signing operations.
3.  **Communication**: The system interacts with SEFAZ (Brazilian Tax Authority) web services using the stored certificate for authentication.

### Background Synchronization
Since SEFAZ integrations can be slow or intermittent, heavy operations are offloaded to background queues:
-   **Job Dispatch**: When a user clicks "Synchronize," a background job is dispatched.
-   **Polling**: The UI may poll for the status of these background jobs or receive an update via a WebSocket event.

---

## External Integrations

| Integration | File/Component Reference | Purpose |
| :--- | :--- | :--- |
| **MySQL** | `app/Models/` | Relational data persistence. |
| **SEFAZ APIs** | `GerenciarServicoAction.php` | Transmission of fiscal documents (NF-e, NFC-e). |
| **CNPJ API** | `CreateIssuer.php` | Automated lookup of company registration data. |
| **Filesystem** | `config/filesystems.php` | Storage of XML, PDF reports, and PFX certificates. |

---

## Error Handling & Debugging

### Frontend Errors
Client-side issues in Alpine.js or Filament components are visible in the browser console. The `vendor/filament/support/resources/js/utilities/select.js` and other utilities include internal checks to prevent common data binding errors.

### Backend Exceptions
-   **Validation Errors**: Automatically caught by Filament/Livewire and displayed as inline form errors.
-   **System Errors**: Logged to `storage/logs/laravel.log`.
-   **Fiscal Communication Errors**: Specific errors from government web services are typically parsed and displayed to the user via "Danger" notifications to facilitate troubleshooting.

---

## Related Documentation
-   **Models**: See `app/Models` for schema definitions.
-   **Resources**: See `app/Filament/Resources` for UI logic and form schemas.
-   **Policies**: See `app/Policies` for data access rules.
