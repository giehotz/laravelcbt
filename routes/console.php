<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $files = Storage::disk('public')->files('backups');
    foreach ($files as $file) {
        if (Storage::disk('public')->lastModified($file) < now()->subHour()->timestamp) {
            Storage::disk('public')->delete($file);
        }
    }
})->hourly();

Schedule::command('cbt:generate-token')->everyFifteenMinutes();
