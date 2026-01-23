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
        'filament.admin.resources.tenants.index',
        'filament.admin.resources.tenants.create',
        'filament.admin.resources.tenants.edit',
    ],
];
