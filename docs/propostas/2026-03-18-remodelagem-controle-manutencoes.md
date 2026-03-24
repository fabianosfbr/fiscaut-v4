# Proposta de Remodelagem do Controle de Manutenções Programadas

**Data:** 18 de Março de 2026  
**Status:** Proposta  
**Prioridade:** Alta  

---

## 1. Visão Geral e Contexto Atual

### 1.1 Situação Atual

O módulo de **Manutenções Programadas** atualmente utiliza:

- **Modelo de dados:** `IssuerControl` com estrutura JSON flexível (`value` column)
- **Tipo de controle:** `ControlTypeEnum::MANUTENCAO_PROGRAMADA`
- **Tipos de manutenção:** Model `TipoManutencao` (tenant-specific)
- **Campos armazenados:**
  - `tipo_manutencao` (string)
  - `data_realizacao` (date)
  - `data_vencimento` (date)
  - `document_path` (PDF attachments)

### 1.2 Limitações Identificadas

1. **Falta de estrutura para recorrência:** Não há controle de periodicidade ou calendarização automática
2. **Ausência de status:** Não é possível trackear se a manutenção está "pendente", "realizada", "atrasada", "vencida"
3. **Sem histórico de execuções:** Cada registro é estático, não há vínculo entre manutenções recorrentes
4. **Informações limitadas:** Não há campos para fornecedor, custo, responsável, observações técnicas
5. **Sem alertas/notificações:** Não existe sistema de lembrete para vencimentos
6. **Validação fraca:** Datas em formato de texto (mask) ao invés de date pickers
7. **Sem integração com facilities:** Estrutura atual não prepara para futura gestão de fornecedores e cotações

---

## 2. Arquitetura Proposta

### 2.1 Nova Estrutura de Banco de Dados

#### 2.1.1 Tabela: `maintenance_plans` (Planos de Manutenção)

Armazena a **definição** das manutenções preventivas com suas recorrências.

```php
Schema::create('maintenance_plans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('issuer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tipo_manutencao_id')->constrained()->cascadeOnDelete();
    $table->string('title'); // Ex: "Manutenção Preventiva - Ar Condicionado"
    $table->text('description')->nullable();
    $table->enum('priority', ['baixa', 'media', 'alta', 'critica'])->default('media');
    
    // Recorrência
    $table->enum('recurrence_type', ['diaria', 'semanal', 'quinzenal', 'mensal', 'bimestral', 'trimestral', 'semestral', 'anual', 'personalizada']);
    $table->integer('recurrence_interval')->nullable(); // Ex: 2 (a cada 2 semanas)
    $table->json('recurrence_config')->nullable(); // Config avançada (dia da semana, mês, etc)
    
    // Datas base
    $table->date('start_date'); // Quando inicia o plano
    $table->date('end_date')->nullable(); // Quando termina (opcional)
    
    // Responsáveis e fornecedores
    $table->foreignId('responsible_user_id')->nullable()->constrained('users');
    $table->string('responsible_contact')->nullable(); // Nome/contato externo
    $table->foreignId('supplier_id')->nullable()->constrained('suppliers'); // Futura integração
    
    // Custos
    $table->decimal('estimated_cost', 12, 2)->nullable();
    $table->enum('cost_type', ['fixo', 'variavel', 'por_evento'])->default('fixo');
    
    // Status do plano
    $table->enum('status', ['ativo', 'pausado', 'cancelado', 'concluido'])->default('ativo');
    
    // Anexos do plano (modelos, manuais, especificações)
    $table->json('attachments')->nullable();
    
    // Checklist/Instruções
    $table->json('checklist_items')->nullable(); // Itens a verificar em cada execução
    $table->text('technical_instructions')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['issuer_id', 'status']);
    $table->index(['issuer_id', 'recurrence_type']);
});
```

#### 2.1.2 Tabela: `maintenance_executions` (Execuções de Manutenção)

Armazena cada **ocorrência** real de uma manutenção (preventiva ou corretiva).

