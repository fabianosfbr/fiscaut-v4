---
name: Test Generation
description: Generate comprehensive test cases for code
phases: [E, V]
source_tool: codex
source_path: .codex/skills/test-generation/SKILL.md
imported_at: 2026-03-05T10:14:52.027Z
ai_context_version: 0.7.1
---

# Test Generation

## Quando usar
- Criar testes de regressão para bugs e testes de feature para funcionalidades novas.

## Instruções
1. Identifique o tipo de teste adequado: Unit vs Feature.
2. Prefira factories e dados sintéticos; não use dados reais.
3. Para Filament/Livewire, cubra: validação, autorização, persistência e caminhos de erro.
4. Garanta que o teste falha antes do fix (regressão) e passa depois.

## Restrições (sigilo)
- Fiscaut é uma aplicação comercial proprietária: não copie cenários com dados reais de clientes.