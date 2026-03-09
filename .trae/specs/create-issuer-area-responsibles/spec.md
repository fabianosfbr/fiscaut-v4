# Responsáveis de Área Spec

## Why
Permitir que os usuários atribuam responsáveis (usuários do sistema) para áreas específicas de atendimento de uma Issuer (Empresa). Isso facilita a definição de quem é o ponto de contato para diferentes demandas, como financeiro, consultoria, fechamento, etc.

## What Changes
- **Novo Enum**: `App\Enums\AreaAtendimentoEnum` com as áreas: consultor, contas a pagar, fechamento, departamento pessoal, gerente, financeiro e cobrança.
- **Novo Model**: `App\Models\IssuerAreaResponsible` para persistir as atribuições.
- **Nova Migration**: Tabela `issuer_area_responsibles` com `tenant_id`, `issuer_id`, `user_id` e `area`.
- **Novo Resource Filament**: `App\Filament\Condominio\Resources\IssuerAreaResponsibleResource` para gerenciar essas atribuições no painel de Condomínio.
- **Relacionamentos**: Adição do relacionamento `areaResponsibles` nos models `Issuer` e `User`.

## Impact
- **Affected specs**: Gestão de Issuers e Usuários.
- **Affected code**: `App\Models\Issuer`, `App\Models\User`.

## ADDED Requirements
### Requirement: Atribuição de Responsáveis por Área
O sistema deve permitir vincular um usuário do sistema a uma área de atendimento específica para uma determinada empresa.

#### Scenario: Sucesso na Atribuição
- **WHEN** o usuário seleciona uma empresa, uma área de atendimento e um usuário do sistema.
- **THEN** a atribuição é salva e exibida na listagem.

#### Scenario: Atribuição Múltipla
- **WHEN** o mesmo usuário é atribuído a diferentes áreas de atendimento.
- **THEN** o sistema deve permitir e salvar cada atribuição de forma independente.

#### Scenario: Atribuição Opcional
- **WHEN** uma área de atendimento não tem um usuário atribuído.
- **THEN** o sistema deve permitir que o campo fique vazio ou a atribuição seja removida.

## MODIFIED Requirements
Não há requisitos existentes modificados.

## REMOVED Requirements
Não há requisitos removidos.
