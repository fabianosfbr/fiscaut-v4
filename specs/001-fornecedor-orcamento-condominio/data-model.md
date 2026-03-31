# Data Model: Fornecedores e Orçamentos - Condomínio

**Feature**: 001-fornecedor-orcamento-condominio | **Date**: 2026-03-31

---

## Entities

### 1. Supplier (Fornecedor)

**Table**: `suppliers`

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| `id` | bigint | PK, auto-increment | ID único |
| `issuer_id` | bigint | FK → issuers.id, not null | Condomínio/Associação |
| `name` | string(255) | required, min:2, max:255 | Nome do fornecedor |
| `email` | string(255) | required, email, unique per issuer | Email de contato |
| `whatsapp` | string(20) | required, regex: `^(\+55)?[1-9]{2}9[0-9]{8}$` | WhatsApp (apenas móvil) |
| `phone` | string(20) | nullable | Telefone fixo (opcional) |
| `address` | text | nullable | Endereço |
| `notes` | text | nullable | Observações |
| `rating` | decimal(3,2) | default: 3.00, min:0, max:5 | Rating atual (0-5) |
| `is_low_priority` | boolean | default: false | Flag de baixa prioridade |
| `total_requests` | integer | default: 0 | Total de orçamentos solicitados |
| `total_responses` | integer | default: 0 | Total de respostas recebidas |
| `total_wins` | integer | default: 0 | Total de orçamentos ganhos |
| `avg_response_time_hours` | decimal(6,2) | nullable | Tempo médio de resposta (horas) |
| `last_interaction_at` | timestamp | nullable | Última interação |
| `created_at` | timestamp | auto | |
| `updated_at` | timestamp | auto | |

**Relationships**:
- belongsTo Issuer
- belongsToMany SupplierCategory (via `supplier_category_supplier`)
- hasMany BudgetRequest
- hasMany BudgetProposal
- hasMany SupplierInteraction

---

### 2. SupplierCategory (Categoria de Serviço)

**Table**: `supplier_categories`

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| `id` | bigint | PK, auto-increment | ID único |
| `issuer_id` | bigint | FK → issuers.id, not null | Condomínio/Associação |
| `name` | string(100) | required, unique per issuer | Nome da categoria |
| `description` | text | nullable | Descrição |
| `icon` | string(50) | nullable | Ícone (Heroicon name) |
| `color` | string(20) | nullable | Cor para UI |
| `created_at` | timestamp | auto | |
| `updated_at` | timestamp | auto | |

**Relationships**:
- belongsTo Issuer
- belongsToMany Supplier (via `supplier_category_supplier`)

**Pivot Table**: `supplier_category_supplier`
| Field | Type |
|-------|------|
| `supplier_category_id` | bigint |
| `supplier_id` | bigint |

---

### 3. BudgetRequest (Solicitação de Orçamento)

**Table**: `budget_requests`

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| `id` | bigint | PK, auto-increment | ID único |
| `issuer_id` | bigint | FK → issuers.id, not null | Condomínio |
| `issuer_control_id` | bigint | FK → issuer_controls.id, nullable | Controle de origem (ex: manutenção, seguro) |
| `title` | string(255) | required | Título do orçamento |
| `description` | text | nullable | Descrição do serviço/produto |
| `required_fields` | json | nullable | Campos obrigatórios definidos pelo solicitante |
| `deadline` | date | nullable | Data limite para propostas (pode usar data_programada do IssuerControl) |
| `status` | enum | `draft`, `sent`, `partial`, `completed`, `cancelled` | Status |
| `category_id` | bigint | FK → supplier_categories.id, nullable | Categoria filtro |
| `min_proposals` | integer | default: 1 | Mínimo de propostas esperado |
| `best_proposal_id` | bigint | FK → budget_proposals.id, nullable | Melhor proposta sugerida |
| `notes` | text | nullable | Observações internas |
| `created_by` | bigint | FK → users.id | Usuário que criou |
| `created_at` | timestamp | auto | |
| `updated_at` | timestamp | auto | |

**Relationships**:
- belongsTo Issuer
- belongsTo IssuerControl (nullable)
- belongsTo SupplierCategory
- belongsTo User (createdBy)
- hasMany BudgetProposal

**Nota**: Quando vinculado a um IssuerControl, o `deadline` pode ser automaticamente calculado como `data_programada - alerta_dias_antecedencia` (valor definido no IssuerControlType do controle). Isso garante que a cotação seja obtida antes do alerta padrão do controle.

