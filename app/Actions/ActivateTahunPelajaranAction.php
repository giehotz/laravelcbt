<?php

namespace App\Actions;

use App\Events\TahunPelajaranActivated;
use App\Models\TahunPelajaran;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ActivateTahunPelajaranAction
{
    /**
     * Execute the action to activate a TahunPelajaran and deactivate all others in a transaction.
     */
    public function execute(TahunPelajaran $tahunPelajaran): void
    {
        DB::transaction(function () use ($tahunPelajaran) {
            // Deactivate all academic years
            TahunPelajaran::query()->update(['active' => false]);

            // Activate the specific academic year
            $tahunPelajaran->update(['active' => true]);

            // Clear cache
            Cache::forget('tp_active');
            Cache::forget('tp_active_array');

            // Dispatch event
            event(new TahunPelajaranActivated($tahunPelajaran));
        });
    }
}
