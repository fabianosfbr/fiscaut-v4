---
status: updated
generated: 2026-02-11
updated: 2026-02-15
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

# Gerar TXT das NFe para Integração com Domínio Plan

> Desenvolver funcionalidade para geração de arquivos TXT contendo informações das Notas Fiscais Eletrônicas (NFe) extraídas do XML da NFe e considerando configurações cadastradas das etiquetas aplicadas à NFe, Acumuladores, Cfops equivalentes, etiquetas equivalentes e produtos genéricos cadastrados do current issuer, organizadas conforme o leiaute da Domínio Sistemas

## Task Snapshot
- **Primary goal:** Criar uma funcionalidade robusta que gere arquivos TXT padronizados contendo informações essenciais das Notas Fiscais Eletrônicas (NFe) extraídas diretamente do XML da NFe e considerando configurações cadastradas das etiquetas aplicadas à NFe, Acumuladores, Cfops equivalentes, etiquetas equivalentes e produtos genéricos cadastrados do current issuer, organizadas conforme o leiaute especificado pela Domínio Sistemas
- **Success signal:** Sistema capaz de extrair dados do XML das NFe e exportar em formato TXT seguindo o padrão da Domínio Sistemas, considerando as configurações cadastradas das etiquetas, acumuladores, CFOPs equivalentes, etiquetas equivalentes e produtos genéricos do current issuer, com cobertura de testes adequada e documentação clara para desenvolvedores e usuários
- **Key references:**
  - [Documentation Index](../docs/README.md)
  - [Agent Handbook](../agents/README.md)
  - [Layout Domínio Sistemas](../docs/integracoes/layout-dominio-sistemas.md)
  - [Sample NFe XML](../docs/integracoes/samples/35260255462958000196550010000006061021790417.xml)
  - [Sample TXT Output](../docs/integracoes/samples/rCbddAZt.txt)
  - [Plans Index](./README.md)

## Codebase Context
- **Total files analyzed:** 32
- **Total symbols discovered:** 219
- **Architecture layers:** Models, Components
- **Entry points:** resources/js/app.js, public/js/filament/filament/app.js

## Agent Lineup
| Agent | Role in this plan | Playbook | First responsibility focus |
| --- | --- | --- | --- |
| Code Reviewer | Revisar o código implementado para garantir qualidade e aderência às práticas recomendadas | [Code Reviewer](../agents/code-reviewer.md) | Revisar código de qualidade, estilo e melhores práticas |
| Bug Fixer | Identificar e corrigir possíveis bugs durante o desenvolvimento | [Bug Fixer](../agents/bug-fixer.md) | Analisar relatórios de erros e mensagens |
| Feature Developer | Implementar a funcionalidade de geração de TXT das NFe | [Feature Developer](../agents/feature-developer.md) | Implementar novas funcionalidades de acordo com especificações |
| Refactoring Specialist | Melhorar estrutura do código conforme necessário | [Refactoring Specialist](../agents/refactoring-specialist.md) | Identificar oportunidades de melhoria no código |
| Test Writer | Escrever testes unitários e de integração para a nova funcionalidade | [Test Writer](../agents/test-writer.md) | Escrever testes abrangentes de unidade e integração |
| Documentation Writer | Documentar a nova funcionalidade para desenvolvedores e usuários | [Documentation Writer](../agents/documentation-writer.md) | Criar documentação clara e abrangente |
| Performance Optimizer | Garantir que a geração dos arquivos TXT seja eficiente | [Performance Optimizer](../agents/performance-optimizer.md) | Identificar gargalos de desempenho |
| Security Auditor | Verificar segurança na manipulação dos dados fiscais | [Security Auditor](../agents/security-auditor.md) | Identificar vulnerabilidades de segurança |
| Backend Specialist | Implementar a lógica de negócios no servidor | [Backend Specialist](../agents/backend-specialist.md) | Projetar e implementar arquitetura do lado do servidor |
| Architect Specialist | Definir a arquitetura da solução | [Architect Specialist](../agents/architect-specialist.md) | Projetar arquitetura geral e padrões do sistema |
| Devops Specialist | Configurar CI/CD para a nova funcionalidade | [Devops Specialist](../agents/devops-specialist.md) | Projetar e manter pipelines de CI/CD |
| Database Specialist | Otimizar consultas para obtenção dos dados das NFe | [Database Specialist](../agents/database-specialist.md) | Projetar e otimizar esquemas de banco de dados |

