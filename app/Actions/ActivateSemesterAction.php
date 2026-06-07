<?php

namespace App\Actions;

use App\Events\SemesterActivated;
use App\Models\Semester;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ActivateSemesterAction
{
    /**
     * Execute the action to activate a Semester and deactivate all others in a transaction.
     */
    public function execute(Semester $semester): void
    {
        DB::transaction(function () use ($semester) {
            // Deactivate all semesters
            Semester::query()->update(['active' => false]);

            // Activate the specific semester
            $semester->update(['active' => true]);

            // Clear cache
            Cache::forget('semester_active');
            Cache::forget('semester_active_array');

            // Dispatch event
            event(new SemesterActivated($semester));
        });
    }
}
