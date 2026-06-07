<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SoalSiswaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $this->durasi will be loaded when we fetch the questions
        // and $this->jadwal will also be loaded
        $bolehLihatKunci = $this->whenLoaded('jadwal', function () {
            // Kita butuh tau status ujian siswa. Bisa di-pass lewat parameter request
            // atau lewat relasi tambahan. Anggap saja kita load relasi durasi jika ini endpoint siswa.
            // Jika guru yang koreksi, bisa load khusus atau tidak pakai resource ini.
            $durasi = $this->siswa ? $this->siswa->durasi()->where('jadwal_id', $this->jadwal_id)->first() : null;

            // Aturan kondisional sesuai review user:
            if ($durasi && $durasi->status === 2 && $this->jadwal->hasil_tampil) {
                return true;
            }
            // Juga jika guru yang koreksi bisa dicek dari role
            if (auth()->check() && (auth()->user()->hasRole('guru') || auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin'))) {
                return true;
            }

            return false;
        }, false);

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
            'jawaban_benar' => $bolehLihatKunci ? $this->jawaban_benar : null,
            'jawaban_alias' => $bolehLihatKunci ? $this->jawaban_alias : null,
        ];
    }
}
