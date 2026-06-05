<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'dari_user_id',
        'dari_group',
        'kepada',
        'text',
    ];

    protected $casts = [
        'kepada' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }
}
