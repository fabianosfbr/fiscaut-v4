<?php

namespace App\Services;

use App\Models\Schedule;

class ScheduleService
{

    private $model;

    public function __construct()
    {
        $this->model = new Schedule();
    }

    public function getActives()
    {
        if (config('schedule.cache.enabled')) {
            return $this->getFromCache();
        }
        return $this->model->active()->get();
    }

    public function clearCache()
    {
        $store = config('schedule.cache.store');
        $key = config('schedule.cache.key');

        cache()->store($store)->forget($key);
    }

    private function getFromCache()
    {
        $store = config('schedule.cache.store');
        $key = config('schedule.cache.key');

        return cache()->store($store)->remember($key, config('schedule.cache.ttl'), function () {
            return $this->model->active()->get();
        });
    }
}
