<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableView extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'filters' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
