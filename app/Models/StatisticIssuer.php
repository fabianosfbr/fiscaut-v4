<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatisticIssuer extends Model
{
    protected $table = 'statistic_issuers';

    protected $guarded = ['id'];

    protected $casts = [
        'data_ref' => 'date',
        'valor' => 'decimal:4',
    ];
}
