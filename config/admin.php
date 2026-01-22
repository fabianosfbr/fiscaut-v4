<?php

return [
    'roles' => [
        '0' => 'Proprietário',
        '1' => 'Super Admin',
        '2' => 'Administrador',
        '3' => 'Contabilidade',
        '4' => 'Usuário',
    ],

    'permissions' => [
        '0' => 'Manifestar nota',
        '1' => 'Classificar nota',
        '2' => 'Marcar documento como apurado',
    ],
    'doc_types' => [
        '1' => 'NFS Tomada',
        '2' => 'Fatura',
        '3' => 'Boleto',
        '4' => 'Nota Débito',
        '5' => 'Documentos contábeis',
        '6' => 'Extrato bancário',
        '7' => 'Contratos',
        '8' => 'Planilhas de controle',
    ],

    'cnpj_ja_api_key' => env('CNPJ_JA_API_KEY'),
    'sieg_api_key' => env('SIEG_API_KEY'),
    'sieg_url' => env('SIEG_URL'),
    'footer_credits_danfe' => env('APP_FOOTER_CREDITS_DANFE', 'FiscAut Sistemas - www.fiscaut.com.br'),

    'environment' => [
        'HAMBIENTE_SEFAZ' => 1,
    ],
];
