<?php

return [
    'distdfe' => [
        'sleep_seconds' => env('SEFAZ_DISTDFE_SLEEP_SECONDS', 2),
        'mock' => [
            'enabled' => env('SEFAZ_DISTDFE_MOCK', true),
            'path' => env('SEFAZ_DISTDFE_MOCK_PATH', base_path('resources/mocks/sefaz/distdfe.xml')),
        ],
    ],
];

