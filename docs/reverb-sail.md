# Reverb no Laravel Sail

Este guia descreve como operar o Laravel Reverb no ambiente Docker (Laravel Sail) neste projeto.

## Variáveis de ambiente

Backend (broadcasting):

```
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

Servidor Reverb (listener no container):

```
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
REVERB_SERVER_PATH=
```

Frontend (Vite / Echo):

```
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## Subindo os containers

Após mudanças no container (ex.: processos do supervisor), suba com rebuild:

```
./vendor/bin/sail up -d --build
```

## Executando o Reverb

O Reverb é iniciado como processo do supervisor no container `laravel.test`.

Se você precisar iniciar manualmente em foreground (para debug):

```
./vendor/bin/sail artisan reverb:start --host=0.0.0.0 --port=8080
```

## Monitoramento contínuo

Para acompanhar logs em tempo real:

```
./vendor/bin/sail artisan pail
```

Para acompanhar logs do container:

```
./vendor/bin/sail logs -f laravel.test
```

## SSL (wss/https)

Em ambientes com proxy TLS (ex.: Nginx/Traefik), configure:

```
REVERB_SCHEME=https
VITE_REVERB_SCHEME=https
REVERB_PORT=443
VITE_REVERB_PORT=443
```
