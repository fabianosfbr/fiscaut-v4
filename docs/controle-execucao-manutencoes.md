# Controle de Execução de Manutenções

Este documento explica como controlar o status das manutenções, registrar quem executou o serviço e acompanhar todo o histórico de alterações.

## 🎯 **Objetivo**

Controlar o ciclo de vida completo de uma manutenção, desde a programação até a conclusão, registrando:
- Alterações de status
- Responsável pela execução
- Usuário que alterou o status
- Histórico completo de todas as alterações

## 📋 **Fluxo de Controle de Execução**

```
Programada → Em Andamento → Concluída
    ↓           ↓            ↓
Data Agendada  Início       Finalização
              Execução     Documentação
```

## 🔄 **Alterações de Status**

### **Status Disponíveis:**
1. **Programada** - Manutenção agendada para futuro
2. **Em Andamento** - Manutenção em execução
3. **Concluída** - Manutenção finalizada com sucesso
4. **Atrasada** - Manutenção não realizada no prazo
5. **Cancelada** - Manutenção cancelada

### **Campos para Controle de Execução:**

#### **Campos Principais:**
- **Status** - Define o estado atual da manutenção
- **Data Início** - Quando a execução começou
- **Data Conclusão** - Quando a execução terminou
- **Responsável Execução** - Quem executou o serviço
- **Usuário Alteração** - Quem alterou o status

#### **Campos Complementares:**
- **Observações** - Detalhes da execução
- **Custo Real** - Valor efetivamente gasto
- **Anexos** - Fotos, notas fiscais, relatórios

## 📝 **Exemplo Prático - Limpeza de Filtros**

### **Cenário:**
- **Manutenção:** Limpeza de Filtros - 15/03/2024
- **Data Programada:** 15/03/2024
- **Prestador:** Empresa de Limpeza ABC
- **Execução:** 16/03/2024 (com atraso de 1 dia)

### **Passo-a-Passo de Controle:**

#### **Passo 1: Manutenção Programada (15/03/2024)**
```
Status: Programada
Data Programada: 15/03/2024
Responsável: Manutenção Predial
Observações: Agendado com empresa ABC para limpeza preventiva
```

#### **Passo 2: Manutenção Em Andamento (16/03/2024)**
```
Status: Em Andamento
Data Início: 16/03/2024 08:00
Responsável Execução: João Silva (Empresa ABC)
Usuário Alteração: Maria Santos (Supervisora)
Observações: Início da limpeza, empresa ABC no local
```

#### **Passo 3: Manutenção Concluída (16/03/2024)**
```
Status: Concluída
Data Conclusão: 16/03/2024 10:30
Responsável Execução: João Silva (Empresa ABC)
Usuário Alteração: Maria Santos (Supervisora)
Custo Real: R$ 250,00
Observações: Limpeza concluída com sucesso, filtros limpos e secos
Anexos: Fotos antes/depois, Nota Fiscal 12345
```

## 🖥️ **Interface de Controle no Sistema**

### **Formulário de Edição de Manutenção:**

#### **Seção 1: Dados Principais**
```
Título: Limpeza de Filtros - 15/03/2024
Tipo de Manutenção: Limpeza de Filtros
Status: [Dropdown] Programada → Em Andamento → Concluída
Prioridade: Média
Data Programada: 15/03/2024
```

#### **Seção 2: Controle de Execução**
```
Data Início: [Data/Hora] (preenchido quando status = Em Andamento)
Data Conclusão: [Data/Hora] (preenchido quando status = Concluída)
Responsável Execução: [Texto] João Silva (Empresa ABC)
Usuário Alteração: [Automático] Maria Santos
Custo Real: [Valor] R$ 250,00
```

#### **Seção 3: Observações e Documentos**
```
Observações: [Textarea] Detalhes da execução
Anexos: [Upload] Fotos, documentos, notas fiscais
```

## 📊 **Relatórios de Controle**

### **Relatório 1: Manutenções por Status**
```
Filtros:
- Status: Concluída
- Período: 01/03/2024 a 31/03/2024
- Responsável Execução: João Silva

Resultados:
- Total de manutenções: 15
- Valor total: R$ 3.200,00
- Tempo médio de execução: 2,5 horas
```

### **Relatório 2: Histórico de Alterações**
```
Manutenção: Limpeza de Filtros - 15/03/2024

Histórico:
1. 15/03/2024 09:00 - Status: Programada → Em Andamento
   Usuário: Maria Santos
   Observação: Início da execução

2. 16/03/2024 10:30 - Status: Em Andamento → Concluída
   Usuário: Maria Santos
   Observação: Execução finalizada com sucesso
```

