<?php

namespace App\Http\Controllers\Setting;

use App\Actions\CreateGuruAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuruUserRequest;
use App\Http\Requests\UpdateGuruUserRequest;
use App\Models\Master\Guru;
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

class GuruUserController extends Controller
{
    /**
     * Display a listing of guru users.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', User::class);

        $gurus = Guru::with('user')->latest()->paginate(10);

        return Inertia::render('Setting/User/GuruIndex', [
            'gurus' => $gurus,
            'imported_users' => session('imported_users'),
        ]);
    }

    /**
     * Download Excel template for importing Guru.
     */
    public function downloadTemplate(): StreamedResponse
    {
        Gate::authorize('viewAny', User::class);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Guru');

        $headers = [
            'Nama Guru*',
            'Username*',
            'Email',
            'NIP',
            'Kode Guru',
            'No KTP',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Jenis Kelamin (L/P)',
            'Agama',
            'No HP',
            'Alamat',
            'NUPTK',
            'Jenis PTK',
            'Status Pegawai',
            'Status Aktif',
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
        $sheet->getStyle('A1:P1')->applyFromArray($headerStyle);

        $example = [
            'Ahmad Syarif, S.Pd.',
            'ahmadsyarif',
            'ahmad@school.sch.id',
            '198506122010011002',
            'GR01',
            '3201020304050607',
            'Jakarta',
            '1985-06-12',
            'L',
            'Islam',
            '081234567890',
            'Jl. Raya No. 45',
            '1234567890123456',
            'Guru Kelas',
            'PNS',
            'Aktif',
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
            'Content-Disposition' => 'attachment; filename="template_guru.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import Guru from Excel file.
     */
    public function import(Request $request, CreateGuruAction $action): RedirectResponse
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
        $excelCodes = [];

        foreach ($dataRows as $index => $row) {
            $rowNum = $index + 2;

            $nama = isset($row[0]) ? trim(strip_tags((string) $row[0])) : '';
            $username = isset($row[1]) ? trim(strip_tags((string) $row[1])) : '';
            $email = isset($row[2]) ? trim(strip_tags((string) $row[2])) : '';
            $nip = isset($row[3]) ? trim(strip_tags((string) $row[3])) : '';
            $kode_guru = isset($row[4]) ? trim(strip_tags((string) $row[4])) : '';
            $no_ktp = isset($row[5]) ? trim(strip_tags((string) $row[5])) : '';
            $tempat_lahir = isset($row[6]) ? trim(strip_tags((string) $row[6])) : '';
            $tgl_lahir_raw = isset($row[7]) ? trim(strip_tags((string) $row[7])) : '';
            $jenis_kelamin = isset($row[8]) ? trim(strip_tags((string) $row[8])) : '';
            $agama = isset($row[9]) ? trim(strip_tags((string) $row[9])) : '';
            $no_hp = isset($row[10]) ? trim(strip_tags((string) $row[10])) : '';
            $alamat = isset($row[11]) ? trim(strip_tags((string) $row[11])) : '';
            $nuptk = isset($row[12]) ? trim(strip_tags((string) $row[12])) : '';
            $jenis_ptk = isset($row[13]) ? trim(strip_tags((string) $row[13])) : '';
            $status_pegawai = isset($row[14]) ? trim(strip_tags((string) $row[14])) : '';
            $status_aktif = isset($row[15]) ? trim(strip_tags((string) $row[15])) : 'Aktif';

            if (empty($nama)) {
                $errors[] = "Baris {$rowNum}: Nama Guru tidak boleh kosong.";

                continue;
            }
            if (empty($username)) {
                $errors[] = "Baris {$rowNum}: Username tidak boleh kosong.";

                continue;
            }

            if (! preg_match("/^[a-zA-Z\s\.,'\(\)]+$/", $nama)) {
                $errors[] = "Baris {$rowNum}: Nama Guru mengandung karakter tidak valid.";
            }
            if (strlen($nama) > 100) {
                $errors[] = "Baris {$rowNum}: Nama Guru maksimal 100 karakter.";
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

            $tgl_lahir = null;
            if (! empty($tgl_lahir_raw)) {
                try {
                    $tgl_lahir = Carbon::createFromFormat('Y-m-d', $tgl_lahir_raw)->format('Y-m-d');
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

            if (! empty($kode_guru)) {
                if (in_array($kode_guru, $excelCodes)) {
                    $errors[] = "Baris {$rowNum}: Kode Guru '{$kode_guru}' terduplikat di dalam file.";
                } else {
                    $excelCodes[] = $kode_guru;
                }
            }

            $validatedData[] = [
                'nama_guru' => $nama,
                'username' => $username,
                'email' => empty($email) ? null : $email,
                'nip' => empty($nip) ? null : $nip,
                'kode_guru' => empty($kode_guru) ? null : $kode_guru,
                'no_ktp' => empty($no_ktp) ? null : $no_ktp,
                'tempat_lahir' => empty($tempat_lahir) ? null : $tempat_lahir,
                'tgl_lahir' => $tgl_lahir,
                'jenis_kelamin' => empty($jenis_kelamin) ? null : $jenis_kelamin,
                'agama' => empty($agama) ? null : $agama,
                'no_hp' => empty($no_hp) ? null : $no_hp,
                'alamat' => empty($alamat) ? null : $alamat,
                'nuptk' => empty($nuptk) ? null : $nuptk,
                'jenis_ptk' => empty($jenis_ptk) ? null : $jenis_ptk,
                'status_pegawai' => empty($status_pegawai) ? null : $status_pegawai,
                'status_aktif' => $status_aktif === 'Non-Aktif' ? 'Non-Aktif' : 'Aktif',
                'password' => 'password',
                'rowNum' => $rowNum,
            ];
        }

        if (count($errors) > 0) {
            return redirect()->back()->withErrors(['import_errors' => $errors]);
        }

        $dbUsernames = User::whereIn('username', $excelUsernames)->pluck('username')->toArray();
        $dbEmails = User::whereIn('email', array_filter($excelEmails))->pluck('email')->toArray();
        $dbCodes = Guru::whereIn('kode_guru', array_filter($excelCodes))->pluck('kode_guru')->toArray();

        foreach ($validatedData as $data) {
            if (in_array($data['username'], $dbUsernames)) {
                $errors[] = "Baris {$data['rowNum']}: Username '{$data['username']}' sudah digunakan di sistem.";
            }
            if ($data['email'] && in_array($data['email'], $dbEmails)) {
                $errors[] = "Baris {$data['rowNum']}: Email '{$data['email']}' sudah digunakan di sistem.";
            }
            if ($data['kode_guru'] && in_array($data['kode_guru'], $dbCodes)) {
                $errors[] = "Baris {$data['rowNum']}: Kode Guru '{$data['kode_guru']}' sudah digunakan di sistem.";
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
                    'message' => 'Imported '.count($importedUsernames).' guru accounts.',
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

        return redirect()->route('setting.user.guru.index')
            ->with('success', 'Berhasil mengimpor '.count($importedUsernames).' guru.')
            ->with('imported_users', $validatedData);
    }

    /**
     * Store a newly created guru.
     */
    public function store(StoreGuruUserRequest $request, CreateGuruAction $action): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $action->execute($request->validated());

        return redirect()->route('setting.user.guru.index')
            ->with('success', 'Akun guru berhasil dibuat.');
    }

    /**
     * Update the specified guru.
     */
    public function update(UpdateGuruUserRequest $request, Guru $guru): RedirectResponse
    {
        $user = $guru->user;
        Gate::authorize('update', $user);

        $data = $request->validated();

        DB::transaction(function () use ($guru, $user, $data) {
            // Update associated User
            $user->name = $data['nama_guru'];
            $user->email = $data['email'] ?? null;
            $user->username = $data['username'];

            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();

            // Update Guru profile
            $guru->update($data);
        });

        return redirect()->route('setting.user.guru.index')
            ->with('success', 'Akun guru berhasil diperbarui.');
    }

    /**
     * Delete the specified guru.
     */
    public function destroy(Guru $guru): RedirectResponse
    {
        $user = $guru->user;
        Gate::authorize('delete', $user);

        DB::transaction(function () use ($guru, $user) {
            $guru->delete();
            $user->delete();
        });

        return redirect()->route('setting.user.guru.index')
            ->with('success', 'Akun guru berhasil dihapus.');
    }
}
