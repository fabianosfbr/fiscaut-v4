<?php

namespace App\Models;

use App\Observers\ScheduleHistoryObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(ScheduleHistoryObserver::class)]
class ScheduleHistory extends Model
{
  /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    protected $fillable = [
        'schedule_id',
        'command',
        'params',
        'output',
        'options',
    ];

    protected $casts = [
        'params' => 'array',
        'options' => 'array',
    ];

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
    }

    public function command()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'id');
    }
}
