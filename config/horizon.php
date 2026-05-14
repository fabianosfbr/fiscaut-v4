<?php

use Illuminate\Support\Str;

return [
    'name' => env('HORIZON_NAME', env('APP_NAME')),

    'domain' => env('HORIZON_DOMAIN'),

    'path' => env('HORIZON_PATH', 'horizon'),

    'use' => env('HORIZON_USE', 'default'),

    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_horizon:'
    ),

    'middleware' => ['web'],

    'waits' => [
        'redis:sefaz' => (int) env('HORIZON_WAIT_SEFAZ', 60),
        'redis:sieg' => (int) env('HORIZON_WAIT_SIEG', 60),
        'redis:default' => (int) env('HORIZON_WAIT_DEFAULT', 60),
        'redis:low' => (int) env('HORIZON_WAIT_LOW', 300),
    ],

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],

    'silenced' => [],

    'silenced_tags' => [],

    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],

    'fast_termination' => (bool) env('HORIZON_FAST_TERMINATION', true),

    'memory_limit' => (int) env('HORIZON_MEMORY_LIMIT', 256),

    'defaults' => [
        'supervisor-sefaz' => [
            'connection' => 'redis',
            'queue' => ['sefaz'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 1,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => (int) env('HORIZON_WORKER_MEMORY', 256),
            'tries' => (int) env('HORIZON_TRIES', 3),
            'timeout' => (int) env('HORIZON_TIMEOUT_SEFAZ', 900),
            'nice' => 0,
        ],

        'supervisor-sieg' => [
            'connection' => 'redis',
            'queue' => ['sieg'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 1,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => (int) env('HORIZON_WORKER_MEMORY', 256),
            'tries' => (int) env('HORIZON_TRIES', 3),
            'timeout' => (int) env('HORIZON_TIMEOUT_SIEG', 900),
            'nice' => 0,
        ],

        'supervisor-default' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 1,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => (int) env('HORIZON_WORKER_MEMORY', 256),
            'tries' => (int) env('HORIZON_TRIES', 3),
            'timeout' => (int) env('HORIZON_TIMEOUT_DEFAULT', 900),
            'nice' => 0,
        ],

        'supervisor-low' => [
            'connection' => 'redis',
            'queue' => ['low'],
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'maxProcesses' => 1,
            'maxTime' => 0,
            'maxJobs' => 0,
            'memory' => (int) env('HORIZON_WORKER_MEMORY', 256),
            'tries' => (int) env('HORIZON_TRIES_LOW', 1),
            'timeout' => (int) env('HORIZON_TIMEOUT_LOW', 7200),
            'nice' => 0,
        ],
    ],

    'environments' => [
        'production' => [
            'horizon-workers-high-prod' => [
                'connection' => 'redis',
                'queue' => ['high'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
                'tries' => 2,
                'timeout' => (60 * 10) - 10,
            ],
            'horizon-workers-default-prod' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 3,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
                'tries' => 2,
                'timeout' => (60 * 10) - 10,
            ],
            'horizon-workers-low-prod' => [
                'connection' => 'redis',
                'queue' => ['low'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 2,
                'timeout' => (90 * 10),
            ],
            'horizon-workers-sieg-prod' => [
                'connection' => 'redis',
                'queue' => ['sieg'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 2,
                'timeout' => (90 * 10),
            ],
            'horizon-workers-sefaz-prod' => [
                'connection' => 'redis',
                'queue' => ['sefaz'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 2,
                'timeout' => (90 * 10),
            ],
        ],

        'development' => [
            'horizon-workers-high-dev' => [
                'connection' => 'redis',
                'queue' => ['high'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
                'tries' => 2,
                'timeout' => (60 * 10) - 10,
            ],
            'horizon-workers-default-dev' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 3,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
                'tries' => 2,
                'timeout' => (60 * 10) - 10,
            ],
            'horizon-workers-low-dev' => [
                'connection' => 'redis',
                'queue' => ['low'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 2,
                'timeout' => (90 * 10),
            ],
        ],

        'local' => [
            'horizon-workers-high' => [
                'connection' => 'redis',
                'queue' => ['high'],
                'balance' => 'auto',
                'processes' => 5,
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
                'tries' => 2,
                'timeout' => 60,
            ],
            'horizon-workers-default' => [
                'connection' => 'redis',
                'queue' => ['default'],
                'balance' => 'auto',
                'processes' => 5,
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
                'tries' => 2,
                'timeout' => 60,
            ],
            'horizon-workers-low' => [
                'connection' => 'redis',
                'queue' => ['low'],
                'balance' => 'auto',
                'processes' => 5,
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
                'tries' => 2,
                'timeout' => 60,
            ],
        ],

    ],

    'watch' => [
        'app',
        'bootstrap',
        'config/**/*.php',
        'database/**/*.php',
        'public/**/*.php',
        'resources/**/*.php',
        'routes',
        'composer.lock',
        'composer.json',
        '.env',
    ],
];
