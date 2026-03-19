# Execução da Seed de Manutenções

Este documento explica como executar a seed de manutenções para gerar dados de teste reais no sistema.

## 🎯 **Objetivo**

Gerar dados de teste reais para validar o funcionamento completo do sistema de manutenções, incluindo:
- Tipos de Manutenção
- Templates de Recorrência
- Manutenções Individuais
- Histórico de Alterações de Status

## 📋 **Comandos para Execução**

### **1. Executar a Seed Específica**

#### **Comando Básico:**
```bash
php artisan db:seed --class=ManutencaoSeeder
```

#### **Comando com Sail (Docker):**
```bash
./vendor/bin/sail artisan db:seed --class=ManutencaoSeeder
```

#### **Comando com Sail (Docker) - Modo Detached:**
```bash
./vendor/bin/sail exec -T php artisan db:seed --class=ManutencaoSeeder
```

### **2. Executar Todas as Seeds**

#### **Comando Básico:**
```bash
php artisan db:seed
```

#### **Comando com Sail (Docker):**
```bash
./vendor/bin/sail artisan db:seed
```

### **3. Executar com Refresh (Limpar e Popular)**
```bash
# Limpar banco e executar todas as seeds
php artisan migrate:fresh --seed

# Com Sail
./vendor/bin/sail artisan migrate:fresh --seed
```

### **4. Executar Apenas a Seed de Manutenções (Forçar)**
```bash
# Forçar execução mesmo em produção
php artisan db:seed --class=ManutencaoSeeder --force

# Com Sail
./vendor/bin/sail artisan db:seed --class=ManutencaoSeeder --force
```

## 📊 **Dados Gerados**

### **Tipos de Manutenção (4 registros):**
1. **Limpeza de Caixa D'Água** - Preventiva, alta prioridade
2. **Troca de Lâmpadas da Garagem** - Corretiva, baixa prioridade
3. **Inspeção de Segurança** - Inspeção, alta prioridade
4. **Pintura de Fachada** - Preventiva, média prioridade

### **Templates de Recorrência (2 registros):**
1. **Limpeza de Caixa D'Água** - Mensal, dia 15, antecedência 7 dias
2. **Inspeção de Segurança** - Semanal, segunda-feira, antecedência 3 dias

### **Manutenções Individuais (4 registros):**
1. **Limpeza de Caixa D'Água - 15/03/2024** - Concluída
2. **Troca de Lâmpadas Queimadas - Garagem** - Em Andamento
3. **Pintura de Fachada - Edifício Principal** - Programada
4. **Inspeção de Segurança - 18/03/2024** - Atrasada

### **Histórico de Alterações (6 registros):**
- Alterações de status para manutenções concluídas
- Alterações de status para manutenções em andamento
- Alterações de status para manutenções atrasadas
- Registro de custos reais

## 🔍 **Validação dos Dados**

### **Consultar Tipos de Manutenção:**
```sql
SELECT * FROM tipo_manutencaos WHERE issuer_id = 60;
```

### **Consultar Templates de Recorrência:**
```sql
SELECT * FROM manutencao_recorrencias WHERE issuer_id = 60;
```

### **Consultar Manutenções:**
```sql
SELECT * FROM manutencoes WHERE issuer_id = 60 ORDER BY data_programada;
```

### **Consultar Histórico de Alterações:**
```sql
SELECT 
    mh.*,
    m.titulo as manutencao_titulo
FROM manutencao_historicos mh
JOIN manutencoes m ON mh.manutencao_id = m.id
WHERE m.issuer_id = 60
ORDER BY mh.created_at;
```

## 📈 **Fluxo de Status Gerado**

### **Manutenção 1: Limpeza de Caixa D'Água**
```
Data Base: 10/03/2024 (10 dias atrás)
1. 10/03/2024: Programada
2. 12/03/2024: Em Andamento (2 dias depois)
3. 14/03/2024: Concluída (2 dias depois)
   - Responsável: João Silva (Empresa de Limpeza ABC)
   - Custo Real: R$ 250,00
```

### **Manutenção 2: Troca de Lâmpadas**
```
Data Base: 12/03/2024 (8 dias atrás)
1. 12/03/2024: Programada
2. 14/03/2024: Em Andamento (2 dias depois)
   - Responsável: Carlos Oliveira (Manutenção Elétrica)
```

### **Manutenção 3: Pintura de Fachada**
```
Data Base: 16/03/2024 (4 dias atrás)
1. 16/03/2024: Programada
   - Agendado com empresa terceirizada
```

### **Manutenção 4: Inspeção de Segurança**
```
Data Base: 08/03/2024 (12 dias atrás)
1. 08/03/2024: Programada
2. 18/03/2024: Atrasada (passou da data programada)
   - Motivo: Falta de equipe disponível
```

## 🚨 **Observações Importantes**

### **Pré-requisitos:**
1. **Banco de Dados:** Deve estar configurado e acessível
2. **Migrations:** Devem estar executadas
3. **Enums:** Devem estar disponíveis no sistema
4. **Models:** Devem estar criados e configurados

### **Dependências:**
- `App\Enums\ManutencaoCategoriaEnum`
- `App\Enums\ManutencaoFrequenciaEnum`
- `App\Enums\ManutencaoPrioridadeEnum`
- `App\Enums\ManutencaoStatusEnum`
- `App\Models\TipoManutencao`
- `App\Models\ManutencaoRecorrencia`
- `App\Models\Manutencao`
- `App\Models\ManutencaoHistorico`

### **Configurações:**
- **Issuer ID:** 60 (fixo na seed)
- **Usuário Admin:** ID 1 (para histórico de alterações)
- **Datas:** Calculadas a partir de "hoje - 10 dias"

## 📞 **Comandos Úteis para Debug**

### **Verificar Conexão com Banco:**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### **Listar Seeds Disponíveis:**
```bash
php artisan db:seed --list
```

### **Verificar Logs:**
```bash
tail -f storage/logs/laravel.log | grep "ManutencaoSeeder"
```

### **Resetar Tabelas de Manutenção:**
```bash
# Em tinker
>>> DB::table('manutencao_historicos')->whereHas('manutencao', function($q) { $q->where('issuer_id', 60); })->delete();
>>> DB::table('manutencoes')->where('issuer_id', 60)->delete();
>>> DB::table('manutencao_recorrencias')->where('issuer_id', 60)->delete();
>>> DB::table('tipo_manutencaos')->where('issuer_id', 60)->delete();
```

## 🎉 **Resultado Esperado**

Após executar a seed, você terá:

1. **4 Tipos de Manutenção** cadastrados
2. **2 Templates de Recorrência** configurados
3. **4 Manutenções Individuais** criadas com diferentes status
4. **6 Registros de Histórico** mostrando alterações de status
5. **Dados Realistas** para testar todo o fluxo do sistema

**Pronto para testar o sistema de manutenções com dados reais!**