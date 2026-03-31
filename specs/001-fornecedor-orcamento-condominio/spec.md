# Feature Specification: Fornecedores e Orçamentos - Condomínio

**Feature Branch**: `001-fornecedor-orcamento-condominio`  
**Created**: 2026-03-31  
**Status**: Draft  
**Input**: "Criar uma feature para atender o requisito abaixo no painel condominio Cadastrar e Gerenciar Fornecedores"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Cadastro e Gerenciamento de Fornecedores (Priority: P1)

Como gestor de compras, eu quero cadastrar fornecedores com informações de contato e sistema de rating, para que eu possa priorizar fornecedores com melhor desempenho histórico.

**Why this priority**: Core functionality para gestão de fornecedores no condomínio.

**Independent Test**: Pode ser testado unitariamente sem necessidade de rede real ou banco de dados.

**Acceptance Scenarios**:

1. **Given** um novo fornecedor com dados válidos (nome, email, WhatsApp, categorias), **When** o formulário é submetido, **Then** o fornecedor é criado com rating inicial neutro (3.0).
2. **Given** um fornecedor com email inválido, **When** tenta salvar, **Then** retorna erro de validação de email.
3. **Given** um fornecedor com WhatsApp inválido, **When** tenta salvar, **Then** retorna erro de validação de formato WhatsApp.
4. **Given** um fornecedor existente, **When** é realizada uma interação de orçamento, **Then** o rating é recalculado baseado em taxa de resposta, tempo de resposta, taxa de ganho e qualidade.
5. **Given** um fornecedor com baixa performance (não responde + baixa taxa de ganho) ao longo de período configurável, **When** o rating é atualizado, **Then** o fornecedor é marcado como low-priority.
6. **Given** busca por fornecedores, **When** filtros aplicados (categoria, nome, contato), **Then** retorna resultados ordenados por rating.
7. **Given** histórico de interações, **When** visualiza fornecedor, **Then** exibe todas as interações, orçamentos, tempos de resposta e resultados.

---

### User Story 2 - Solicitar e Comparar Orçamentos (Priority: P1)

Como gestor de compras, eu quero enviar solicitações de orçamento para múltiplos fornecedores e comparar respostas, para que eu possa escolher a melhor opção.

**Why this priority**: Core functionality para processo de cotação no condomínio.

**Independent Test**: Pode ser testado unitariamente com mocks.

**Acceptance Scenarios**:

1. **Given** um orçamento criado com múltiplos fornecedores selecionados, **When** fornecedores são escolhidos de uma categoria, **Then** cada fornecedor recebe a solicitação com os campos definidos.
2. **Given** um fornecedor acessa o formulário de orçamento, **When** decide responder, **Then** pode preencher campos estruturados OU anexar documento.
3. **Given** fornecedores enviam propostas, **When** proposals são submetidas, **Then** dados do formulário, documento e timestamp são armazenados.
4. **Given** múltiplas propostas recebidas, **When** visualiza comparação, **Then** exibe todas ordenadas por valor com dados estruturados e documentos.
5. **Given** comparação de propostas, **When** sistema sugere melhor orçamento, **Then** considera menor valor, rating do fornecedor e prazo de entrega.

---

## Requirements *(mandatory)*

### Functional Requirements - Fornecedores

- **FR-001**: O sistema DEVE prover gestão completa de fornecedores em `app/Filament/Condominio/Resources/FornecedorResource.php`.
- **FR-002**: O fornecedor DEVE ter os campos: nome, email, WhatsApp, categorias de serviço, rating, flag de prioridade.
- **FR-003**: O fornecedor DEVE validar formato de email na criação/atualização.
- **FR-004**: O fornecedor DEVE validar formato de WhatsApp (formato brasileiro: +55XXXXXXXXXXX) na criação/atualização.
- **FR-005**: O fornecedor DEVE inicializar com rating neutro (3.0) na criação.
- **FR-006**: O sistema DEVE calcular rating baseado em: taxa de resposta, taxa de ganho (win rate), tempo de resposta, qualidade do serviço.
- **FR-007**: O sistema DEVE atualizar rating automaticamente após cada interação de orçamento.
- **FR-008**: O sistema DEVE marcar fornecedor como low-priority quando: taxa de resposta < X% OU taxa de ganho < Y% por período configurável (padrão 90 dias).
- **FR-009**: O fornecedor DEVE associar categorias de serviço para filtro e busca.
- **FR-010**: O sistema DEVE permitir busca/filtro por categoria, nome, contato e ordenação por rating.
- **FR-011**: O sistema DEVE manter histórico de interações, orçamentos, tempos de resposta e resultados.

### Functional Requirements - Orçamentos

- **FR-012**: O sistema DEVE criar orçamentos com seleção múltipla de fornecedores de uma categoria.
- **FR-012a**: O orçamento PODE estar vinculado a um IssuerControl (ex: seguro, manutenção) para contexto. Quando vinculado, o `deadline` pode ser automaticamente derivado da `data_programada` do controle.
- **FR-013**: O orçamento DEVE permitir definição de campos obrigatórios para a proposta.
- **FR-014**: O sistema DEVE gerar formulário único com campos: valor, prazo entrega, condições, campos customizados.
- **FR-015**: O sistema DEVE enviar orçamentos via email e WhatsApp com link para formulário.
- **FR-016**: O fornecedor DEVE poder responder via formulário estruturado OU anexar documento.
- **FR-017**: O sistema DEVE armazenar: dados do formulário, documento anexado, timestamp de submissão.
- **FR-018**: O sistema DEVE exibir comparação de propostas ordenadas por valor.
- **FR-019**: O sistema DEVE sugerir melhor orçamento baseado em: menor valor, maior rating, menor prazo.

### Key Entities *(include if feature involves data)*

- **Fornecedor**: Novo modelo em `app/Models/Supplier.php` — dados do fornecedor.
- **SupplierCategory**: Novo modelo em `app/Models/SupplierCategory.php` — categorias de serviço.
- **BudgetRequest**: Novo modelo em `app/Models/BudgetRequest.php` — solicitação de orçamento (pode estar vinculado a IssuerControl).
- **BudgetProposal**: Novo modelo em `app/Models/BudgetProposal.php` — proposta do fornecedor.
- **SupplierInteraction**: Novo modelo em `app/Models/SupplierInteraction.php` — histórico de interações.
- **Issuer**: Modelo existente em `app/Models/Issuer.php` — condomínio/associação.
- **IssuerControl**: Modelo existente — controle de manutenções, seguros, etc. (opcional para vincular orçamentos).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: CRUD de fornecedores funciona com validações de email e WhatsApp.
- **SC-002**: Rating é calculado automaticamente após cada interação.
- **SC-003**: Fornecedores low-priority são identificados automaticamente.
- **SC-004**: Busca/filtro por categoria, nome, contato e ordenação por rating funciona.
- **SC-005**: Histórico de interações é mantido e visualizável.
- **SC-006**: Criação de orçamento com múltiplos fornecedores funciona.
- **SC-007**: Envio de orçamento via email/WhatsApp funciona.
- **SC-008**: Fornecedor pode responder via formulário ou documento anexado.
- **SC-009**: Comparação de propostas ordenadas por valor funciona.
- **SC-010**: Suggestão de melhor orçamento considera valor, rating e prazo.