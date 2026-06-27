<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SoalKoreksiResource extends JsonResource
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
            'jenis_soal' => $this->jenis_soal,
            'no_soal_alias' => $this->no_soal_alias,
            'opsi_alias_a' => $this->opsi_alias_a,
            'opsi_alias_b' => $this->opsi_alias_b,
            'opsi_alias_c' => $this->opsi_alias_c,
            'opsi_alias_d' => $this->opsi_alias_d,
            'opsi_alias_e' => $this->opsi_alias_e,
            'jawaban_siswa' => $this->jawaban_siswa,
            'ragu_ragu' => $this->ragu_ragu ?? false,
            'soal_end' => $this->soal_end,
            'jawaban_benar' => $this->jawaban_benar,
            'jawaban_alias' => $this->jawaban_alias,
            'soal' => $this->whenLoaded('soal', function () {
                $data = [
                    'soal' => $this->soal->soal,
                    'deskripsi' => $this->soal->deskripsi,
                    'opsi_a' => $this->soal->opsi_a,
                    'opsi_b' => $this->soal->opsi_b,
                    'opsi_c' => $this->soal->opsi_c,
                    'opsi_d' => $this->soal->opsi_d,
                    'opsi_e' => $this->soal->opsi_e,
                    'file1' => $this->soal->file1,
                    'fileA' => $this->soal->fileA,
                    'fileB' => $this->soal->fileB,
                    'fileC' => $this->soal->fileC,
                    'fileD' => $this->soal->fileD,
                    'fileE' => $this->soal->fileE,
                ];

                if ((int) $this->soal->jenis === 3) {
                    $this->soal->loadMissing('pairs');

                    // Shuffle left items with original IDs
                    $data['matching_left'] = $this->soal->pairs->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'text' => strip_tags(html_entity_decode($p->kiri)),
                        ];
                    })->shuffle()->values()->toArray();

                    // Shuffle right items with original IDs
                    $data['matching_right'] = $this->soal->pairs->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'text' => strip_tags(html_entity_decode($p->kanan)),
                        ];
                    })->shuffle()->values()->toArray();
                }

                return $data;
            }),
        ];
    }
}
