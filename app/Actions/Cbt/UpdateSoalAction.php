<?php

namespace App\Actions\Cbt;

use App\Models\Cbt\Soal;
use App\Services\SoalSanitizer;
use Illuminate\Support\Facades\DB;

class UpdateSoalAction
{
    protected SoalSanitizer $sanitizer;

    public function __construct(SoalSanitizer $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    public function execute(Soal $soal, array $data): Soal
    {
        // Sanitize all HTML fields
        if (isset($data['soal'])) {
            $data['soal'] = $this->sanitizer->sanitize($data['soal']);
        }

        $opsiFields = ['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e'];
        foreach ($opsiFields as $opsi) {
            if (isset($data[$opsi])) {
                $data[$opsi] = $this->sanitizer->sanitize($data[$opsi]);
            }
        }

        $jenis = isset($data['jenis']) ? (int) $data['jenis'] : (int) $soal->jenis;
        $pairs = [];

        if ($jenis === 3 && isset($data['jawaban']) && is_array($data['jawaban'])) {
            $pairs = $data['jawaban'];
            $data['jawaban'] = null;
        }

        return DB::transaction(function () use ($soal, $data, $jenis, $pairs) {
            $soal->update($data);

            if ($jenis === 3) {
                // Clear old pairs
                $soal->pairs()->delete();

                // Re-insert new pairs
                if (! empty($pairs)) {
                    foreach ($pairs as $pair) {
                        $soal->pairs()->create([
                            'kiri' => $this->sanitizer->sanitize($pair['kiri']),
                            'kanan' => $this->sanitizer->sanitize($pair['kanan']),
                        ]);
                    }
                }
            }

            return $soal;
        });
    }
}
