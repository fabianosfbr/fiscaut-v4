# Guia do Usuário - Sistema de Controle de Manutenções

Este guia prático explica como utilizar o sistema de controle de manutenções do Fiscaut, com exemplos claros e passo-a-passo para diferentes cenários de uso.

## 🎯 **Visão Geral do Sistema**

O sistema de manutenções é composto por 4 entidades principais que trabalham em conjunto:

1. **Tipos de Manutenção** - Definem o que será feito
2. **Templates de Recorrência** - Definem quando será feito (para manutenções periódicas)
3. **Manutenções Individuais** - São as manutenções criadas manualmente ou geradas automaticamente
4. **Histórico de Auditoria** - Registra todas as alterações realizadas

## 📋 **Fluxo de Trabalho**

```
Tipo de Manutenção → Template de Recorrência → Manutenção Gerada
       ↓                    ↓                      ↓
  O Que Fazer        Quando Fazer           Execução Real
```

## 🚀 **Passo-a-Passo: Configurando Manutenções**

### **Passo 1: Criar Tipos de Manutenção**

**Onde acessar:** Controle > Tipos de Manutenção

**Exemplo 1: Limpeza de Caixa D'Água**
- **Nome:** Limpeza de Caixa D'Água
- **Categoria:** preventiva
- **Alerta (dias):** 15 dias antes
- **Prioridade Padrão:** alta
- **Responsável Padrão:** Manutenção Hidráulica

**Exemplo 2: Troca de Lâmpadas**
- **Nome:** Troca de Lâmpadas da Garagem
- **Categoria:** corretiva
- **Alerta (dias):** 0 dias
- **Prioridade Padrão:** baixa
- **Responsável Padrão:** Manutenção Elétrica

### **Passo 2: Criar Templates de Recorrência (Para Manutenções Periódicas)**

**Onde acessar:** Controle > Templates de Recorrência

#### **Caso 1: Manutenção Mensal - Limpeza de Filtros**
- **Tipo de Manutenção:** Limpeza de Filtros
- **Título Template:** Limpeza de Filtros - {data}
- **Frequência:** mensal
- **Intervalo:** 1 (todo mês)
- **Dia do Mês:** 15
- **Data de Início:** 01/01/2024
- **Data de Fim:** 31/12/2025
- **Gerar com Antecedência:** 7 dias

**Resultado:** Será gerada uma manutenção todo dia 15 de cada mês, começando em 15/01/2024.

#### **Caso 2: Manutenção Semanal - Inspeção de Segurança**
- **Tipo de Manutenção:** Inspeção de Segurança
- **Título Template:** Inspeção de Segurança - {data}
- **Frequência:** semanal
- **Intervalo:** 1 (toda semana)
- **Dia da Semana:** 1 (segunda-feira)
- **Data de Início:** 01/01/2024
- **Data de Fim:** 31/12/2024
- **Gerar com Antecedência:** 3 dias

**Resultado:** Será gerada uma manutenção toda segunda-feira, começando na primeira segunda após 01/01/2024.

#### **Caso 3: Manutenção Anual - Recarga de Extintores**
- **Tipo de Manutenção:** Recarga de Extintores
- **Título Template:** Recarga de Extintores - {data}
- **Frequência:** mensal
- **Intervalo:** 12 (todo ano)
- **Dia do Mês:** 1
- **Mês:** 1 (janeiro)
- **Data de Início:** 01/01/2024
- **Data de Fim:** 31/12/2026
- **Gerar com Antecedência:** 30 dias

**Resultado:** Será gerada uma manutenção todo dia 1º de janeiro, nos anos de 2024, 2025 e 2026.

### **Passo 3: Criar Manutenções Individuais (Para Manutenções Corretivas)**

**Onde acessar:** Controle > Manutenções > Criar

#### **Caso 1: Manutenção Corretiva - Lâmpada Queimada**
- **Título:** Troca de Lâmpada Queimada - Garagem
- **Tipo de Manutenção:** Troca de Lâmpadas da Garagem
- **Status:** programada
- **Prioridade:** baixa
- **Data Programada:** 20/03/2024
- **Local:** Garagem
- **Responsável:** Manutenção Elétrica
- **Template de Recorrência:** Não vinculado

