<?php

namespace Tests\Feature\Master;

use App\Models\Master\BukuInduk;
use App\Models\Master\Guru;
use App\Models\Master\Jurusan;
use App\Models\Master\Kelas;
use App\Models\Master\LevelKelas;
use App\Models\Master\Siswa;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KelasStudentsImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $waliKelasUser;

    protected User $regularGuruUser;

    protected Kelas $kelas;

    protected TahunPelajaran $tp;

    protected Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'superadmin']);
        Role::create(['name' => 'operator']);
        Role::create(['name' => 'guru']);
        Role::create(['name' => 'siswa']);

        $this->admin = User::factory()->create(['username' => 'admin']);
        $this->admin->assignRole('superadmin');

        $this->tp = TahunPelajaran::create(['tahun' => '2025/2026', 'active' => true]);
        $this->semester = Semester::create(['smt' => '1', 'nama_smt' => 'Ganjil', 'active' => true]);

        $jurusan = Jurusan::create(['nama_jurusan' => 'IPA', 'kode_jurusan' => 'IPA', 'status' => true]);
        $level = LevelKelas::create(['level' => 'X']);

        // Create Wali Kelas Guru and associated User
        $this->waliKelasUser = User::factory()->create(['username' => 'walikelas']);
        $this->waliKelasUser->assignRole('guru');
        $waliGuru = Guru::create([
            'user_id' => $this->waliKelasUser->id,
            'nip' => '111111',
            'nama_guru' => 'Wali Kelas Guru',
            'kode_guru' => 'WLG',
            'username' => 'walikelas',
        ]);

        // Create Regular Guru and associated User
        $this->regularGuruUser = User::factory()->create(['username' => 'regularguru']);
        $this->regularGuruUser->assignRole('guru');
        Guru::create([
            'user_id' => $this->regularGuruUser->id,
            'nip' => '222222',
            'nama_guru' => 'Regular Guru',
            'kode_guru' => 'RG',
            'username' => 'regularguru',
        ]);

        $this->kelas = Kelas::create([
            'tahun_pelajaran_id' => $this->tp->id,
            'semester_id' => $this->semester->id,
            'nama_kelas' => 'X-IPA-1',
            'kode_kelas' => 'X-IPA-1',
            'jurusan_id' => $jurusan->id,
            'level_id' => $level->id,
            'guru_id' => $waliGuru->id,
        ]);

        $this->waliKelasUser->refresh();
        $this->regularGuruUser->refresh();
    }

    private function makeExcelFile(array $rows, string $filename = 'siswa.xlsx'): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIdx => $row) {
            foreach ($row as $colIdx => $value) {
                $sheet->setCellValue(
                    Coordinate::stringFromColumnIndex($colIdx + 1).($rowIdx + 1),
                    $value
                );
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'siswa_test_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile(
            $tempPath,
            $filename,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    private function validHeader(): array
    {
        return [
            'Nama Siswa*', 'NISN*', 'NIS*', 'Username*', 'Email',
            'Jenis Kelamin (L/P)', 'Tahun Masuk', 'Sekolah Asal',
            'Tempat Lahir', 'Tanggal Lahir (YYYY-MM-DD)', 'Agama',
            'No HP', 'NIK', 'Warga Negara',
        ];
    }

    private function validRow(array $overrides = []): array
    {
        $defaults = [
            'Budi Santoso',      // nama
            '0081234567',        // nisn (10 digits)
            '10245',             // nis
            'budisantoso',       // username
            'budi@sch.id',       // email
            'L',                 // jenis_kelamin
            '2025',              // tahun_masuk
            'SMPN 1 Jakarta',    // sekolah_asal
            'Jakarta',           // tempat_lahir
            '2010-08-15',        // tanggal_lahir
            'Islam',             // agama
            '085678901234',      // hp
            '3201021508100001',  // nik
            'WNI',               // warga_negara
        ];

        foreach ($overrides as $index => $value) {
            $defaults[$index] = $value;
        }

        return $defaults;
    }

    // ─── Authorization ──────────────────────────────────────────────

    public function test_unauthorized_user_cannot_import_siswa_to_class()
    {
        $file = $this->makeExcelFile([$this->validHeader(), $this->validRow()]);

        $this->actingAs($this->regularGuruUser)
            ->post(route('master.kelas.students.import', $this->kelas->id), ['file' => $file])
            ->assertForbidden();
    }

    public function test_wali_kelas_can_import_siswa_to_class()
    {
        $file = $this->makeExcelFile([$this->validHeader(), $this->validRow()]);

        $response = $this->actingAs($this->waliKelasUser)
            ->post(route('master.kelas.students.import', $this->kelas->id), ['file' => $file]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_admin_can_import_siswa_to_class()
    {
        $file = $this->makeExcelFile([$this->validHeader(), $this->validRow()]);

        $response = $this->actingAs($this->admin)
            ->post(route('master.kelas.students.import', $this->kelas->id), ['file' => $file]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_unauthorized_user_cannot_download_class_siswa_template()
    {
        $this->actingAs($this->regularGuruUser)
            ->get(route('master.kelas.students.template', $this->kelas->id))
            ->assertForbidden();
    }

    public function test_wali_kelas_can_download_class_siswa_template()
    {
        $response = $this->actingAs($this->waliKelasUser)
            ->get(route('master.kelas.students.template', $this->kelas->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="template_siswa_kelas_x-ipa-1.xlsx"');
    }

    // ─── Successful Import & Roster Assignment ──────────────────────────

    public function test_successful_siswa_import_creates_records_and_enrolls_to_class()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('master.kelas.students.import', $this->kelas->id), ['file' => $file]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify User was created
        $this->assertDatabaseHas('users', [
            'username' => 'budisantoso',
            'name' => 'Budi Santoso',
        ]);

        // Verify Siswa was created
        $this->assertDatabaseHas('siswa', [
            'nama' => 'Budi Santoso',
            'nisn' => '0081234567',
            'nis' => '10245',
            'jenis_kelamin' => 'L',
        ]);

        $siswa = Siswa::where('nisn', '0081234567')->first();

        // Verify BukuInduk was created
        $this->assertDatabaseHas('buku_induk', [
            'siswa_id' => $siswa->id,
        ]);

        // Verify Enrollment into class for current academic year & semester
        $this->assertDatabaseHas('kelas_siswa', [
            'siswa_id' => $siswa->id,
            'kelas_id' => $this->kelas->id,
            'tahun_pelajaran_id' => $this->kelas->tahun_pelajaran_id,
            'semester_id' => $this->kelas->semester_id,
        ]);
    }

    // ─── Validation & Transaction Rollback ──────────────────────────

    public function test_validation_errors_prevent_any_siswa_insertion_and_rollback()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Valid Siswa', 1 => '0011111111', 2 => 'S001', 3 => 'valid1']),
            $this->validRow([0 => '', 1 => '0022222222', 2 => 'S002', 3 => 'valid2']), // empty name
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('master.kelas.students.import', $this->kelas->id), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');

        $this->assertDatabaseCount('siswa', 0);
        $this->assertDatabaseMissing('users', ['username' => 'valid1']);
        $this->assertDatabaseCount('kelas_siswa', 0);
    }
}
