# Gestão de Documentos AGEs da Empresa (IssuerAge) Spec

## Why
O sistema precisa gerenciar múltiplas Assembleias Gerais Extraordinárias (AGEs) para cada empresa (Issuer), permitindo o armazenamento de documentos e o acompanhamento de prazos de vigência e editais.

## What Changes
- **IssuerAge Model**: Atualizar o model para incluir os campos necessários.
- **IssuerAge Migration**: Atualizar a migration para refletir os campos do model.
- **IssuerAgeResource**:
    - **IssuerAgeForm**: Implementar o formulário com a aba `Aba_Essenciais` e os campos solicitados.
    - **IssuerAgesTable**: Implementar a listagem de AGEs.
    - **Document Upload**: Lógica para salvar documentos no diretório `rag/{tenant_id}/{cnpj}/documents`.
    - **Confirmation on Deletion**: Garantir que a exclusão solicite confirmação.

## Impact
- **Affected code**:
    - `app/Models/IssuerAge.php`
    - `database/migrations/2026_03_10_140849_create_issuer_ages_table.php`
    - `app/Filament/Condominio/Resources/IssuerAges/IssuerAgeResource.php`
    - `app/Filament/Condominio/Resources/IssuerAges/Schemas/IssuerAgeForm.php`
    - `app/Filament/Condominio/Resources/IssuerAges/Tables/IssuerAgesTable.php`

## ADDED Requirements
### Requirement: Gerenciar Múltiplas AGEs
O sistema SHALL permitir registrar múltiplas AGEs para cada empresa (Issuer), com seus respectivos documentos e vigências.

#### Scenario: Success case
- **WHEN** o usuário acessa o recurso de AGEs e clica em Criar.
- **THEN** o formulário deve exibir a aba `Aba_Essenciais` com os campos: Empresa, Documento, Vigência (opcional), Data Limite Edital, Prazo Técnico e Observações.
- **AND** ao salvar, o documento deve ser armazenado em `rag/{tenant_id}/{cnpj}/documents`.

### Requirement: Exclusão com Confirmação
O sistema SHALL solicitar confirmação antes de remover uma AGE.

#### Scenario: Success case
- **WHEN** o usuário tenta excluir uma AGE da listagem ou do formulário.
- **THEN** o sistema deve exibir um alerta de confirmação.
