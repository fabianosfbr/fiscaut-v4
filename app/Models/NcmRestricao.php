<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NcmRestricao extends Model
{
    protected $table = 'ncm_restricoes';

    protected $guarded = ['id'];

    protected $casts = [
        'valor_match' => 'array',
        'setores' => 'array',
        'excluir_ncm' => 'array',
        'possui_ex' => 'boolean',
    ];
}
