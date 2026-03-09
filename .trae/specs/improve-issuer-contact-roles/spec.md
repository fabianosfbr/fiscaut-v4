# Melhoria na Gestão de Funções de Contatos (IssuerContact) Spec

## Why
Atualmente, o cadastro de contatos (`IssuerContact`) não possui um campo para definir a função ou cargo do contato na empresa. De acordo com o Requisito 9, a função do contato deve ser obrigatória e as opções devem variar dependendo do tipo de empresa (`Issuer`).

## What Changes
- Adição do campo `funcao` na tabela `issuer_contacts`.
- Criação de um Enum `IssuerContactRoleEnum` para gerenciar as funções de contato.
- Atualização do model `IssuerContact` para incluir o cast do novo campo.
- Atualização do formulário `IssuerContactForm` no Filament para incluir o campo `funcao` com opções dinâmicas baseadas no tipo de empresa (`issuer_type`).
- Adição da obrigatoriedade do campo `funcao`.

## Impact
- **Affected specs**: Gestão de Contatos.
- **Affected code**: 
    - `app/Models/IssuerContact.php`
    - `app/Filament/Condominio/Resources/IssuerContacts/Schemas/IssuerContactForm.php`
    - Nova migração para a tabela `issuer_contacts`.
    - Novo Enum `App\Enums\IssuerContactRoleEnum`.

## ADDED Requirements
### Requirement: Definir Função do Contato Baseada no Tipo da Empresa
O sistema deve permitir a seleção da função do contato de acordo com o tipo da empresa associada.

#### Scenario: Empresa do tipo "Condomínio"
- **WHEN** o usuário estiver criando ou editando um contato para uma empresa do tipo "Condomínio"
- **THEN** as opções de função devem ser: "Síndico", "Sub-síndico", "Conselheiro", "Administrador" ou "Demais".

#### Scenario: Empresa do tipo "Associação"
- **WHEN** o usuário estiver criando ou editando um contato para uma empresa do tipo "Associação"
- **THEN** as opções de função devem ser: "Presidente", "Vice Presidente", "Secretário", "Diretoria", "Conselho", "Administrador" ou "Demais".

#### Scenario: Campo Obrigatório
- **WHEN** o usuário tentar salvar um contato sem selecionar uma função
- **THEN** o sistema deve exibir um erro de validação informando que o campo é obrigatório.

## MODIFIED Requirements
### Requirement: Formulário de Contato
O `IssuerContactForm` será modificado para incluir o campo `funcao` (Select) que reage ao `issuer_id` selecionado (ou ao contexto do issuer atual).