```php
Schema::create('maintenance_executions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('issuer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('maintenance_plan_id')->nullable()->constrained()->cascadeOnDelete();
    $table->foreignId('tipo_manutencao_id')->constrained()->cascadeOnDelete();
    
    // Tipo de ocorrência
    $table->enum('execution_type', ['preventiva', 'corretiva', 'preditiva', 'emergencial']);
    
    // Identificação
    $table->string('title');
    $table->text('description')->nullable();
    
    // Datas
    $table->date('scheduled_date'); // Data programada
    $table->date('realized_date')->nullable(); // Data real de execução
    $table->date('due_date'); // Data de vencimento (validade da manutenção)
    $table->timestamp('completed_at')->nullable();
    
    // Status
    $table->enum('status', ['pendente', 'agendada', 'em_andamento', 'concluida', 'atrasada', 'cancelada', 'recusada']);
    $table->string('status_reason')->nullable(); // Justificativa (cancelamento, recusa, etc)
    
    // Responsáveis
    $table->foreignId('assigned_user_id')->nullable()->constrained('users');
    $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
    $table->string('supplier_contact')->nullable(); // Nome técnico/contato
    $table->string('supplier_phone')->nullable();
    $table->string('supplier_email')->nullable();
    
    // Custos reais
    $table->decimal('real_cost', 12, 2)->nullable();
    $table->text('cost_breakdown')->nullable(); // Detalhamento (mão de obra, peças, etc)
    
    // Resultado
    $table->text('observations')->nullable();
    $table->text('technical_report')->nullable(); // Laudo técnico
    $table->json('checklist_results')->nullable(); // Resultado de cada item do checklist
    $table->enum('outcome', ['sucesso', 'parcial', 'falha', 'pendente'])->nullable();
    $table->text('follow_up_required')->nullable(); // Ações necessárias após manutenção
    
    // Documentos e anexos
    $table->json('documents')->nullable(); // Laudos, fotos, relatórios em PDF
    $table->json('photos')->nullable(); // Fotos antes/depois
    
    // Origem
    $table->enum('origin', ['plano', 'solicitacao', 'emergencia', 'inspecao'])->default('plano');
    $table->foreignId('maintenance_request_id')->nullable()->constrained('maintenance_requests'); // Link com solicitação
    
    // Metadados
    $table->integer('delay_days')->default(0); // Dias de atraso
    $table->boolean('is_urgent')->default(false);
    $table->json('metadata')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['issuer_id', 'status']);
    $table->index(['issuer_id', 'scheduled_date']);
    $table->index(['maintenance_plan_id', 'status']);
    $table->index(['status', 'scheduled_date']); // Para dashboard de pendências
});
```

#### 2.1.3 Tabela: `maintenance_requests` (Solicitações de Manutenção)

Permite que usuários solicitem manutenções corretivas.

```php
Schema::create('maintenance_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('issuer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tipo_manutencao_id')->constrained()->cascadeOnDelete();
    $table->foreignId('requester_user_id')->constrained('users');
    
    $table->string('title');
    $table->text('description');
    $table->text('location')->nullable(); // Local específico (ex: "2º andar, sala 201")
    $table->enum('urgency', ['baixa', 'media', 'alta', 'emergencial']);
    $table->enum('status', ['aberta', 'em_analise', 'aprovada', 'rejeitada', 'em_execucao', 'concluida', 'cancelada'])->default('aberta');
    
    $table->date('request_date');
    $table->date('expected_date')->nullable();
    $table->date('completed_date')->nullable();
    
    $table->foreignId('assigned_execution_id')->nullable()->constrained('maintenance_executions');
    $table->text('analysis_notes')->nullable();
    $table->text('rejection_reason')->nullable();
    
    $table->json('attachments')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['issuer_id', 'status']);
});
```

#### 2.1.4 Tabela: `maintenance_notifications` (Notificações e Alertas)

```php
Schema::create('maintenance_notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('issuer_id')->constrained()->cascadeOnDelete();
    $table->foreignId('maintenance_execution_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    
    $table->enum('type', ['lembrete', 'vencimento', 'atraso', 'atribuicao', 'conclusao']);
    $table->string('subject');
    $table->text('message');
    
    $table->timestamp('scheduled_at'); // Quando deve ser enviada
    $table->timestamp('sent_at')->nullable();
    $table->timestamp('read_at')->nullable();
    
    $table->enum('channel', ['email', 'push', 'in_app', 'whatsapp'])->default('email');
    $table->enum('status', ['pendente', 'enviada', 'entregue', 'lida', 'falha'])->default('pendente');
    
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
    $table->index(['scheduled_at', 'status']);
});
```

### 2.2 Models Eloquent

#### 2.2.1 `MaintenancePlan`

