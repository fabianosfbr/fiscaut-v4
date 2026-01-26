<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Permission extends Model
{
    protected $guarded = ['id'];

    // Generate slug on save
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function roles(): BelongsToMany
    {

        return $this->belongsToMany(Role::class);
    }

    public function users(): BelongsToMany
    {

        return $this->belongsToMany(User::class);
    }
}
