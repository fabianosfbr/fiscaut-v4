---
name: laravel-12-filament-v5-expert
description: Orienta o desenvolvimento de aplicações Laravel 12 utilizando Laravel Sail para ambiente Docker e FilamentPHP v5 para painéis administrativos.
---

---
name: laravel-12-filament-v5-expert
description: Orienta o desenvolvimento de aplicações Laravel 12 utilizando Laravel Sail para ambiente Docker e FilamentPHP v5 para painéis administrativos.
---

# Laravel 12 & Filament v5 Expert Skill

## Description
Esta skill configura o agente para atuar como um engenheiro de software especializado no ecossistema moderno do Laravel. Ela garante que todos os comandos sejam executados dentro do ambiente containerizado (Sail), utiliza as funcionalidades mais recentes do PHP 8.4/Laravel 12 e segue as melhores práticas de componentes e performance do FilamentPHP v5.

## When to use
Use esta skill sempre que precisar:
* Criar ou modificar Resources, Pages ou Widgets no FilamentPHP v5.
* Executar comandos de migração, criação de modelos ou testes.
* Refatorar código PHP para padrões modernos (PHP 8.4+).
* Gerenciar dependências e assets via Composer ou NPM em ambiente Docker.

## Instructions
1. **Prefixo de Comando:** Sempre utilize o prefixo `sail` antes de qualquer comando CLI (ex: `sail artisan`, `sail composer`, `sail npm`, `sail pest`).
2. **Sintaxe PHP 8.4:** Utilize obrigatoriamente `declare(strict_types=1);` e aproveite *Property Hooks* para getters/setters lógicos e *Constructor Property Promotion*.
3. **Padrões Filament v5:**
    * Implemente formulários e tabelas seguindo a documentação da v5.
    * Use o método `getEloquentQuery()` para evitar carregamento lento (Eager Loading) de relacionamentos.
    * Registre Resources seguindo a estrutura de diretórios padrão: `app/Filament/Resources/`.
4. **Migrações e Modelos:** Utilize as novas tipagens de retorno nativas do Laravel 12 em relacionamentos de modelos.
5. **Testes:** Escreva testes de funcionalidade usando **Pest PHP** para cada nova funcionalidade do Filament criada.
6. **Segurança:** Nunca exponha segredos de ambiente; use sempre o helper `config()` ou `env()` e garanta que as permissões do Filament estejam vinculadas a Policies.