## Objetivo
- Configurar Laravel Horizon para produção (servidor médio porte) usando Redis.
- Separar processamento em **4 filas** e **4 supervisores**: `sefaz`, `sieg`, `default`, `low`.
- Alterar jobs/dispatchers para enviar cada tarefa à fila correta.
- Criar **obrigatoriamente** `docs/horizon-producao.md` com o guia operacional.

## Diagnóstico (estado atual do repo)
- `laravel/horizon` está instalado, mas faltam `config/horizon.php` e `App\\Providers\\HorizonServiceProvider` (dashboard tende a ficar inacessível em produção sem gate).
- Há uso atual de `onQueue('low')` em trechos ligados a SEFAZ e em jobs pesados; o restante cai em `default`.
- `.env.example` usa `QUEUE_CONNECTION=database`, mas não há migration da tabela `jobs`; produção com Horizon deve padronizar **Redis**.

## Implementação (o que será criado/alterado)
### 1) Horizon (config + gate)
- Criar `config/horizon.php` com supervisores por fila e parâmetros por env (maxProcesses, balance, timeouts).
- Criar `app/Providers/HorizonServiceProvider.php` com gate `viewHorizon` restrito a perfis administrativos (integrando com o sistema de permissões do projeto).
- Registrar o provider em `bootstrap/providers.php`.

### 2) Redis/Queue (produção)
- Documentar e alinhar `QUEUE_CONNECTION=redis` em produção.
- Ajustar `config/queue.php` (Redis `retry_after`) para ser maior que o maior timeout real, evitando reprocessamento prematuro.
- Tratar o outlier de timeout extremo (job com `$timeout=120000`): manter em `low` e ajustar estratégia para não impactar as demais filas.

### 3) Roteamento de jobs por domínio
- **SEFAZ → `sefaz`**: atualizar os pontos onde hoje vai para `low` mas pertence ao fluxo SEFAZ; garantir que downloads/processamentos SEFAZ (NFe/CTe) usem `sefaz`.
- **SIEG → `sieg`**: mapear jobs/services de SIEG e aplicar `onQueue('sieg')` (ou `$queue='sieg'` no próprio Job quando fizer sentido).
- **Pesados/ETL/Bulk → `low`**: manter/padronizar jobs longos (bulk download, ETL de dashboard, agregações) em `low`.
- **Demais → `default`**: fallback quando não houver categorização.

## Supervisores (desenho inicial para servidor médio porte)
- `supervisor-sefaz`: mais workers (prioridade operacional), `queue=['sefaz']`, `balance='auto'`.
- `supervisor-sieg`: workers moderados, `queue=['sieg']`.
- `supervisor-default`: workers moderados, `queue=['default']`.
- `supervisor-low`: poucos workers, `queue=['low']`, `timeout` maior.
- Valores de processos/timeouts ficarão parametrizáveis por env para ajuste fino sem redeploy.

## Operação em produção (obrigatório em docs)
- Criar `docs/horizon-producao.md` contendo:
  - variáveis de ambiente recomendadas (Redis + Horizon);
  - exemplo de serviço (systemd ou Supervisor) para rodar `php artisan horizon`;
  - procedimento de deploy com `php artisan horizon:terminate`;
  - agendamento de `php artisan horizon:snapshot`;
  - checklist de saúde (dashboard, filas, falhas, métricas) e tuning inicial.

## Validação pós-implementação
- Subir Horizon e confirmar status.
- Disparar jobs representativos de `sefaz`, `sieg`, `default`, `low` e verificar no dashboard:
  - consumo por supervisor correto;
  - timeouts/retries coerentes;
  - `low` não degrada `sefaz`/`sieg`.