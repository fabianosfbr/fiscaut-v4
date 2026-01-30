<?php

namespace App\Observers;

use App\Enums\ScheduleStatusEnum;
use App\Models\Schedule;
use App\Services\ScheduleHistoryService;
use App\Services\ScheduleService;

class ScheduleObserver
{
    /**
     * Handle the Schedule "created" event.
     */
    public function created(Schedule $schedule): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Schedule "updated" event.
     */
    public function updated(Schedule $schedule): void
    {
        if ($schedule->limit_history_count === true && $schedule->isDirty(['limit_history_count', 'max_history_count'])) {
            ScheduleHistoryService::prune($schedule);
        }
        $this->clearCache();
    }

    /**
     * Handle the Schedule "deleted" event.
     */
    public function deleted(Schedule $schedule): void
    {
        $schedule->status = ScheduleStatusEnum::Trashed;
        $schedule->saveQuietly();
        $this->clearCache();
    }

    /**
     * Handle the Schedule "restored" event.
     */
    public function restored(Schedule $schedule): void
    {
        $schedule->status = ScheduleStatusEnum::Inactive;
        $schedule->saveQuietly();
    }

    public function saved(Schedule $schedule)
    {
        $this->clearCache();
    }

    /**
     * Handle the Schedule "force deleted" event.
     */
    public function forceDeleted(Schedule $schedule): void
    {
        //
    }

    protected function clearCache()
    {
        if (config('schedule.cache.enabled')) {
            $scheduleService = app(ScheduleService::class);
            $scheduleService->clearCache();
        }
    }
}
