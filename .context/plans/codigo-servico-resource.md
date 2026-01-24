---
status: filled
generated: 2026-01-24
agents:
  - type: "code-reviewer"
    role: "Review code changes for quality, style, and best practices"
  - type: "bug-fixer"
    role: "Analyze bug reports and error messages"
  - type: "feature-developer"
    role: "Implement new features according to specifications"
  - type: "refactoring-specialist"
    role: "Identify code smells and improvement opportunities"
  - type: "test-writer"
    role: "Write comprehensive unit and integration tests"
  - type: "documentation-writer"
    role: "Create clear, comprehensive documentation"
  - type: "performance-optimizer"
    role: "Identify performance bottlenecks"
  - type: "security-auditor"
    role: "Identify security vulnerabilities"
  - type: "backend-specialist"
    role: "Design and implement server-side architecture"
  - type: "frontend-specialist"
    role: "Design and implement user interfaces"
  - type: "architect-specialist"
    role: "Design overall system architecture and patterns"
  - type: "devops-specialist"
    role: "Design and maintain CI/CD pipelines"
  - type: "database-specialist"
    role: "Design and optimize database schemas"
  - type: "mobile-specialist"
    role: "Develop native and cross-platform mobile applications"
docs:
  - "project-overview.md"
  - "architecture.md"
  - "development-workflow.md"
  - "testing-strategy.md"
  - "glossary.md"
  - "data-flow.md"
  - "security.md"
  - "tooling.md"
phases:
  - id: "phase-1"
    name: "Discovery & Alignment"
    prevc: "P"
  - id: "phase-2"
    name: "Implementation & Iteration"
    prevc: "E"
  - id: "phase-3"
    name: "Validation & Handoff"
    prevc: "V"
---

# Plano — Resource de Códigos de Serviço (Filament)

Objetivo: entregar a feature de listagem, cadastro e edição de Códigos de Serviço no admin (Filament), seguindo o mesmo padrão já aplicado em CNAE e CFOP.

## Task Snapshot
- **Meta principal:** disponibilizar CRUD (List/Create/Edit) para `CodigoServico` no Filament.
- **Sinal de sucesso:** usuário consegue listar/filtrar, cadastrar e editar registros sem erros; validações e relacionamentos funcionando.
- **Padrão de referência:** copiar estrutura e decisões de UI/UX de CNAE/CFOP (Resource + Schemas + Tables + Pages).
- **Referências-chave:**
  - [Plans Index](./README.md)
  - [CnaeResource](../../app/Filament/Resources/Cnaes/CnaeResource.php)
  - [CfopResource](../../app/Filament/Resources/Cfops/CfopResource.php)

## Contexto no Codebase
- **Model existente:** [CodigoServico](../../app/Models/CodigoServico.php) (tabela `codigos_servico`, relacionamento opcional com CNAE).
- **Migrations relevantes:**
  - Criação: [create_codigo_servicos_table](../../database/migrations/2025_10_24_081054_create_codigo_servicos_table.php)
  - FK CNAE: [add_cnae_id_in_codigos_servico_table](../../database/migrations/2025_10_29_165205_add_cnae_id_in_codigos_servico_table.php)
  - `descricao` como text: [alter_descricao_to_text_in_codigos_servico_table](../../database/migrations/2025_10_29_164220_alter_descricao_to_text_in_codigos_servico_table.php)
  - Ajustes de coluna (inclui remoção de `anexo`): [drop_anexo_field_in_codigos_servico_table](../../database/migrations/2025_10_29_165441_drop_anexo_field_in_codigos_servico_table.php)
- **CRUDs de referência (padrão a replicar):**
  - CNAE: [CnaeForm](../../app/Filament/Resources/Cnaes/Schemas/CnaeForm.php) e [CnaesTable](../../app/Filament/Resources/Cnaes/Tables/CnaesTable.php)
  - CFOP: [CfopForm](../../app/Filament/Resources/Cfops/Schemas/CfopForm.php) e [CfopsTable](../../app/Filament/Resources/Cfops/Tables/CfopsTable.php)

## Escopo da Feature (o que entra)
- **Admin Filament**
  - Menu/navegação: entrada para “Códigos de Serviço”.
  - Listagem: busca e filtros (mínimo: por código e descrição; opcional: por CNAE).
  - Cadastro: formulário com validação (mínimo: código, descrição; CNAE opcional).
  - Edição: permitir editar os mesmos campos do cadastro.
