<!-- 
Sync Impact Report:
Version change: [TOKEN] -> 1.0.0
Modified principles:
- [PRINCIPLE_1_NAME] -> I. Code Quality & Modularity
- [PRINCIPLE_2_NAME] -> II. Testing Standards (Non-Negotiable)
- [PRINCIPLE_3_NAME] -> III. User Experience Consistency
- [PRINCIPLE_4_NAME] -> IV. Performance-First Development
Added sections: Core Principles, Governance
Templates requiring updates:
- .specify/templates/plan-template.md (⚠ pending)
- .specify/templates/spec-template.md (⚠ pending)
- .specify/templates/tasks-template.md (⚠ pending)
-->

# Fiscaut Constitution

## Core Principles

### I. Code Quality & Modularity
**Rule**: All code must adhere to PSR-12 standards, utilize strict typing (`declare(strict_types=1)`), and follow Clean Code principles. Every feature should be modular and encapsulated within appropriate namespaces.  
**Rationale**: Ensures long-term maintainability, reduces technical debt, and allows for easier scaling of the Fiscaut platform.

### II. Testing Standards (Non-Negotiable)
**Rule**: Test-Driven Development (TDD) is mandatory for all new features and bug fixes. Business logic must maintain at least 90% test coverage using PHPUnit/Pest. Integration tests are required for all API endpoints and database interactions.  
**Rationale**: Guarantees reliability, prevents regressions, and serves as live documentation for system behavior.

### III. User Experience Consistency
**Rule**: All UI elements must be built using FilamentPHP components or Livewire. Custom CSS is only permitted when native components cannot meet the requirement, and must follow the project's design tokens.  
**Rationale**: Provides a unified, professional interface for users and speeds up development by leveraging a robust component library.

### IV. Performance-First Development
**Rule**: Database queries must be optimized (no N+1 issues), and heavy processing must be dispatched to background queues. Frontend assets must be optimized for fast Core Web Vitals (LCP < 2.5s).  
**Rationale**: Ensures the application remains responsive and efficient as the user base and data volume grow.

## Governance
1. **Amendment Process**: Any change to this constitution requires a version bump (Major for removals, Minor for additions, Patch for wording) and a documented rationale.
2. **Migration Plan**: Significant changes to principles must include a plan to migrate existing code to the new standards within two development cycles.
3. **Compliance**: All Pull Requests must be reviewed against these core principles. Complexity increases must be explicitly justified.

**Version**: 1.0.0 | **Ratified**: 2026-01-23 | **Last Amended**: 2026-01-23
