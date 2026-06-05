<?php

namespace App\Events;

use App\Models\TahunPelajaran;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TahunPelajaranActivated
{
    use Dispatchable, SerializesModels;

    public $tahunPelajaran;

    public function __construct(TahunPelajaran $tahunPelajaran)
    {
        $this->tahunPelajaran = $tahunPelajaran;
    }
}