- **Model / Banco**
  - Alinhar `fillable` e validações com o schema real (há migration removendo `anexo`; decidir se o campo fica fora do CRUD e do model).
  - Garantir relacionamento com CNAE exibível/selecionável no CRUD quando aplicável.

## Fora de escopo (neste ciclo)
- Importação em massa / seed / integração com NFe.
- Telas públicas fora do Filament.
- Regras fiscais avançadas em cima de `CodigoServico` (além do CRUD).

## Decisões e Regras de Negócio
- **Chave de negócio:** `codigo` é o identificador funcional do código de serviço.
- **Validações mínimas recomendadas:**
  - `codigo`: obrigatório; tamanho máximo 10 (conforme migration); idealmente único.
  - `descricao`: obrigatória; texto (migration original `string(255)`, mas existe alteração para text em migrations posteriores).
  - `cnae_id`: opcional; deve referenciar `cnaes.id` quando preenchido.
- **Coluna `anexo`:** atualmente existe migration que remove a coluna. O CRUD deve refletir o schema vigente; se o campo for necessário, reverter decisão de schema antes de expor na UI.

## Estrutura proposta (espelhando CNAE/CFOP)
- **Resource:** `app/Filament/Resources/CodigosServicos/CodigoServicoResource.php`
- **Schemas:** `.../Schemas/CodigoServicoForm.php`
- **Tables:** `.../Tables/CodigosServicosTable.php`
- **Pages:** `.../Pages/ListCodigosServicos.php`, `CreateCodigoServico.php`, `EditCodigoServico.php`

## Riscos e Mitigações
| Risco | Prob. | Impacto | Mitigação |
| --- | --- | --- | --- |
| Divergência entre Model e schema (`anexo`, tipo de `descricao`) | Média | Média | Conferir migrations vigentes e alinhar Model + Form/Tables com o schema atual |
| Volume alto de registros impactar listagem | Baixa | Média | Replicar paginação, índices e busca do padrão CNAE/CFOP; adicionar índices se necessário |
| Validação de unicidade de `codigo` conflitar com dados existentes | Média | Média | Validar dados atuais antes de impor unique; se necessário, normalizar/limpar duplicatas |

## Dependências
- **Internas:** recursos já existentes de CNAE ([CnaeResource](../../app/Filament/Resources/Cnaes/CnaeResource.php)) para reuso de padrões e seleção.
- **Técnicas:** padrão Filament v5 (mesma abordagem de Schemas/Tables/Pages de CNAE/CFOP).

## Fases de Trabalho
### Phase 1 — Alinhamento (Discovery & Alignment)
1. Confirmar campos vigentes em `codigos_servico` a partir das migrations (em especial `anexo` e tipo de `descricao`).
2. Definir regras de validação (principalmente unicidade de `codigo`) e comportamento de CNAE (obrigatório vs opcional).
3. Definir nomenclatura, group/label e permissões (seguir estilo de CNAE/CFOP).

### Phase 2 — Implementação (Implementation & Iteration)
1. Criar o Filament Resource e Pages no mesmo padrão de [CnaeResource](../../app/Filament/Resources/Cnaes/CnaeResource.php) e [CfopResource](../../app/Filament/Resources/Cfops/CfopResource.php).
2. Implementar `Form` e `Table`:
   - Campos: `codigo`, `descricao`, `cnae_id` (select/pesquisa de CNAE).
   - Ações: create/edit; bulk delete se já usado nos demais recursos.
   - Busca/ordenação: por código e descrição.
3. Ajustar o Model `CodigoServico` se necessário (campos fillable e casts coerentes com schema).

### Phase 3 — Validação e Entrega (Validation & Handoff)
1. Cobrir o fluxo mínimo com testes (quando viável) e validação manual no Filament.
2. Verificar que rotas do Filament, navegação e permissões estão corretas.
3. Registrar evidências: prints ou checklist de “listar/criar/editar”.

## Rollback (se necessário)
- Reverter o Resource/Pages e remover do menu do Filament.
- Se houver migrations novas, reverter com segurança (snapshot/backup antes, se ambiente produtivo).

## Evidências esperadas
- Screenshot da listagem com busca.
- Screenshot do formulário de criação e edição.
- Registro de execução de testes (quando aplicável).
