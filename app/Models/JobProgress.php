<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JobProgress extends Model
{
    use HasUuids;

    protected $table = 'job_progress';

    protected $guarded = ['id'];
}
