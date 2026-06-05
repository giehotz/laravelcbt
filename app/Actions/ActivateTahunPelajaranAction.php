<?php

namespace App\Actions;

use App\Models\TahunPelajaran;
use App\Events\TahunPelajaranActivated;
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
            \Illuminate\Support\Facades\Cache::forget('tp_active');
            \Illuminate\Support\Facades\Cache::forget('tp_active_array');

            // Dispatch event
            event(new TahunPelajaranActivated($tahunPelajaran));
        });
    }
}
