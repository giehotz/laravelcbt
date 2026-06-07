<?php

namespace App\Http\Controllers\Setting;

use App\Actions\CreateSiswaAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSiswaUserRequest;
use App\Http\Requests\UpdateSiswaUserRequest;
use App\Models\Master\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiswaUserController extends Controller
{
    /**
     * Display a listing of siswa users.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', User::class);

        $siswas = Siswa::with('user')->latest()->paginate(10);

        return Inertia::render('Setting/User/SiswaIndex', [
            'siswas' => $siswas,
            'imported_users' => session('imported_users'),
        ]);
    }

    /**
     * Download Excel template for importing Siswa.
     */
    public function downloadTemplate(): StreamedResponse
    {
        Gate::authorize('viewAny', User::class);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Siswa');

        $headers = [
            'Nama Siswa*',
            'NISN*',
            'NIS*',
            'Username*',
            'Email',
            'Jenis Kelamin (L/P)',
            'Tahun Masuk',
            'Sekolah Asal',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Agama',
            'No HP',
            'NIK',
            'Warga Negara',
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
        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

        $example = [
            'Budi Santoso',
            '0081234567',
            '10245',
            'budisantoso',
            'budi@school.sch.id',
            'L',
            '2025',
            'SMPN 1 Jakarta',
            'Jakarta',
            '2010-08-15',
            'Islam',
            '085678901234',
            '3201021508100001',
            'WNI',
        ];
        foreach ($example as $colIdx => $val) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue($colLetter.'2', $val);
        }

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_siswa.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import Siswa from Excel file.
     */
    public function import(Request $request, CreateSiswaAction $action): RedirectResponse
    {
        Gate::authorize('create', User::class);

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

            $nama = isset($row[0]) ? trim(strip_tags((string) $row[0])) : '';
            $nisn = isset($row[1]) ? trim(strip_tags((string) $row[1])) : '';
            $nis = isset($row[2]) ? trim(strip_tags((string) $row[2])) : '';
            $username = isset($row[3]) ? trim(strip_tags((string) $row[3])) : '';
            $email = isset($row[4]) ? trim(strip_tags((string) $row[4])) : '';
            $jenis_kelamin = isset($row[5]) ? trim(strip_tags((string) $row[5])) : '';
            $tahun_masuk = isset($row[6]) ? trim(strip_tags((string) $row[6])) : '';
            $sekolah_asal = isset($row[7]) ? trim(strip_tags((string) $row[7])) : '';
            $tempat_lahir = isset($row[8]) ? trim(strip_tags((string) $row[8])) : '';
            $tanggal_lahir_raw = isset($row[9]) ? trim(strip_tags((string) $row[9])) : '';
            $agama = isset($row[10]) ? trim(strip_tags((string) $row[10])) : '';
            $hp = isset($row[11]) ? trim(strip_tags((string) $row[11])) : '';
            $nik = isset($row[12]) ? trim(strip_tags((string) $row[12])) : '';
            $warga_negara = isset($row[13]) ? trim(strip_tags((string) $row[13])) : 'WNI';

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
                    $tanggal_lahir = Carbon::createFromFormat('Y-m-d', $tanggal_lahir_raw)->format('Y-m-d');
                } catch (\Exception $e) {
                    $errors[] = "Baris {$rowNum}: Format tanggal lahir harus YYYY-MM-DD.";
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
                'nik' => empty($nik) ? null : $nik,
                'warga_negara' => empty($warga_negara) ? 'WNI' : $warga_negara,
                'password' => 'password',
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
                $action->execute($data);
                $importedUsernames[] = $data['username'];
            }

            DB::table('activity_log')->insert([
                'user_id' => auth()->id(),
                'group_id' => 1,
                'group_name' => 'User Management',
                'log_type' => 1,
                'log_desc' => json_encode([
                    'message' => 'Imported '.count($importedUsernames).' siswa accounts.',
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

        return redirect()->route('setting.user.siswa.index')
            ->with('success', 'Berhasil mengimpor '.count($importedUsernames).' siswa.')
            ->with('imported_users', $validatedData);
    }

    /**
     * Store a newly created siswa.
     */
    public function store(StoreSiswaUserRequest $request, CreateSiswaAction $action): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $action->execute($request->validated());

        return redirect()->route('setting.user.siswa.index')
            ->with('success', 'Akun siswa berhasil dibuat.');
    }

    /**
     * Update the specified siswa.
     */
    public function update(UpdateSiswaUserRequest $request, Siswa $siswa): RedirectResponse
    {
        $user = $siswa->user;
        Gate::authorize('update', $user);

        $data = $request->validated();

        DB::transaction(function () use ($siswa, $user, $data) {
            // Update associated User
            $user->name = $data['nama'];
            $user->email = $data['email'] ?? null;
            $user->username = $data['username'];

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            // Update Siswa profile
            $siswa->update($data);
        });

        return redirect()->route('setting.user.siswa.index')
            ->with('success', 'Akun siswa berhasil diperbarui.');
    }

    /**
     * Delete the specified siswa.
     */
    public function destroy(Siswa $siswa): RedirectResponse
    {
        $user = $siswa->user;
        Gate::authorize('delete', $user);

        DB::transaction(function () use ($siswa, $user) {
            $siswa->delete();
            $user->delete();
        });

        return redirect()->route('setting.user.siswa.index')
            ->with('success', 'Akun siswa berhasil dihapus.');
    }
}
