<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tagging_tags';

    protected $guarded = ['id'];

    protected $with = ['category'];

    protected $appends = ['namecode'];

    public function category()
    {
        return $this->belongsTo(CategoryTag::class, 'category_id');
    }

    public function getNameCodeAttribute()
    {
        return "{$this->code} - {$this->name}";
    }
}
