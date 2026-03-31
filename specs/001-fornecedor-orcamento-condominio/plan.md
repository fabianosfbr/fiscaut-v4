# Implementation Plan: Fornecedores e Orçamentos - Condomínio

**Branch**: `001-fornecedor-orcamento-condominio` | **Date**: 2026-03-31 | **Spec**: [spec.md](./spec.md)

## Summary

Criar módulo de fornecedores e orçamentos para o painel Condomínio do Fiscaut. Inclui: (1) gestão de fornecedores com sistema de rating automático baseado em performance, (2) criação e comparação de orçamentos com múltiplos fornecedores, (3) envio via email/WhatsApp e resposta por formulário ou documento.

## Technical Context

**Language/Version**: PHP 8.2+ / Laravel 12  
**Primary Dependencies**: Filament 5, Livewire 4, Alpine.js  
**Storage**: MySQL 8.0  
**Testing**: Pest/PHPUnit  
**Target Platform**: Linux server (web)  
**Project Type**: Web application (Filament admin panel - Condomínio)  
**Performance Goals**: <200ms para operações de CRUD, <500ms para comparação de orçamentos  
**Constraints**: Multi-tenant por Tenant, dados isolados por Issuer (Condomínio)  
**Scale**: 10-100 fornecedores por condomínio, 10-50 orçamentos/mês

## Constitution Check

*Gate: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Gate: Filament Resources** - Novo recurso em `app/Filament/Condominio/Resources/FornecedorResource.php` seguindo padrão existente do projeto.
- **Gate: Multi-tenancy** - Todos os modelos devem usar `issuer_id` para isolamento de dados (tenant inferido via Issuer).
- **Gate: Database migrations** - Novas tabelas com foreign keys para Issuer.

## Project Structure

### Documentation (this feature)

```text
specs/001-fornecedor-orcamento-condominio/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output (se necessário)
└── tasks.md             # Phase 2 output
```

### Source Code (repository root)

```text
app/
├── Filament/
│   └── Condominio/
│       └── Resources/
│           ├── FornecedorResource.php      # CRUD Fornecedores
│           ├── FornecedorCategoryResource.php # CRUD Categorias
│           ├── OrcamentoResource.php       # CRUD Orçamentos
│           └── ProposalResource.php        # Visualização Propostas
├── Models/
│   ├── Supplier.php            # Fornecedor
│   ├── SupplierCategory.php    # Categoria de serviço
│   ├── BudgetRequest.php       # Solicitação orçamento
│   ├── BudgetProposal.php      # Proposta recebida
│   └── SupplierInteraction.php # Histórico interações
├── Services/
│   ├── SupplierRatingService.php    # Cálculo de rating
│   ├── BudgetNotificationService.php # Envio email/WhatsApp
│   └── BudgetComparisonService.php   # Comparação propostas
└── Jobs/
    └── UpdateSupplierRatingJob.php   # Job assíncrono para atualizar rating

database/migrations/
├── [YEAR_MM_DD]_create_suppliers_table.php
├── [YEAR_MM_DD]_create_supplier_categories_table.php
├── [YEAR_MM_DD]_create_budget_requests_table.php
├── [YEAR_MM_DD]_create_budget_proposals_table.php
└── [YEAR_MM_DD]_create_supplier_interactions_table.php

resources/views/filament/condominio/
├── fornecedor/                     # Componentes customizados
├── orcamento/                      # Views de comparação
└── widgets/                        # Widgets de overview

tests/
├── Feature/
│   ├── SupplierManagementTest.php
│   ├── BudgetRequestTest.php
│   └── BudgetComparisonTest.php
└── Unit/
    ├── SupplierRatingTest.php
    └── BudgetSuggestionTest.php
```

## Complexity Tracking

> Preenchido apenas se houver violações de constituição que precisam ser justificadas.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|---------------------------------------|
| Novo domínio de dados (Fornecedor/Orçamento) | Requisito de negócio não existente no sistema atual | N/A - primeira implementação |
| Múltplas entidades relacionadas | Necessário para histórico completo e rating | Entidade única seria insuficiente para requisitos |

---

## Phase 0: Research ✅ CONCLUÍDO

### Unknowns to resolve

| Item | Question | Resolução | Status |
|------|----------|------------|--------|
| Formato WhatsApp | Qual o padrão de validação aceito? | Regex `^(\+55)?[1-9]{2}9[0-9]{8}$` para móviles brasileiros | ✅ Resolvido em research.md |
| Cálculo rating | Quais pesos usar para cada fator? | Média ponderada: 25% response + 30% win + 20% time + 25% quality | ✅ Resolvido em research.md |
| Threshold low-priority | Quais valores inicial para flagging? | Configurável via config/admin.php (padrão: <30% resposta ou <20% ganho em 90 dias) | ✅ Resolvido em research.md |
| Envio WhatsApp | Qual API usar para enviar mensagens? | Config placeholder + interface abstrata (sem provedor definido) | ✅ Resolvido em research.md |
| Link formulário | Como gerar URL única para fornecedor? | SHA256 hash com expiração de 7 dias | ✅ Resolvido em research.md |

**Detalhamento**: Ver [research.md](./research.md)

---

## Phase 1: Design

### Outputs expected

1. **data-model.md** - Entidades, campos, relacionamentos, validações
2. **research.md** - Decisões técnicas resolvidas
3. **quickstart.md** - Guia de implementação para desenvolvedor
4. **Agent context** - Atualizado com novas tecnologias/decisões

---

## Phase 2: Tasks

*Não criado nesta fase - será gerado pelo comando /speckit.tasks*