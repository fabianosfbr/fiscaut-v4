<?php

use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api/v1/',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (BroadcastException $e): ?bool {
            if (! app()->isLocal()) {
                return null;
            }

            $message = $e->getMessage();

            $matchedUrl = (string) Str::of($message)->match('/https?:\/\/\S+/');
            $sanitizedUrl = null;
            $urlHost = null;
            $urlPort = null;
            $urlScheme = null;

            if ($matchedUrl !== '') {
                $parts = parse_url($matchedUrl);

                if (is_array($parts)) {
                    $urlScheme = $parts['scheme'] ?? null;
                    $urlHost = $parts['host'] ?? null;
                    $urlPort = $parts['port'] ?? null;
                    $urlPath = $parts['path'] ?? null;

                    if (is_string($urlScheme) && is_string($urlHost) && is_string($urlPath)) {
                        $sanitizedUrl = $urlScheme . '://' . $urlHost . ($urlPort ? ':' . $urlPort : '') . $urlPath;
                    }
                }
            }

            $rateKey = 'broadcast-exception:' . sha1(implode('|', [
                $urlScheme ?? 'unknown',
                $urlHost ?? 'unknown',
                (string) ($urlPort ?? ''),
                $sanitizedUrl ?? 'no-url',
            ]));

            RateLimiter::attempt($rateKey, 1, function () use ($e, $message, $sanitizedUrl, $urlHost, $urlPort, $urlScheme): void {
                Log::warning('Broadcast falhou', [
                    'exception' => class_basename($e),
                    'code' => $e->getCode(),
                    'message' => Str::limit($message, 1000),
                    'url' => $sanitizedUrl,
                    'url_host' => $urlHost,
                    'url_port' => $urlPort,
                    'url_scheme' => $urlScheme,
                    'broadcast_connection' => config('broadcasting.default'),
                    'reverb' => [
                        'host' => config('broadcasting.connections.reverb.options.host'),
                        'port' => config('broadcasting.connections.reverb.options.port'),
                        'scheme' => config('broadcasting.connections.reverb.options.scheme'),
                    ],
                ]);
            }, 60);

            return false;
        });
    })->create();
