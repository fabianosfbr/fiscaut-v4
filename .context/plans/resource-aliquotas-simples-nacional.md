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
  - type: "documentation-writer"
    role: "Create clear, comprehensive documentation"
  - type: "performance-optimizer"
    role: "Identify performance bottlenecks"
  - type: "security-auditor"
    role: "Identify security vulnerabilities"
  - type: "backend-specialist"
    role: "Design and implement server-side architecture"
  - type: "architect-specialist"
    role: "Design overall system architecture and patterns"
  - type: "devops-specialist"
    role: "Design and maintain CI/CD pipelines"
  - type: "database-specialist"
    role: "Design and optimize database schemas"
docs:
  - "project-overview.md"
  - "architecture.md"
  - "development-workflow.md"
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

# Plano — Resource de Alíquotas do Simples Nacional

> Implementar um Filament Resource para manter a tabela `simples_nacional_aliquotas` (por anexo e faixa), seguindo a mesma organização do Resource de CFOP (Resource + Pages + Schema + Table).

## Task Snapshot
- **Objetivo principal:** disponibilizar CRUD no painel Filament para cadastrar/editar/excluir alíquotas do Simples Nacional por anexo e faixa de receita.
- **Sinal de sucesso:** um usuário admin consegue listar, filtrar/ordenar e criar/editar registros com validações consistentes (faixas sem sobreposição e percentuais válidos), e os dados persistem corretamente no MySQL.
- **Referências principais:**
  - Resource de CFOP: [CfopResource.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cfops/CfopResource.php)
  - Form de CFOP: [CfopForm.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cfops/Schemas/CfopForm.php)
  - Table de CFOP: [CfopsTable.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Resources/Cfops/Tables/CfopsTable.php)
  - Migration das alíquotas: [2024_01_01_000002_create_simples_nacional_aliquotas_table.php](file:///root/projetos/fiscaut-v4.1/database/migrations/2024_01_01_000002_create_simples_nacional_aliquotas_table.php)

## Escopo
- **Inclui**
  - Model Eloquent para `simples_nacional_aliquotas` (e opcionalmente para `simples_nacional_anexos` para alimentar options).
  - Filament Resource (List/Create/Edit) com Form + Table no padrão do CFOP.
  - Validações de integridade no formulário (e, se necessário, regra reutilizável no backend).
- **Não inclui (neste ciclo)**
  - Importador/seed oficial de anexos/alíquotas.
  - Regras de cálculo de Simples Nacional (já existe tabela de cálculos, mas não é o alvo deste resource).

## Padrão de implementação (alinhado ao CFOP)
- Pastas do Resource:
  - `app/Filament/Resources/<NomeDoResource>/` com `Resource.php`, `Pages/`, `Schemas/` e `Tables/`.
- Model labels e menu:
  - `navigationGroup = "Configurações"`.
  - `modelLabel` e `pluralModelLabel` com `hasTitleCaseModelLabel = false` quando necessário (ex.: CFOP/CNAE).

## Agentes (papéis)
| Agent | Por que entra | Entregas esperadas |
| --- | --- | --- |
| Feature Developer | Implementação do Resource e Models seguindo padrões do repositório. | Novos arquivos do Resource + Models com validações. |
| Database Specialist | Revisão de constraints/índices e regras de integridade das faixas. | Checklist de integridade e ajustes necessários. |
| Code Reviewer | Aderência a estilo e padrões Filament v5. | Revisão de qualidade e consistência do código. |

## Riscos e mitigação
| Risco | Prob. | Impacto | Mitigação |
| --- | --- | --- | --- |
| Sobreposição de faixas por anexo (inconsistência) | Média | Alta | Validar no Form (consulta por anexo) e/ou regra de domínio reutilizável no backend. |
| Formatação/precisão de decimais (% e moeda) | Média | Média | Padronizar `step` nos inputs e `number_format` na tabela. |
| Falta de dados base em `simples_nacional_anexos` | Baixa | Média | Se tabela estiver vazia, provisionar seed/importação antes de liberar o CRUD. |

## Dependências e premissas
- Migrations já existentes e aplicadas no banco:
  - `simples_nacional_anexos` (FK por `anexo` string).
  - `simples_nacional_aliquotas` e `ipi_percentual` adicional.
- Premissa: o cadastro é **global** (sem `tenant_id`), então o Resource será visível para todos os tenants.

## Working Phases
### Phase 1 — Discovery & Alignment
**Objetivo:** fechar regras de validação e UX mínima, ancorado no padrão do CFOP.

**Steps**
1. Confirmar campos/tipos do schema e quais colunas vão para Listagem x Edição.
2. Definir as regras de validação:
   - `faixa_inicial <= faixa_final`.
   - percentuais entre 0 e 100 (quando aplicável).
   - evitar sobreposição de faixas no mesmo `anexo`.
3. Definir labels: “Alíquota”, “Valor a deduzir”, “Faixa inicial/final”, percentuais (IRPJ/CSLL/COFINS/PIS/CPP/ICMS/ISS/IPI).

**Checkpoint**
- Atualizar este plano com as regras finais e UX definida.

### Phase 2 — Implementation & Iteration
**Objetivo:** entregar Models e Resource com CRUD completo.

**Steps**
1. Models
   - Criar `SimplesNacionalAliquota` com `protected $table = 'simples_nacional_aliquotas'`.
   - Criar `SimplesNacionalAnexo` (opcional, mas recomendado) para alimentar Select via `pluck('descricao', 'anexo')` ou similar.
2. Resource
   - Criar `SimplesNacionalAliquotaResource` (grupo “Configurações”).
   - Criar `Schemas/SimplesNacionalAliquotaForm.php` com inputs numéricos/validações.
   - Criar `Tables/SimplesNacionalAliquotasTable.php` com formatação de moeda/% e ações `Edit/Delete`.
3. UX de tabela
   - Ordenação padrão por `anexo` e `faixa_inicial`.
   - Filtro por `anexo` (opcional).

**Checkpoint**
- Lista de arquivos criados e principais decisões (ex.: validação de sobreposição) registrada no final do plano.

### Phase 3 — Validation & Handoff
**Objetivo:** validar manualmente o CRUD e as validações-chave no fluxo.

**Steps**
1. Validação manual no Filament
   - Criar/editar/excluir e conferir mensagens de validação e formatação em listagem.
2. Documentação (se necessário)
   - Atualizar glossário com definição dos campos e convenções de percentuais/valores.

## Rollback Plan
- Reversão rápida: remover Resource/Models adicionados (não envolve migrations novas).
- Impacto: apenas o admin; dados eventualmente cadastrados no banco permanecem, mas deixam de ser editáveis pela UI.

## Evidence & Follow-up
- Evidências
  - Arquivos entregues:
    - `app/Models/SimplesNacionalAnexo.php`
    - `app/Models/SimplesNacionalAliquota.php`
    - `app/Filament/Resources/SimplesNacionalAliquotas/SimplesNacionalAliquotaResource.php`
    - `app/Filament/Resources/SimplesNacionalAliquotas/Pages/{List,Create,Edit}SimplesNacionalAliquota(s).php`
    - `app/Filament/Resources/SimplesNacionalAliquotas/Schemas/SimplesNacionalAliquotaForm.php`
    - `app/Filament/Resources/SimplesNacionalAliquotas/Tables/SimplesNacionalAliquotasTable.php`
  - Lista de validações aplicadas no Form.
  - Validação de integridade no backend: bloqueia sobreposição de faixas por `anexo` e `faixa_inicial/faixa_final` no salvamento do model.
  - Prints do Resource (List/Create/Edit) funcionando.
  - Validação técnica: `php -l` sem erros nos arquivos criados.
- Follow-up (opcional)
  - Adicionar automação de validação quando o ambiente estiver pronto.
  - Importador/seed oficial de alíquotas e anexos do Simples Nacional.
