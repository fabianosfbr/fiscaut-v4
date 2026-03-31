# Quickstart: Fornecedores e Orçamentos - Condomínio

**Feature**: 001-fornecedor-orcamento-condominio | **Date**: 2026-03-31

---

## Visão Geral

Este guia fornece instruções para implementar o módulo de fornecedores e orçamentos no painel Condomínio do Fiscaut.

### Escopo

1. **Gestão de Fornecedores**: CRUD completo com sistema de rating automático
2. **Categorias de Serviço**: Classificação de fornecedores por tipo de serviço
3. **Orçamentos**: Criação, envio, recebimento e comparação de propostas
4. **Histórico**: Registro de todas as interações para análise de performance

---

## Pré-requisitos

- PHP 8.2+ com Laravel 12
- Filament 5 instalado
- MySQL 8.0
- Ambiente multi-tenant configurado

---

## Passos de Implementação

### 1. Criar Migrations

```bash
# Tabela de categorias de fornecedor
php artisan make:migration create_supplier_categories_table

# Tabela pivot fornecedor-categoria
php artisan make:migration create_supplier_category_supplier_table

# Tabela de fornecedores
php artisan make:migration create_suppliers_table

# Tabela de solicitações de orçamento
php artisan make:migration create_budget_requests_table

# Tabela de propostas
php artisan make:migration create_budget_proposals_table

# Tabela de interações
php artisan make:migration create_supplier_interactions_table
```

### 2. Criar Models

```php
// app/Models/Supplier.php
// app/Models/SupplierCategory.php
// app/Models/BudgetRequest.php
// app/Models/BudgetProposal.php
// app/Models/SupplierInteraction.php
```

Cada model deve incluir:
- Trait `HasFactory`
- Relationships com Issuer (tenant é inferido via Issuer)
- Scope para filtragem por issuer

### 3. Criar Services

```php
// app/Services/SupplierRatingService.php
// Cálculo e atualização de rating

// app/Services/BudgetNotificationService.php
// Envio de emails (implementar depois)

// app/Services/BudgetComparisonService.php
// Comparação e sugestão de melhores propostas
```

### 4. Criar Filament Resources

```php
// app/Filament/Condominio/Resources/SupplierCategoryResource.php
// app/Filament/Condominio/Resources/SupplierResource.php
// app/Filament/Condominio/Resources/BudgetRequestResource.php
```

### 5. Adicionar Configurações

Em `config/admin.php`:

```php
'supplier' => [
    'low_priority' => [
        'response_rate_threshold' => env('SUPPLIER_RESPONSE_THRESHOLD', 0.3),
        'win_rate_threshold' => env('SUPPLIER_WIN_THRESHOLD', 0.2),
        'period_days' => env('SUPPLIER_PERIOD_DAYS', 90),
    ],
],
```

Em `config/services.php`:

```php
'whatsapp' => [
    'api_url' => env('WHATSAPP_API_URL'),
    'api_key' => env('WHATSAPP_API_KEY'),
    'from_number' => env('WHATSAPP_FROM_NUMBER'),
],
```

---

## Validações

### WhatsApp

```php
// Regex para números brasileiros móveis
$regex = '/^(\+55)?[1-9]{2}9[0-9]{8}$/';
```

Aceita formatos:
- `+5511999999999`
- `5511999999999`
- `11999999999`

### Rating

- Inicial: 3.0 (neutro)
- Range: 1.0 a 5.0
- Atualiza automaticamente após cada interação

---

## Estrutura de Diretórios

```
app/
├── Filament/
│   └── Condominio/
│       └── Resources/
│           ├── SupplierCategoryResource.php
│           ├── SupplierResource.php
│           └── BudgetRequestResource.php
├── Models/
│   ├── Supplier.php
│   ├── SupplierCategory.php
│   ├── BudgetRequest.php
│   ├── BudgetProposal.php
│   └── SupplierInteraction.php
├── Services/
│   ├── SupplierRatingService.php
│   ├── BudgetNotificationService.php
│   └── BudgetComparisonService.php
└── Http/
    └── Controllers/
        └── BudgetProposalController.php  // Para supplier externo

database/migrations/
├── [date]_create_supplier_categories_table.php
├── [date]_create_supplier_category_supplier_table.php
├── [date]_create_suppliers_table.php
├── [date]_create_budget_requests_table.php
├── [date]_create_budget_proposals_table.php
└── [date]_create_supplier_interactions_table.php

routes/
└── web.php
    // Route para fornecedor responder orçamento
```

---

## API para Fornecedor Externo

Rota pública para fornecedor responder orçamento:
```php
Route::get('/orcamento/{budgetRequest}/responder/{token}', [BudgetProposalController::class, 'show']);
Route::post('/orcamento/{budgetRequest}/responder/{token}', [BudgetProposalController::class, 'submit']);
```

Token gerado com hash SHA256, expiração de 7 dias.

---

## Integração com IssuerControl

O BudgetRequest pode ser vinculado a um IssuerControl para contexto:

### Criar orçamento a partir de um IssuerControl

1. Na página de detalhes do IssuerControl, adicionar botão "Solicitar Orçamento"
2. Ao criar, vincular automaticamente: `budget_request->issuer_control_id = issuer_control->id`
3. Preencher `deadline` automaticamente: `data_programada - dias_antecedencia` (ex: -15 dias para seguros)

### Exemplo: Seguro automotivo vencendo em 60 dias

```php
// Ao criar orçamento vinculado ao controle
$control = IssuerControl::with('typeControl')->find($controlId);
$daysBeforeDeadline = $control->typeControl->alerta_dias_antecedencia ?? 15; // Do IssuerControlType

$budgetRequest = BudgetRequest::create([
    'issuer_id' => $control->issuer_id,
    'issuer_control_id' => $control->id,
    'title' => "Cotação: {$control->titulo}",
    'description' => $control->descricao,
    'deadline' => $control->data_programada->subDays($daysBeforeDeadline),
    // ... outros campos
]);
```

**Nota**: O `alerta_dias_antecedencia` é configurado no **IssuerControlType** (ex: tipo "Seguro Veicular" pode ter 15 dias, tipo "Manutenção" pode ter 7 dias).

### Busca de orçamentos por IssuerControl

```php
// Listar orçamentos de um controle específico
$orcamentos = BudgetRequest::where('issuer_control_id', $controlId)->get();

// Listar todos orçamentos com controle vencido ou próximos do prazo
$orcamentosUrgentes = BudgetRequest::whereNotNull('issuer_control_id')
    ->where('deadline', '<=', now()->addDays(7))
    ->whereIn('status', ['sent', 'partial'])
    ->get();
```

### Unitários
- Testar cálculo de rating
- Testar validação de WhatsApp
- Testar sugestão de melhor proposta

### Feature
- Testar CRUD de fornecedores
- Testar criação de orçamento
- Testar comparação de propostas

---

## Variáveis de Ambiente

```env
# Supplier Configuration
SUPPLIER_RESPONSE_THRESHOLD=0.3
SUPPLIER_WIN_THRESHOLD=0.2
SUPPLIER_PERIOD_DAYS=90

# WhatsApp (futuro)
WHATSAPP_API_URL=
WHATSAPP_API_KEY=
WHATSAPP_FROM_NUMBER=
```

---

## Próximos Passos

1. Executar migrations
2. Criar models com relationships
3. Implementar services de rating e comparação
4. Criar Filament Resources
5. Adicionar rotas para resposta de fornecedor
6. Criar testes unitários e de feature