<?php

namespace App\Http\Resources\Cbt;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RuangResource extends JsonResource
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
            'nama_ruang' => $this->nama_ruang,
            'kode_ruang' => $this->kode_ruang,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
