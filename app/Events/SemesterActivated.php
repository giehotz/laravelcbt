<?php

namespace App\Events;

use App\Models\Semester;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SemesterActivated
{
    use Dispatchable, SerializesModels;

    public $semester;

    public function __construct(Semester $semester)
    {
        $this->semester = $semester;
    }
}
