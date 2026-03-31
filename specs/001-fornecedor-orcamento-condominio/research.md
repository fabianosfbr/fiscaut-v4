# Research: Fornecedores e Orçamentos - Condomínio

**Date**: 2026-03-31 | **Feature**: 001-fornecedor-orcamento-condominio

---

## Decision: Formato de Validação WhatsApp

**Question**: Qual o padrão de validação aceito para números WhatsApp brasileiros?

**Decision**: Regex `^(\+55)?[1-9]{2}9[0-9]{8}$` - aceita formatos:
- Com código país: `+5511999999999`
- Sem código país: `5511999999999` (assume 55)
- Com DDD: `11999999999`

**Rationale**: Padrão brasileiro para celulares (9 dígitos após DDD). Aceita tanto formato internacional quanto nacional.

**Alternatives considered**:
- Aceitar também telefones fixos (8 dígitos) - não incluído pois WhatsApp é tipicamente móvel
- Validar se é definitivamente móvel - complexidade desnecessária

---

## Decision: Cálculo de Rating de Fornecedor

**Question**: Como calcular o rating baseado em taxa de resposta, tempo de resposta, taxa de ganho e qualidade?

**Decision**: Usar média ponderada com pesos configuráveis:
```
rating = (response_rate * 0.25) + (win_rate * 0.30) + (response_time_score * 0.20) + (quality_score * 0.25)
```

Onde:
- **response_rate**: % de orçamentos respondidos (0-1)
- **win_rate**: % de orçamentos ganhos/vencedores (0-1)
- **response_time_score**: Score inverso ao tempo médio de resposta (1 = instantâneo, 0 = >7 dias)
- **quality_score**: Média de feedbacks de qualidade (1-5 normalizado para 0-1)

**Rating inicial**: 3.0 (neutro em escala 1-5)

**Rationale**: Pesos equilibrados priorizando win_rate (ganhos efetivos) mas mantendo equilíbrio com outros fatores.

**Alternatives considered**:
- Média simples - não captura importância relativa
- Algoritmo customizado por fornecedor - complexidade desnecessária

---

## Decision: Thresholds para Low-Priority

**Question**: Quais valores usar para identificar fornecedores de baixa performance?

**Decision**: Configurável via `config/admin.php`:
```php
'supplier' => [
    'low_priority' => [
        'response_rate_threshold' => 0.3,    // < 30% resposta
        'win_rate_threshold' => 0.2,          // < 20% ganhos
        'period_days' => 90,                  // Período de análise
    ],
]
```

**Default**: Fornecedor é marcado `low_priority` se:
- Taxa de resposta < 30% POR período de 90 dias, OU
- Taxa de ganho < 20% POR período de 90 dias

**Rationale**: Valores razoáveis que podem ser ajustados conforme necessidade do condomínio.

---

## Decision: Envio de WhatsApp

**Question**: Qual API usar para enviar mensagens WhatsApp?

**Decision**: Usar configuração em `config/services.php` com placeholder para futura integração:
```php
'whatsapp' => [
    'api_url' => env('WHATSAPP_API_URL'),
    'api_key' => env('WHATSAPP_API_KEY'),
    'from_number' => env('WHATSAPP_FROM_NUMBER'),
],
```

Implementar interface abstrata `WhatsAppServiceInterface` para permitir múltiplos provedores (Twilio, Zenvia, etc.).

**Rationale**: O projeto não possui integração WhatsApp existente. Adicionar configuração pronta para futura integração sem afetar código atual.

**Alternatives considered**:
- Integrar diretamente com provedor específico - prematuro sem requisitos definidos
- Usar apenas email - insuficiente conforme requisitos

---

## Decision: Geração de URL para Formulário de Orçamento

**Question**: Como gerar URL única e segura para fornecedor acessar formulário?

**Decision**: Usar tokens hashing com expiração:
```php
// Geração
$token = Str::random(64);
$hash = hash('sha256', $token);

// URL: /orcamento/{budget_request_id}/responder/{token}
// Expiração: 7 dias (configurável)
```

**Rationale**: Simples, seguro e não requer banco de dados adicional.

**Alternatives considered**:
- UUID com check de expiração no banco - overhead desnecessário
- JWT - complexidade desnecessária para este caso de uso

---

## Decision: Estrutura do Banco de Dados

**Question**: Como organizar as entidades para suportar todos requisitos?

**Decision**: 5 tabelas principais:
1. `suppliers` - dados do fornecedor
2. `supplier_categories` - categorias de serviço
3. `budget_requests` - solicitação de orçamento
4. `budget_proposals` - propostas recebidas
5. `supplier_interactions` - histórico de interações

**Relacionamentos**:
- Supplier belongsToMany SupplierCategory
- BudgetRequest belongsTo Supplier (many), belongsTo Issuer
- BudgetProposal belongsTo BudgetRequest, belongsTo Supplier
- SupplierInteraction belongsTo Supplier, belongsTo BudgetRequest

**Rationale**: Estrutura normalizada que suporta histórico completo e rating.

---

## Decision: Integração com Painel Condomínio

**Question**: Como integrar os novos resources ao painel Condomínio existente?

**Decision**: Criar em `app/Filament/Condominio/Resources/`:
- `FornecedorResource.php` - CRUD fornecedores
- `FornecedorCategoryResource.php` - CRUD categorias  
- `BudgetRequestResource.php` - CRUD orçamentos
- Navigation group: "Fornecedores" ou "Compras"

**Rationale**: Seguir padrão existente do projeto (IssuerContactResource, etc.)

---

## Summary

| Item | Decision |
|------|----------|
| WhatsApp validation | Regex `^(\+55)?[1-9]{2}9[0-9]{8}$` |
| Rating calculation | Média ponderada (25% response, 30% win, 20% time, 25% quality) |
| Low-priority threshold | < 30% response_rate OU < 20% win_rate em 90 dias |
| WhatsApp API | Config placeholder + interface abstrata |
| URL token | SHA256 hash com expiração de 7 dias |
| Database | 5 tabelas normalizadas |
| Integration | Condominio Resources (Filament) |