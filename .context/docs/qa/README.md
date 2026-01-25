# Quality Assurance and Developer Q&A

Welcome to the central documentation hub for **Fiscaut-v4.1**. This section is designed to provide developers and QA engineers with quick access to common questions, setup guides, and architectural overviews.

This project is primarily a **web-api** built on the Laravel framework, utilizing **Filament** for its administrative interface and **Livewire** for reactive components.

## Documentation Index

### 🚀 Getting Started
Before you begin development or testing, ensure your environment is correctly configured.
- [How do I set up and run this project?](./getting-started.md) — Covers environment requirements, dependency installation (Composer/NPM), and local server configuration.

### 🏗️ Architecture
Understanding the underlying structure is crucial for debugging and extending functionality.
- [How is the codebase organized?](./project-structure.md) — An overview of the directory structure, including where Filament resources, schemas, and custom components reside.
- [How does routing work?](./routing.md) — Details on API endpoints, web routes, and how Filament handles resource-based routing.
- [How does middleware work?](./middleware.md) — Explanation of the request lifecycle, authentication guards, and custom middleware applied across the application.

### 🛠️ Features & Data
Details on how business logic and data persistence are implemented.
- [How is data stored and accessed?](./database.md) — Documentation on the database schema, Eloquent models, and the repository pattern (if applicable).

---

## Technical Context

### Frontend & UI Components
The project relies heavily on **Filament v3** for its UI. Most interactive elements are governed by:
- **Schemas**: Located in `vendor/filament/schemas/resources/js/components`, these define the structure of forms and wizards.
- **Form Components**: Custom logic for fields like `RichEditor`, `Select`, and `DateTimePicker`.
- **Table Columns**: Logic for data presentation including `ToggleColumn` and `TextInputColumn`.

### Key Dependencies
- **Laravel**: The core PHP framework.
- **Filament**: Used for the admin panel and form/table builders.
- **Livewire**: Powers the asynchronous, reactive parts of the interface.
- **Shiki**: Utilized for code highlighting in specific views.

## QA Focus Areas

When performing quality assurance for this project, focus on the following high-impact areas:
1.  **Form Validation**: Ensure that Filament form schemas correctly validate data before persistence.
2.  **API Consistency**: Verify that API responses follow the established structure defined in the routing documentation.
3.  **Component Reactivity**: Test Livewire-driven components (like the `StatsOverview` or `Wizard` schemas) for state consistency during long-running processes.
4.  **Permissions**: Validate that middleware and policies correctly restrict access to sensitive administrative resources.

---
*Last Updated: 2026-01-23*
