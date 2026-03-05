---
source: .continue/rules/README.md
type: continue
---

# Quality Assurance and Developer Q&A

Welcome to the central documentation hub for **Fiscaut-v4.1**. This guide provides developers and QA engineers with a technical overview of the system architecture, component structures, and testing priorities.

Fiscaut-v4.1 is a robust web-api and administrative platform built on the **Laravel** framework, leveraging **Filament v3** for its UI/UX and **Livewire** for real-time reactivity.

---

## 🚀 Getting Started

Before contributing or testing, ensure your environment matches the production requirements:

*   **Setup Guide**: [How do I set up and run this project?](./getting-started.md) — Covers environment requirements, PHP/Node dependencies, and local server configuration.
*   **Dependencies**: Ensure `Composer` and `NPM` are installed. The project utilizes high-performance UI assets located in `public/js/filament/`.

---

## 🏗️ Project Architecture

The application follows a structured approach where logic is distributed between Laravel's backend and Filament's reactive frontend.

### Directory & Component Mapping

| Category | Primary Locations | Purpose |
| :--- | :--- | :--- |
| **Models/Schemas** | `vendor/filament/schemas/resources/js` | Defines the data structure and field definitions for forms/wizards. |
| **Form Components** | `public/js/filament/forms/components` | Logic for interactive inputs like `RichEditor`, `Select`, and `TagsInput`. |
| **Table Columns** | `public/js/filament/tables/components/columns` | Rendering logic for data grids (e.g., `ToggleColumn`, `TextInputColumn`). |
| **Widgets** | `public/js/filament/widgets/components` | Dashboard elements like `StatsOverview` and charting tools. |
| **Utilities** | `vendor/filament/support/resources/js/utilities` | Core helper functions for selection, pluralization, and DOM manipulation. |

### Component Reactivity
The frontend heavily utilizes **Livewire**. For debugging, pay attention to:
*   `findClosestLivewireComponent`: Used to link JS events to PHP backend state.
*   `Livewire.dispatch()`: Common pattern for inter-component communication.

---

## 🛠️ Technical Context

### Key Dependencies
*   **Laravel Core**: Handles routing, middleware, and database ORM (Eloquent).
*   **Filament v3**: Powers the Administrative Panel, Form Builder, and Table Builder.
*   **Livewire**: Provides the bridge between PHP and JavaScript without writing full REST APIs for every interaction.
*   **Shiki**: Used for high-fidelity code syntax highlighting in specific views.

### Public APIs & Utilities
Developers can leverage internal utilities for consistency:
*   **Select Utility**: Located at `vendor/filament/support/resources/js/utilities/select.js`. Use the `Select` class for handling complex dropdown logic.
*   **Notification System**: Use the `Notification` class in `vendor/filament/notifications/resources/js/Notification.js` to trigger UI alerts from JS.

---

## 🧪 QA Focus Areas

When performing quality assurance, focus on these critical paths:

### 1. Form Validation & Persistence
Verify that Filament form schemas correctly enforce constraints.
*   Check that `RichEditor` content is correctly sanitized.
*   Ensure `Wizard` components maintain state across steps.
*   Test `FileUpload` handlers for proper error reporting.

### 2. Component Reactivity (Livewire)
Ensure the UI remains synchronized with the server state.
*   **Stats Overview**: Verify that "Stat" widgets update when underlying data changes.
*   **Unsaved Changes Alert**: Test that the `unsaved-changes-alert.js` triggers correctly when a user attempts to navigate away from a dirty form.

### 3. Table Interactions
*   Test **Bulk Actions** in data tables.
*   Verify that `ToggleColumn` updates the database immediately via AJAX.
*   Check that `TextInputColumn` validates input before saving.

### 4. Permissions & Security
*   Validate that **Middleware** (located in `app/Http/Middleware`) correctly intercepts unauthorized requests.
*   Ensure that administrative resources are restricted to users with the appropriate roles defined in Filament policies.

---

## ❓ Common Developer Q&A

**Q: Where do I add custom JavaScript for a specific form field?**
A: Custom logic should be placed in `public/js/filament/forms/components`. Ensure you hook into the `Alpine.data()` or `Livewire` lifecycle.

**Q: How do I debug Livewire event listeners?**
A: Use the browser console to monitor `Livewire.on` events. You can also refer to `vendor/livewire/livewire/src/Features/SupportEvents/fake-echo.js` for examples of how events are mocked in tests.

**Q: How is the "Unsaved Changes" logic handled?**
A: The logic is centralized in `vendor/filament/filament/resources/js/unsaved-changes-alert.js`. It monitors the state of form fields and prevents navigation if changes are detected but not saved.

---
*Last Updated: 2026-01-23*
