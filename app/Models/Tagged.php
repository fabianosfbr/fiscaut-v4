<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Tagged extends Model
{
    
    protected $table = 'tagging_tagged';

    public $timestamps = false;

    protected $cachePrefix = 'tagging_tagged';

    protected $guarded = ['id'];

    protected $casts = [
        'product' => 'json',
    ];

    public function taggable()
    {
        return $this->morphTo();
    }

    public function tag()
    {

        return $this->belongsTo(Tag::class, 'tag_id', 'id');
    }

    public function tagNamesWithCode(): array
    {
        return $this->tagged->map(function ($item) {
            return $item->tag->code.' - '.$item->tag_name;
        })->toArray();
    }
}
