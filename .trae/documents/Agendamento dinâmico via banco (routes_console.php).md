## Contexto Atual
- O projeto já tem Model/Services para schedules no banco: [Schedule](file:///root/projetos/fiscaut-v4.1/app/Models/Schedule.php), [ScheduleService](file:///root/projetos/fiscaut-v4.1/app/Services/ScheduleService.php) e cache em [config/schedule.php](file:///root/projetos/fiscaut-v4.1/config/schedule.php).
- Falta o “plugar” no Scheduler do Laravel: hoje [routes/console.php](file:///root/projetos/fiscaut-v4.1/routes/console.php) só tem o comando `inspire`.
- Há Observers para invalidar cache, mas **não estão registrados**; logo, hoje só o TTL do cache funcionaria.

## Objetivo
- Carregar tasks do banco (apenas `status=active`) e registrar no Scheduler do Laravel para que:
  - `php artisan schedule:list` mostre as tarefas.
  - `php artisan schedule:run` execute conforme CRON/flags/params.
  - criar/editar/desativar pelo banco reflita automaticamente (com cache/invalidations).

## Implementação (código)
1. **Atualizar `routes/console.php` para registrar schedules dinâmicos**
   - Importar `Schedule` (facade), `Log`, `CronExpression`, e `ScheduleService`.
   - Encapsular a lógica em uma função/closure (ex.: `registerDynamicSchedules()`), com:
     - `try-catch` externo para falhas de banco (ex.: tabela não existe ainda / migrations / conexão).
     - Log `info` com quantidade carregada e se veio do cache.
     - Loop por registro com `try-catch` individual (1 tarefa inválida não derruba todas).
   - Para cada registro:
     - Determinar comando: `command_custom` quando `command === 'custom'`, senão `command`.
     - Validar expressão CRON via `CronExpression::isValidExpression($expression)`; se inválida, `Log::error()` e pular.
     - Montar parâmetros:
       - Usar `Schedule::command($command, $parameters)`.
       - Converter opções armazenadas como strings (ex.: `--foo=bar`, `--flag`) em array compatível com o scheduler (`['--foo' => 'bar', '--flag' => true]`).
       - Mesclar com argumentos retornados por `Schedule::getArguments()`.
     - Aplicar atributos suportados:
       - `->cron($expression)`
       - `->description("DB #{$id} {$command}")` (para aparecer bem no `schedule:list`)
       - `->evenInMaintenanceMode()`, `->withoutOverlapping()`, `->onOneServer()`, `->runInBackground()`
       - `->pingBefore($webhook_before)` e `->thenPing($webhook_after)`
       - Email: se `email_output` presente:
         - `sendmail_success` → `->emailOutputTo($email)`
         - `sendmail_error` → `->emailOutputOnFailure($email)`
       - Output em arquivo: se `log_filename` presente → `->appendOutputTo(storage_path('logs/'.$log_filename))`
     - Logs por execução:
       - `->before(...)` com `Log::info()` (início)
       - `->onSuccess(...)` e `->onFailure(...)` com `Log::info()` / `Log::error()`.

2. **Adicionar o comando `schedule:run-dynamic` em `routes/console.php`**
   - Registrar `Artisan::command('schedule:run-dynamic {--verbose} {--force}', ...)`.
   - No handler:
     - Logar `info` (“executando schedule dinâmico”).
     - Delegar para `schedule:run`, repassando `--verbose/--force` quando definidos.

3. **Invalidar cache ao criar/editar schedule (novo padrão Laravel 12)**
   - Em vez de registrar no `AppServiceProvider`, aplicar o padrão com Attribute:
     - Em [Schedule](file:///root/projetos/fiscaut-v4.1/app/Models/Schedule.php): `#[ObservedBy(ScheduleObserver::class)]`
     - Em [ScheduleHistory](file:///root/projetos/fiscaut-v4.1/app/Models/ScheduleHistory.php): `#[ObservedBy(ScheduleHistoryObserver::class)]`
   - Isso ativa automaticamente os Observers e garante `ScheduleService::clearCache()` ao salvar/atualizar/excluir.

## Validação
- Criar/editar/desativar uma tarefa via Filament e confirmar que o cache invalida.
- Rodar:
  - `php artisan schedule:list` e verificar entradas “DB #ID ...”.
  - `php artisan schedule:run` e validar logs: “tarefas carregadas” + “executando tarefa” + “sucesso/erro”.
  - `php artisan schedule:run-dynamic` e checar que delega corretamente.
- Testar casos:
  - CRON inválida (logar erro e ignorar registro).
  - `command=custom` sem `command_custom` (logar erro e ignorar).

## Observações
- Para refletir mudanças sem reiniciar processos, o caminho ideal é o cron do SO chamando `schedule:run` a cada minuto. O `schedule:work` é loop longo e não recarrega definições automaticamente.

Aprovando este plano, eu implemento as mudanças em `routes/console.php` e adiciono os Attributes `#[ObservedBy(...)]` nos Models para garantir invalidação de cache conforme o padrão do Laravel 12.