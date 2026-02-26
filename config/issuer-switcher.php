<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rotas Excluídas
    |--------------------------------------------------------------------------
    |
    | Aqui você pode listar os nomes das rotas onde o componente IssuerSwitcher
    | não deve ser renderizado. Isso é útil para evitar a exibição do seletor
    | em páginas de gerenciamento de empresas ou tenants.
    |
    */
    'exclude_routes' => [
        'filament.app.resources.tenants.index',
        'filament.app.resources.tenants.create',
        'filament.app.resources.tenants.edit',
    ],
];