**Status Transitions**:
```
draft → sent (ao enviar para fornecedores)
sent → partial (ao receber primeira proposta)
sent/completed → cancelled (ao cancelar)
partial → completed (ao selecionar proposta)
```

---

### 4. BudgetProposal (Proposta do Fornecedor)

**Table**: `budget_proposals`

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| `id` | bigint | PK, auto-increment | ID único |
| `budget_request_id` | bigint | FK → budget_requests.id, not null | Orçamento |
| `supplier_id` | bigint | FK → suppliers.id, not null | Fornecedor |
| `amount` | decimal(12,2) | required, min:0 | Valor proposto |
| `delivery_time` | string(50) | nullable | Prazo de entrega |
| `conditions` | text | nullable | Condições/observações |
| `custom_fields` | json | nullable | Campos customizados |
| `document_path` | string(500) | nullable | Caminho do documento anexado |
| `document_name` | string(255) | nullable | Nome do documento |
| `is_selected` | boolean | default: false | Proposta selecionada |
| `submitted_at` | timestamp | nullable | Data/hora da submissão |
| `response_time_hours` | decimal(6,2) | nullable | Tempo de resposta |
| `created_at` | timestamp | auto | |
| `updated_at` | timestamp | auto | |

**Relationships**:
- belongsTo BudgetRequest
- belongsTo Supplier
- belongsTo User (acceptedBy - quando selecionada)

---

### 5. SupplierInteraction (Histórico de Interações)

**Table**: `supplier_interactions`

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| `id` | bigint | PK, auto-increment | ID único |
| `supplier_id` | bigint | FK → suppliers.id, not null | Fornecedor |
| `budget_request_id` | bigint | FK → budget_requests.id, nullable | Orçamento relacionado |
| `type` | enum | `request_sent`, `response_received`, `proposal_submitted`, `budget_won`, `budget_lost`, `contact_updated`, `rating_updated` | Tipo de interação |
| `details` | json | nullable | Detalhes específicos |
| `metadata` | json | nullable | Metadados adicionais |
| `created_at` | timestamp | auto | |

**Relationships**:
- belongsTo Supplier
- belongsTo BudgetRequest (nullable)

---

## Validation Rules Summary

### Supplier (Create/Update)

```php
'name' => 'required|string|min:2|max:255'
'email' => 'required|email|max:255|unique:suppliers,email,NULL,id,issuer_id,' . $issuerId
'whatsapp' => 'required|string|regex:/^(\+55)?[1-9]{2}9[0-9]{8}$/'
'phone' => 'nullable|string|max:20'
'address' => 'nullable|string|max:500'
'notes' => 'nullable|string'
'category_ids' => 'nullable|array'
'category_ids.*' => 'exists:supplier_categories,id,issuer_id,' . $issuerId
```

### BudgetRequest (Create/Update)

```php
'title' => 'required|string|min:3|max:255'
'description' => 'nullable|string|max:2000'
'required_fields' => 'nullable|array'
'deadline' => 'nullable|date|after:today'
'category_id' => 'nullable|exists:supplier_categories,id,issuer_id,' . $issuerId
'supplier_ids' => 'required|array|min:1'
'supplier_ids.*' => 'exists:suppliers,id,issuer_id,' . $issuerId
```

### BudgetProposal (Submit)

```php
'amount' => 'required|numeric|min:0'
'delivery_time' => 'nullable|string|max:50'
'conditions' => 'nullable|string|max:2000'
'custom_fields' => 'nullable|array'
'document' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg|max:10240'
```

---

## Rating Calculation Algorithm

```php
function calculateRating(Supplier $supplier): float
{
    $period = now()->subDays(config('admin.supplier.low_priority.period_days', 90));
    
    $interactions = $supplier->interactions()
        ->where('created_at', '>=', $period)
        ->get();
    
    // Taxa de resposta
    $requests = $interactions->where('type', 'request_sent')->count();
    $responses = $interactions->where('type', 'response_received')->count();
    $responseRate = $requests > 0 ? $responses / $requests : 0;
    
    // Taxa de ganho
    $wins = $interactions->where('type', 'budget_won')->count();
    $winRate = $requests > 0 ? $wins / $requests : 0;
    
    // Tempo de resposta (score inverso - menor = melhor)
    $avgTime = $supplier->avg_response_time_hours ?? 168; // default 7 days
    $responseTimeScore = max(0, 1 - ($avgTime / 168)); // 168h = 7 dias
    
    // Qualidade (normalizar rating atual)
    $qualityScore = $supplier->rating / 5;
    
    // Média ponderada
    $rating = ($responseRate * 0.25) 
            + ($winRate * 0.30) 
            + ($responseTimeScore * 0.20) 
            + ($qualityScore * 0.25);
    
    // Converter para escala 1-5 (multiplicar por 5)
    return max(1, min(5, $rating * 5));
}
```

