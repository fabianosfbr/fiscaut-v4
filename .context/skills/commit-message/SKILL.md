---
type: skill
name: Commit Message
description: Generate commit messages following conventional commits with scope detection
skillSlug: commit-message
phases: [E, C]
generated: 2026-01-23
status: filled
scaffoldVersion: "2.0.0"
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
