<?php

namespace App\Http\Resources\Cbt;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SesiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_sesi' => $this->nama_sesi,
            'kode_sesi' => $this->kode_sesi,
            // Format time correctly for input type="time"
            'waktu_mulai' => $this->waktu_mulai ? Carbon::parse($this->waktu_mulai)->format('H:i') : null,
            'waktu_akhir' => $this->waktu_akhir ? Carbon::parse($this->waktu_akhir)->format('H:i') : null,
            'aktif' => $this->aktif,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
