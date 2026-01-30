<?php

namespace App\Models;

use App\Enums\ScheduleStatusEnum;
use App\Observers\ScheduleObserver;
use Illuminate\Console\Scheduling\ManagesFrequencies;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy(ScheduleObserver::class)]
class Schedule extends Model
{
    use ManagesFrequencies;
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    protected $fillable = [
        'command',
        'command_custom',
        'description',
        'params',
        'options',
        'options_with_value',
        'expression',
        'even_in_maintenance_mode',
        'without_overlapping',
        'on_one_server',
        'webhook_before',
        'webhook_after',
        'email_output',
        'sendmail_error',
        'sendmail_success',
        'log_success',
        'log_error',
        'status',
        'run_in_background',
        'log_filename',
        'environments',
        'max_history_count',
        'limit_history_count',
    ];

    protected $attributes = [
        'expression' => '* * * * *',
        'params' => '{}',
        'options' => '{}',
        'options_with_value' => '{}',
    ];

    protected $casts = [
        'params' => 'array',
        'options' => 'array',
        'options_with_value' => 'array',
        'environments' => 'array',
        'max_history_count' => 'integer',
        'limit_history_count' => 'boolean',
        'status' => ScheduleStatusEnum::class,
    ];

    /**
     * Creates a new instance of the model.
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

    }

    public function histories()
    {
        return $this->hasMany(ScheduleHistory::class, 'schedule_id', 'id');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', ScheduleStatusEnum::Inactive);
    }

    public function scopeActive($query)
    {
        return $query->where('status', ScheduleStatusEnum::Active);
    }

    public function getArguments(): array
    {
        $arguments = [];

        foreach (($this->params ?? []) as $argument => $value) {
            if (empty($value['value'])) {
                continue;
            }
            if (isset($value['type']) && $value['type'] === 'function') {
                eval('$arguments[$argument] = (string) '.$value['value']);
            } else {
                $arguments[$value['name'] ?? $argument] = $value['value'];
            }
        }

        return $arguments;
    }

    public function getOptions(): array
    {
        $options = collect($this->options ?? []);

        $options_with_value = $this->options_with_value ?? [];
        if (! empty($options_with_value)) {
            $options = $options->merge($options_with_value);
        }

        return $options->map(function ($value, $key) {

            if (is_array($value)) {
                if (isset($value['value'])) {
                    return '--'.($value['name'] ?? $key).'='.$value['value'];
                }

            } else {
                $value = (string) $value;

                if ($value === 'verbose') {
                    return '-v';
                }

                if (str_starts_with($value, '-')) {
                    return $value;
                }

                return "--{$value}";
            }
        })->filter()->values()->toArray();
    }

    public static function getEnvironments()
    {
        return static::whereNotNull('environments')
            ->groupBy('environments')
            ->get('environments')
            ->pluck('environments', 'environments');
    }
}
