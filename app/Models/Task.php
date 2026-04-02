<?php

namespace App\Models;

use App\Enums\TaskStatusCaseEnum;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Relaticle\Comments\Concerns\HasComments;
use Relaticle\Comments\Contracts\Commentable;

class Task extends Model implements Commentable
{
    use HasComments;

    protected $guarded = ['id'];

    protected $with = ['assignees'];

    protected $casts = [
        'due_date' => 'datetime',
        'urgent' => 'boolean',
        'progress' => 'integer',
        'status' => TaskStatusCaseEnum::class,
        'order_column' => 'integer',
    ];

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }
}