```php
class MaintenancePlan extends Model
{
    protected $guarded = ['id'];
    
    protected $casts = [
        'recurrence_config' => 'array',
        'checklist_items' => 'array',
        'attachments' => 'array',
        'estimated_cost' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'deleted_at' => 'datetime',
    ];
    
    // Relacionamentos
    public function issuer(): BelongsTo
    public function tipoManutencao(): BelongsTo
    public function responsibleUser(): BelongsTo
    public function supplier(): BelongsTo
    public function executions(): HasMany
    
    // Scopes
    public function scopeActive($query)
    public function scopeDueThisMonth($query)
    public function scopeOverdue($query)
    
    // Métodos auxiliares
    public function getNextExecutionDate(): Carbon
    public function generateExecutionForPeriod(Carbon $period): MaintenanceExecution
    public function isDue(): bool
}
```

#### 2.2.2 `MaintenanceExecution`

```php
class MaintenanceExecution extends Model
{
    protected $guarded = ['id'];
    
    protected $casts = [
        'checklist_results' => 'array',
        'documents' => 'array',
        'photos' => 'array',
        'metadata' => 'array',
        'real_cost' => 'decimal:2',
        'scheduled_date' => 'date',
        'realized_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'is_urgent' => 'boolean',
        'deleted_at' => 'datetime',
    ];
    
    // Relacionamentos
    public function issuer(): BelongsTo
    public function plan(): BelongsTo
    public function tipoManutencao(): BelongsTo
    public function assignedUser(): BelongsTo
    public function supplier(): BelongsTo
    public function request(): BelongsTo
    public function notifications(): HasMany
    
    // Scopes
    public function scopePending($query)
    public function scopeOverdue($query)
    public function scopeDueThisWeek($query)
    public function scopeDueThisMonth($query)
    public function scopeCompleted($query)
    
    // Métodos auxiliares
    public function isOverdue(): bool
    public function getDelayDays(): int
    public function markAsCompleted(array $data): void
    public function attachDocument($file, string $type): void
}
```

#### 2.2.3 `MaintenanceRequest`

```php
class MaintenanceRequest extends Model
{
    protected $guarded = ['id'];
    
    protected $casts = [
        'attachments' => 'array',
        'request_date' => 'date',
        'expected_date' => 'date',
        'completed_date' => 'date',
        'deleted_at' => 'datetime',
    ];
    
    // Relacionamentos
    public function issuer(): BelongsTo
    public function tipoManutencao(): BelongsTo
    public function requester(): BelongsTo
    public function execution(): BelongsTo
    
    // Métodos
    public function approve(): void
    public function reject(string $reason): void
    public function convertToExecution(): MaintenanceExecution
}
```

---

## 3. Funcionalidades Propostas

### 3.1 Gestão de Planos de Manutenção

#### 3.1.1 CRUD de Planos
- **Criar plano:** Definir tipo, recorrência, responsáveis, custos estimados, checklist
- **Editar plano:** Atualizar configurações (com versionamento opcional)
- **Pausar/Retomar plano:** Suspender execuções temporariamente
- **Cancelar plano:** Encerrar com justificativa
- **Duplicar plano:** Copiar configuração para criar novo plano similar

#### 3.1.2 Tipos de Recorrência

| Tipo | Configuração | Exemplo |
|------|-------------|---------|
| Diária | Intervalo: 1, 2, 3... | A cada 3 dias |
| Semanal | Dia da semana | Toda segunda-feira |
| Quinzenal | Dias (1º/15º) | Dias 1 e 15 |
| Mensal | Dia do mês | Todo dia 10 |
| Bimestral | Mês base + intervalo | A cada 2 meses |
| Trimestral | Mês base | Jan, Abr, Jul, Out |
| Semestral | Mês base | Jan e Jul |
| Anual | Mês e dia | Todo 15 de Março |
| Personalizada | Regra customizada | Última sexta do mês |

### 3.2 Gestão de Execuções

#### 3.2.1 Ciclo de Vida

```
[PENDENTE] → [AGENDADA] → [EM ANDAMENTO] → [CONCLUÍDA]
     ↓            ↓              ↓
 [ATRASADA]  [RECUSADA]    [CANCELADA]
```

#### 3.2.2 Funcionalidades

