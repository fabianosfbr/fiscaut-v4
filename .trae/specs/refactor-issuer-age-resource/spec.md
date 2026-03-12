# Refactor IssuerAgeResource Spec

## Why
The current `IssuerAgeResource` is limited to managing AGE (Assembleia Geral Extraordinária). The system needs to support AGO (Assembleia Geral Ordinária) as well, which comes with a specific set of requirements for managing deadlines, mandates, billing configurations, and exemptions/remunerations. This refactoring will enable the system to manage both types of assemblies under a unified resource.

## What Changes
- **Database Schema (`issuer_ages` table):**
    - Add `type` column (enum: 'AGO', 'AGE').
    - Add fields for AGO specific data:
        - `data_limite_ago` (date).
        - `prazo_tecnico_edital` (string) - *Renaming or mapping existing `prazo_tecnico` to this context if applicable, or adding new.*
        - `mandato_fim` (date) and `prazo_tecnico_mandato` (string).
        - `mandato_conselho_fim` (date) and `prazo_tecnico_mandato_conselho` (string).
        - `mandato_banco_fim` (date) and `prazo_tecnico_mandato_banco` (string).
    - Add fields for Boleto Configuration:
        - `boleto_dia_vencimento` (integer).
        - `boleto_tipo_prazo` (string/enum: 'uteis', 'corridos').
        - `boleto_gerado_por` (string/enum: 'administradora', 'garantidora').
        - `boleto_forma_rateio` (string/enum: 'ideal', 'unidade', 'm2').
    - Add fields for Isenção/Remuneração:
        - `tem_isencao_remuneracao` (boolean).
        - `quem_recebe_isencao` (json/array).
        - `valor_isencao_remuneracao` (decimal).

- **Filament Resource (`IssuerAgeResource`):**
    - Add a `type` selection field (Radio or Select).
    - Implement conditional logic to show/hide fields based on `type`.
    - **AGO Fields Group:**
        - Document upload (existing).
        - Data Limite AGO.
        - Data Limite Edital & Prazo Técnico.
        - Mandato Síndico (Fim & Prazo Técnico).
        - Mandato Conselho (Fim & Prazo Técnico).
        - Mandato Banco (Fim & Prazo Técnico).
        - Boleto Configuration Section.
        - Isenção/Remuneração Section (with dynamic options for "Quem recebe" based on Issuer Type).

## Impact
- **Affected specs:** None directly.
- **Affected code:**
    - `database/migrations/xxxx_create_issuer_ages_table.php` (modify or create new migration).
    - `app/Models/IssuerAge.php`.
    - `app/Filament/Condominio/Resources/IssuerAges/IssuerAgeResource.php`.
    - `app/Filament/Condominio/Resources/IssuerAges/Schemas/IssuerAgeForm.php`.

## ADDED Requirements

### Requirement: Manage AGO Type
The system SHALL allow the user to select the type of assembly (AGO or AGE).

### Requirement: Gerenciar Informações de AGO
The system SHALL capture the following for AGO:
- Document (upload).
- Data limite da AGO.
- Data limite para expedição do edital AND Prazo técnico.
- Prazo de mandato AND Prazo técnico.
- Prazo de mandato para conselho AND Prazo técnico.
- Prazo de mandato para o banco AND Prazo técnico.
- Observações gerais.

### Requirement: Configurar Informações de Vencimento de Boleto na AGO
The system SHALL capture:
- Dia do vencimento do boleto.
- Tipo de prazo ("dias úteis", "dias corridos").
- Boleto gerado por ("administradora", "garantidora").
- Forma de rateio ("rateio ideal", "unidade", "por m2").

### Requirement: Configurar Isenção ou Remuneração na AGO
The system SHALL capture:
- Tem isenção ou remuneração (Yes/No).
- Quem recebe (Multiple Select):
    - If Issuer is "Condomínio": Options ["síndico", "sub-síndico", "conselheiro", "administrador"].
    - If Issuer is "Associação": Options ["presidente", "vice-presidente", "conselheiro", "administrador"].
- Valor da isenção ou remuneração.

## MODIFIED Requirements
### Requirement: Existing AGE Feature
The existing AGE functionality SHALL be preserved, but fields specific to AGO should be hidden when AGE is selected, and vice-versa (if AGE has specific fields, currently it seems to share generic ones).