### **Relatório 3: Eficiência de Execução**
```
Indicadores:
- Manutenções concluídas no prazo: 85%
- Manutenções atrasadas: 15%
- Tempo médio de execução: 3,2 horas
- Custo médio por manutenção: R$ 180,00
```

## 🔍 **Consultas Específicas**

### **Consulta 1: Manutenções em Execução**
```
Filtros:
- Status: Em Andamento
- Data Início: Últimas 24 horas

Resultados:
- Manutenção: Pintura de Fachada
- Responsável Execução: Carlos Oliveira
- Início: 16/03/2024 07:00
- Tempo decorrido: 15 horas
```

### **Consulta 2: Manutenções Concluídas por Prestador**
```
Filtros:
- Status: Concluída
- Responsável Execução: Empresa ABC
- Período: Últimos 30 dias

Resultados:
- Total: 8 manutenções
- Valor total: R$ 1.800,00
- Avaliação média: 4,5/5
```

### **Consulta 3: Manutenções Atrasadas**
```
Filtros:
- Status: Atrasada
- Data Programada: Antes de hoje

Resultados:
- Manutenção: Inspeção de Elevadores
- Data Programada: 10/03/2024
- Atraso: 6 dias
- Motivo: Falta de peças
```

## 📝 **Procedimentos Operacionais**

### **Procedimento 1: Iniciar Execução**
1. **Verifique a manutenção** no painel de controle
2. **Altere o status** para "Em Andamento"
3. **Preencha a data de início** (data/hora atual)
4. **Registre o responsável** pela execução
5. **Salve as alterações**

### **Procedimento 2: Concluir Execução**
1. **Verifique a conclusão** da manutenção
2. **Altere o status** para "Concluída"
3. **Preencha a data de conclusão**
4. **Registre o custo real** (se houver)
5. **Adicione observações** sobre a execução
6. **Anexe documentos** (fotos, notas fiscais)
7. **Salve as alterações**

### **Procedimento 3: Cancelar Manutenção**
1. **Verifique o motivo** do cancelamento
2. **Altere o status** para "Cancelada"
3. **Registre o motivo** no campo de observações
4. **Indique quem autorizou** o cancelamento
5. **Salve as alterações**

## 🚨 **Regras de Negócio**

### **Regras de Alteração de Status:**
1. **Programada → Em Andamento:** Pode ser alterada a qualquer momento
2. **Em Andamento → Concluída:** Só pode ser alterada se houver data de início
3. **Concluída:** Não pode ser alterada (apenas visualização)
4. **Qualquer status → Cancelada:** Pode ser cancelada a qualquer momento

### **Regras de Preenchimento:**
1. **Data de início** só pode ser preenchida quando status = "Em Andamento"
2. **Data de conclusão** só pode ser preenchida quando status = "Concluída"
3. **Custo real** só pode ser preenchido quando status = "Concluída"
4. **Responsável execução** deve ser preenchido quando status = "Em Andamento" ou "Concluída"

### **Validações:**
1. **Data de conclusão** não pode ser anterior à data de início
2. **Data de início** não pode ser anterior à data programada
3. **Custo real** não pode ser negativo
4. **Observações** são obrigatórias para status "Cancelada"

## 📈 **Indicadores de Performance**

### **Indicadores Operacionais:**
- **Taxa de conclusão no prazo:** (Manutenções concluídas no prazo / Total programado) × 100
- **Tempo médio de execução:** Soma dos tempos de execução / Total de manutenções concluídas
- **Custo médio por manutenção:** Soma dos custos reais / Total de manutenções concluídas
- **Índice de atraso:** (Manutenções atrasadas / Total programado) × 100

### **Indicadores de Qualidade:**
- **Satisfação do cliente:** Avaliação média das manutenções concluídas
- **Reincidência de falhas:** Manutenções corretivas no mesmo equipamento em 30 dias
- **Eficiência de recursos:** Horas trabalhadas / Valor investido

## 📞 **Dicas Operacionais**

### **Para Supervisores:**
1. **Atualize o status** em tempo real
2. **Registre observações** detalhadas
3. **Anexe documentação** completa
4. **Comunique atrasos** imediatamente

### **Para Executores:**
1. **Confirme o início** da execução
2. **Comunique eventuais problemas**
3. **Registre o término** imediatamente após conclusão
4. **Forneça feedback** sobre a execução

### **Para Administradores:**
1. **Monitore indicadores** regularmente
2. **Revise processos** mensalmente
3. **Capacite a equipe** continuamente
4. **Ajuste planejamento** conforme necessidade

---

**Este controle garante transparência, responsabilidade e eficiência na gestão de manutenções.**