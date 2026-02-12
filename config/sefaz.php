<?php

return [
    'distdfe' => [
        'sleep_seconds' => env('SEFAZ_DISTDFE_SLEEP_SECONDS', 2),
        'mock' => [
            'enabled' => env('SEFAZ_DISTDFE_MOCK', true),
            'nfe_path' => env('SEFAZ_DISTDFE_MOCK_NFE_PATH', base_path('resources/mocks/sefaz/nfe_distdfe.xml')),
            'cte_path' => env('SEFAZ_DISTDFE_MOCK_CTE_PATH', base_path('resources/mocks/sefaz/cte_distdfe.xml')),
        ],
    ],
];
