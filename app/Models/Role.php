<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $guarded = ['id'];

    // Generate slug on save
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function permissions(): BelongsToMany
    {

        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {

        return $this->belongsToMany(User::class);
    }
}
