<?php

namespace App\Events;

use App\Models\Master\Siswa;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatusSiswaChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $siswa;
    public $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Siswa $siswa, int $newStatus)
    {
        $this->siswa = $siswa;
        $this->newStatus = $newStatus;
    }
}
