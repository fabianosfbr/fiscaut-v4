<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTag extends Model
{
    protected $table = 'categories_tag';

    protected $guarded = ['id'];

    public function issuer()
    {
        return $this->belongsTo(Issuer::class);
    }

    public function tags()
    {
        return $this->hasMany(Tag::class, 'category_id');
    }
}
