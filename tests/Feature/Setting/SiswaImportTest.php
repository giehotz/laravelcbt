<?php

namespace Tests\Feature\Setting;

use App\Actions\CreateSiswaAction;
use App\Models\BukuInduk;
use App\Models\Master\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SiswaImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'superadmin']);
        Role::create(['name' => 'operator']);
        Role::create(['name' => 'guru']);
        Role::create(['name' => 'siswa']);

        $this->admin = User::factory()->create(['username' => 'admin']);
        $this->admin->assignRole('superadmin');
    }

    private function makeExcelFile(array $rows, string $filename = 'siswa.xlsx'): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIdx => $row) {
            foreach ($row as $colIdx => $value) {
                $sheet->setCellValue(
                    \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . ($rowIdx + 1),
                    $value
                );
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'siswa_test_') . '.xlsx';
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

    public function test_unauthorized_user_cannot_import_siswa()
    {
        $user = User::factory()->create(['username' => 'regularguru']);
        $user->assignRole('guru');

        $file = $this->makeExcelFile([$this->validHeader(), $this->validRow()]);

        $this->actingAs($user)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_download_siswa_template()
    {
        $user = User::factory()->create(['username' => 'regularguru2']);
        $user->assignRole('guru');

        $this->actingAs($user)
            ->get(route('setting.user.siswa.template'))
            ->assertForbidden();
    }

    // ─── Template Download ──────────────────────────────────────────

    public function test_admin_can_download_siswa_template()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('setting.user.siswa.template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="template_siswa.xlsx"');
    }

    // ─── Successful Import ──────────────────────────────────────────

    public function test_successful_siswa_import_creates_user_siswa_and_buku_induk()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file]);

        $response->assertRedirect(route('setting.user.siswa.index'));
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

        // Verify BukuInduk was created
        $siswa = Siswa::where('nisn', '0081234567')->first();
        $this->assertDatabaseHas('buku_induk', [
            'siswa_id' => $siswa->id,
        ]);

        // Verify password is hashed 'password'
        $user = User::where('username', 'budisantoso')->first();
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_successful_import_multiple_siswa()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Siswa Satu', 1 => '0011111111', 2 => 'S001', 3 => 'siswa1', 4 => 's1@sch.id']),
            $this->validRow([0 => 'Siswa Dua', 1 => '0022222222', 2 => 'S002', 3 => 'siswa2', 4 => 's2@sch.id']),
            $this->validRow([0 => 'Siswa Tiga', 1 => '0033333333', 2 => 'S003', 3 => 'siswa3', 4 => '']),
        ]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertRedirect(route('setting.user.siswa.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('siswa', 3);
        $this->assertDatabaseCount('buku_induk', 3);
    }

    // ─── XSS Stripping ──────────────────────────────────────────────

    public function test_xss_in_siswa_name_is_caught()
    {
        $xssRow = $this->validRow([
            0 => '<script>alert("xss")</script>',
        ]);

        $file = $this->makeExcelFile([$this->validHeader(), $xssRow]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file]);

        // After strip_tags, name becomes 'alert("xss")' which fails regex
        $response->assertSessionHasErrors('import_errors');
    }

    public function test_xss_in_optional_siswa_fields_is_stripped()
    {
        $xssRow = $this->validRow([
            7 => '<b>SMPN 1</b><script>bad</script> Jakarta',
        ]);

        $file = $this->makeExcelFile([$this->validHeader(), $xssRow]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertRedirect(route('setting.user.siswa.index'));

        $siswa = Siswa::where('nisn', '0081234567')->first();
        $this->assertNotNull($siswa);
        $this->assertStringNotContainsString('<script>', $siswa->sekolah_asal);
        $this->assertStringNotContainsString('<b>', $siswa->sekolah_asal);
    }

    // ─── Required Field Validation ──────────────────────────────────

    public function test_empty_nama_siswa_returns_error()
    {
        $row = $this->validRow([0 => '']);

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');
    }

    public function test_empty_nisn_returns_error()
    {
        $row = $this->validRow([1 => '']);

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');
    }

    public function test_empty_nis_returns_error()
    {
        $row = $this->validRow([2 => '']);

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');
    }

    public function test_empty_username_returns_error()
    {
        $row = $this->validRow([3 => '']);

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');
    }

    // ─── Format Validation ──────────────────────────────────────────

    public function test_nisn_must_be_10_digits()
    {
        $row = $this->validRow([1 => '12345']); // only 5 digits

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');
    }

    public function test_nisn_must_be_numeric()
    {
        $row = $this->validRow([1 => 'ABCDEFGHIJ']); // letters, not digits

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');
    }

    public function test_invalid_siswa_date_format_returns_error()
    {
        $row = $this->validRow([9 => '15-08-2010']); // DD-MM-YYYY

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');
    }

    public function test_invalid_siswa_gender_returns_error()
    {
        $row = $this->validRow([5 => 'X']);

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');
    }

    // ─── Intra-file Duplicate Detection ─────────────────────────────

    public function test_duplicate_username_in_siswa_file_returns_error()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Siswa A', 1 => '0011111111', 2 => 'S001', 3 => 'sameuser', 4 => 'a@sch.id']),
            $this->validRow([0 => 'Siswa B', 1 => '0022222222', 2 => 'S002', 3 => 'sameuser', 4 => 'b@sch.id']),
        ]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');

        $this->assertDatabaseCount('siswa', 0);
    }

    public function test_duplicate_nisn_in_file_returns_error()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Siswa A', 1 => '0011111111', 2 => 'S001', 3 => 'user1', 4 => 'a@sch.id']),
            $this->validRow([0 => 'Siswa B', 1 => '0011111111', 2 => 'S002', 3 => 'user2', 4 => 'b@sch.id']),
        ]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');

        $this->assertDatabaseCount('siswa', 0);
    }

    public function test_duplicate_nis_in_file_returns_error()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Siswa A', 1 => '0011111111', 2 => 'SAME', 3 => 'user1', 4 => 'a@sch.id']),
            $this->validRow([0 => 'Siswa B', 1 => '0022222222', 2 => 'SAME', 3 => 'user2', 4 => 'b@sch.id']),
        ]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');

        $this->assertDatabaseCount('siswa', 0);
    }

    public function test_duplicate_email_in_siswa_file_returns_error()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Siswa A', 1 => '0011111111', 2 => 'S001', 3 => 'user1', 4 => 'same@sch.id']),
            $this->validRow([0 => 'Siswa B', 1 => '0022222222', 2 => 'S002', 3 => 'user2', 4 => 'same@sch.id']),
        ]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');

        $this->assertDatabaseCount('siswa', 0);
    }

    // ─── Database Duplicate Detection ───────────────────────────────

    public function test_existing_username_in_db_returns_error()
    {
        User::factory()->create(['username' => 'budisantoso']);

        $file = $this->makeExcelFile([$this->validHeader(), $this->validRow()]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');
    }

    public function test_existing_nisn_in_db_returns_error()
    {
        $existingUser = User::factory()->create(['username' => 'existing']);
        Siswa::create([
            'user_id' => $existingUser->id,
            'nama' => 'Existing Siswa',
            'nisn' => '0081234567',
            'nis' => '99999',
        ]);

        $file = $this->makeExcelFile([$this->validHeader(), $this->validRow()]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');
    }

    public function test_existing_nis_in_db_returns_error()
    {
        $existingUser = User::factory()->create(['username' => 'existing']);
        Siswa::create([
            'user_id' => $existingUser->id,
            'nama' => 'Existing Siswa',
            'nisn' => '9999999999',
            'nis' => '10245',
        ]);

        $file = $this->makeExcelFile([$this->validHeader(), $this->validRow()]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertSessionHasErrors('import_errors');
    }

    // ─── Activity Log Audit ─────────────────────────────────────────

    public function test_successful_siswa_import_creates_activity_log()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file]);

        $this->assertDatabaseCount('activity_log', 1);

        $log = DB::table('activity_log')->first();
        $this->assertEquals($this->admin->id, $log->user_id);
        $this->assertEquals(1, $log->group_id);
        $this->assertEquals('User Management', $log->group_name);

        $logData = json_decode($log->log_desc, true);
        $this->assertArrayHasKey('message', $logData);
        $this->assertArrayHasKey('count', $logData);
        $this->assertEquals(1, $logData['count']);
        $this->assertContains('budisantoso', $logData['usernames']);
        $this->assertStringContainsString('siswa', strtolower($logData['message']));
    }

    public function test_failed_siswa_import_does_not_create_activity_log()
    {
        $file = $this->makeExcelFile([$this->validHeader()]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file]);

        $this->assertDatabaseCount('activity_log', 0);
    }

    // ─── Transaction Rollback ───────────────────────────────────────

    public function test_validation_errors_prevent_any_siswa_insertion()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Valid Siswa', 1 => '0011111111', 2 => 'S001', 3 => 'valid1']),
            $this->validRow([0 => '', 1 => '0022222222', 2 => 'S002', 3 => 'valid2']), // empty name
        ]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file]);

        $this->assertDatabaseCount('siswa', 0);
        $this->assertDatabaseMissing('users', ['username' => 'valid1']);
    }

    // ─── Edge Cases ─────────────────────────────────────────────────

    public function test_optional_siswa_fields_can_be_empty()
    {
        $row = [
            'Budi Santoso',  // nama
            '0081234567',    // nisn
            '10245',         // nis
            'budisantoso',   // username
            '',              // email
            '',              // jenis_kelamin
            '',              // tahun_masuk
            '',              // sekolah_asal
            '',              // tempat_lahir
            '',              // tanggal_lahir
            '',              // agama
            '',              // hp
            '',              // nik
            '',              // warga_negara
        ];

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertRedirect(route('setting.user.siswa.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('siswa', ['nama' => 'Budi Santoso']);
    }

    public function test_siswa_gender_is_normalized_to_uppercase()
    {
        $row = $this->validRow([5 => 'p']); // lowercase

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertRedirect(route('setting.user.siswa.index'));

        $siswa = Siswa::where('nisn', '0081234567')->first();
        $this->assertEquals('P', $siswa->jenis_kelamin);
    }

    public function test_warga_negara_defaults_to_wni()
    {
        $row = $this->validRow([13 => '']); // empty

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.siswa.import'), ['file' => $file])
            ->assertRedirect(route('setting.user.siswa.index'));

        $siswa = Siswa::where('nisn', '0081234567')->first();
        $this->assertEquals('WNI', $siswa->warga_negara);
    }
}
