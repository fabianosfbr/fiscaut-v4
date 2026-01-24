---
name: Code Review
description: Review code quality, patterns, and best practices
phases: [R, V]
---

# Code Review

## Quando usar
- Revisar mudanças em PHP/Laravel, Filament v5, Livewire v4, assets e configurações.

## Checklist de revisão
1. Correção funcional e cobertura de testes relevantes.
2. Padrões Laravel: validação, authorization (Policies/Gates), Eloquent (mass assignment, eager loading).
3. Filament/Livewire: componentes coerentes, regras de autorização por Resource/Page, UX sem efeitos colaterais.
4. Segurança: não vazar segredos, evitar logs com dados sensíveis, sanitizar erros, evitar SQL raw sem binding.
5. Qualidade: legibilidade, nomes, duplicação, migrações seguras e reversíveis.

## Restrições (sigilo)
- Fiscaut é uma aplicação comercial proprietária: relatórios de review devem ser sanitizados e internos.