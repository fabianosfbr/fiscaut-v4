<?php

namespace App\Models;

use App\Enums\TaskStatusCaseEnum;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Relaticle\Comments\Concerns\HasComments;
use Relaticle\Comments\Contracts\Commentable;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Task extends Model implements Commentable, Sortable
{
    use HasComments, SortableTrait;

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
