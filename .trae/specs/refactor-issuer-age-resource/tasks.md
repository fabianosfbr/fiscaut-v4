# Tasks

- [x] Task 1: Update Database Schema
    - [x] SubTask 1.1: Create migration to add `type` column and new AGO fields to `issuer_ages` table.
    - [x] SubTask 1.2: Run migration.

- [x] Task 2: Update Model and Logic
    - [x] SubTask 2.1: Update `IssuerAge.php` model with new fillable fields and casts.
    - [x] SubTask 2.2: Add Enums for Boleto options if necessary (or just handle in Filament).

- [x] Task 3: Refactor Filament Resource
    - [x] SubTask 3.1: Add `type` selection field (Radio or Select) to `IssuerAgeForm.php`.
    - [x] SubTask 3.2: Implement conditional logic to show/hide fields based on `type`.
    - [x] SubTask 3.3: Implement "Quem recebe" logic based on Issuer Type.
    - [x] SubTask 3.4: Verify AGE functionality is preserved.

# Task Dependencies
- Task 2 depends on Task 1.
- Task 3 depends on Task 2.
