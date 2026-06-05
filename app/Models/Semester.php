<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semesters';

    protected $fillable = [
        'smt',
        'nama_smt',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
