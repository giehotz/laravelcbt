<?php

namespace App\Actions\Cbt;

use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Soal;
use App\Services\SoalSanitizer;
use Illuminate\Support\Facades\DB;

class CreateSoalAction
{
    protected SoalSanitizer $sanitizer;

    public function __construct(SoalSanitizer $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    public function execute(BankSoal $bank, array $data): Soal
    {
        // Sanitize all HTML fields
        $data['soal'] = $this->sanitizer->sanitize($data['soal']);
        
        $opsiFields = ['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e'];
        foreach ($opsiFields as $opsi) {
            if (isset($data[$opsi])) {
                $data[$opsi] = $this->sanitizer->sanitize($data[$opsi]);
            }
        }

        $data['bank_id'] = $bank->id;
        $data['mapel_id'] = $bank->mapel_id;

        $jenis = (int) $data['jenis'];
        $pairs = [];

        if ($jenis === 3 && isset($data['jawaban']) && is_array($data['jawaban'])) {
            $pairs = $data['jawaban'];
            $data['jawaban'] = null;
        }

        return DB::transaction(function () use ($data, $jenis, $pairs) {
            $soal = Soal::create($data);

            if ($jenis === 3 && !empty($pairs)) {
                foreach ($pairs as $pair) {
                    $soal->pairs()->create([
                        'kiri' => $this->sanitizer->sanitize($pair['kiri']),
                        'kanan' => $this->sanitizer->sanitize($pair['kanan']),
                    ]);
                }
            }

            return $soal;
        });
    }
}
