---
type: skill
name: Refactoring
description: Safe code refactoring with step-by-step approach
skillSlug: refactoring
phases: [E]
generated: 2026-01-23
status: filled
scaffoldVersion: "2.0.0"
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
