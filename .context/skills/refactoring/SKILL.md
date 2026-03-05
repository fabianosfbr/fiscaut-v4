---
name: Refactoring
description: Safe code refactoring with step-by-step approach
phases: [E]
source_tool: codex
source_path: .codex/skills/refactoring/SKILL.md
imported_at: 2026-03-05T10:14:52.008Z
ai_context_version: 0.7.1
---

# Refactoring

## Quando usar
- Melhorar estrutura interna (Laravel/Filament/Livewire) sem alterar comportamento externo.

## Instruções
1. Comece com testes verdes (ou adicione testes de caracterização).
2. Faça mudanças pequenas e verificáveis; prefira extrações para Actions/Services quando útil.
3. Preserve contratos: rotas, Resources/Pages do Filament, componentes Livewire e migrações existentes.
4. Valide performance (N+1, queries pesadas) e segurança (logs, validação, autorização).

## Restrições (sigilo)
- Fiscaut é uma aplicação comercial proprietária: mantenha descrições e exemplos sanitizados.