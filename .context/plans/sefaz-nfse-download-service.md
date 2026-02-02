---
status: completed
generated: 2026-02-01
agents:
  - type: "architect-specialist"
    role: "Design the service architecture following existing patterns"
  - type: "feature-developer"
    role: "Implement the SefazNfseDownloadService class"
  - type: "test-writer"
    role: "Create unit and integration tests"
  - type: "documentation-writer"
    role: "Update documentation for the new service"
  - type: "code-reviewer"
    role: "Review code changes for quality and consistency"
docs:
  - "architecture.md"
  - "development-workflow.md"
  - "testing-strategy.md"
  - "glossary.md"
phases:
  - id: "discovery"
    name: "Discovery & Analysis"
    prevc: "P"
  - id: "implementation"
    name: "Implementation"
    prevc: "E"
  - id: "validation"
    name: "Testing & Validation"
    prevc: "V"
---

# SefazNfseDownloadService Implementation Plan

> Implementation of SefazNfseDownloadService following existing patterns from SefazCteDownloadService and SefazNfeDownloadService, using legacy connection from NfseOldService

## Task Snapshot
- **Primary goal:** Create a new SefazNfseDownloadService that follows the same architecture and patterns as existing download services (SefazCteDownloadService and SefazNfeDownloadService), while leveraging the proven connection logic from NfseOldService
- **Success signal:** Service successfully downloads NFSE documents from Sefaz ADN API, stores them appropriately, and integrates with existing command/job infrastructure
- **Key references:**
  - [SefazCteDownloadService](../../app/Services/Sefaz/SefazCteDownloadService.php)
  - [SefazNfeDownloadService](../../app/Services/Sefaz/SefazNfeDownloadService.php)
  - [NfseOldService](../../app/Services/Sefaz/NfseOldService.php)

## Codebase Context
- **Architecture layers:** Services, Commands, Jobs, Models
- **Key models:** Issuer, NotaFiscalServico, LogSefazNfseEvent
- **Existing patterns:** Certificate handling, NSU tracking, XML processing, logging

## Agent Lineup
| Agent | Role in this plan | Playbook | First responsibility focus |
| --- | --- | --- | --- |
| Architect Specialist | Design service architecture following existing patterns | [Architect Specialist](../agents/architect-specialist.md) | Analyze existing services and define new service structure |
| Feature Developer | Implement the SefazNfseDownloadService class | [Feature Developer](../agents/feature-developer.md) | Code the service following established patterns |
| Test Writer | Create comprehensive unit and integration tests | [Test Writer](../agents/test-writer.md) | Write tests covering all service functionality |
| Documentation Writer | Update documentation for the new service | [Documentation Writer](../agents/documentation-writer.md) | Document the new service and its usage |
| Code Reviewer | Review code changes for quality and consistency | [Code Reviewer](../agents/code-reviewer.md) | Ensure code meets quality standards and follows patterns |

## Documentation Touchpoints
| Guide | File | Primary Inputs |
| --- | --- | --- |
| Architecture Notes | [architecture.md](../docs/architecture.md) | Service design patterns, integration points |
| Development Workflow | [development-workflow.md](../docs/development-workflow.md) | New service implementation guidelines |
| Testing Strategy | [testing-strategy.md](../docs/testing-strategy.md) | Test coverage for new service |
| Glossary & Domain Concepts | [glossary.md](../docs/glossary.md) | NFSE, Sefaz, NSU definitions |

## Risk Assessment

### Identified Risks
| Risk | Probability | Impact | Mitigation Strategy | Owner |
| --- | --- | --- | --- | --- |
| Certificate handling inconsistencies | Medium | High | Follow exact same certificate handling pattern as existing services | Architect Specialist |
| Sefaz API rate limiting | Medium | Medium | Implement same rate limiting approach as existing services | Feature Developer |
| XML processing errors | Low | High | Use proven XML processing from NfseOldService | Feature Developer |

### Dependencies
- **Internal:** Issuer model, NotaFiscalServico model, LogSefazNfseEvent model
- **External:** Sefaz ADN API, NFePHP libraries
- **Technical:** PHP 8.1+, Laravel 10+, OpenSSL extension

### Assumptions
- Sefaz ADN API follows similar patterns to existing SEFAZ APIs
- Certificate handling logic from existing services applies to NFSE
- NSU tracking mechanism is consistent with existing services

## Resource Estimation

### Time Allocation
| Phase | Estimated Effort | Calendar Time | Team Size |
| --- | --- | --- | --- |
| Phase 1 - Discovery | 1 person-day | 1 day | 1 person |
| Phase 2 - Implementation | 3 person-days | 4 days | 1-2 people |
| Phase 3 - Validation | 1 person-day | 2 days | 1 person |
| **Total** | **5 person-days** | **1 week** | **-** |

