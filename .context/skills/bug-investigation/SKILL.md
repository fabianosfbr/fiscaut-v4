---
type: skill
name: Bug Investigation
description: Systematic bug investigation and root cause analysis
skillSlug: bug-investigation
phases: [E, V]
generated: 2026-01-23
status: filled
scaffoldVersion: "2.0.0"
---

# Bug Investigation

## Quando usar
- Investigar erros em produção/homologação, falhas de Livewire/Filament, problemas de performance ou inconsistências de dados.

## Instruções
1. Colete evidências mínimas: mensagem, stack trace sanitizado, request/rota, usuário/perfil (sem PII).
2. Reproduza localmente (Laravel v12 / Filament v5 / Livewire v4).
3. Isole a causa: validação, autorização, query Eloquent, estado de Livewire, migração/config.
4. Corrija com mudança mínima e adicione teste de regressão.
5. Revalide e documente a causa-raiz de forma sanitizada.

## Restrições (sigilo)
- Não compartilhe logs completos, dumps ou dados reais; sanitize sempre.
