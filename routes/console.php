<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Each active branch gets its daily checklist shortly after midnight, café
// time. Needs the scheduler running (`php artisan schedule:work`, or a cron
// entry for `schedule:run`); opening the Daily checklist page also creates
// today's checklist on demand, so nothing breaks if the scheduler isn't set up.
Schedule::command('checklists:generate')
    ->dailyAt('00:05')
    ->timezone(config('app.business_timezone'));

// Same for each active branch's daily inventory sheet (opening the Daily
// inventory page also creates today's sheet on demand).
Schedule::command('inventories:generate')
    ->dailyAt('00:05')
    ->timezone(config('app.business_timezone'));
