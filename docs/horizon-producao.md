# Horizon em Produção (Redis)

Este guia descreve como operar o Laravel Horizon em produção neste projeto, com separação de filas para:

- `sefaz`: chamadas e documentos SEFAZ
- `sieg`: chamadas e documentos SIEG
- `default`: tarefas gerais
- `low`: tarefas longas (ETL, importações, bulk actions)

## Pré-requisitos

- Redis acessível pelo servidor da aplicação
- PHP com extensão de Redis instalada (phpredis recomendado)
- Variáveis de ambiente de Redis configuradas (`REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`, etc.)

## Variáveis de ambiente recomendadas

Mínimo:

```
QUEUE_CONNECTION=redis
REDIS_QUEUE_RETRY_AFTER=7500
REDIS_QUEUE_BLOCK_FOR=5
```

Horizon (ajustes operacionais):

```
HORIZON_NAME=fiscaut
HORIZON_PATH=horizon
HORIZON_FAST_TERMINATION=true
HORIZON_MEMORY_LIMIT=256
HORIZON_WORKER_MEMORY=256
```

Supervisores por fila (valores iniciais para servidor médio porte):

```
HORIZON_SEFAZ_MAX_PROCESSES=8
HORIZON_SIEG_MAX_PROCESSES=4
HORIZON_DEFAULT_MAX_PROCESSES=4
HORIZON_LOW_MAX_PROCESSES=2
```

Timeouts (devem ser maiores que os tempos esperados dos jobs):

```
HORIZON_TIMEOUT_SEFAZ=900
HORIZON_TIMEOUT_SIEG=900
HORIZON_TIMEOUT_DEFAULT=900
HORIZON_TIMEOUT_LOW=7200
```

Acesso ao dashboard (escolha pelo menos 1 estratégia):

Por role (padrão):

```
HORIZON_ALLOWED_ROLES=super-admin
```

Por permissão:

```
HORIZON_ALLOWED_PERMISSION=horizon.view
```

Por e-mail:

```
HORIZON_ALLOWED_EMAILS=admin@empresa.com.br,devops@empresa.com.br
```

## Executando o Horizon

O Horizon deve rodar como um serviço único (master) por instância da aplicação:

```
php artisan horizon
```

## Rodando via Laravel Sail (ambiente de desenvolvimento/staging)

Suba os containers:

```
./vendor/bin/sail up -d
```

Verifique o status do Horizon:

```
./vendor/bin/sail artisan horizon:status
```

Inicie o Horizon (processo em foreground):

```
./vendor/bin/sail artisan horizon
```

Finalize os workers de forma graciosa (após deploy/rebuild de assets/código):

```
./vendor/bin/sail artisan horizon:terminate
```

## Exemplo com systemd

Crie um unit file como referência (ajuste paths/usuário/ambiente):

```
[Unit]
Description=Laravel Horizon
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/fiscaut
ExecStart=/usr/bin/php artisan horizon
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Comandos típicos:

```
systemctl daemon-reload
systemctl enable --now laravel-horizon
systemctl status laravel-horizon
```

## Exemplo com Supervisor

```
[program:laravel-horizon]
process_name=%(program_name)s
command=/usr/bin/php /var/www/fiscaut/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/laravel-horizon.log
stopwaitsecs=3600
```

## Deploy (restart gracioso)

Após publicar uma nova versão do código, finalize os workers para recarregar o código:

```
php artisan horizon:terminate
```

Com `HORIZON_FAST_TERMINATION=true`, o restart tende a ser mais rápido.

## Agendamento de métricas

Agende o snapshot para o histórico de métricas do dashboard:

```
php artisan horizon:snapshot
```

Recomendação: rodar a cada minuto no scheduler do servidor (cron).

## Checklist de saúde

- Dashboard abre e mostra supervisores `supervisor-sefaz`, `supervisor-sieg`, `supervisor-default`, `supervisor-low`
- Jobs aparecem nas filas corretas (`sefaz`, `sieg`, `default`, `low`)
- Falhas/retries ocorrem sem duplicação prematura (ver `REDIS_QUEUE_RETRY_AFTER` e timeouts)
- `low` não degrada throughput das filas `sefaz` e `sieg`

## Tuning rápido

- Aumentar `HORIZON_SEFAZ_MAX_PROCESSES` se houver backlog em `sefaz`
- Aumentar `HORIZON_SIEG_MAX_PROCESSES` se houver backlog em `sieg`
- Manter `HORIZON_LOW_MAX_PROCESSES` baixo para evitar que jobs longos consumam CPU/memória
- Ajustar `HORIZON_WORKER_MEMORY` se houver reinícios por memória
