<?php

namespace App\Observers;

use App\Models\ScheduleHistory;
use App\Services\ScheduleHistoryService;

class ScheduleHistoryObserver
{
    /**
     * Handle the ScheduleHistory "created" event.
     */
    public function created(ScheduleHistory $scheduleHistory): void
    {
        $schedule = $scheduleHistory->command()->first();
        if ($schedule->limit_history_count) {
            ScheduleHistoryService::prune($schedule);
        }
    }

    /**
     * Handle the ScheduleHistory "updated" event.
     */
    public function updated(ScheduleHistory $scheduleHistory): void
    {
        //
    }

    /**
     * Handle the ScheduleHistory "deleted" event.
     */
    public function deleted(ScheduleHistory $scheduleHistory): void
    {
        //
    }

    /**
     * Handle the ScheduleHistory "restored" event.
     */
    public function restored(ScheduleHistory $scheduleHistory): void
    {
        //
    }

    /**
     * Handle the ScheduleHistory "force deleted" event.
     */
    public function forceDeleted(ScheduleHistory $scheduleHistory): void
    {
        //
    }
}
