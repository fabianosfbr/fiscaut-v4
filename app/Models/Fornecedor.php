<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    protected $guarded = ['id'];

    protected $table = 'contabil_fornecedores';

    protected $casts = [
        'conta_contabil' => 'array',
        'descricao_conta_contabil' => 'array',
        'colunas_arquivo' => 'array',
    ];
}
