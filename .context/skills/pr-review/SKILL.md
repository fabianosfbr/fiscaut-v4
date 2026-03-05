---
name: Pr Review
description: Review pull requests against team standards and best practices
phases: [R, V]
source_tool: codex
source_path: .codex/skills/pr-review/SKILL.md
imported_at: 2026-03-05T10:14:52.002Z
ai_context_version: 0.7.1
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