- **Agendamento:** Selecionar data/hora, atribuir responsável
- **Check-in/Check-out:** Registrar início e término da execução
- **Checklist interativo:** Marcar itens conforme execução
- **Upload de documentos:** Laudos, fotos, relatórios
- **Registro de custos:** Mão de obra, peças, serviços terceiros
- **Laudo técnico:** Campo estruturado para relatório
- **Acompanhamento:** Ações pendentes pós-manutenção

### 3.3 Solicitações de Manutenção

- **Abertura de chamados:** Qualquer usuário pode solicitar
- **Classificação:** Urgência, tipo, local
- **Anexos:** Fotos, documentos
- **Fluxo de aprovação:** Análise antes de converter em execução
- **Acompanhamento:** Status visível para solicitante

### 3.4 Notificações e Alertas

#### 3.4.1 Gatilhos

| Evento | Antecedência | Canal |
|--------|-------------|-------|
| Manutenção programada | 7 dias | Email |
| Manutenção programada | 2 dias | Email + Push |
| Manutenção vencendo hoje | 0 dias | Email + Push + WhatsApp |
| Manutenção atrasada | +1 dia | Email + Push |
| Atribuição de tarefa | Imediato | Email + Push |
| Conclusão | Imediato | Email |

#### 3.4.2 Configurações

- Preferências por usuário (canais, frequência)
- Escalonamento para gestores (atrasos críticos)
- Notificações para fornecedores (opcional)

### 3.5 Dashboard e Relatórios

#### 3.5.1 Dashboard Principal

```
┌─────────────────────────────────────────────────────────┐
│  RESUMO DO MÊS                                          │
├─────────────────────────────────────────────────────────┤
│  📊 24  │  ✅ 18  │  ⏳ 4  │  ⚠️ 2  │  📅 6            │
│  Total  │ Realiz. │  Pend. │ Atras. │ Próx.           │
└─────────────────────────────────────────────────────────┘

┌─────────────────┐  ┌─────────────────┐
│  STATUS POR     │  │  CUSTOS DO MÊS  │
│  TIPO           │  │  R$ 12.450,00   │
│  ■ Preventiva   │  │  vs. R$ 15.000  │
│  ■ Corretiva    │  │  (estimado)     │
└─────────────────┘  └─────────────────┘

┌─────────────────────────────────────────────────────────┐
│  PRÓXIMAS MANUTENÇÕES (7 dias)                          │
├─────────────────────────────────────────────────────────┤
│  19/03 - Ar Condicionado (Alta) - Fornecedor: XYZ      │
│  20/03 - Extintores (Média) - Resp: João               │
│  21/03 - Elevador (Crítica) - Fornecedor: ABC          │
└─────────────────────────────────────────────────────────┘
```

#### 3.5.2 Relatórios

1. **Execução por período:** Todas as manutenções realizadas
2. **Custos por tipo/centro de custo:** Análise financeira
3. **Atrasos e justificativas:** Compliance
4. **Performance de fornecedores:** SLA, qualidade
5. **Histórico por equipamento:** Lifecycle
6. **Preventiva vs. Corretiva:** Ratio de eficiência

---

## 4. Integração com Facilities (Futuro)

### 4.1 Modelo de Dados Preparado

```php
// Tabela: suppliers (já preparada)
Schema::create('suppliers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained();
    $table->string('name');
    $table->string('cnpj')->nullable();
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->json('services')->nullable(); // Serviços prestados
    $table->enum('status', ['ativo', 'inativo', 'suspenso'])->default('ativo');
    $table->decimal('rating', 3, 2)->nullable(); // Avaliação
    $table->timestamps();
});
```

### 4.2 Fluxo de Cotações

```
[Plano de Manutenção]
        ↓
[Solicitar Cotação] → [Fornecedores]
        ↓
[Receber Propostas] → [Comparar]
        ↓
[Aprovar] → [Gerar Ordem de Serviço]
        ↓
[Agendar Execução]
```

### 4.3 Ordem de Serviço (OS)

Futura tabela `work_orders`:
- Vínculo com execução
- Dados do fornecedor
- Valores aprovados
- Termo de aceite
- Faturamento

---

## 5. Migração de Dados

### 5.1 Estratégia

