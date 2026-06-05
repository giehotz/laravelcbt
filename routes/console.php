<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $files = Illuminate\Support\Facades\Storage::disk('public')->files('backups');
    foreach ($files as $file) {
        if (Illuminate\Support\Facades\Storage::disk('public')->lastModified($file) < now()->subHour()->timestamp) {
            Illuminate\Support\Facades\Storage::disk('public')->delete($file);
        }
    }
})->hourly();

Schedule::command('cbt:generate-token')->everyFifteenMinutes();