## Documentation Touchpoints
| Guide | File | Primary Inputs |
| --- | --- | --- |
| Project Overview | [project-overview.md](../docs/project-overview.md) | Roadmap, README, notas de stakeholders |
| Architecture Notes | [architecture.md](../docs/architecture.md) | ADRs, limites de serviço, gráficos de dependência |
| Development Workflow | [development-workflow.md](../docs/development-workflow.md) | Regras de branching, configurações de CI, guia de contribuição |
| Testing Strategy | [testing-strategy.md](../docs/testing-strategy.md) | Configurações de teste, gates de CI, suítes conhecidas como instáveis |
| Glossary & Domain Concepts | [glossary.md](../docs/glossary.md) | Terminologia de negócio, personas de usuário, regras de domínio |
| Data Flow & Integrations | [data-flow.md](../docs/data-flow.md) | Diagramas de sistema, especificações de integração, tópicos de fila |
| Layout Domínio Sistemas | [layout-dominio-sistemas.md](../docs/integracoes/layout-dominio-sistemas.md) | Leiaute de registros TXT, campos obrigatórios, formatação específica |
| Sample NFe XML | [35260255462958000196550010000006061021790417.xml](../docs/integracoes/samples/35260255462958000196550010000006061021790417.xml) | Estrutura de dados da NFe, campos obrigatórios, exemplos reais |
| Sample TXT Output | [rCbddAZt.txt](../docs/integracoes/samples/rCbddAZt.txt) | Formato esperado de saída, mapeamento de campos XML para TXT |
| Security & Compliance Notes | [security.md](../docs/security.md) | Modelo de autenticação, gerenciamento de segredos, requisitos de conformidade |
| Tooling & Productivity Guide | [tooling.md](../docs/tooling.md) | Scripts de CLI, configurações de IDE, fluxos de automação |

## Risk Assessment
Identificar potenciais bloqueadores, dependências e estratégias de mitigação antes de iniciar o trabalho.

### Identified Risks
| Risk | Probability | Impact | Mitigation Strategy | Owner |
| --- | --- | --- | --- | --- |
| Mudanças nas regras fiscais afetando o formato do TXT | Média | Alta | Manter formato flexível e documentar pontos de extensão | Backend Specialist |
| Problemas de desempenho ao processar grandes volumes de NFe | Baixa | Média | Implementar paginação e otimização de consultas | Performance Optimizer |

### Dependencies
- **Internal:** Modelos de NFe já existentes no sistema, componentes de exportação, XML das NFe
- **External:** Documentação do leiaute da Domínio Sistemas (@docs/integracoes/layout-dominio-sistemas.md)
- **Technical:** Laravel Framework, FilamentPHP, bibliotecas de manipulação de XML e texto

### Assumptions
- Os modelos de dados das NFe já estão implementados no sistema
- Os arquivos XML das NFe estão disponíveis e acessíveis
- O leiaute específico da Domínio Sistemas está documentado em @docs/integracoes/layout-dominio-sistemas.md
- Exemplos reais de NFe XML e seus respectivos arquivos TXT de saída estão disponíveis em @docs/integracoes/samples/
- As configurações de etiquetas aplicadas à NFe, Acumuladores, Cfops equivalentes, etiquetas equivalentes e produtos genéricos cadastrados do current issuer estão disponíveis no sistema
- O sistema tem capacidade de processar lotes de NFe para geração de arquivos TXT
- Será necessário implementar uma estrutura de classes bem definida para representar os diferentes tipos de registros (0000, 0010, 0020, 0030, 0100, 1000, 1010, 1020, 1030, 1500, etc.) conforme especificação

## Architecture of Class Structure

