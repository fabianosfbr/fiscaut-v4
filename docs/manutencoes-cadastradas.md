# Cadastro de Manutenções - Dados de Exemplo

Este documento contém os dados necessários para cadastrar as manutenções solicitadas no sistema de gestão de manutenções do Fiscaut, contemplando as features de **Tipo de Manutenção** e **Template de Recorrência**.

## 1. Tipos de Manutenção a Cadastrar

### 1.1 Recarga de Extintores
- **Nome**: Recarga de Extintores
- **Categoria**: preventiva
- **Descrição**: Recarga e inspeção de extintores de incêndio conforme normas de segurança
- **Alerta (dias)**: 30 dias antes
- **Prioridade Padrão**: alta
- **Responsável Padrão**: Segurança do Trabalho
- **Ativo**: Sim

### 1.2 Limpeza de Caixa D'Água
- **Nome**: Limpeza de Caixa D'Água
- **Categoria**: preventiva
- **Descrição**: Limpeza e higienização da caixa d'água para garantir a qualidade da água
- **Alerta (dias)**: 15 dias antes
- **Prioridade Padrão**: alta
- **Responsável Padrão**: Manutenção Hidráulica
- **Ativo**: Sim

### 1.3 Limpeza de Caixa de Inspeção
- **Nome**: Limpeza de Caixa de Inspeção
- **Categoria**: preventiva
- **Descrição**: Limpeza e inspeção das caixas de inspeção de redes hidráulicas e elétricas
- **Alerta (dias)**: 7 dias antes
- **Prioridade Padrão**: media
- **Responsável Padrão**: Manutenção Predial
- **Ativo**: Sim

### 1.4 Troca de Telas do Campo de Futebol
- **Nome**: Troca de Telas do Campo de Futebol
- **Categoria**: corretiva
- **Descrição**: Substituição das telas de proteção do campo de futebol quando danificadas
- **Periodicidade Padrão**: não recorrente
- **Alerta (dias)**: 0 dias
- **Prioridade Padrão**: media
- **Responsável Padrão**: Manutenção Esportiva
- **Ativo**: Sim

### 1.5 Troca de Lâmpadas da Garagem
- **Nome**: Troca de Lâmpadas da Garagem
- **Categoria**: corretiva
- **Descrição**: Substituição de lâmpadas queimadas na área de garagem
- **Periodicidade Padrão**: não recorrente
- **Alerta (dias)**: 0 dias
- **Prioridade Padrão**: baixa
- **Responsável Padrão**: Manutenção Elétrica
- **Ativo**: Sim

## 2. Templates de Recorrência a Cadastrar

### 2.1 Recarga de Extintores - Anual
- **Título Template**: Recarga de Extintores - {data}
- **Tipo de Manutenção**: Recarga de Extintores
- **Frequência**: anual
- **Intervalo**: 1
- **Data de Início**: 01/01/2024
- **Data de Fim**: 31/12/2025
- **Gerar com Antecedência**: 30 dias
- **Ativo**: Sim

### 2.2 Limpeza de Caixa D'Água - Semestral
- **Título Template**: Limpeza de Caixa D'Água - {data}
- **Tipo de Manutenção**: Limpeza de Caixa D'Água
- **Frequência**: mensal
- **Intervalo**: 6
- **Data de Início**: 01/01/2024
- **Data de Fim**: 31/12/2025
- **Gerar com Antecedência**: 15 dias
- **Ativo**: Sim

### 2.3 Limpeza de Caixa de Inspeção - Trimestral
- **Título Template**: Limpeza de Caixa de Inspeção - {data}
- **Tipo de Manutenção**: Limpeza de Caixa de Inspeção
- **Frequência**: mensal
- **Intervalo**: 3
- **Data de Início**: 01/01/2024
- **Data de Fim**: 31/12/2025
- **Gerar com Antecedência**: 7 dias
- **Ativo**: Sim

## 3. Manutenções Individuais a Cadastrar

### 3.1 Troca de Telas do Campo de Futebol
- **Título**: Troca de Telas do Campo de Futebol
- **Tipo de Manutenção**: Troca de Telas do Campo de Futebol
- **Status**: programada
- **Prioridade**: media
- **Data Programada**: 15/03/2024
- **Local**: Campo de Futebol
- **Responsável**: Manutenção Esportiva
- **Template de Recorrência**: Não vinculado (manutenção corretiva)
- **Ativo**: Sim

### 3.2 Troca de Lâmpadas da Garagem
- **Título**: Troca de Lâmpadas da Garagem
- **Tipo de Manutenção**: Troca de Lâmpadas da Garagem
- **Status**: programada
- **Prioridade**: baixa
- **Data Programada**: 20/03/2024
- **Local**: Garagem
- **Responsável**: Manutenção Elétrica
- **Template de Recorrência**: Não vinculado (manutenção corretiva)
- **Ativo**: Sim

## 4. Manutenções Geradas Automaticamente

Após cadastrar os templates de recorrência, o sistema gerará automaticamente as manutenções programadas:

### 4.1 Manutenções da Recarga de Extintores (Anual)
- **01/01/2024**: Recarga de Extintores - 01/01/2024
- **01/01/2025**: Recarga de Extintores - 01/01/2025