```sql
-- 1. Manter tabela issuer_controls ativa durante transição
-- 2. Criar novas tabelas em paralelo
-- 3. Script de migração dos dados históricos:

INSERT INTO maintenance_executions (
    issuer_id,
    tipo_manutencao_id,
    execution_type,
    title,
    scheduled_date,
    realized_date,
    due_date,
    status,
    documents,
    created_at
)
SELECT 
    ic.issuer_id,
    tm.id,
    'preventiva',
    CONCAT('Manutenção: ', ic.value->>'tipo_manutencao'),
    ic.value->>'data_realizacao',
    ic.value->>'data_realizacao',
    ic.value->>'data_vencimento',
    'concluida',
    JSON_ARRAY(ic.value->>'document_path'),
    ic.created_at
FROM issuer_controls ic
LEFT JOIN tipos_manutencao tm 
    ON tm.nome = ic.value->>'tipo_manutencao'
    AND tm.tenant_id = (SELECT tenant_id FROM issuers WHERE id = ic.issuer_id)
WHERE ic.control_type = 'manutencao_programada';
```

### 5.2 Compatibilidade Retroativa

- Manter `IssuerControl` para outros tipos de controle
- Criar *read model* para exibir dados migrados
- Flag `migrated` para controle de dualidade

---

## 6. Implementação Técnica

### 6.1 Fases de Desenvolvimento

#### **Fase 1: Fundação (2 semanas)**
- [ ] Criar migrations das novas tabelas
- [ ] Implementar Models e relacionamentos
- [ ] Criar enums e value objects
- [ ] Setup de factories e seeders

#### **Fase 2: Planos de Manutenção (2 semanas)**
- [ ] CRUD de planos (Filament)
- [ ] Configuração de recorrência
- [ ] Checklist e instruções técnicas
- [ ] Anexos de documentos do plano

#### **Fase 3: Execuções (3 semanas)**
- [ ] Geração automática de execuções (scheduler)
- [ ] CRUD de execuções
- [ ] Fluxo de status (pendente → concluída)
- [ ] Upload de documentos e fotos
- [ ] Checklist de execução

#### **Fase 4: Solicitações (1 semana)**
- [ ] CRUD de solicitações
- [ ] Fluxo de aprovação
- [ ] Conversão em execução

#### **Fase 5: Notificações (1 semana)**
- [ ] Sistema de notificações
- [ ] Scheduler de alertas
- [ ] Preferências por usuário

#### **Fase 6: Dashboard e Relatórios (2 semanas)**
- [ ] Dashboard principal
- [ ] Widgets e métricas
- [ ] Relatórios exportáveis

#### **Fase 7: Migração e Polimento (1 semana)**
- [ ] Script de migração de dados
- [ ] Testes automatizados
- [ ] Bug fixes e ajustes

**Total estimado:** 12 semanas (3 meses)

### 6.2 Jobs e Scheduler

```php
// App/Console/Commands/GenerateMaintenanceExecutions.php
class GenerateMaintenanceExecutions extends Command
{
    public function handle()
    {
        // Gera execuções para os próximos 30 dias
        MaintenancePlan::active()->get()->each(function ($plan) {
            $plan->generateExecutionsForPeriod(
                now(),
                now()->addDays(30)
            );
        });
    }
}

// App/Console/Commands/SendMaintenanceNotifications.php
class SendMaintenanceNotifications extends Command
{
    public function handle()
    {
        // Notificações de vencimento (7 dias antes)
        $this->notifyUpcoming();
        
        // Notificações de atraso
        $this->notifyOverdue();
        
        // Resumo diário
        $this->sendDailyDigest();
    }
}
```

### 6.3 Jobs de Processamento

```php
// Processar checklist e atualizar status
class ProcessMaintenanceChecklist extends Job
{
    public function handle()
    {
        // Valida checklist
        // Atualiza status
        // Dispara notificações
    }
}

// Upload e processamento de documentos
class ProcessMaintenanceDocument extends Job
{
    public function handle()
    {
        // OCR em PDFs
        // Compressão de imagens
        // Geração de thumbnails
    }
}
```

---

## 7. Melhorias de UX/UI

### 7.1 Componentes Filament

#### 7.1.1 Table de Execuções

```php
TextColumn::make('status')
    ->badge()
    ->color(fn (string $state): string => match ($state) {
        'pendente' => 'gray',
        'agendada' => 'info',
        'em_andamento' => 'warning',
        'concluida' => 'success',
        'atrasada' => 'danger',
    })
    ->formatStateUsing(fn (string $state): string => match ($state) {
        'em_andamento' => 'Em Andamento',
        'concluida' => 'Concluída',
        default => ucfirst($state),
    });

TextColumn::make('scheduled_date')
    ->label('Programada')
    ->date('d/m/Y')
    ->color(fn ($record) => $record->isOverdue() ? 'danger' : null);

TextColumn::make('delay_days')
    ->label('Atraso')
    ->badge()
    ->color('danger')
    ->visible(fn ($record) => $record->delay_days > 0)
    ->formatStateUsing(fn ($state) => "{$state} dias");
```