---

## Budget Suggestion Algorithm

```php
function suggestBestProposal(BudgetRequest $request): ?BudgetProposal
{
    $proposals = $request->budgetProposals()
        ->with('supplier')
        ->get();
    
    if ($proposals->isEmpty()) {
        return null;
    }
    
    // Normalizar scores
    $minAmount = $proposals->min('amount');
    $maxRating = $proposals->max(fn($p) => $p->supplier->rating);
    
    // Calcular score para cada proposta
    $scored = $proposals->map(function ($proposal) use ($minAmount, $maxRating) {
        // Amount score: menor = melhor (invertido)
        $amountScore = $minAmount > 0 
            ? 1 - ($proposal->amount / $maxAmount)  // normalizado
            : 0.5;
        
        // Rating score (0-1)
        $ratingScore = $maxRating > 0 
            ? $proposal->supplier->rating / $maxRating 
            : 0.5;
        
        // Delivery time score (menor = melhor)
        $deliveryDays = (int) preg_replace('/\D/', '', $proposal->delivery_time ?? '30');
        $deliveryScore = max(0, 1 - ($deliveryDays / 30)); // 30 dias = score 0
        
        // Score final ponderado
        return [
            'proposal' => $proposal,
            'score' => ($amountScore * 0.40) + ($ratingScore * 0.35) + ($deliveryScore * 0.25)
        ];
    });
    
    return $scored->sortByDesc('score')->first()['proposal'] ?? null;
}
```

---

## Entity Relationship Diagram

```
┌─────────────────────┐       ┌──────────────────────────┐
│      Issuer         │       │   SupplierCategory      │
│                     │       │                          │
│  - id               │       │  - id                    │
│  - name             │◄──────│  - issuer_id (FK)       │
│  - cnpj             │       │  - name                  │
└─────────────────────┘       │  - description          │
        │                     └────────────┬─────────────┘
        │                                  │
        │                    ┌──────────────┴──────────────┐
        │                    │ supplier_category_supplier │ (pivot)
        │                    └──────────────┬──────────────┘
        │                                  │
        ▼                                  ▼
┌─────────────────────┐       ┌─────────────────────┐
│      Supplier       │       │   BudgetRequest    │
│                     │       │                     │
│  - id               │       │  - id               │
│  - issuer_id (FK)   │───────│  - issuer_id (FK)   │
│  - name             │       │  - title            │
│  - email            │       │  - status           │
│  - whatsapp         │       │  - category_id (FK) │
│  - rating           │       └──────────┬──────────┘
│  - is_low_priority  │                  │
└──────────┬──────────┘                  │
           │                            │
           │         ┌─────────────────┼─────────────────┐
           │         │                 │                 │
           ▼         ▼                 ▼                 ▼
┌─────────────────────┐    ┌─────────────────────┐    ┌─────────────────────┐
│  BudgetProposal    │    │ SupplierInteraction │    │   User              │
│                     │    │                     │    │                     │
│  - id               │    │  - id               │    │  - id               │
│  - budget_request_id│    │  - supplier_id (FK) │    │  - name             │
│  - supplier_id (FK) │    │  - type             │    └─────────────────────┘
│  - amount           │    │  - details          │
│  - is_selected      │    │  - created_at       │
│  - submitted_at    │    └─────────────────────┘
└─────────────────────┘
```

---

## Indexes & Performance

| Table | Index | Fields | Reason |
|-------|-------|--------|--------|
| suppliers | idx_issuer | issuer_id | Queries por condomínio |
| suppliers | idx_rating | rating | Ordenação por rating |
| suppliers | idx_low_priority | is_low_priority, issuer_id | Filtro low-priority |
| supplier_categories | idx_issuer_unique | issuer_id, name (unique) | Unique constraint |
| budget_requests | idx_issuer_status | issuer_id, status | Filtros frequentes |
| budget_requests | idx_issuer_control | issuer_control_id | JOIN com IssuerControl |
| budget_proposals | idx_request | budget_request_id | JOINs |
| supplier_interactions | idx_supplier_date | supplier_id, created_at | Análise periódica |