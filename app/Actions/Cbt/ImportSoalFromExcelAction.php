<?php

namespace App\Actions\Cbt;

use App\Models\Cbt\BankSoal;
use App\Services\SoalSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportSoalFromExcelAction
{
    protected SoalSanitizer $sanitizer;

    protected const JENIS_MAP = [
        'PG' => 1,
        'Kompleks' => 2,
        'Jodohkan' => 3,
        'Isian' => 4,
        'Esai' => 5,
    ];

    public function __construct(SoalSanitizer $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    public function parse(UploadedFile $file, BankSoal $bank): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());

        $result = [
            'sheets' => [],
            'total_rows' => 0,
            'errors' => [],
            'warnings' => [],
        ];

        foreach (self::JENIS_MAP as $sheetName => $jenis) {
            if (! $spreadsheet->sheetNameExists($sheetName)) {
                $result['warnings'][] = "Sheet \"{$sheetName}\" tidak ditemukan, dilewati.";

                continue;
            }

            $sheet = $spreadsheet->getSheetByName($sheetName);
            $parsed = $this->parseSheet($sheet, $jenis, $bank);

            $result['sheets'][] = [
                'name' => $sheetName,
                'jenis' => $jenis,
                'rows' => $parsed['data'],
                'count' => $parsed['count'],
                'errors' => $parsed['errors'],
            ];
            $result['total_rows'] += $parsed['count'];
            $result['errors'] = array_merge($result['errors'], $parsed['errors']);
        }

        if (empty($result['sheets'])) {
            $result['warnings'][] = 'Tidak ada sheet valid yang ditemukan. Sheet yang didukung: PG, Kompleks, Jodohkan, Isian, Esai.';
        }

        if ($result['total_rows'] > 200) {
            $result['warnings'][] = "File berisi {$result['total_rows']} baris. Import besar mungkin lambat — pertimbangkan dibagi per batch.";
        }

        return $result;
    }

    public function execute(array $sheetsData, BankSoal $bank): array
    {
        $imported = ['pg' => 0, 'kompleks' => 0, 'jodohkan' => 0, 'isian' => 0, 'esai' => 0];
        $jenisKeyMap = [1 => 'pg', 2 => 'kompleks', 3 => 'jodohkan', 4 => 'isian', 5 => 'esai'];

        $bank->skipSyncJumlah = true;

        DB::transaction(function () use ($sheetsData, $bank, &$imported, $jenisKeyMap) {
            foreach ($sheetsData as $sheet) {
                foreach ($sheet['rows'] as $row) {
                    $data = $this->buildCreateData($row, $bank);
                    $data['jenis'] = $sheet['jenis'];

                    $soal = app(CreateSoalAction::class)->execute($bank, $data);

                    if ($sheet['jenis'] === 3 && ! empty($row['pairs'])) {
                        foreach ($row['pairs'] as $pair) {
                            $soal->pairs()->create([
                                'kiri' => $this->sanitizer->sanitize($pair['kiri']),
                                'kanan' => $this->sanitizer->sanitize($pair['kanan']),
                            ]);
                        }
                    }

                    $imported[$jenisKeyMap[(int) $sheet['jenis']]]++;
                }
            }
        });

        $bank->skipSyncJumlah = false;
        $bank->syncJumlahSoal();

        return ['total' => array_sum($imported), 'details' => $imported];
    }

    private function parseSheet(Worksheet $sheet, int $jenis, BankSoal $bank): array
    {
        return match ($jenis) {
            1, 2 => $this->parsePgKompleks($sheet, $jenis, $bank),
            3 => $this->parseJodohkan($sheet, $bank),
            4, 5 => $this->parseIsianEsai($sheet, $jenis),
            default => ['data' => [], 'count' => 0, 'errors' => []],
        };
    }

    private function parsePgKompleks(Worksheet $sheet, int $jenis, BankSoal $bank): array
    {
        $opsiFields = ['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e'];
        $headers = array_merge(['nomor_soal', 'soal'], array_slice($opsiFields, 0, $bank->opsi), ['jawaban', 'kesulitan', 'timer', 'timer_menit', 'tampilkan']);
        $rows = $this->readRows($sheet, $headers);
        $result = ['data' => [], 'count' => 0, 'errors' => []];
        $seenNomor = [];

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $error = $this->validateCommon($row, $seenNomor);

            if (! $error && $jenis === 1) {
                $jwb = strtoupper(trim($row['jawaban'] ?? ''));
                $validOps = array_slice(['A', 'B', 'C', 'D', 'E'], 0, $bank->opsi);
                if (! in_array($jwb, $validOps)) {
                    $error = 'Jawaban harus '.implode('/', $validOps).", diisi: {$row['jawaban']}";
                }
            }

            if (! $error && $jenis === 2) {
                $jawabanArr = array_map('trim', explode(',', $row['jawaban'] ?? ''));
                $validOps = array_slice(['A', 'B', 'C', 'D', 'E'], 0, $bank->opsi);
                $invalid = array_diff($jawabanArr, $validOps);
                if (! empty($invalid)) {
                    $error = 'Jawaban harus kombinasi '.implode(',', $validOps).', ditemukan: '.implode(',', $invalid);
                }
                $row['jawaban'] = $jawabanArr;
            }

            if (! $error && empty(trim($row['jawaban'] ?? '')) && $jenis === 1) {
                $error = "Jawaban wajib diisi (nomor {$row['nomor_soal']})";
            }

            $seenNomor[] = $row['nomor_soal'];

            $data = array_merge([
                'nomor_soal' => (int) ($row['nomor_soal'] ?? 0),
                'soal' => $row['soal'] ?? '',
                'jawaban' => $jenis === 2 ? ($row['jawaban'] ?? []) : (strtoupper(trim($row['jawaban'] ?? ''))),
                'kesulitan' => (int) ($row['kesulitan'] ?? 1),
                'timer' => filter_var($row['timer'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'timer_menit' => (int) ($row['timer_menit'] ?? 0),
                'tampilkan' => filter_var($row['tampilkan'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'valid' => ! $error,
                'error_msg' => $error,
            ], $this->extractOpsi($row, $bank));

            $result['data'][] = $data;
            if (! $error) {
                $result['count']++;
            } else {
                $result['errors'][] = ['row' => $rowNum, 'field' => '-', 'message' => $error];
            }
        }

        return $result;
    }

    private function parseJodohkan(Worksheet $sheet, BankSoal $bank): array
    {
        $headers = ['nomor_soal', 'soal', 'kiri', 'kanan', 'kesulitan', 'timer', 'timer_menit', 'tampilkan'];
        $rows = $this->readRows($sheet, $headers);
        $result = ['data' => [], 'count' => 0, 'errors' => []];
        $grouped = [];

        foreach ($rows as $row) {
            $no = (int) ($row['nomor_soal'] ?? 0);
            if ($no < 1) {
                $result['errors'][] = ['row' => '-', 'field' => 'nomor_soal', 'message' => 'nomor_soal harus angka positif'];

                continue;
            }
            if (! isset($grouped[$no])) {
                $grouped[$no] = [
                    'nomor_soal' => $no,
                    'soal' => '',
                    'pairs' => [],
                    'kesulitan' => 1,
                    'timer' => false,
                    'timer_menit' => 0,
                    'tampilkan' => true,
                ];
            }
            if (empty($grouped[$no]['soal']) && ! empty(trim($row['soal'] ?? ''))) {
                $grouped[$no]['soal'] = $row['soal'];
                $grouped[$no]['kesulitan'] = (int) ($row['kesulitan'] ?? 1);
                $grouped[$no]['timer'] = filter_var($row['timer'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $grouped[$no]['timer_menit'] = (int) ($row['timer_menit'] ?? 0);
                $grouped[$no]['tampilkan'] = filter_var($row['tampilkan'] ?? true, FILTER_VALIDATE_BOOLEAN);
            }
            $grouped[$no]['pairs'][] = [
                'kiri' => $row['kiri'] ?? '',
                'kanan' => $row['kanan'] ?? '',
            ];
        }

        foreach ($grouped as $no => $g) {
            $error = null;
            if (empty($g['soal'])) {
                $error = "Soal wajib diisi (nomor {$no})";
            }
            if (count($g['pairs']) < 2) {
                $error = 'Minimal 2 pasangan diperlukan, ditemukan '.count($g['pairs'])." (nomor {$no})";
            }
            foreach ($g['pairs'] as $pi => $p) {
                if (empty(trim($p['kiri']))) {
                    $error = 'Pasangan kiri ke-'.($pi + 1)." kosong (nomor {$no})";
                }
                if (empty(trim($p['kanan']))) {
                    $error = 'Pasangan kanan ke-'.($pi + 1)." kosong (nomor {$no})";
                }
            }

            $result['data'][] = [
                'nomor_soal' => $no,
                'soal' => $g['soal'],
                'pairs' => $g['pairs'],
                'jawaban' => '-',
                'kesulitan' => $g['kesulitan'],
                'timer' => $g['timer'],
                'timer_menit' => $g['timer_menit'],
                'tampilkan' => $g['tampilkan'],
                'valid' => ! $error,
                'error_msg' => $error,
            ];

            if (! $error) {
                $result['count']++;
            } else {
                $result['errors'][] = ['row' => "- (group {$no})", 'field' => '-', 'message' => $error];
            }
        }

        return $result;
    }

    private function parseIsianEsai(Worksheet $sheet, int $jenis): array
    {
        $headers = ['nomor_soal', 'soal', 'jawaban', 'kesulitan', 'timer', 'timer_menit', 'tampilkan'];
        $rows = $this->readRows($sheet, $headers);
        $result = ['data' => [], 'count' => 0, 'errors' => []];
        $seenNomor = [];

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $error = $this->validateCommon($row, $seenNomor);

            if (! $error && empty(trim($row['jawaban'] ?? ''))) {
                $error = "Jawaban wajib diisi (nomor {$row['nomor_soal']})";
            }

            $seenNomor[] = $row['nomor_soal'];

            $result['data'][] = [
                'nomor_soal' => (int) ($row['nomor_soal'] ?? 0),
                'soal' => $row['soal'] ?? '',
                'jawaban' => $row['jawaban'] ?? '',
                'kesulitan' => (int) ($row['kesulitan'] ?? 1),
                'timer' => filter_var($row['timer'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'timer_menit' => (int) ($row['timer_menit'] ?? 0),
                'tampilkan' => filter_var($row['tampilkan'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'valid' => ! $error,
                'error_msg' => $error,
            ];

            if (! $error) {
                $result['count']++;
            } else {
                $result['errors'][] = ['row' => $rowNum, 'field' => '-', 'message' => $error];
            }
        }

        return $result;
    }

    private function validateCommon(array $row, array &$seenNomor): ?string
    {
        $no = $row['nomor_soal'] ?? null;
        if ($no === null || $no === '' || ! is_numeric($no) || (int) $no < 1) {
            return 'nomor_soal wajib diisi dengan angka positif';
        }
        if (in_array((int) $no, $seenNomor)) {
            return "nomor_soal {$no} duplikat dalam sheet yang sama";
        }
        if (empty(trim($row['soal'] ?? ''))) {
            return "Soal wajib diisi (nomor {$no})";
        }

        return null;
    }

    private function extractOpsi(array $row, BankSoal $bank): array
    {
        $opsiFields = ['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e'];
        $result = [];
        for ($i = 0; $i < $bank->opsi; $i++) {
            $result[$opsiFields[$i]] = $row[$opsiFields[$i]] ?? '';
        }

        return $result;
    }

    private function readRows(Worksheet $sheet, array $headers): array
    {
        $highestRow = $sheet->getHighestRow();
        $rows = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $data = [];
            $allEmpty = true;

            foreach ($headers as $colIdx => $header) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                $val = $sheet->getCell($colLetter.$row)->getCalculatedValue();
                $data[$header] = $val !== null ? trim((string) $val) : '';
                if ($data[$header] !== '') {
                    $allEmpty = false;
                }
            }

            if (! $allEmpty) {
                $rows[] = $data;
            }
        }

        return $rows;
    }

    private function buildCreateData(array $row, BankSoal $bank): array
    {
        $data = [
            'nomor_soal' => (int) ($row['nomor_soal'] ?? 0),
            'soal' => $row['soal'] ?? '',
            'kesulitan' => (int) ($row['kesulitan'] ?? 1),
            'timer' => (bool) ($row['timer'] ?? false),
            'timer_menit' => (int) ($row['timer_menit'] ?? 0),
            'tampilkan' => (bool) ($row['tampilkan'] ?? true),
        ];

        foreach (['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e'] as $field) {
            if (isset($row[$field])) {
                $data[$field] = $row[$field];
            }
        }

        if (isset($row['pairs'])) {
            $data['jawaban'] = $row['pairs'];
        } elseif (isset($row['jawaban'])) {
            $data['jawaban'] = $row['jawaban'];
        }

        return $data;
    }
}
