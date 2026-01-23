---
type: skill
name: Pr Review
description: Review pull requests against team standards and best practices
skillSlug: pr-review
phases: [R, V]
generated: 2026-01-23
status: filled
scaffoldVersion: "2.0.0"
---

# PR Review

## Quando usar
- Revisão final antes de merge (mudanças de feature, bugfix, refactor, infra).

## Instruções
1. Confirme objetivo do PR, escopo e riscos.
2. Verifique migrações, seeds e compatibilidade com Laravel v12 / Filament v5 / Livewire v4.
3. Valide que há testes/validação adequada e que não há regressões óbvias.
4. Procure vazamentos: logs, commits, configs e documentação não devem conter segredos/dados sensíveis.
5. Cheque se docs em `.context/` foram atualizados quando necessário.

## Restrições (sigilo)
- Fiscaut é uma aplicação comercial proprietária: evite colar diffs completos em ferramentas externas.