#### 7.1.2 Widgets de Dashboard

```php
// StatsOverviewWidget
class MaintenanceStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Manutenções Pendentes', $this->pendingCount)
                ->description('Próximos 7 dias')
                ->color('info'),
            Stat::make('Atrasadas', $this->overdueCount)
                ->description('Requer atenção')
                ->color('danger'),
            Stat::make('Realizadas este mês', $this->completedCount)
                ->description('+12% vs. mês anterior')
                ->color('success'),
        ];
    }
}
```

### 7.2 Calendar View

```php
// Componente de calendário para visualização mensal
<x-maintenance-calendar 
    :executions="$executions"
    :plans="$plans"
    view="month"
/>
```

---

## 8. Validações e Regras de Negócio

### 8.1 Validações Importantes

```php
// Em MaintenancePlan
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($plan) {
        // Validar data início < data fim
        if ($plan->end_date && $plan->end_date < $plan->start_date) {
            throw new \InvalidArgumentException('Data de término deve ser posterior à data de início');
        }
        
        // Validar recorrência personalizada
        if ($plan->recurrence_type === 'personalizada' && !$plan->recurrence_config) {
            throw new \InvalidArgumentException('Recorrência personalizada requer configuração');
        }
    });
}
```

### 8.2 Regras de Negócio

1. **Não excluir execuções com documentos:** Apenas soft delete
2. **Uma execução não pode ser concluída sem checklist:** Validação obrigatória
3. **Fornecedor só pode ser alterado antes do início:** Travas de workflow
4. **Custos só podem ser lançados após conclusão:** Controle financeiro
5. **Notificações de atraso escalonadas:** 1 dia, 3 dias, 7 dias, gestor

---

## 9. Benefícios Esperados

### 9.1 Operacionais

- ✅ **Redução de atrasos:** Alertas proativos
- ✅ **Maior visibilidade:** Dashboard centralizado
- ✅ **Padronização:** Checklists e instruções
- ✅ **Histórico completo:** Rastreabilidade total

### 9.2 Financeiros

- ✅ **Controle de custos:** Orçado vs. realizado
- ✅ **Previsibilidade:** Planejamento anual
- ✅ **Negociação:** Histórico para cotações
- ✅ **Redução de corretivas:** Mais preventivas

### 9.3 Compliance

- ✅ **Auditoria:** Logs e documentos centralizados
- ✅ **Validades:** Controle de vencimentos
- ✅ **Normas:** Checklist conforme regulamentações
- ✅ **Relatórios:** Evidências para fiscalização

---

## 10. Próximos Passos Imediatos

### 10.1 Validação da Proposta

1. **Revisão com stakeholders:** Alinhar expectativas
2. **Priorização de funcionalidades:** MVP vs. futuro
3. **Definição de cronograma:** Recursos disponíveis

### 10.2 Pré-Implementação

1. **Criar repositório de documentos:** Estrutura de pastas
2. **Configurar filas:** Redis/database para jobs
3. **Setup de notificações:** SMTP, WhatsApp API

### 10.3 Decisões Pendentes

- [ ] Integrar com sistema de facilities existente?
- [ ] Suporte a múltiplos locais/unidades?
- [ ] QR Code para check-in em equipamentos?
- [ ] App mobile para técnicos?
- [ ] Integração com ERP financeiro?

---

## 11. Apêndices

### A. Glossário

- **Plano:** Definição da manutenção recorrente
- **Execução:** Ocorrência real de uma manutenção
- **Solicitação:** Pedido de manutenção corretiva
- **Checklist:** Lista de verificação obrigatória

### B. Referências

- Normas ABNT para manutenção predial
- Best practices de facilities management
- ISO 55000 (Gestão de Ativos)

### C. Contatos

- **Responsável técnico:** [A definir]
- **Product Owner:** [A definir]
- **Stakeholders:** Facilities, Financeiro, Compliance

---

**Documento criado em:** 18 de Março de 2026  
**Última atualização:** 18 de Março de 2026  
**Versão:** 1.0
