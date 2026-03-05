---
name: Api Design
description: Design RESTful APIs following best practices
phases: [P, R]
source_tool: codex
source_path: .codex/skills/api-design/SKILL.md
imported_at: 2026-03-05T10:14:51.957Z
ai_context_version: 0.7.1
---

# API Design

## Quando usar
- Projetar ou revisar endpoints em `routes/api.php` (ou integrações internas) no contexto do Fiscaut.

## Instruções
1. Defina recursos, verbos HTTP, status codes e contratos de payload (request/response).
2. Garanta autenticação/autorização (Sanctum/guards/policies conforme configuração do projeto).
3. Validação: Form Requests, regras explícitas e mensagens apropriadas.
4. Observabilidade: logs sanitizados, IDs de correlação, erros padronizados.
5. Performance: paginação, filtros, índices e prevenção de N+1.

## Restrições (sigilo)
- Fiscaut é uma aplicação comercial proprietária: não exponha detalhes internos desnecessários nem inclua dados reais em exemplos.