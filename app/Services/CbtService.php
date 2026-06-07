<?php

namespace App\Services;

use App\Models\Cbt\DurasiSiswa;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\Nilai;
use App\Models\Cbt\SoalSiswa;
use App\Models\Master\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CbtService
{
    /**
     * Distribusi soal dari bank soal ke siswa untuk jadwal tertentu.
     * Menggunakan pessimistic lock via DB::transaction dan lockForUpdate
     * untuk mencegah race condition (misal klik ganda dari siswa).
     */
    public function distribusiSoal(Siswa $siswa, Jadwal $jadwal): void
    {
        DB::transaction(function () use ($siswa, $jadwal) {
            // 1. Dapatkan atau buat sesi durasi siswa dengan pessimistic lock
            // Ini akan mengunci baris (atau proses create) sehingga request konkuren akan menunggu.
            $durasi = DurasiSiswa::where('siswa_id', $siswa->id)
                ->where('jadwal_id', $jadwal->id)
                ->lockForUpdate()
                ->first();

            if (! $durasi) {
                $durasi = DurasiSiswa::create([
                    'siswa_id' => $siswa->id,
                    'jadwal_id' => $jadwal->id,
                    'status' => 0,
                    'reset' => 0,
                ]);
            }

            // 2. Cek apakah distribusi sudah pernah dilakukan (mencegah duplikasi soal jika reset=2 atau reload)
            $sudahDistribusi = SoalSiswa::where('siswa_id', $siswa->id)
                ->where('jadwal_id', $jadwal->id)
                ->exists();

            if ($sudahDistribusi) {
                return; // Sudah didistribusi, tidak perlu re-distribusi
            }

            // 3. Ambil soal dari bank soal
            $soals = $jadwal->bankSoal->soals()->get();

            if ($soals->isEmpty()) {
                return; // Bank soal kosong
            }

            // Pisahkan berdasarkan jenis soal agar bisa diacak per kelompok (jika acak_soal aktif)
            // Jenis: 1=PG, 2=Ganda Kompleks, 3=Menjodohkan, 4=Isian Singkat, 5=Uraian/Esai
            $groupedSoals = $soals->groupBy('jenis');

            $nomorUrut = 1;
            $soalSiswaInsertData = [];

            foreach ($groupedSoals as $jenis => $soalsPerJenis) {
                // Acak soal jika disetting di jadwal
                if ($jadwal->acak_soal) {
                    $soalsPerJenis = $soalsPerJenis->shuffle();
                }

                foreach ($soalsPerJenis as $index => $soal) {
                    // Acak Opsi (hanya berlaku untuk PG dan sejenisnya yang punya opsi A-E)
                    // Kita mapping A=>C, B=>A, C=>D, D=>B, E=>E
                    $opsiAlias = ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E'];

                    if ($jadwal->acak_opsi && in_array($jenis, [1, 2])) {
                        // Opsi yang ada dari soal asli (misal soal punya sampai opsi E, atau cuma D)
                        $availableOpsis = [];
                        if ($soal->opsi_a || $soal->fileA) {
                            $availableOpsis[] = 'A';
                        }
                        if ($soal->opsi_b || $soal->fileB) {
                            $availableOpsis[] = 'B';
                        }
                        if ($soal->opsi_c || $soal->fileC) {
                            $availableOpsis[] = 'C';
                        }
                        if ($soal->opsi_d || $soal->fileD) {
                            $availableOpsis[] = 'D';
                        }
                        if ($soal->opsi_e || $soal->fileE) {
                            $availableOpsis[] = 'E';
                        }

                        if (count($availableOpsis) > 0) {
                            $shuffled = collect($availableOpsis)->shuffle()->toArray();
                            // Mapping original => shuffled (alias)
                            foreach ($availableOpsis as $i => $originalOpsi) {
                                $opsiAlias[$originalOpsi] = $shuffled[$i];
                            }
                        }
                    }

                    // Tentukan jawaban benar setelah dialias (jika soal PG murni / PG Kompleks)
                    $jawabanAlias = null;
                    if ($jenis == 1 && $soal->jawaban) {
                        $jawabanAlias = $opsiAlias[$soal->jawaban] ?? $soal->jawaban;
                    } elseif ($jenis == 2 && $soal->jawaban) {
                        $jawabanArr = is_array($soal->jawaban) ? $soal->jawaban : json_decode($soal->jawaban, true);
                        if (is_array($jawabanArr)) {
                            $mapped = [];
                            foreach ($jawabanArr as $opt) {
                                $mapped[] = $opsiAlias[$opt] ?? $opt;
                            }
                            sort($mapped);
                            $jawabanAlias = json_encode($mapped);
                        }
                    }

                    // Untuk Kompleks/Jodohkan/Isian, logika alias bisa lebih kompleks,
                    // tapi standarnya kita tetapkan mapping $opsiAlias ke field

                    $isLast = false;
                    // Nanti kita set soal_end = true untuk soal terakhir dari seluruh distribusi.
                    // Di sini kita catat dulu.

                    $soalSiswaInsertData[] = [
                        'id' => (string) Str::ulid(),
                        'bank_id' => $jadwal->bank_id,
                        'jadwal_id' => $jadwal->id,
                        'soal_id' => $soal->id,
                        'siswa_id' => $siswa->id,
                        'jenis_soal' => $jenis,
                        'no_soal_alias' => $nomorUrut++,
                        'opsi_alias_a' => $opsiAlias['A'] ?? null,
                        'opsi_alias_b' => $opsiAlias['B'] ?? null,
                        'opsi_alias_c' => $opsiAlias['C'] ?? null,
                        'opsi_alias_d' => $opsiAlias['D'] ?? null,
                        'opsi_alias_e' => $opsiAlias['E'] ?? null,
                        'jawaban_benar' => is_array($soal->jawaban) ? json_encode($soal->jawaban) : $soal->jawaban, // Cache jawaban benar ori (tetap tersembunyi)
                        'jawaban_alias' => $jawabanAlias, // Jawaban benar versi sudah ter-alias
                        'soal_end' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (! empty($soalSiswaInsertData)) {
                // Tandai soal terakhir
                $lastIndex = count($soalSiswaInsertData) - 1;
                $soalSiswaInsertData[$lastIndex]['soal_end'] = true;

                // Bulk insert
                // Gunakan chunk jika bank soal sangat besar, tapi biasanya maksimal ~100 soal per mapel
                SoalSiswa::insert($soalSiswaInsertData);
            }
        });
    }

    /**
     * Hitung nilai otomatis untuk ujian siswa.
     */
    public function hitungNilai(Siswa $siswa, Jadwal $jadwal): void
    {
        DB::transaction(function () use ($siswa, $jadwal) {
            $soalSiswas = SoalSiswa::with('soal.pairs')
                ->where('siswa_id', $siswa->id)
                ->where('jadwal_id', $jadwal->id)
                ->get();

            if ($soalSiswas->isEmpty()) {
                return;
            }

            $bank = $jadwal->bankSoal;

            $pgBenar = 0;
            $pgNilai = 0.0;
            $kompleksNilai = 0.0;
            $kompleksFractionSum = 0.0;
            $jodohkanNilai = 0.0;
            $jodohkanFractionSum = 0.0;
            $isianNilai = 0.0;

            // Evaluasi jawaban per tipe soal
            foreach ($soalSiswas as $ss) {
                // 1 = PG
                if ($ss->jenis_soal == 1) {
                    if ($ss->jawaban_siswa && $ss->jawaban_siswa === $ss->jawaban_alias) {
                        $pgBenar++;
                    }
                }

                // 2 = Ganda Kompleks
                if ($ss->jenis_soal == 2) {
                    $jawabanSiswa = json_decode($ss->jawaban_siswa, true) ?? [];
                    $jawabanBenar = json_decode($ss->jawaban_alias, true) ?? []; // Map with student alias

                    sort($jawabanSiswa);
                    sort($jawabanBenar);

                    if ($bank->skoring_kompleks === 'partial') {
                        $totalKeys = count($jawabanBenar);
                        if ($totalKeys > 0) {
                            $correct = 0;
                            $incorrect = 0;
                            foreach ($jawabanSiswa as $item) {
                                if (in_array($item, $jawabanBenar)) {
                                    $correct++;
                                } else {
                                    $incorrect++;
                                }
                            }
                            $qScore = ($correct - $incorrect) / $totalKeys;
                            $kompleksFractionSum += max(0.0, $qScore);
                        }
                    } else {
                        // All or Nothing
                        if (! empty($jawabanBenar) && $jawabanSiswa === $jawabanBenar) {
                            $kompleksFractionSum += 1.0;
                        }
                    }
                }

                // 3 = Menjodohkan
                if ($ss->jenis_soal == 3) {
                    $validPairIds = $ss->soal->pairs->pluck('id')->toArray();
                    $totalPairs = count($validPairIds);

                    if ($totalPairs > 0 && $ss->jawaban_siswa) {
                        $connections = json_decode($ss->jawaban_siswa, true);
                        if (is_array($connections)) {
                            $correctMatches = 0;
                            foreach ($connections as $conn) {
                                $kiriId = isset($conn['kiri_id']) ? (int) $conn['kiri_id'] : null;
                                $kananId = isset($conn['kanan_id']) ? (int) $conn['kanan_id'] : null;

                                if ($kiriId && $kananId && in_array($kiriId, $validPairIds) && in_array($kananId, $validPairIds)) {
                                    if ($kiriId === $kananId) {
                                        $correctMatches++;
                                    }
                                }
                            }
                            $jodohkanFractionSum += ($correctMatches / $totalPairs);
                        }
                    }
                }
            }

            // Kalkulasi skor akhir berdasar bobot di bank_soal
            if ($bank->jml_pg > 0) {
                $bobotPg = $bank->bobot_pg ?? 0;
                $pgNilai = ($pgBenar / $bank->jml_pg) * $bobotPg;
            }

            if ($bank->jml_kompleks > 0) {
                $bobotKompleks = $bank->bobot_kompleks ?? 0;
                $kompleksNilai = ($kompleksFractionSum / $bank->jml_kompleks) * $bobotKompleks;
            }

            if ($bank->jml_jodohkan > 0) {
                $bobotJodohkan = $bank->bobot_jodohkan ?? 0;
                $jodohkanNilai = ($jodohkanFractionSum / $bank->jml_jodohkan) * $bobotJodohkan;
            }

            $total = $pgNilai + $kompleksNilai + $jodohkanNilai + $isianNilai;
            // Esai belum dinilai, maka esai_nilai = 0, dikoreksi = false

            // Simpan ke Nilai
            Nilai::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'jadwal_id' => $jadwal->id,
                ],
                [
                    'pg_benar' => $pgBenar,
                    'pg_nilai' => number_format($pgNilai, 2, '.', ''),
                    'kompleks_nilai' => number_format($kompleksNilai, 2, '.', ''),
                    'jodohkan_nilai' => number_format($jodohkanNilai, 2, '.', ''),
                    'isian_nilai' => number_format($isianNilai, 2, '.', ''),
                    'esai_nilai' => '0',
                    'dikoreksi' => false,
                ]
            );
        });
    }
}
