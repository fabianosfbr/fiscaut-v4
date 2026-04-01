<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'due_date' => 'datetime',
        'urgent' => 'boolean',
        'progress' => 'integer',
        'order_column' => 'integer',
    ];
}
