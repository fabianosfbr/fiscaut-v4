# Research: FiscautConnector Sync Service

**Feature**: `001-fiscaconnector-sync-service`  
**Date**: 2026-03-20  
**Status**: Complete — all unknowns resolved

## Decisions Made

### 1. Service Location and Architecture

**Decision**: Criar `app/Services/FiscautConnectorService.php` como service class stateful com `cgc_emp` injetado via construtor.

**Rationale**: O projeto segue uma convenção estabelecida onde serviços de integração externa vivem em `app/Services/`. O `CnpjJaService` (`app/Services/CnpjJaService.php`) é o análogo mais próximo — faz requisição HTTP com API key do config, retorna dados parseados, e lança exceções em caso de erro. O `SiegConnect` job também serve como referência para o padrão de chamada HTTP com credenciais de tenant.

**Alternatives considered**:
- Colocar a lógica diretamente em um Filament Action: rejeitado — violaria o princípio de separação de concerns (Constitution §Architecture Constraints).
- Criar como queued Job: rejeitado — a chamada de sync sob demanda é pequena e síncrona por design; jobs são para pipelines de alto volume (Constitution §IV Performance Requirements).

---

### 2. CNPJ Injection Method

**Decision**: Passar `cgc_emp` via construtor do serviço.

**Rationale**: O serviço é stateful com `cgc_emp` armazenado como propriedade. Isso evita passar o CNPJ a cada chamada de `sync()` e permite que o Issuer seja resolvido pelo chamador antes de instanciar o serviço. O ponto de chamada (Action/Controller) extrai o CNPJ via `currentIssuer()->cnpj` e passa ao construtor.

**Alternatives considered**:
- Passar `Issuer` no construtor e extrair `$issuer->cnpj` internamente: rejeitado — aumenta acoplamento com o modelo.
- Passar CNPJ como parâmetro de `sync()`: rejeitado — mais limpo ter o CNPJ no estado do serviço.

---

### 3. Authentication Method

**Decision**: Usar `Http::withToken($apiKey)` da facade Http do Laravel.

**Rationale**: Bearer token é o padrão mais comum para APIs REST. A API key do tenant será armazenada em `config('admin.fiscaconnector_api_key')` injetada do `.env`. O padrão query-string (usado no SiegConnect) foi evitado porque bearer tokens em headers são mais seguros e evitam logging de credenciais em URLs.

**Alternatives considered**:
- Query string `?api_key=...` (estilo SiegConnect): rejeitado — expõe credencial em logs de servidor.
- Basic Auth: rejeitado — FiscautConnector aparentemente espera bearer.

---

### 4. Response Validation

**Decision**: Retornar `true` para `status === 'OK'`; `false` para `status !== 'OK'` (mas HTTP 200); lançar `FiscautConnectorException` para HTTP 4xx/5xx.

**Rationale**: A especificação da feature pede "verificar se a resposta é OK". Isso implica dois cenários distintos:
1. HTTP bem-sucedido + body OK → retorno `true`
2. HTTP bem-sucedido + body não OK → retorno `false`
3. HTTP erro → exceção

Isso permite ao chamador tratar resposta negativa como resultado válido (não-excepcional), enquanto falhas de rede/HTTP são tratadas como exceções.

**Alternatives considered**:
- Sempre lançar exceção em qualquer resultado não-OK: rejeitado — o chamador precisa distinguir entre "sync retornou que não há nada a fazer" e "a API está fora do ar".
- Retornar `bool` para todos os casos: rejeitado — perderia informação de debug em caso de falha de rede.

---

### 5. Exception Class

**Decision**: Criar `app/Exceptions/FiscautConnectorException.php` estendendo `Exception`.

**Rationale**: Exceções customizadas permitem ao chamador capturar erros específicos deste serviço com `catch (FiscautConnectorException $e)` sem conflitar com outras exceções HTTP. Segue a convenção Laravel de exceptions em `app/Exceptions/`.

---

### 6. Config Structure

**Decision**: Armazenar URL e API key em `config/admin.php` (`fiscaconnector_url`, `fiscaconnector_api_key`).

**Rationale**: O projeto já usa `config/admin.php` para chaves de APIs externas (ex.: `sieg_api_key`, `cnpj_ja_api_key`). Seguir a mesma convenção mantém consistência.

---

## Open Questions Resolved

| Question | Resolution |
|----------|------------|
| Qual a URL base do FiscautConnector? | Configurável via `config/admin.php` → `fiscaconnector_url` env var |
| Qual o formato do body? | `{ "cgc_emp": "<cnpj>", "sync": true }` (confirmado pelo usuário) |
| Como autenticar? | `Http::withToken($apiKey)` (Laravel Http facade — confirmado pelo usuário) |
| Custom exception? | Sim — `FiscautConnectorException` (confirmado pelo usuário) |
| CNPJ via construtor? | Sim (confirmado pelo usuário) |
| Onde guardar a API key? | `config('admin.fiscaconnector_api_key')` → `FISCAUTCONNECTOR_API_KEY` no `.env` |
