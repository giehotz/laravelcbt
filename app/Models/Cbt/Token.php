<?php

namespace App\Models\Cbt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $table = 'cbt_token';

    protected $fillable = [
        'token',
        'auto',
    ];

    protected $casts = [
        'auto' => 'boolean',
    ];

    public static function generate()
    {
        $tokenStr = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        $token = self::first();
        if ($token) {
            $token->update(['token' => $tokenStr]);

            return $token;
        }

        return self::create([
            'token' => $tokenStr,
            'auto' => false,
        ]);
    }
}
