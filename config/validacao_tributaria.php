<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Regras de Validação Tributária
    |--------------------------------------------------------------------------
    |
    | Define as regras de validação disponíveis e se estão ativas por padrão.
    | Cada issuer pode sobrescrever estas configurações via tabela específica
    | ou general setting.
    |
    */

    'regras' => [
        'cst_vs_regime' => [
            'nome' => 'CST vs Regime Tributário',
            'descricao' => 'Valida se o CST/CSOSN dos produtos é compatível com o regime tributário do emitente.',
            'ativo' => true,
            'severidade' => 'aviso',
        ],
        'calculo_icms' => [
            'nome' => 'Cálculo ICMS',
            'descricao' => 'Valida se vICMS ≈ vBC × pICMS / 100 para cada produto.',
            'ativo' => true,
            'severidade' => 'aviso',
        ],
        'calculo_ipi' => [
            'nome' => 'Cálculo IPI',
            'descricao' => 'Valida se vIPI ≈ vBC_IPI × pIPI / 100 para cada produto com IPI.',
            'ativo' => true,
            'severidade' => 'aviso',
        ],
        'calculo_pis' => [
            'nome' => 'Cálculo PIS',
            'descricao' => 'Valida se vPIS ≈ vBC_PIS × pPIS / 100 para cada produto.',
            'ativo' => true,
            'severidade' => 'aviso',
        ],
        'calculo_cofins' => [
            'nome' => 'Cálculo COFINS',
            'descricao' => 'Valida se vCOFINS ≈ vBC_COFINS × pCOFINS / 100 para cada produto.',
            'ativo' => true,
            'severidade' => 'aviso',
        ],
        'difal' => [
            'nome' => 'DIFAL',
            'descricao' => 'Valida se DIFAL foi calculado para operações interestaduais com ICMS tributado.',
            'ativo' => true,
            'severidade' => 'aviso',
        ],
        'totais_vs_produtos' => [
            'nome' => 'Totais vs Produtos',
            'descricao' => 'Valida se os valores do cabeçalho batem com a soma dos produtos.',
            'ativo' => true,
            'severidade' => 'aviso',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tolerância para diferenças de arredondamento
    |--------------------------------------------------------------------------
    |
    | Valor máximo de diferença aceita entre o valor calculado e o informado
    | sem gerar validação.
    |
    */
    'tolerancia' => 0.01,

    /*
    |--------------------------------------------------------------------------
    | Configurações por Issuer
    |--------------------------------------------------------------------------
    |
    | Array de issuer_id => regras ativas específicas.
    | Exemplo: [1 => ['cst_vs_regime' => false, 'calculo_icms' => true]]
    |
    */
    'por_issuer' => [],
];
