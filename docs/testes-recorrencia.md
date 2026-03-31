# Testes - Sistema de Recorrência de Manutenções

Este documento contém os procedimentos de teste para validar a implementação do sistema de geração automática de manutenções recorrentes.

## 🧪 **Testes a Serem Realizados**

### **1. Teste Manual do Command CLI**

#### **1.1 Teste Básico**
```bash
# Executar o command manualmente
php artisan manutencao:generate-recorrencias

# Verificar logs
tail -f storage/logs/laravel.log | grep "manutencao:generate-recorrencias"
```

#### **1.2 Teste com Opções**
```bash
# Forçar execução fora do horário comercial
php artisan manutencao:generate-recorrencias --force

# Executar em fila específica
php artisan manutencao:generate-recorrencias --queue=manutencao

# Agendar com delay
php artisan manutencao:generate-recorrencias --delay=30
```

### **2. Teste do Job em Background**

#### **2.1 Verificar Processamento na Fila**
```bash
# Iniciar worker da fila
php artisan queue:work --queue=manutencao --tries=3

# Ou iniciar worker geral
php artisan queue:work --tries=3
```

#### **2.2 Monitorar Jobs**
```bash
# Verificar jobs na fila
php artisan queue:monitor

# Verificar jobs falhados
php artisan queue:failed
```

### **3. Teste de Geração Automática**

#### **3.1 Configurar Templates de Teste**

**Template 1 - Frequência Diária**
- Tipo de Manutenção: Troca de Lâmpadas da Garagem
- Frequência: diaria
- Intervalo: 1
- Data de Início: Hoje
- Data de Fim: +30 dias
- Gerar com Antecedência: 1 dia

**Template 2 - Frequência Semanal**
- Tipo de Manutenção: Limpeza de Caixa de Inspeção
- Frequência: semanal
- Intervalo: 1
- Dia da Semana: 1 (Segunda-feira)
- Data de Início: Hoje
- Data de Fim: +60 dias
- Gerar com Antecedência: 3 dias

**Template 3 - Frequência Mensal**
- Tipo de Manutenção: Recarga de Extintores
- Frequência: mensal
- Intervalo: 1
- Dia do Mês: 15
- Data de Início: Hoje
- Data de Fim: +12 meses
- Gerar com Antecedência: 7 dias

### **4. Teste de Validação de Dados**

#### **4.1 Verificar Manutenções Geradas**
```sql
-- Consultar manutenções geradas por recorrência
SELECT 
    m.id,
    m.titulo,
    m.data_programada,
    m.status,
    m.recorrencia_id,
    r.titulo_template
FROM manutencoes m
JOIN manutencao_recorrencias r ON m.recorrencia_id = r.id
WHERE m.recorrencia_id IS NOT NULL
ORDER BY m.data_programada DESC;
```

#### **4.2 Verificar Duplicatas**
```sql
-- Verificar manutenções duplicadas
SELECT 
    recorrencia_id,
    data_programada,
    COUNT(*) as quantidade
FROM manutencoes
WHERE recorrencia_id IS NOT NULL
GROUP BY recorrencia_id, data_programada
HAVING COUNT(*) > 1;
```

### **5. Teste de Edge Cases**

#### **5.1 Recorrência com Data de Fim**
- Criar recorrência com data de fim próxima
- Verificar se para de gerar após a data de fim

#### **5.2 Recorrência Inativa**
- Criar recorrência inativa
- Verificar se não gera manutenções

#### **5.3 Recorrência sem Data de Fim**
- Criar recorrência sem data de fim
- Verificar geração contínua

#### **5.4 Conflito de Datas**
- Criar duas recorrências que gerariam na mesma data
- Verificar se o sistema evita duplicatas

### **6. Teste de Performance**

#### **6.1 Múltiplas Recorrências**
- Criar 50+ recorrências ativas
- Executar o command e medir tempo de processamento
- Verificar consumo de memória

#### **6.2 Batch Processing**
- Testar com grande volume de recorrências
- Verificar se o sistema lida bem com carga

### **7. Teste de Logs**

#### **7.1 Logs de Sucesso**
```bash
# Verificar logs de geração bem-sucedida
grep "Manutenção gerada com sucesso" storage/logs/laravel.log
```

#### **7.2 Logs de Erro**
```bash
# Verificar logs de erro
grep "Erro ao processar recorrência" storage/logs/laravel.log
```

#### **7.3 Logs de Comando**
```bash
# Verificar execução do command
grep "Command manutencao:generate-recorrencias executado" storage/logs/laravel.log
```

### **8. Teste de Schedule**

#### **8.1 Verificar Configuração**
```bash
# Listar comandos agendados
php artisan schedule:list
```

#### **8.2 Teste de Execução Automática**
- Aguardar a execução automática às 02:00
- Verificar logs da execução
- Confirmar geração de manutenções

### **9. Teste de Integração**

#### **9.1 Integração com Filament**
- Verificar se as manutenções aparecem corretamente no painel
- Testar filtros e buscas
- Verificar histórico de auditoria

#### **9.2 Integração com Multi-Tenancy**
- Testar com diferentes Issuers
- Verificar isolamento de dados

## 📋 **Checklist de Testes**

- [ ] Command CLI funciona corretamente
- [ ] Job processa corretamente na fila
- [ ] Geração automática via Schedule funciona
- [ ] Templates de recorrência geram manutenções corretas
- [ ] Não há duplicatas de manutenções
- [ ] Validação de datas e períodos funciona
- [ ] Logs são gerados corretamente
- [ ] Performance é aceitável
- [ ] Integração com Filament funciona
- [ ] Multi-tenancy é respeitado

## 🔧 **Comandos Úteis para Testes**

```bash
# Limpar dados de teste
php artisan tinker
>>> Manutencao::whereNotNull('recorrencia_id')->delete();
>>> ManutencaoRecorrencia::where('titulo_template', 'like', '%TESTE%')->delete();

# Resetar jobs falhados
php artisan queue:flush
php artisan queue:failed:flush

# Limpar logs
> storage/logs/laravel.log

# Verificar status da aplicação
php artisan down
php artisan up
```

## 🚨 **Pontos de Atenção**

1. **Horário de Execução**: O schedule está configurado para executar às 02:00
2. **Fila**: Recomenda-se usar fila específica para manutenções
3. **Logs**: Monitorar logs para identificar problemas
4. **Performance**: Testar com volume real de dados
5. **Backup**: Sempre fazer backup antes de testes em produção

## 📊 **Métricas de Sucesso**

- **Tempo de Processamento**: < 30 segundos para 100 recorrências
- **Taxa de Sucesso**: > 95% das recorrências processadas com sucesso
- **Memória**: Uso < 100MB durante processamento
- **Duplicatas**: Zero manutenções duplicadas geradas
- **Logs**: 100% das operações registradas nos logs