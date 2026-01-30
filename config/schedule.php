<?php

return [
    'commands' => [
        'enable_custom' => true,
        'supported' => [
            // ex."erp:*"
        ],
        'exclude' => [
            'help',
            'list',
            'test',
            'down',
            'up',
            'env',
            'serve',
            'tinker',
            'clear-compiled',
            'key:generate',
            'package:discover',
            'storage:link',
            'notifications:table',
            'session:table',
            'stub:publish',
            'vendor:publish',
            'route:*',
            'event:*',
            'migrate:*',
            'cache:*',
            'auth:*',
            'config:*',
            'db:*',
            'optimize*',
            'make:*',
            'queue:*',
            'schedule:*',
            'view:*',
            'phpunit:*',
        ],
    ],
    'tool-help-cron-expression' => [
        'enable' => true,
        'url' => 'https://crontab.cronhub.io/',
    ],
    'cache' => [
        'enabled' => ! config('app.debug'),
        'store' => 'file',
        'key' => 'schedule_cache_',
        'ttl' => 60 * 5,
    ],
];
