---
name: Feature Breakdown
description: Break down features into implementable tasks
phases: [P]
source_tool: codex
source_path: .codex/skills/feature-breakdown/SKILL.md
imported_at: 2026-03-05T10:14:51.994Z
ai_context_version: 0.7.1
---

# Feature Breakdown

## Quando usar
- Planejar novas funcionalidades e dividir em tarefas implementáveis no stack Laravel v12 + Filament v5 + Livewire v4.

## Instruções
1. Descreva o objetivo do usuário e o “definition of done”.
2. Separe em fatias verticais: migração/DB → Model/Policy → Filament Resource/Page → Livewire/UX → testes → docs.
3. Identifique riscos: migrações destrutivas, autorização, performance (N+1), dados sensíveis.
4. Liste dependências e pontos de validação (testes/rotas/telas).

## Restrições (sigilo)
- Fiscaut é uma aplicação comercial proprietária: planeje sem expor informações confidenciais fora de canais autorizados.