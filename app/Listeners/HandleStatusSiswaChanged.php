<?php

namespace App\Listeners;

use App\Events\StatusSiswaChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class HandleStatusSiswaChanged
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(StatusSiswaChanged $event): void
    {
        $siswa = $event->siswa;
        $newStatus = $event->newStatus; // 2=Lulus, 3=Pindah, 4=Keluar

        if (in_array($newStatus, [2, 3, 4])) {
            // Nonaktifkan user account
            if ($siswa->user_id) {
                User::where('id', $siswa->user_id)->update(['is_active' => false]);
            }
            
            // Note: Data kelas_siswa and cbt_durasi_siswa are kept as history
            // unless specific business rules require deletion.
        }
    }
}
