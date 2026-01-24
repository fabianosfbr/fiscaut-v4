---
name: Documentation
description: Generate and update technical documentation
phases: [P, C]
---

# Documentation

## Quando usar
- Criar/atualizar documentação em `.context/docs` e `.context/agents`.
- Ajustar textos para refletir a stack real do Fiscaut (Laravel v12, FilamentPHP v5, Livewire v4).

## Instruções
1. Identifique quais arquivos precisam mudar e mantenha o tom e estrutura existentes.
2. Referencie código usando links relativos e evite “inventar” APIs/fluxos não verificados.
3. Destaque sempre que necessário: Fiscaut é um produto comercial proprietário (confidencial).

## Restrições (sigilo)
- Nunca inclua segredos, dumps, credenciais, URLs internas, dados de clientes ou PII.
- Se precisar de exemplos, use dados sintéticos.

## Saídas esperadas
- Markdown atualizado com links válidos e informações consistentes entre arquivos.