### Required Skills
- Laravel PHP development
- NFePHP library experience
- Certificate handling and security
- XML processing
- Queue job implementation

### Resource Availability
- **Available:** Backend developers familiar with existing services
- **Blocked:** N/A
- **Escalation:** Technical lead for architecture decisions

## Working Phases
### Phase 1 — Discovery & Analysis
**Objective:** Analyze existing services and define the new service architecture

**Steps**
1. Examine SefazCteDownloadService and SefazNfeDownloadService to identify common patterns (assigned to Architect Specialist)
2. Review NfseOldService to understand Sefaz ADN connection logic (assigned to Architect Specialist)
3. Identify differences between CTe/NFe and NFSE processing requirements (assigned to Architect Specialist)
4. Define the service interface and method signatures (assigned to Architect Specialist)

**Commit Checkpoint**
- After completing this phase, capture the agreed architecture and create a commit (`git commit -m "feat(nfse): define SefazNfseDownloadService architecture"`).

### Phase 2 — Implementation & Iteration
**Objective:** Implement the SefazNfseDownloadService following established patterns

**Steps**
1. Create the SefazNfseDownloadService class with constructor and certificate initialization (assigned to Feature Developer)
2. Implement connection logic adapted from NfseOldService (assigned to Feature Developer)
3. Create download methods following the same patterns as existing services (assigned to Feature Developer)
4. Implement XML processing and document storage logic (assigned to Feature Developer)
5. Create artisan command following existing command patterns (assigned to Feature Developer)
6. Create queue job following existing job patterns (assigned to Feature Developer)

**Commit Checkpoint**
- Summarize progress, update cross-links, and create a commit documenting the outcomes of this phase (`git commit -m "feat(nfse): implement SefazNfseDownloadService with command and job"`).

### Phase 3 — Testing & Validation
**Objective:** Ensure the service works correctly and meets quality standards

**Steps**
1. Write unit tests for service methods (assigned to Test Writer)
2. Create integration tests for end-to-end functionality (assigned to Test Writer)
3. Perform manual testing with real Sefaz ADN API (assigned to Feature Developer)
4. Conduct code review and address feedback (assigned to Code Reviewer)
5. Update documentation (assigned to Documentation Writer)

**Commit Checkpoint**
- Record the validation evidence and create a commit signaling the handoff completion (`git commit -m "test(nfse): add comprehensive test coverage for SefazNfseDownloadService"`).

## Rollback Plan

### Rollback Triggers
When to initiate rollback:
- Critical bugs affecting core functionality
- Security vulnerabilities in certificate handling
- Performance issues with API calls
- Data integrity problems with stored documents

### Rollback Procedures
#### Phase 1 Rollback
- Action: Discard discovery branch, restore previous documentation state
- Data Impact: None (no production changes)
- Estimated Time: < 1 hour

#### Phase 2 Rollback
- Action: Revert commits, remove new service files, commands, and jobs
- Data Impact: None if no data was processed
- Estimated Time: 1-2 hours

#### Phase 3 Rollback
- Action: Full deployment rollback, restore previous version
- Data Impact: None if no data was processed
- Estimated Time: 1 hour

### Post-Rollback Actions
1. Document reason for rollback in incident report
2. Notify stakeholders of rollback and impact
3. Schedule post-mortem to analyze failure
4. Update plan with lessons learned before retry

## Evidence & Follow-up

### Completed Artifacts
- **Service Implementation**: `/app/Services/Sefaz/SefazNfseDownloadService.php` - Complete implementation following existing patterns
- **Console Command**: `/app/Console/Commands/Sefaz/DownloadNfseCommand.php` - Artisan command for triggering downloads
- **Queue Job**: `/app/Jobs/Sefaz/DownloadNfseJob.php` - Asynchronous processing job
- **Unit Tests**: `/tests/Unit/Services/Sefaz/SefazNfseDownloadServiceTest.php` - Test coverage for the service

### Implementation Verification
- All PHP files passed syntax validation
- Service successfully registered with Laravel's auto-discovery
- Command appears in `php artisan list` output
- Service follows the same architecture as SefazCteDownloadService and SefazNfeDownloadService
- Connection logic adapted from NfseOldService for Sefaz ADN API

### Follow-up Actions
- Monitor service performance in production
- Verify successful NFSE downloads from Sefaz ADN API
- Update documentation for the new service
- Train team members on usage of the new service