#### **Caso 2: Manutenção Preventiva Especial - Pintura de Fachada**
- **Título:** Pintura de Fachada - Edifício Principal
- **Tipo de Manutenção:** Pintura de Fachada
- **Status:** programada
- **Prioridade:** media
- **Data Programada:** 15/04/2024
- **Local:** Fachada Principal
- **Responsável:** Empresa Terceirizada
- **Custo Estimado:** R$ 5.000,00

## 🔄 **Geração Automática de Manutenções**

### **Como Funciona:**
1. **Processamento Automático:** O sistema gera manutenções automaticamente todos os dias às 02:00
2. **Baseado em Templates:** Cada template define quando novas manutenções devem ser criadas
3. **Antecedência:** As manutenções são geradas com antecedência configurada
4. **Validação:** O sistema evita duplicatas e respeita períodos de vigência

### **Exemplo Prático:**
Se hoje é 10/03/2024 e você tem um template de limpeza mensal com:
- **Dia do Mês:** 15
- **Gerar com Antecedência:** 7 dias

**Resultado:** Hoje será gerada a manutenção para 15/03/2024, pois faltam 5 dias (menos que os 7 dias de antecedência configurados).

## 📊 **Cenários e Casos de Uso**

### **Cenário 1: Condomínio Residencial**

**Objetivo:** Controlar manutenções preventivas e corretivas de forma organizada.

**Configuração:**
1. **Tipos de Manutenção:**
   - Limpeza de Caixa D'Água (preventiva)
   - Troca de Lâmpadas (corretiva)
   - Inspeção de Elevadores (preventiva)
   - Manutenção de Portaria (preventiva)

2. **Templates de Recorrência:**
   - Limpeza de Caixa D'Água: semestral (jan e jul)
   - Inspeção de Elevadores: mensal (dia 10)
   - Manutenção de Portaria: mensal (último sábado)

3. **Manutenções Individuais:**
   - Lâmpada queimada no corredor 3
   - Vazamento na torneira do apartamento 101

### **Cenário 2: Empresa Comercial**

**Objetivo:** Manter equipamentos e áreas comuns em pleno funcionamento.

**Configuração:**
1. **Tipos de Manutenção:**
   - Limpeza de Ar-Condicionado (preventiva)
   - Manutenção de Computadores (preventiva)
   - Inspeção de Extintores (preventiva)
   - Manutenção de Copiadora (corretiva)

2. **Templates de Recorrência:**
   - Limpeza de Ar-Condicionado: trimestral
   - Manutenção de Computadores: mensal
   - Inspeção de Extintores: anual

3. **Manutenções Individuais:**
   - Computador com problema no setor financeiro
   - Copiadora com erro de papel

### **Cenário 3: Escola**

**Objetivo:** Garantir segurança e funcionalidade das instalações.

**Configuração:**
1. **Tipos de Manutenção:**
   - Limpeza de Banheiros (preventiva)
   - Manutenção de Quadros (corretiva)
   - Inspeção de Brinquedos (preventiva)
   - Pintura de Salas (preventiva)

2. **Templates de Recorrência:**
   - Limpeza de Banheiros: semanal
   - Inspeção de Brinquedos: mensal
   - Pintura de Salas: anual (janeiro)

3. **Manutenções Individuais:**
   - Quadro quebrado na sala 3
   - Vazamento no banheiro masculino

## 🔍 **Consultas e Relatórios**

### **Filtros Disponíveis:**

#### **Por Status:**
- **Programada:** Manutenções agendadas para o futuro
- **Em Andamento:** Manutenções em execução
- **Concluída:** Manutenções finalizadas
- **Atrasada:** Manutenções não realizadas no prazo
- **Cancelada:** Manutenções canceladas

#### **Por Prioridade:**
- **Crítica:** Urgente, impacta segurança
- **Alta:** Importante, impacta operação
- **Média:** Normal, rotina
- **Baixa:** Opcional, melhoria

