---
name: Commit Message
description: Generate commit messages following conventional commits with scope detection
phases: [E, C]
source_tool: codex
source_path: .codex/skills/commit-message/SKILL.md
imported_at: 2026-03-05T10:14:51.979Z
ai_context_version: 0.7.1
---

# Commit Message

## Quando usar
- Antes de criar commits relacionados ao Fiscaut.

## Instruções
1. Use Conventional Commits: `feat|fix|refactor|test|docs|chore|perf|build|ci|revert`.
2. Prefira escopos que reflitam o domínio: `filament`, `livewire`, `auth`, `db`, `infra`, `tests`, `docs`, `security`.
3. No corpo, descreva impacto e pontos de validação (ex.: testes executados) sem incluir dados sensíveis.

## Restrições (sigilo)
- Fiscaut é uma aplicação comercial proprietária: não cite informações confidenciais no commit message.
- Nunca inclua segredos, credenciais, dados de clientes ou links internos.

## Exemplos
- `fix(filament): corrigir validação de CFOP no formulário`
- `refactor(auth): extrair regra de autorização para Policy`