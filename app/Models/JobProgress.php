<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class JobProgress extends Model
{
    use HasUuids;

    protected $table = 'job_progress';

    protected $guarded = ['id'];
}
