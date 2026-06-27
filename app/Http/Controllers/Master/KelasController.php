<?php

namespace App\Http\Controllers\Master;

use App\Actions\CreateSiswaAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKelasRequest;
use App\Http\Requests\UpdateKelasRequest;
use App\Http\Requests\UpdateKelasStudentsRequest;
use App\Models\Master\Guru;
use App\Models\Master\Jurusan;
use App\Models\Master\Kelas;
use App\Models\Master\LevelKelas;
use App\Models\Master\Siswa;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\User;
use App\Services\KelasStudentService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KelasController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Kelas::class);

        $kelas = Kelas::with(['levelKelas', 'jurusan', 'waliKelas', 'tahunPelajaran', 'semester'])
            ->latest()
            ->paginate(10);

        $years = TahunPelajaran::latest()->get();
        $semesters = Semester::latest()->get();
        $jurusans = Jurusan::active()->get();
        $levels = LevelKelas::latest()->get();
        $gurus = Guru::latest()->get();

        return Inertia::render('Master/Kelas/Index', [
            'kelas' => $kelas,
            'years' => $years,
            'semesters' => $semesters,
            'jurusans' => $jurusans,
            'levels' => $levels,
            'gurus' => $gurus,
        ]);
    }

    public function store(StoreKelasRequest $request): RedirectResponse
    {
        Gate::authorize('create', Kelas::class);

        Kelas::create($request->validated());

        return redirect()->route('master.kelas.index')
            ->with('success', 'Rombel/Kelas berhasil ditambahkan.');
    }

    public function update(UpdateKelasRequest $request, Kelas $kelas): RedirectResponse
    {
        Gate::authorize('update', $kelas);

        $kelas->update($request->validated());

        return redirect()->route('master.kelas.index')
            ->with('success', 'Rombel/Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        Gate::authorize('delete', $kelas);

        if ($kelas->siswa()->exists()) {
            return redirect()->route('master.kelas.index')
                ->with('error', 'Kelas tidak dapat dihapus karena memiliki siswa terdaftar.');
        }

        $kelas->delete();

        return redirect()->route('master.kelas.index')
            ->with('success', 'Rombel/Kelas berhasil dihapus.');
    }

    /**
     * Show the form for managing students in the class.
     */
    public function editStudents(Kelas $kelas): Response
    {
        Gate::authorize('manageStudents', $kelas);

        $tpId = $kelas->tahun_pelajaran_id;
        $smtId = $kelas->semester_id;
        $search = request('q');

        // 1. Get students currently assigned to this class for the period
        $assigned = $kelas->siswa()
            ->wherePivot('tahun_pelajaran_id', $tpId)
            ->wherePivot('semester_id', $smtId)
            ->get();

        // 2. Query students not assigned to ANY class for the period
        $unassignedQuery = Siswa::whereDoesntHave('kelasSiswa', function ($q) use ($tpId, $smtId) {
            $q->where('tahun_pelajaran_id', $tpId)->where('semester_id', $smtId);
        });

        if ($search) {
            $unassignedQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $unassigned = $unassignedQuery->paginate(20)->withQueryString();

        return Inertia::render('Master/Kelas/ManageStudents', [
            'kelas' => $kelas->load(['levelKelas', 'jurusan', 'waliKelas', 'tahunPelajaran', 'semester']),
            'assigned' => $assigned,
            'unassigned' => $unassigned,
            'filters' => [
                'q' => $search,
            ],
        ]);
    }

    /**
     * Update student roster for the class.
     */
    public function updateStudents(
        UpdateKelasStudentsRequest $request,
        Kelas $kelas,
        KelasStudentService $service
    ): RedirectResponse {
        // FormRequest handles the authorize gate check

        try {
            $service->syncStudents(
                $kelas,
                $request->input('siswa_ids', []),
                $kelas->tahun_pelajaran_id,
                $kelas->semester_id
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('master.kelas.index')
            ->with('success', 'Anggota rombel/kelas berhasil disimpan.');
    }

    /**
     * Download Excel template for importing Siswa to this class.
     */
    public function downloadStudentsTemplate(Kelas $kelas): StreamedResponse
    {
        Gate::authorize('manageStudents', $kelas);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Siswa');

        $headers = [
            'No',
            'Nis Lokal',
            'NISN',
            'NAMA',
            'JENIS KELAMIN',
            'Tempat Lahir',
            'Tgl Lahir (dd-mm-yyyy)',
            'Password',
            'Alamat Siswa',
            'Agama',
            'Status Keluarga',
            'Anak Ke',
            'Nomor HP',
            'Sekolah Asal',
            'Tgl Terima',
            'Tingkat Awal',
            'Nama Ayah',
            'Nama Ibu',
            'Pekerjaan Ayah',
            'Pekerjaan Ibu',
            'Alamat Orang Tua',
            'Nama Wali',
            'Pekerjaan Wali',
            'Alamat Wali',
        ];

        foreach ($headers as $colIdx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue($colLetter.'1', $header);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB'],
            ],
        ];
        $sheet->getStyle('A1:X1')->applyFromArray($headerStyle);

        $example = [
            '1',
            '10245',
            '0081234567',
            'Budi Santoso',
            'L',
            'Jakarta',
            '15-08-2010',
            'password123',
            'Jl. Merdeka No 1',
            'Islam',
            'K',
            '2',
            '085678901234',
            'SMPN 1 Jakarta',
            '2025',
            '10',
            'Ayah Budi',
            'Ibu Budi',
            'Wiraswasta',
            'Ibu Rumah Tangga',
            'Jl. Merdeka No 1',
            '',
            '',
            '',
        ];
        foreach ($example as $colIdx => $val) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue($colLetter.'2', $val);
        }

        $className = Str::slug($kelas->nama_kelas);
        $filename = "template_siswa_kelas_{$className}.xlsx";

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import Siswa from Excel directly into this class.
     */
    public function importStudents(Request $request, Kelas $kelas, CreateSiswaAction $action): RedirectResponse
    {
        Gate::authorize('manageStudents', $kelas);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');

        try {
            $reader = IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file' => 'Gagal membaca file Excel. Pastikan format file sesuai.']);
        }

        if (count($rows) <= 1) {
            return redirect()->back()->withErrors(['file' => 'File Excel kosong atau hanya berisi header.']);
        }

        if (count($rows) > 1001) {
            return redirect()->back()->withErrors(['file' => 'Jumlah baris maksimal yang diperbolehkan adalah 1000 baris.']);
        }

        $dataRows = array_slice($rows, 1);
        $errors = [];
        $validatedData = [];

        $excelUsernames = [];
        $excelEmails = [];
        $excelNisns = [];
        $excelNiss = [];

        foreach ($dataRows as $index => $row) {
            $rowNum = $index + 2;

            // Stop processing if row is completely empty
            $isEmptyRow = true;
            foreach ($row as $cell) {
                if ($cell !== null && trim((string) $cell) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) {
                continue;
            }

            $nis = isset($row[1]) ? trim(strip_tags((string) $row[1])) : '';
            $nisn = isset($row[2]) ? trim(strip_tags((string) $row[2])) : '';
            $nama = isset($row[3]) ? trim(strip_tags((string) $row[3])) : '';
            $jenis_kelamin = isset($row[4]) ? trim(strip_tags((string) $row[4])) : '';
            $tempat_lahir = isset($row[5]) ? trim(strip_tags((string) $row[5])) : '';
            $tanggal_lahir_raw = isset($row[6]) ? trim(strip_tags((string) $row[6])) : '';
            $password = isset($row[7]) ? trim(strip_tags((string) $row[7])) : '';
            $alamat = isset($row[8]) ? trim(strip_tags((string) $row[8])) : '';
            $agama = isset($row[9]) ? trim(strip_tags((string) $row[9])) : '';
            $status_keluarga = isset($row[10]) ? trim(strip_tags((string) $row[10])) : '';
            $anak_ke_raw = isset($row[11]) ? trim(strip_tags((string) $row[11])) : '';
            $hp = isset($row[12]) ? trim(strip_tags((string) $row[12])) : '';
            $sekolah_asal = isset($row[13]) ? trim(strip_tags((string) $row[13])) : '';
            $tahun_masuk = isset($row[14]) ? trim(strip_tags((string) $row[14])) : '';
            $kelas_awal_raw = isset($row[15]) ? trim(strip_tags((string) $row[15])) : '';
            $nama_ayah = isset($row[16]) ? trim(strip_tags((string) $row[16])) : '';
            $nama_ibu = isset($row[17]) ? trim(strip_tags((string) $row[17])) : '';
            $pekerjaan_ayah = isset($row[18]) ? trim(strip_tags((string) $row[18])) : '';
            $pekerjaan_ibu = isset($row[19]) ? trim(strip_tags((string) $row[19])) : '';
            $alamat_ortu = isset($row[20]) ? trim(strip_tags((string) $row[20])) : '';
            $nama_wali = isset($row[21]) ? trim(strip_tags((string) $row[21])) : '';
            $pekerjaan_wali = isset($row[22]) ? trim(strip_tags((string) $row[22])) : '';
            $alamat_wali = isset($row[23]) ? trim(strip_tags((string) $row[23])) : '';

            $anak_ke = is_numeric($anak_ke_raw) ? (int) $anak_ke_raw : null;
            $kelas_awal = is_numeric($kelas_awal_raw) ? (int) $kelas_awal_raw : null;
            $username = $nis;
            $email = $username ? "{$username}@sch.id" : null;

            if (empty($nama)) {
                $errors[] = "Baris {$rowNum}: Nama Siswa tidak boleh kosong.";

                continue;
            }
            if (empty($nisn)) {
                $errors[] = "Baris {$rowNum}: NISN tidak boleh kosong.";

                continue;
            }
            if (empty($nis)) {
                $errors[] = "Baris {$rowNum}: NIS tidak boleh kosong.";

                continue;
            }
            if (empty($username)) {
                $errors[] = "Baris {$rowNum}: Username tidak boleh kosong.";

                continue;
            }

            if (! preg_match("/^[a-zA-Z\s\.,'\(\)]+$/", $nama)) {
                $errors[] = "Baris {$rowNum}: Nama Siswa mengandung karakter tidak valid.";
            }
            if (strlen($nama) > 100) {
                $errors[] = "Baris {$rowNum}: Nama Siswa maksimal 100 karakter.";
            }

            if (! preg_match('/^[0-9]{10}$/', $nisn)) {
                $errors[] = "Baris {$rowNum}: NISN harus berupa 10 digit angka.";
            }

            if (! preg_match("/^[0-9a-zA-Z\-\/]+$/", $nis)) {
                $errors[] = "Baris {$rowNum}: NIS hanya boleh berisi huruf, angka, strip, dan garis miring.";
            }
            if (strlen($nis) > 20) {
                $errors[] = "Baris {$rowNum}: NIS maksimal 20 karakter.";
            }

            if (! preg_match("/^[a-zA-Z0-9_\-\.]+$/", $username)) {
                $errors[] = "Baris {$rowNum}: Username hanya boleh mengandung huruf, angka, underscore, strip, dan titik.";
            }
            if (strlen($username) > 50) {
                $errors[] = "Baris {$rowNum}: Username maksimal 50 karakter.";
            }

            if (! empty($email)) {
                if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
                    $errors[] = "Baris {$rowNum}: Format email tidak valid.";
                }
            }

            $tanggal_lahir = null;
            if (! empty($tanggal_lahir_raw)) {
                try {
                    $tanggal_lahir = Carbon::createFromFormat('d-m-Y', $tanggal_lahir_raw)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $tanggal_lahir = Carbon::parse($tanggal_lahir_raw)->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $errors[] = "Baris {$rowNum}: Format tanggal lahir harus DD-MM-YYYY.";
                    }
                }
            }

            if (! empty($jenis_kelamin)) {
                $jenis_kelamin = strtoupper($jenis_kelamin);
                if ($jenis_kelamin !== 'L' && $jenis_kelamin !== 'P') {
                    $errors[] = "Baris {$rowNum}: Jenis kelamin harus 'L' atau 'P'.";
                }
            }

            if (in_array($username, $excelUsernames)) {
                $errors[] = "Baris {$rowNum}: Username '{$username}' terduplikat di dalam file.";
            } else {
                $excelUsernames[] = $username;
            }

            if (! empty($email)) {
                if (in_array($email, $excelEmails)) {
                    $errors[] = "Baris {$rowNum}: Email '{$email}' terduplikat di dalam file.";
                } else {
                    $excelEmails[] = $email;
                }
            }

            if (in_array($nisn, $excelNisns)) {
                $errors[] = "Baris {$rowNum}: NISN '{$nisn}' terduplikat di dalam file.";
            } else {
                $excelNisns[] = $nisn;
            }

            if (in_array($nis, $excelNiss)) {
                $errors[] = "Baris {$rowNum}: NIS '{$nis}' terduplikat di dalam file.";
            } else {
                $excelNiss[] = $nis;
            }

            $validatedData[] = [
                'nama' => $nama,
                'nisn' => $nisn,
                'nis' => $nis,
                'username' => $username,
                'email' => empty($email) ? null : $email,
                'jenis_kelamin' => empty($jenis_kelamin) ? null : $jenis_kelamin,
                'tahun_masuk' => empty($tahun_masuk) ? null : $tahun_masuk,
                'sekolah_asal' => empty($sekolah_asal) ? null : $sekolah_asal,
                'tempat_lahir' => empty($tempat_lahir) ? null : $tempat_lahir,
                'tanggal_lahir' => $tanggal_lahir,
                'agama' => empty($agama) ? null : $agama,
                'hp' => empty($hp) ? null : $hp,
                'alamat' => empty($alamat) ? null : $alamat,
                'status_keluarga' => empty($status_keluarga) ? null : $status_keluarga,
                'anak_ke' => $anak_ke,
                'kelas_awal' => $kelas_awal,
                'nama_ayah' => empty($nama_ayah) ? null : $nama_ayah,
                'nama_ibu' => empty($nama_ibu) ? null : $nama_ibu,
                'pekerjaan_ayah' => empty($pekerjaan_ayah) ? null : $pekerjaan_ayah,
                'pekerjaan_ibu' => empty($pekerjaan_ibu) ? null : $pekerjaan_ibu,
                'alamat_ayah' => empty($alamat_ortu) ? null : $alamat_ortu,
                'alamat_ibu' => empty($alamat_ortu) ? null : $alamat_ortu,
                'nama_wali' => empty($nama_wali) ? null : $nama_wali,
                'pekerjaan_wali' => empty($pekerjaan_wali) ? null : $pekerjaan_wali,
                'alamat_wali' => empty($alamat_wali) ? null : $alamat_wali,
                'nik' => null,
                'warga_negara' => 'WNI',
                'password' => empty($password) ? 'password' : $password,
                'rowNum' => $rowNum,
            ];
        }

        if (count($errors) > 0) {
            return redirect()->back()->withErrors(['import_errors' => $errors]);
        }

        $dbUsernames = User::whereIn('username', $excelUsernames)->pluck('username')->toArray();
        $dbEmails = User::whereIn('email', array_filter($excelEmails))->pluck('email')->toArray();
        $dbNisns = Siswa::whereIn('nisn', $excelNisns)->pluck('nisn')->toArray();
        $dbNiss = Siswa::whereIn('nis', $excelNiss)->pluck('nis')->toArray();

        foreach ($validatedData as $data) {
            if (in_array($data['username'], $dbUsernames)) {
                $errors[] = "Baris {$data['rowNum']}: Username '{$data['username']}' sudah digunakan di sistem.";
            }
            if ($data['email'] && in_array($data['email'], $dbEmails)) {
                $errors[] = "Baris {$data['rowNum']}: Email '{$data['email']}' sudah digunakan di sistem.";
            }
            if (in_array($data['nisn'], $dbNisns)) {
                $errors[] = "Baris {$data['rowNum']}: NISN '{$data['nisn']}' sudah digunakan di sistem.";
            }
            if (in_array($data['nis'], $dbNiss)) {
                $errors[] = "Baris {$data['rowNum']}: NIS '{$data['nis']}' sudah digunakan di sistem.";
            }
        }

        if (count($errors) > 0) {
            return redirect()->back()->withErrors(['import_errors' => $errors]);
        }

        $importedUsernames = [];

        DB::beginTransaction();
        try {
            foreach ($validatedData as $data) {
                // 1. Create student
                $siswa = $action->execute($data);
                $importedUsernames[] = $data['username'];

                // 2. Attach to class pivot table (kelas_siswa) for active period of the class
                $kelas->siswa()->attach($siswa->id, [
                    'tahun_pelajaran_id' => $kelas->tahun_pelajaran_id,
                    'semester_id' => $kelas->semester_id,
                ]);
            }

            DB::table('activity_log')->insert([
                'user_id' => auth()->id(),
                'group_id' => 1,
                'group_name' => 'User Management',
                'log_type' => 1,
                'log_desc' => json_encode([
                    'message' => 'Imported '.count($importedUsernames).' siswa accounts and assigned to class '.$kelas->nama_kelas.'.',
                    'file' => $file->getClientOriginalName(),
                    'count' => count($importedUsernames),
                    'usernames' => array_slice($importedUsernames, 0, 5),
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'device' => 'Server',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->getCode() == '23000') {
                return redirect()->back()->withErrors(['file' => 'Terjadi kesalahan duplikasi data di database: '.$e->getMessage()]);
            }

            return redirect()->back()->withErrors(['file' => 'Terjadi kesalahan database: '.$e->getMessage()]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['file' => 'Terjadi kesalahan saat memproses data: '.$e->getMessage()]);
        }

        return redirect()->back()
            ->with('success', 'Berhasil mengimpor '.count($importedUsernames).' siswa langsung ke kelas '.$kelas->nama_kelas.'.')
            ->with('imported_users', $validatedData);
    }
}
