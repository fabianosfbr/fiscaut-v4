## Análise do Código Atual
- A lógica de “schedules dinâmicos do banco” está concentrada em [console.php](file:///root/projetos/fiscaut-v4.1/routes/console.php#L29-L204), misturando:
  - leitura do banco (`ScheduleService::getActives()`), validações (CRON/comando), montagem de argumentos/opções, registro no Scheduler, e persistência de histórico.
  - uso direto de Facades (`Schedule`, `Log`) e de helpers globais (`storage_path()`), o que aumenta acoplamento e dificulta testes.
- A criação do histórico hoje depende dos callbacks do evento (onSuccess/onFailure) e de um arquivo de saída temporário. Essa regra também está “embutida” no arquivo de rotas.

## Viabilidade
- É viável extrair integralmente essa lógica para uma classe dedicada, mantendo o comportamento funcional.
- A refatoração reduz o acoplamento do ponto de chamada (routes/console) para um único método público e permite testes unitários com mocks (Schedule, Event, relation histories, filesystem).

## Nova Classe (Design)
- Criar `App\Console\Scheduling\DynamicTaskCommandExecutor` (nome próximo ao sugerido).
- Responsabilidades da classe:
  - Orquestrar leitura dos schedules ativos e registrar cada tarefa no Scheduler.
  - Encapsular validações (comando/CRON), montagem de tokens de parâmetros (args + options), configuração do evento (flags/webhooks/email), e criação do histórico.
- Interface pública proposta:
  - `public function registerFromDatabase(?string $artisanCommand): void`
    - Faz o “guard” (só roda quando o comando atual começa com `schedule:`) e registra todos.
  - `public function executeTask(\App\Models\Schedule $dbSchedule): void`
    - Encapsula a construção/registro de um único evento.
- Injeção de dependências no construtor (para testabilidade):
  - `ScheduleService` (leitura/cache), `Illuminate\Console\Scheduling\Schedule` (scheduler), `Psr\Log\LoggerInterface`, `Illuminate\Filesystem\Filesystem` (leitura/gravação do output histórico), `Illuminate\Contracts\Config\Repository` (chaves/limites).

## Refatoração do Ponto de Chamada
- Em [console.php](file:///root/projetos/fiscaut-v4.1/routes/console.php), substituir o closure `$registerDynamicSchedules` por algo declarativo:
  - obter `$artisanCommand` de `$_SERVER['argv'][1]` (como hoje)
  - chamar `app(DynamicTaskCommandExecutor::class)->registerFromDatabase($artisanCommand)`
- Manter o comando `schedule:run-dynamic` inalterado.

## Ajustes de Implementação (sem alterar comportamento)
- Preservar exatamente as regras atuais:
  - `command_custom` quando `command === 'custom'`
  - validação de CRON
  - tokens de `params` (respeitando `type=function` como hoje) + tokens de `getOptions()`
  - hooks/flags/envs/email
  - histórico em `schedule_histories` no sucesso/erro lendo o arquivo `storage/logs/schedule-history-{id}.log`.
- Garantir que a classe continue capturando o output para histórico mesmo quando `log_filename` estiver setado (se necessário, replicar o conteúdo para o arquivo configurado sem “perder” o arquivo do histórico).

## Testes
1. **Novo teste unitário da classe** `tests/Unit/Console/DynamicTaskCommandExecutorTest.php`:
   - `registerFromDatabase` não registra nada se o comando não for `schedule:*`.
   - Quando for `schedule:run`, chama `ScheduleService::getActives()` e registra eventos.
   - Verifica que `Schedule->command($name, $tokens)` recebe tokens corretos (incluindo `-v/-vv/-vvv`).
   - Simula callbacks de sucesso/erro e valida que `histories()->create([...])` é chamado com `schedule_id/command/params/options/output`.
2. **Atualizar testes existentes**:
   - `tests/Unit/Services/CommandServiceTest.php` hoje espera `'verbose'` nas opções. Atualizar para esperar `-v/-vv/-vvv` (e manter `flag`).

## Verificação (local)
- Rodar via Sail:
  - `./vendor/bin/sail artisan test`
  - `./vendor/bin/sail artisan schedule:list --verbose`
  - `./vendor/bin/sail artisan schedule:run -v`
- Confirmar que:
  - schedules continuam sendo listados/executados
  - histórico continua sendo gravado em `schedule_histories`.

Se aprovar, eu implemento a classe, refatoro o `routes/console.php` para usar apenas a interface pública dela e ajusto/crio os testes para cobrir o novo fluxo.