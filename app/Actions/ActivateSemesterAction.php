<?php

namespace App\Actions;

use App\Models\Semester;
use App\Events\SemesterActivated;
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
            \Illuminate\Support\Facades\Cache::forget('semester_active');
            \Illuminate\Support\Facades\Cache::forget('semester_active_array');

            // Dispatch event
            event(new SemesterActivated($semester));
        });
    }
}
