# Glossary & Domain Concepts

## Glossary & Domain Concepts
This document defines the core terminology used within the Fiscaut system.

## Nota de Contexto
Fiscaut é uma aplicação comercial proprietária construída com Laravel v12, FilamentPHP v5 e Livewire v4. Mantenha termos e exemplos sem expor dados sensíveis.

- **CFOP** (Código Fiscal de Operações e Prestações): A Brazilian tax code used to identify the nature of circulation of goods or provision of services. It is a critical entity in this system.
- **Simples Nacional**: Regime tributário simplificado no Brasil, com alíquotas por “Anexo” e “Faixa” de receita.
- **Filament**: The admin panel framework used to build the UI.
- **Resource**: A Filament concept representing a CRUD interface for a specific Eloquent model.
- **Issuer (Empresa)**: Entidade/empresa operada dentro do sistema. Na UI, é gerenciada pelo `IssuerResource` e costuma ser usada como escopo (“empresa atual”) em outras telas.
- **Tenant (Assinante)**: Organização/cliente do sistema. Vários recursos filtram dados pelo `tenant_id` do usuário autenticado.
- **Category Tag (Categoria de etiqueta)**: Agrupador de etiquetas (“tags”) usado para classificação/regras, com filtros e contagem de etiquetas relacionadas no admin.
- **NFe** (Nota Fiscal Eletrônica): Electronic Invoice (likely a related concept in this domain).

## Type Definitions
- **Model**: Represents a database table row (e.g., `App\Models\Cfop`).
- **Policy**: Authorization logic class (e.g., `App\Policies\CfopPolicy`).

## Enumerations
(To be populated as enums are discovered in `app/Enums`)

## Core Terms
- **Tenant**: If the application is multi-tenant, this refers to the organization using the software.
- **Fiscal Document**: Generic term for invoices, receipts, etc.
- **Anexo (Simples Nacional)**: Categoria de atividade/receita usada para determinar a tabela de faixas e alíquotas aplicáveis (ex.: Anexo I, II, III...).
- **Faixa (Simples Nacional)**: Intervalo de receita acumulada usado para selecionar a alíquota nominal e o valor a deduzir.
- **Alíquota (nominal)**: Percentual base associado a uma faixa.
- **Valor a deduzir**: Valor usado na fórmula de alíquota efetiva por faixa.
- **Percentuais de distribuição**: Percentuais internos por tributo (IRPJ/CSLL/COFINS/PIS/CPP/ICMS/ISS/IPI) associados à faixa (quando aplicável).

## Acronyms & Abbreviations
- **CRUD**: Create, Read, Update, Delete.
- **MVC**: Model View Controller.
- **ORM**: Object-Relational Mapping (Eloquent).
- **TALL**: Tailwind, Alpine, Laravel, Livewire.

## Personas / Actors
- **Administrator**: Has full access to all system settings and resources.
- **Fiscal Analyst**: Manages fiscal documents, CFOPs, and tax rules.
- **Viewer**: Read-only access to reports.

## Domain Rules & Invariants
- **CFOP Validation**: CFOP codes must follow a specific 4-digit format defined by the government.
- **Uniqueness**: Certain fiscal codes must be unique per operation type.
- **Faixas do Simples Nacional**: Para o mesmo anexo, as faixas não devem se sobrepor (intervalos [faixa_inicial, faixa_final] disjuntos) e `faixa_inicial <= faixa_final`.

## Cross-References
- [project-overview.md](./project-overview.md)
