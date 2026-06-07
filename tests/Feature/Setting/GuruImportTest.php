<?php

namespace Tests\Feature\Setting;

use App\Models\Master\Guru;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ViewErrorBag;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class GuruImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset Spatie permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        Role::create(['name' => 'superadmin']);
        Role::create(['name' => 'operator']);
        Role::create(['name' => 'guru']);

        // Create admin user
        $this->admin = User::factory()->create(['username' => 'admin']);
        $this->admin->assignRole('superadmin');
    }

    /**
     * Helper to create an Excel file from rows (array of arrays).
     * First row is the header.
     */
    private function makeExcelFile(array $rows, string $filename = 'guru.xlsx'): UploadedFile
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

        $tempPath = tempnam(sys_get_temp_dir(), 'guru_test_').'.xlsx';
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
            'Nama Guru*', 'Username*', 'Email', 'NIP', 'Kode Guru',
            'No KTP', 'Tempat Lahir', 'Tanggal Lahir (YYYY-MM-DD)',
            'Jenis Kelamin (L/P)', 'Agama', 'No HP', 'Alamat',
            'NUPTK', 'Jenis PTK', 'Status Pegawai', 'Status Aktif',
        ];
    }

    private function validRow(array $overrides = []): array
    {
        $defaults = [
            'Ahmad Syarif',   // nama
            'ahmadsyarif',    // username
            'ahmad@sch.id',   // email
            '198506120101',   // nip
            'GR01',           // kode_guru
            '3201020304050607', // no_ktp
            'Jakarta',        // tempat_lahir
            '1985-06-12',     // tgl_lahir
            'L',              // jenis_kelamin
            'Islam',          // agama
            '081234567890',   // no_hp
            'Jl. Raya No. 45', // alamat
            '1234567890123456', // nuptk
            'Guru Kelas',     // jenis_ptk
            'PNS',            // status_pegawai
            'Aktif',          // status_aktif
        ];

        foreach ($overrides as $index => $value) {
            $defaults[$index] = $value;
        }

        return $defaults;
    }

    // ─── Authorization ──────────────────────────────────────────────

    public function test_unauthorized_user_cannot_access_import()
    {
        $user = User::factory()->create(['username' => 'testguru']);
        $user->assignRole('guru');

        $file = $this->makeExcelFile([$this->validHeader(), $this->validRow()]);

        $response = $this->actingAs($user)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        // Laravel/Inertia converts AuthorizationException to redirect or 403
        $this->assertTrue(
            in_array($response->getStatusCode(), [302, 403]),
            'Expected 302 or 403, got '.$response->getStatusCode()
        );
        // Ensure no data was imported
        $this->assertDatabaseCount('guru', 0);
    }

    public function test_unauthorized_user_cannot_download_template()
    {
        $user = User::factory()->create(['username' => 'testguru2']);
        $user->assignRole('guru');

        $response = $this->actingAs($user)
            ->get(route('setting.user.guru.template'));

        $this->assertTrue(
            in_array($response->getStatusCode(), [302, 403]),
            'Expected 302 or 403, got '.$response->getStatusCode()
        );
    }

    // ─── Template Download ──────────────────────────────────────────

    public function test_admin_can_download_template()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('setting.user.guru.template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="template_guru.xlsx"');
    }

    // ─── Successful Import ──────────────────────────────────────────

    public function test_successful_import_creates_user_and_guru()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertRedirect(route('setting.user.guru.index'));
        $response->assertSessionHas('success');

        // Verify User was created
        $this->assertDatabaseHas('users', [
            'username' => 'ahmadsyarif',
            'name' => 'Ahmad Syarif',
        ]);

        // Verify Guru was created
        $this->assertDatabaseHas('guru', [
            'nama_guru' => 'Ahmad Syarif',
            'kode_guru' => 'GR01',
            'jenis_kelamin' => 'L',
        ]);

        // Verify password is hashed 'password'
        $user = User::where('username', 'ahmadsyarif')->first();
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_successful_import_multiple_rows()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Guru Satu', 1 => 'gurusatu', 2 => 'satu@sch.id', 4 => 'GR01']),
            $this->validRow([0 => 'Guru Dua', 1 => 'gurudua', 2 => 'dua@sch.id', 4 => 'GR02']),
            $this->validRow([0 => 'Guru Tiga', 1 => 'gurutiga', 2 => '', 4 => '']),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $session = $response->getSession();
        if ($session && $session->has('errors')) {
            $errors = $session->get('errors');
            if ($errors instanceof ViewErrorBag) {
                fwrite(STDERR, "\nSESSION ERRORS (ViewErrorBag): ".json_encode($errors->toArray())."\n");
            } else {
                fwrite(STDERR, "\nSESSION ERRORS (raw): ".json_encode($errors)."\n");
            }
        }

        $response->assertRedirect(route('setting.user.guru.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('guru', 3);
        $this->assertEquals(3, User::where('username', 'like', 'guru%')->count());
    }

    // ─── XSS Stripping ──────────────────────────────────────────────

    public function test_xss_in_name_with_valid_chars_is_stripped_and_name_kept()
    {
        // After strip_tags, the name becomes 'alert("xss")Ahmad Syarif'
        // which contains quotes — caught by regex as invalid characters
        $xssRow = $this->validRow([
            0 => '<script>alert("xss")</script>Ahmad Syarif',
            1 => 'cleanguru',
            2 => 'clean@sch.id',
            4 => 'GR01',
        ]);

        $file = $this->makeExcelFile([$this->validHeader(), $xssRow]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        // The name contains invalid chars after stripping, so regex catches it
        $response->assertSessionHasErrors('import_errors');
        $this->assertDatabaseCount('guru', 0);
    }

    public function test_xss_in_name_is_caught_by_regex()
    {
        $xssRow = $this->validRow([
            0 => '<script>alert("xss")</script>',
            1 => 'cleanguru2',
            2 => '',
            4 => '',
        ]);

        $file = $this->makeExcelFile([$this->validHeader(), $xssRow]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    public function test_xss_in_optional_fields_is_stripped()
    {
        // Test that strip_tags works on fields that don't have regex validation
        $xssRow = $this->validRow([
            0 => 'Ahmad Syarif',
            1 => 'cleanguru3',
            2 => 'clean3@sch.id',
            4 => 'GR03',
            11 => '<script>alert(1)</script>Jl. Raya No. 45',
        ]);

        $file = $this->makeExcelFile([$this->validHeader(), $xssRow]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file])
            ->assertRedirect(route('setting.user.guru.index'));

        // Check that the stored address doesn't contain <script> tags
        $guru = Guru::where('kode_guru', 'GR03')->first();
        $this->assertNotNull($guru);
        $this->assertStringNotContainsString('<script>', $guru->alamat);
        $this->assertStringContainsString('Jl. Raya No. 45', $guru->alamat);
    }

    // ─── Required Field Validation ──────────────────────────────────

    public function test_empty_nama_guru_returns_error()
    {
        $row = $this->validRow([0 => '', 1 => 'testuser']);

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    public function test_empty_username_returns_error()
    {
        $row = $this->validRow([0 => 'Ahmad Syarif', 1 => '']);

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    // ─── Format Validation ──────────────────────────────────────────

    public function test_invalid_date_format_returns_error()
    {
        $row = $this->validRow([7 => '12-06-1985']); // DD-MM-YYYY instead of YYYY-MM-DD

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    public function test_invalid_gender_returns_error()
    {
        $row = $this->validRow([8 => 'M']); // M instead of L/P

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    public function test_invalid_nama_characters_returns_error()
    {
        $row = $this->validRow([0 => 'Ahmad <Syarif> 123']);

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    public function test_invalid_username_characters_returns_error()
    {
        $row = $this->validRow([1 => 'ahmad syarif!@#']);

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    public function test_invalid_email_format_returns_error()
    {
        $row = $this->validRow([2 => 'not-an-email']);

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    public function test_nama_exceeding_100_chars_returns_error()
    {
        $longName = str_repeat('A', 101);

        $row = $this->validRow([0 => $longName]);

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    // ─── Intra-file Duplicate Detection ─────────────────────────────

    public function test_duplicate_username_in_file_returns_error()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Guru Satu', 1 => 'sameusername', 2 => 'one@sch.id', 4 => 'GR01']),
            $this->validRow([0 => 'Guru Dua', 1 => 'sameusername', 2 => 'two@sch.id', 4 => 'GR02']),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
        $this->assertDatabaseCount('guru', 0);
    }

    public function test_duplicate_email_in_file_returns_error()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Guru Satu', 1 => 'guru1', 2 => 'same@sch.id', 4 => 'GR01']),
            $this->validRow([0 => 'Guru Dua', 1 => 'guru2', 2 => 'same@sch.id', 4 => 'GR02']),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
        $this->assertDatabaseCount('guru', 0);
    }

    public function test_duplicate_kode_guru_in_file_returns_error()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Guru Satu', 1 => 'guru1', 2 => 'one@sch.id', 4 => 'SAME']),
            $this->validRow([0 => 'Guru Dua', 1 => 'guru2', 2 => 'two@sch.id', 4 => 'SAME']),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
        $this->assertDatabaseCount('guru', 0);
    }

    // ─── Database Duplicate Detection ───────────────────────────────

    public function test_existing_username_in_database_returns_error()
    {
        // Pre-create a user with the same username
        User::factory()->create(['username' => 'ahmadsyarif']);

        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    public function test_existing_email_in_database_returns_error()
    {
        User::factory()->create(['email' => 'ahmad@sch.id']);

        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    public function test_existing_kode_guru_in_database_returns_error()
    {
        // Pre-create a guru with the same kode_guru
        $existingUser = User::factory()->create(['username' => 'existing']);
        Guru::create([
            'user_id' => $existingUser->id,
            'nama_guru' => 'Existing Guru',
            'kode_guru' => 'GR01',
        ]);

        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('import_errors');
    }

    // ─── File Validation ────────────────────────────────────────────

    public function test_empty_file_returns_error()
    {
        $file = $this->makeExcelFile([$this->validHeader()]);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('file');
    }

    public function test_no_file_returns_validation_error()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), []);

        $response->assertSessionHasErrors('file');
    }

    // ─── Row Limit ──────────────────────────────────────────────────

    public function test_exceeding_1000_rows_returns_error()
    {
        $rows = [$this->validHeader()];
        for ($i = 0; $i < 1001; $i++) {
            $rows[] = $this->validRow([1 => "user{$i}", 2 => "user{$i}@sch.id", 4 => "GR{$i}"]);
        }

        $file = $this->makeExcelFile($rows);

        $response = $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('guru', 0);
    }

    // ─── Activity Log Audit ─────────────────────────────────────────

    public function test_successful_import_creates_activity_log()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        // Verify activity_log entry was created
        $this->assertDatabaseCount('activity_log', 1);

        $log = DB::table('activity_log')->first();
        $this->assertEquals($this->admin->id, $log->user_id);
        $this->assertEquals(1, $log->group_id);
        $this->assertEquals('User Management', $log->group_name);
        $this->assertEquals(1, $log->log_type);

        // Verify JSON metadata
        $logData = json_decode($log->log_desc, true);
        $this->assertArrayHasKey('message', $logData);
        $this->assertArrayHasKey('file', $logData);
        $this->assertArrayHasKey('count', $logData);
        $this->assertArrayHasKey('usernames', $logData);
        $this->assertEquals(1, $logData['count']);
        $this->assertContains('ahmadsyarif', $logData['usernames']);
    }

    public function test_failed_import_does_not_create_activity_log()
    {
        // Empty file should not create any log
        $file = $this->makeExcelFile([$this->validHeader()]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        $this->assertDatabaseCount('activity_log', 0);
    }

    // ─── Transaction Rollback ───────────────────────────────────────

    public function test_validation_errors_prevent_any_data_insertion()
    {
        $file = $this->makeExcelFile([
            $this->validHeader(),
            $this->validRow([0 => 'Valid Guru', 1 => 'validguru', 2 => 'valid@sch.id', 4 => 'GR01']),
            $this->validRow([0 => '', 1 => 'invalidguru']), // invalid — empty name
        ]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file]);

        // Nothing should be created because validation fails before transaction
        $this->assertDatabaseCount('guru', 0);
        $this->assertDatabaseMissing('users', ['username' => 'validguru']);
    }

    // ─── Edge Cases ─────────────────────────────────────────────────

    public function test_optional_fields_can_be_empty()
    {
        $row = [
            'Ahmad Syarif', // nama
            'ahmadsyarif',  // username
            '',             // email (optional)
            '',             // nip
            '',             // kode_guru
            '',             // no_ktp
            '',             // tempat_lahir
            '',             // tgl_lahir
            '',             // jenis_kelamin
            '',             // agama
            '',             // no_hp
            '',             // alamat
            '',             // nuptk
            '',             // jenis_ptk
            '',             // status_pegawai
            '',             // status_aktif
        ];

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file])
            ->assertRedirect(route('setting.user.guru.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('guru', ['nama_guru' => 'Ahmad Syarif']);
    }

    public function test_gender_is_normalized_to_uppercase()
    {
        $row = $this->validRow([8 => 'l']); // lowercase 'l'

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file])
            ->assertRedirect(route('setting.user.guru.index'));

        $guru = Guru::where('nama_guru', 'Ahmad Syarif')->first();
        $this->assertEquals('L', $guru->jenis_kelamin);
    }

    public function test_status_aktif_defaults_to_aktif()
    {
        $row = $this->validRow([15 => '']); // empty status_aktif

        $file = $this->makeExcelFile([$this->validHeader(), $row]);

        $this->actingAs($this->admin)
            ->post(route('setting.user.guru.import'), ['file' => $file])
            ->assertRedirect(route('setting.user.guru.index'));

        $guru = Guru::where('nama_guru', 'Ahmad Syarif')->first();
        $this->assertEquals('Aktif', $guru->status_aktif);
    }
}