### 4.2 Manutenções da Limpeza de Caixa D'Água (Semestral)
- **01/01/2024**: Limpeza de Caixa D'Água - 01/01/2024
- **01/07/2024**: Limpeza de Caixa D'Água - 01/07/2024
- **01/01/2025**: Limpeza de Caixa D'Água - 01/01/2025
- **01/07/2025**: Limpeza de Caixa D'Água - 01/07/2025

### 4.3 Manutenções da Limpeza de Caixa de Inspeção (Trimestral)
- **01/01/2024**: Limpeza de Caixa de Inspeção - 01/01/2024
- **01/04/2024**: Limpeza de Caixa de Inspeção - 01/04/2024
- **01/07/2024**: Limpeza de Caixa de Inspeção - 01/07/2024
- **01/10/2024**: Limpeza de Caixa de Inspeção - 01/10/2024
- **01/01/2025**: Limpeza de Caixa de Inspeção - 01/01/2025
- **01/04/2025**: Limpeza de Caixa de Inspeção - 01/04/2025
- **01/07/2025**: Limpeza de Caixa de Inspeção - 01/07/2025
- **01/10/2025**: Limpeza de Caixa de Inspeção - 01/10/2025

## 5. Observações Importantes

### 5.1 Relacionamentos
- **Tipo de Manutenção**: Cada manutenção individual deve ser vinculada a um Tipo de Manutenção
- **Template de Recorrência**: As manutenções recorrentes são vinculadas ao template que as gerou
- **Manutenções Individuais**: Não precisam de template de recorrência (são corretivas)

### 5.2 Categorias Disponíveis
- **preventiva**: Manutenções programadas para prevenir falhas
- **corretiva**: Manutenções realizadas após falhas detectadas
- **inspecao**: Manutenções de inspeção e verificação
- **calibracao**: Manutenções de calibração de equipamentos

### 5.3 Frequências Disponíveis
- **diaria**: A cada dia
- **semanal**: A cada semana
- **mensal**: A cada mês
- **anual**: A cada ano

### 5.4 Prioridades Disponíveis
- **critica**: Urgente, impacta segurança
- **alta**: Importante, impacta operação
- **media**: Normal, rotina
- **baixa**: Opcional, melhoria

### 5.5 Status Disponíveis
- **programada**: Agendada para futuro
- **em_andamento**: Em execução
- **concluida**: Finalizada
- **cancelada**: Cancelada
- **atrasada**: Não realizada no prazo

### 5.6 Tipos Disponíveis
- **preventiva**: Manutenção preventiva
- **corretiva**: Manutenção corretiva
- **inspecao**: Manutenção de inspeção
- **calibracao**: Manutenção de calibração

## 6. Sequência de Cadastro Recomendada

### 6.1 Primeira Etapa: Tipos de Manutenção
1. Acesse **Controle > Tipos de Manutenção**
2. Cadastre os 5 tipos de manutenção listados na Seção 1
3. Defina as configurações padrão para cada tipo

### 6.2 Segunda Etapa: Templates de Recorrência
1. Acesse **Controle > Templates de Recorrência**
2. Cadastre os 3 templates listados na Seção 2
3. Vincule cada template ao Tipo de Manutenção correspondente
4. Defina as frequências e períodos de vigência

### 6.3 Terceira Etapa: Manutenções Individuais
1. Acesse **Controle > Manutenções**
2. Cadastre as 2 manutenções individuais listadas na Seção 3
3. Não vincule template de recorrência (são corretivas)

### 6.4 Quarta Etapa: Verificação
1. Verifique se as manutenções recorrentes foram geradas automaticamente
2. Confira o histórico de auditoria
3. Teste os filtros e relatórios

## 7. Campos Opcionais

### 7.1 Manutenções Individuais
- **Fornecedor**: Pode ser vinculado a um fornecedor externo
- **Custo Estimado**: Valor estimado para a manutenção
- **Custo Real**: Valor real após conclusão
- **Observações**: Detalhes adicionais sobre a manutenção
- **Anexos**: Documentos, fotos, orçamentos, etc.

### 7.2 Templates de Recorrência
- **Dia da Semana**: Para frequência semanal
- **Dia do Mês**: Para frequência mensal/anual
- **Mês**: Para frequência anual

## 8. Dados de Teste

Para testar o sistema, utilize os dados acima. Após o cadastro, verifique:

1. **Tipos de Manutenção**: Se aparecem corretamente nos selects
2. **Templates de Recorrência**: Se estão gerando manutenções automaticamente
3. **Manutenções Individuais**: Se podem ser criadas e editadas
4. **Histórico de Auditoria**: Se está sendo registrado automaticamente
5. **Filtros**: Se funcionam por status, prioridade, tipo e datas
6. **Upload de Anexos**: Se está operacional
7. **Relacionamentos**: Se os vínculos entre tipos, templates e manutenções estão corretos

## 9. Fluxo de Trabalho

```
Tipo de Manutenção → Template de Recorrência → Manutenção Gerada
       ↓                    ↓                      ↓
  Configurações      Frequência e Período    Execução Real
  Padrão             Geração Automática      Histórico
```

Este fluxo garante que todas as manutenções preventivas sejam programadas automaticamente com base nos templates, enquanto as manutenções corretivas podem ser criadas individualmente conforme a necessidade.