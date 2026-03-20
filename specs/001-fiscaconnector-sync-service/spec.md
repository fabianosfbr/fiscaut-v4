# Feature Specification: FiscautConnector Sync Service

**Feature Branch**: `001-fiscacontor-sync-service`  
**Created**: 2026-03-20  
**Status**: Draft  
**Input**: "Criar um serviço para conectar ao FiscautConnector via post usando as credenciais de api, informando o como parametro o cgce_emp = currentIssuer()->cnpj e sync = true. Verificar se a resposta é OK"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - FiscautConnector Sync Trigger (Priority: P1)

Como desenvolvedor, preciso de um serviço que envie uma requisição POST para o FiscautConnector com as credenciais de API e os parâmetros corretos, para que o sistema possa acionar a sincronização de dados do emitente atual.

**Why this priority**: Este é o requisito central da feature — a capacidade de invocar o FiscautConnector de forma confiável.

**Independent Test**: Pode ser testado unitariamente com um mock do Http facade, sem necessidade de rede real ou banco de dados.

**Acceptance Scenarios**:

1. **Given** um CNPJ válido (14 dígitos), **When** `new FiscautConnectorService($cgcEmp)->sync()` é chamado, **Then** uma requisição POST é enviada para a URL configurada com `cgc_emp` igual ao CNPJ e `sync` igual a `true`.
2. **Given** a API retorna HTTP 200 com body contendo `"status": "OK"`, **When** `FiscautConnectorService::sync()` é chamado, **Then** o método retorna `true`.
3. **Given** a API retorna HTTP 200 com body contendo `"status": "ERROR"` ou qualquer outro código diferente de OK, **When** `FiscautConnectorService::sync()` é chamado, **Then** o método retorna `false`.
4. **Given** a API retorna HTTP 4xx ou 5xx, **When** `FiscautConnectorService::sync()` é chamado, **Then** o método lança uma exceção `FiscautConnectorException`.
5. **Given** o tenant não possui `fiscaconnector_api_key` configurada, **When** `FiscautConnectorService::sync()` é chamado, **Then** o método lança uma exceção `FiscautConnectorException` com mensagem indicando ausência de credencial.

---

### Edge Cases

- O issuer é `null` (nenhum emitente selecionado) — deve lançar exceção ou retornar `false`?
- CNPJ do issuer está vazio ou mal formatado — deve validar antes de enviar?
- Timeout de conexão — deve ser tratado como exceção?
- API retorna JSON inválido — deve ser tratado como exceção?

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: O sistema DEVE prover um serviço `FiscautConnectorService` em `app/Services/FiscautConnectorService.php`.
- **FR-002**: O serviço DEVE aceitar `cgc_emp` (string CNPJ) via construtor, e o Issuer só é usado para extração do CNPJ no ponto de chamada.
- **FR-003**: O serviço DEVE enviar uma requisição POST para a URL base configurada em `config/admin.php` (`fiscaconnector_url`).
- **FR-004**: O payload DEVE conter `cgc_emp` (CNPJ do issuer) e `sync` (boolean `true`).
- **FR-005**: A autenticação DEVE ser feita via header `Authorization: Bearer {api_key}`, onde a chave vem de `config('admin.fiscaconnector_api_key')`.
- **FR-006**: O serviço DEVE retornar `true` quando a resposta HTTP for bem-sucedida e o body conter `"status": "OK"`.
- **FR-007**: O serviço DEVE retornar `false` quando a resposta HTTP for bem-sucedida mas o status não for OK.
- **FR-008**: O serviço DEVE lançar `FiscautConnectorException` em caso de erro HTTP (4xx/5xx) ou exceção de rede.
- **FR-009**: Todos os erros DEVEM ser logados via `Log::error()`.

### Key Entities *(include if feature involves data)*

- **Issuer**: Modelo existente em `app/Models/Issuer.php` — fornecedor do CNPJ.
- **Tenant**: Modelo existente em `app/Models/Tenant.php` — fornecedor da `fiscaconnector_api_key`.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: `FiscautConnectorService::sync()` pode ser chamado com um `Issuer` e retorna `bool` sem lançar exceções inesperadas.
- **SC-002**: O payload enviado contém exatamente `cgc_emp` e `sync` com os valores corretos.
- **SC-003**: O header `Authorization` contém a chave da API configurada.
- **SC-004**: Unit tests cobrem os cenários de retorno OK, retorno não-OK, erro HTTP, e ausência de credencial.
