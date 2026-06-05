<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Ekstra extends Model
{
    protected $table = 'ekstra';

    protected $fillable = [
        'nama_ekstra',
        'kode_ekstra',
    ];
}
