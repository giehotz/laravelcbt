<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'setting';

    protected $fillable = [
        'nama_sekolah',
        'nss',
        'npsn',
        'alamat',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'kode_pos',
        'telp',
        'email',
        'website',
        'logo',
        'kepala_sekolah',
        'nip_kepsek',
        'running_text',
    ];

    /**
     * The boot method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache automatically on any save/update/delete operation
        static::saved(function ($setting) {
            Cache::forget('setting');
            Cache::forget('setting_attributes');
        });

        static::deleted(function ($setting) {
            Cache::forget('setting');
            Cache::forget('setting_attributes');
        });
    }

    /**
     * Get setting singleton (cached)
     */
    public static function get()
    {
        $attributes = Cache::remember('setting_attributes', 3600, function () {
            $setting = self::first();

            return $setting ? $setting->getAttributes() : [];
        });

        return (new self)->forceFill($attributes);
    }
}