### Proposed Class Hierarchy
Para atender aos requisitos de coesão, reutilização e aderência ao princípio DRY (Don't Repeat Yourself), propõe-se a seguinte estrutura de classes:

1. **RegistroBase** - Classe abstrata base que define a estrutura comum a todos os registros
   - Campos: tipo_registro, separador_padrao, codificacao_arquivo
   - Métodos: formatarCampo(), converterParaLinhaTxt()

2. **Registro0000** - Representa o registro de identificação da empresa
   - Herda de: RegistroBase
   - Campos específicos: inscricao_empresa

3. **Registro0010** - Representa o cadastro de clientes
   - Herda de: RegistroBase
   - Campos específicos: inscricao, razao_social, apelido, endereco, numero, complemento, bairro, cod_municipio, uf, cep, etc.

4. **Registro0020** - Representa o cadastro de fornecedores
   - Herda de: RegistroBase
   - Campos específicos: inscricao, razao_social, apelido, endereco, numero, complemento, bairro, cod_municipio, uf, cep, etc.

5. **Registro0030** - Representa o cadastro de remetente e destinatário
   - Herda de: RegistroBase
   - Campos específicos: inscricao_cnpj, razao_social, endereco, codigo_municipio, uf, inscricao_estadual, tipo_inscricao

6. **Registro0100** - Representa o cadastro de produtos
   - Herda de: RegistroBase
   - Campos específicos: codigo_produto, descricao_produto, codigo_ncm, codigo_de_barras, unidade_medida, valor_unitario, etc.

7. **Registro0110** - Representa produtos - vigência (registro filho de 0100)
   - Herda de: RegistroBase
   - Campos específicos: descricao, cst_entrada, base_credito, aliquota_pis_entradas, aliquota_cofins_entradas, etc.

8. **Registro0120** - Representa produtos - unidades comercializadas (registro filho de 0100)
   - Herda de: RegistroBase
   - Campos específicos: sigla_unidade_comercializada, fator_conversao, codigo_barras

9. **Registro0135** - Representa produtos - valor unitário (registro filho de 0100)
   - Herda de: RegistroBase
   - Campos específicos: data, valor_unitario

10. **Registro0150** - Representa produtos - unidade de medida
    - Herda de: RegistroBase
    - Campos específicos: sigla, descricao

11. **Registro1000** - Representa notas fiscais de entrada
    - Herda de: RegistroBase
    - Campos específicos: codigo_especie, inscricao_fornecedor, cfop, numero_documento, serie, data_emissao, valor_contabil, etc.

12. **Registro1010** - Representa informações complementares de notas fiscais (registro filho de 1000)
    - Herda de: RegistroBase
    - Campos específicos: informacoes_complementares

13. **Registro1015** - Representa observações de interesse do fisco (registro filho de 1000)
    - Herda de: RegistroBase
    - Campos específicos: observacoes_interesse_fisco

14. **Registro1020** - Representa impostos de notas fiscais (registro filho de 1000)
    - Herda de: RegistroBase
    - Campos específicos: codigo_imposto, valor_imposto, base_calculo, aliquota, cst, cfop, etc.

15. **Registro1030** - Representa estoque de notas fiscais (registro filho de 1000)
    - Herda de: RegistroBase
    - Campos específicos: codigo_produto, quantidade, valor_unitario, valor_total, unidade_medida, cfop, etc.

16. **Registro1200** - Representa ICMS de empresa do Simples Nacional SP (registro filho de 1000)
    - Herda de: RegistroBase
    - Campos específicos: codigo_produto, cfop, codigo_situacao_tributaria, base_calculo_icms, aliquota_icms, etc.

17. **Registro1500** - Representa parcelas de notas fiscais (registro filho de 1000)
    - Herda de: RegistroBase
    - Campos específicos: numero_parcela, data_vencimento, valor_parcela, codigo_condicao_pagamento, etc.

### Design Patterns a Serem Utilizados
- **Factory Pattern** - Para criação de instâncias de registros específicos
- **Builder Pattern** - Para construção de registros complexos com muitos campos opcionais
- **Strategy Pattern** - Para diferentes estratégias de formatação de campos
- **Template Method Pattern** - Para definir o esqueleto de conversão para formato TXT

### Interfaces e Contratos
- **IRegistro** - Interface que define o contrato mínimo para qualquer tipo de registro
- **IFormatador** - Interface para diferentes formatadores de campos
- **IValidador** - Interface para validação de campos específicos de cada tipo de registro

## Resource Estimation

### Time Allocation
| Phase | Estimated Effort | Calendar Time | Team Size |
| --- | --- | --- | --- |
| Phase 1 - Discovery | 3 person-days | 4-5 dias | 1-2 pessoas |
| Phase 2 - Implementation | 10 person-days | 2.5 semanas | 2-3 pessoas |
| Phase 3 - Validation | 3 person-days | 4-5 dias | 1-2 pessoas |
| **Total** | **16 person-days** | **3.5 semanas** | **-** |

### Required Skills
- Experiência com Laravel PHP
- Conhecimento em manipulação de arquivos XML e formatação de texto
- Experiência com testes automatizados
- Conhecimento em padrões fiscais e tributários (desejável)
- Familiaridade com o leiaute da Domínio Sistemas
- Forte conhecimento em programação orientada a objetos e design patterns
- Experiência com princípios SOLID e DRY

### Resource Availability
- **Available:** Equipe de desenvolvimento backend com experiência em Laravel
- **Blocked:** Nenhum bloqueio identificado
- **Escalation:** Gerente de projeto para questões de escopo ou prioridade

## Working Phases
### Phase 1 — Discovery & Alignment
**Steps**
1. Analisar os modelos de dados existentes relacionados às NFe e os arquivos XML correspondentes (Feature Developer, Backend Specialist)
2. Estudar o leiaute da Domínio Sistemas conforme documentado em @docs/integracoes/layout-dominio-sistemas.md, identificando todos os tipos de registros (0000, 0010, 0020, 0030, 0100, 0110, 0120, 0135, 0150, 1000, 1010, 1015, 1020, 1030, 1200, 1500, etc.) (Architect Specialist, Backend Specialist)
3. Analisar exemplos reais de NFe XML e seus respectivos arquivos TXT de saída em @docs/integracoes/samples/ para entender o mapeamento de campos (Architect Specialist, Backend Specialist, Documentation Writer)
4. Identificar os campos necessários nos registros TXT e mapear com os dados do XML da NFe (Documentation Writer, Backend Specialist)
5. Projetar a estrutura de classes coesa e reaproveitável para representar os diferentes tipos de registros, seguindo os princípios de DRY (Don't Repeat Yourself) e boas práticas de orientação a objetos (Architect Specialist, Backend Specialist)
6. Definir estratégia de extração de dados do XML e transformação para o formato TXT (Architect Specialist, Backend Specialist)
7. Investigar as configurações cadastradas de etiquetas aplicadas à NFe, Acumuladores, Cfops equivalentes, etiquetas equivalentes e produtos genéricos cadastrados do current issuer (Backend Specialist, Database Specialist)
8. Determinar como integrar as informações das configurações com os dados extraídos do XML da NFe (Architect Specialist, Backend Specialist)

**Commit Checkpoint**
- Após concluir esta fase, capturar o contexto acordado e criar um commit (por exemplo, `git commit -m "chore(plan): complete phase 1 discovery"`).

### Phase 2 — Implementation & Iteration
**Steps**
1. Implementar a classe abstrata RegistroBase com os métodos comuns de formatação e conversão para TXT (Backend Specialist, Architect Specialist)
2. Implementar as classes concretas para cada tipo de registro (0000, 0010, 0020, 0030, 0100, 0110, 0120, 0135, 0150, 1000, 1010, 1015, 1020, 1030, 1200, 1500, etc.) seguindo os princípios de DRY e boas práticas de orientação a objetos (Backend Specialist, Architect Specialist)
3. Implementar interfaces contratuais (IRegistro, IFormatador, IValidador) para garantir consistência na implementação (Backend Specialist, Architect Specialist)
4. Implementar padrões de projeto (Factory, Builder, Strategy) para facilitar a criação e manipulação dos registros (Backend Specialist, Architect Specialist)
5. Implementar o parser de XML para extrair dados das NFe, baseando-se nos exemplos reais de @docs/integracoes/samples/ (Feature Developer, Backend Specialist)
6. Implementar a lógica de mapeamento entre os dados do XML e os registros TXT conforme o leiaute da Domínio Sistemas, validando com os exemplos de saída (Feature Developer, Backend Specialist)
7. Implementar a geração dos registros TXT com base nos dados extraídos do XML, utilizando a estrutura de classes projetada (Feature Developer, Backend Specialist)
8. Implementar a lógica para considerar as configurações cadastradas de etiquetas aplicadas à NFe, Acumuladores, Cfops equivalentes, etiquetas equivalentes e produtos genéricos cadastrados do current issuer (Feature Developer, Backend Specialist)
9. Integrar as informações das configurações com os dados extraídos do XML da NFe para gerar os registros TXT conforme o leiaute da Domínio Sistemas (Feature Developer, Backend Specialist)
10. Criar testes unitários e de integração para garantir a correta extração e formatação dos dados, usando os exemplos reais como base de validação (Test Writer)
11. Implementar interfaces de usuário para acionar a geração dos arquivos TXT a partir das NFe (Feature Developer)
12. Realizar revisões de código e refatorações conforme necessário (Code Reviewer, Refactoring Specialist)

**Commit Checkpoint**
- Resumir o progresso, atualizar links cruzados e criar um commit documentando os resultados desta fase (por exemplo, `git commit -m "feat: implement NFe TXT export functionality"`).

### Phase 3 — Validation & Handoff
**Steps**
1. Realizar testes de ponta a ponta para validar a transformação XML para TXT conforme o leiaute da Domínio Sistemas, comparando com os exemplos reais de @docs/integracoes/samples/ (Test Writer, Bug Fixer)
2. Validar que os registros TXT gerados estão em conformidade com o formato especificado, usando os arquivos de amostra como referência (Test Writer, Quality Assurance)
3. Validar que as configurações de etiquetas aplicadas à NFe, Acumuladores, Cfops equivalentes, etiquetas equivalentes e produtos genéricos cadastrados do current issuer foram corretamente consideradas na geração dos registros TXT (Test Writer, Quality Assurance)
4. Atualizar documentação técnica e guias de uso para desenvolvedores (Documentation Writer)
5. Preparar documentação para usuários finais sobre como utilizar a funcionalidade (Documentation Writer)
6. Validar desempenho e segurança da nova funcionalidade, especialmente na manipulação de arquivos XML e geração de TXT (Performance Optimizer, Security Auditor)

**Commit Checkpoint**
- Registrar evidências de validação e criar um commit sinalizando a conclusão da entrega (por exemplo, `git commit -m "docs: complete NFe TXT export documentation"`).

## Rollback Plan
Documentar como reverter alterações se problemas surgirem durante ou após a implementação.

### Rollback Triggers
Quando iniciar rollback:
- Bugs críticos afetando funcionalidades principais
- Degraduação de desempenho além dos limiares aceitáveis
- Problemas de integridade de dados detectados
- Vulnerabilidades de segurança introduzidas
- Erros que afetam o usuário excedendo os limiares de alerta

### Rollback Procedures
#### Phase 1 Rollback
- Action: Descartar branch de descoberta, restaurar estado anterior da documentação
- Data Impact: Nenhum (nenhuma alteração em produção)
- Estimated Time: < 1 hora

#### Phase 2 Rollback
- Action: Reverter commits, remover código da nova funcionalidade
- Data Impact: Nenhum impacto nos dados existentes
- Estimated Time: 2-4 horas

#### Phase 3 Rollback
- Action: Retirar documentação adicionada, remover referências à funcionalidade
- Data Impact: Nenhum impacto nos dados
- Estimated Time: 1-2 horas

### Post-Rollback Actions
1. Documentar motivo do rollback em relatório de incidente
2. Notificar stakeholders sobre o rollback e seu impacto
3. Agendar pós-mortem para analisar falha
4. Atualizar plano com lições aprendidas antes de tentar novamente

## Evidence & Follow-up

Artefatos a coletar: links de PRs, resultados de testes, notas de design, logs de execução da geração de arquivos TXT, comparação com os arquivos de amostra em @docs/integracoes/samples/.
Ações de acompanhamento: monitoramento de uso da nova funcionalidade, revisão de desempenho após implantação em produção, validação contínua contra os exemplos reais de NFe XML e TXT.