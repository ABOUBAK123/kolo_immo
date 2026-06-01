<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Process search alerts every day at 8:00 AM
Schedule::command('alerts:process')->dailyAt('08:00');

// Send renewal reminders every day at 9:00 AM
Schedule::command('renewals:remind')->dailyAt('09:00');

// Send checkout reminders (7 days and 1 day before check-out) at 7:30 AM
Schedule::command('checkout:remind')->dailyAt('07:30');
