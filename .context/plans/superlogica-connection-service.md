---
status: in_progress
generated: 2026-04-11
agents:
  - type: "architect-specialist"
    role: "Definir contrato e fluxo de validação"
  - type: "backend-specialist"
    role: "Implementar service, exception e migration"
  - type: "frontend-specialist"
    role: "Implementar página de credenciais no Settings"
  - type: "test-writer"
    role: "Cobrir cenários unitários e de página"
docs:
  - "architecture.md"
  - "development-workflow.md"
  - "testing-strategy.md"
  - "security.md"
phases:
  - id: "phase-1"
    name: "Discovery & Alignment"
    prevc: "P"
    agent: "architect-specialist"
  - id: "phase-2"
    name: "Implementation & Iteration"
    prevc: "E"
    agent: "backend-specialist"
  - id: "phase-3"
    name: "Validation & Handoff"
    prevc: "V"
    agent: "test-writer"
---

# Superlogica Connection Service Plan

> Implementar credenciais Superlógica por tenant e validação de conexão via endpoint dedicado de health check.

## Task Snapshot
- **Primary goal:** Disponibilizar configuração de `superlogica_base_url`, `superlogica_app_token` e `superlogica_access_token` no Settings e validar conexão com chamada HTTP controlada.
- **Success signal:** Usuário salva credenciais no painel e o botão "Testar Conexão" retorna notificação de sucesso/erro conforme resposta da API.
- **Key references:**
  - `app/Filament/Clusters/Settings/Pages/SiegCredential.php`
  - `app/Filament/Clusters/Settings/Pages/FiscautConnectorCredential.php`
  - `app/Services/FiscautConnectorService.php`

## Decisions
- Endpoint de validação fixo: `/{base_url}/health/check`.
- Headers obrigatórios em todas as chamadas de validação: `Content-Type: application/json`, `app_token`, `access_token`.
- Credenciais por tenant (não global em `config/admin.php`).
- Esta entrega não implementa sincronização de dados de negócio.

## Working Phases

### Phase 1 — Discovery & Alignment
- Confirmar padrão de páginas de credencial no cluster `Settings`.
- Confirmar padrão de serviço de integração HTTP e de exceptions customizadas.
- Registrar plano e linkar no workflow.

### Phase 2 — Implementation & Iteration
- Criar migration para novos campos em `tenants`.
- Criar `SuperlogicaConnectionException`.
- Criar `SuperlogicaConnectionService::validateConnection(Issuer $issuer): array|bool`.
- Criar página `SuperlogicaCredential` + blade com ações `save` e `testConnection`.

### Phase 3 — Validation & Handoff
- Criar testes unitários para o service (headers, erros de credencial/url, HTTP 4xx/5xx, sucesso).
- Criar testes da página (salvar e testar conexão com mock do service).
- Executar suíte focada e registrar evidências.
