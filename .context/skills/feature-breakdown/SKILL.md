---
type: skill
name: Feature Breakdown
description: Break down features into implementable tasks
skillSlug: feature-breakdown
phases: [P]
generated: 2026-01-23
status: filled
scaffoldVersion: "2.0.0"
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
