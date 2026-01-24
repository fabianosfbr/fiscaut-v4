---
name: Security Audit
description: Security review checklist for code and infrastructure
phases: [R, V]
---

# Security Audit

## Quando usar
- Revisar mudanças sensíveis (auth, permissões, dados fiscais, integrações) no Fiscaut.

## Checklist
1. Autenticação: guards corretos, sessões/cookies seguros, proteção contra CSRF.
2. Autorização: Policies/Gates consistentes, checagens em Resources/Pages do Filament.
3. Validação e sanitização: inputs validados, outputs escapados, uploads controlados.
4. Dados sensíveis: criptografia quando aplicável, masking, logs sanitizados.
5. Dependências: verificar vulnerabilidades e configurações inseguras.

## Restrições (sigilo)
- Fiscaut é uma aplicação comercial proprietária: relatórios devem ser internos e sem dados reais.