#### **Por Tipo:**
- **Preventiva:** Manutenções programadas para prevenir falhas
- **Corretiva:** Manutenções realizadas após falhas detectadas
- **Inspeção:** Manutenções de inspeção e verificação
- **Calibração:** Manutenções de calibração de equipamentos

#### **Por Data:**
- **Data Programada:** Filtrar por período de execução
- **Data de Criação:** Filtrar por período de cadastro
- **Atrasadas:** Mostrar apenas manutenções atrasadas

### **Exemplos de Consultas:**

#### **Consulta 1: Manutenções do Mês**
```
Filtros:
- Data Programada: 01/03/2024 a 31/03/2024
- Status: Programada, Em Andamento, Atrasada
```

#### **Consulta 2: Manutenções Críticas**
```
Filtros:
- Prioridade: Crítica
- Status: Todas (exceto Concluída)
```

#### **Consulta 3: Manutenções por Tipo**
```
Filtros:
- Tipo de Manutenção: Limpeza de Caixa D'Água
- Status: Concluída
- Data de Criação: Últimos 6 meses
```

## 📝 **Dicas e Boas Práticas**

### **1. Organização de Tipos de Manutenção**
- Crie tipos específicos para cada necessidade
- Use descrições claras e detalhadas
- Defina prioridades adequadas
- Estabeleça alertas de antecedência realistas

### **2. Configuração de Templates**
- Defina períodos de vigência realistas
- Use antecedência suficiente para planejamento
- Evite sobreposição de datas
- Revise periodicamente a necessidade dos templates

### **3. Gestão de Manutenções**
- Atualize o status em tempo real
- Registre observações detalhadas
- Anexe documentos quando necessário
- Registre custos reais após conclusão

### **4. Monitoramento**
- Consulte regularmente manutenções atrasadas
- Revise a eficácia das manutenções preventivas
- Ajuste frequências conforme necessidade
- Monitore o histórico de falhas

## 🚨 **Problemas Comuns e Soluções**

### **Problema 1: Manutenções Não São Geradas**
**Causas possíveis:**
- Template inativo
- Data de fim já ultrapassada
- Período de vigência não iniciado
- Erro no cálculo de datas

**Solução:**
- Verifique se o template está ativo
- Confira as datas de início e fim
- Consulte os logs do sistema

### **Problema 2: Manutenções Duplicadas**
**Causas possíveis:**
- Múltiplos templates gerando na mesma data
- Erro no processamento automático
- Configuração incorreta de antecedência

**Solução:**
- O sistema já previne duplicatas
- Verifique templates conflitantes
- Consulte histórico de geração

### **Problema 3: Manutenções Atrasadas**
**Causas possíveis:**
- Falta de recursos para execução
- Prioridade inadequada
- Falta de planejamento

**Solução:**
- Reavalie prioridades
- Planeje recursos adequadamente
- Comunique atrasos e justificativas

## 📞 **Suporte e Ajuda**

### **Comandos CLI (Para Administradores):**
```bash
# Gerar manutenções manualmente
php artisan manutencao:generate-recorrencias

# Forçar geração fora do horário
php artisan manutencao:generate-recorrencias --force

# Verificar logs
tail -f storage/logs/laravel.log | grep manutencao
```

### **Contatos de Suporte:**
- **TI:** suporte@empresa.com
- **Manutenção:** manutencao@empresa.com
- **Administração:** admin@empresa.com

## 📈 **Benefícios do Sistema**

1. **Organização:** Todas as manutenções em um único local
2. **Prevenção:** Manutenções preventivas programadas automaticamente
3. **Controle:** Histórico completo de todas as intervenções
4. **Eficiência:** Redução de falhas e paralisações
5. **Custos:** Melhor planejamento e controle de despesas
6. **Compliance:** Documentação para auditorias e normas

---

**Este guia foi criado para ajudar você a utilizar o sistema de manutenções de forma eficiente. Para dúvidas adicionais, consulte o suporte técnico.**