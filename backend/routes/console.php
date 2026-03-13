<?php

use App\Jobs\ProcessSubscriptionRenewals;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Process subscription auto-renewals every day at 02:00
// Picks up subscriptions expiring within the next 8 days (GC settlement window)
Schedule::job(new ProcessSubscriptionRenewals())->dailyAt('02:00');
