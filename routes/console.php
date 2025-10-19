<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Smart Market Data scheduler that adjusts based on market hours
Schedule::command('market:smart-schedule')->everyFiveSeconds()->withoutOverlapping();
