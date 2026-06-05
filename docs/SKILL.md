---
name: garudacbt-laravel
description: >
  Gunakan skill ini untuk membangun aplikasi GarudaCBT — sistem CBT (Computer Based Test)
  sekolah berbasis Laravel (versi terbaru, saat ini v11/v12) + Vue 3 + Vite (monolitik, Inertia.js).
  Skill ini WAJIB digunakan setiap kali pengguna meminta kode, fitur, migrasi, controller,
  komponen Vue, atau penjelasan terkait aplikasi ini. Aktif saat pengguna menyebut:
  "garudacbt", "CBT sekolah", "bank soal", "jadwal ujian", "token ujian", "nilai CBT",
  "rapor", "master siswa", "master guru", "buku induk", "LMS materi", "absensi",
  "rekap nilai", atau scaffolding Laravel dengan database garudacbt.
---

# Skill: GarudaCBT — Laravel + Vue 3 + Inertia.js

## Stack & Arsitektur

| Layer | Teknologi |
|---|---|
| **Backend** | Laravel (terbaru, v11+), PHP 8.3+ |
| **Frontend** | Vue 3 (Composition API + `<script setup>`), Vite |
| **Bridge** | Inertia.js v2 (monolitik, tanpa REST API terpisah) |
| **Auth** | Laravel Breeze (Inertia + Vue preset) + Spatie Laravel-Permission |
| **Database** | MySQL 8+ / MariaDB 10.11+ — nama DB: `garudacbt` |
| **Style** | Tailwind CSS v4 |
| **State** | Pinia (global), props/emits lokal |
| **HTTP** | Inertia router (`router.visit`, `useForm`) — bukan Axios |
| **Editor Soal** | TipTap atau QuillJS (rich text + gambar soal) |
| **Testing** | Pest PHP |

> **Pola arsitektur:** Full-stack Monolith. Laravel render halaman awal via Inertia,
> Vue menangani reaktivitas. Tidak ada endpoint `/api` kecuali untuk fitur real-time
> ujian (polling status peserta via Axios + Laravel route `api.php`).

---

## Role & Permission

Gunakan **Spatie Laravel-Permission**. Role sistem:

| Role | Slug | Akses Utama |
|---|---|---|
| Super Admin | `superadmin` | Semua modul, setting sistem |
| Kepala Sekolah | `kepsek` | Dashboard, laporan, rekap nilai, rapor (read-only) |
| Guru / Wali Kelas | `guru` | Bank soal, jadwal (milik sendiri), materi, nilai, rapor kelas |
| Siswa | `siswa` | Ikut ujian, lihat nilai, unduh materi |
| Proktor / Pengawas | `proktor` | Monitor ujian, reset peserta, absensi ruang |
| Operator / TU | `operator` | Master data, buku induk, import/export data |

### Setup Permission (seeder)
```php
// database/seeders/RolePermissionSeeder.php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$permissions = [
    // Master Data
    'master.siswa.view', 'master.siswa.create', 'master.siswa.edit', 'master.siswa.delete',
    'master.guru.view',  'master.guru.create',  'master.guru.edit',  'master.guru.delete',
    'master.kelas.view', 'master.kelas.create', 'master.kelas.edit', 'master.kelas.delete',
    'master.mapel.view', 'master.mapel.create', 'master.mapel.edit', 'master.mapel.delete',
    // CBT
    'cbt.bank.view', 'cbt.bank.create', 'cbt.bank.edit', 'cbt.bank.delete',
    'cbt.soal.view', 'cbt.soal.create', 'cbt.soal.edit', 'cbt.soal.delete',
    'cbt.jadwal.view','cbt.jadwal.create','cbt.jadwal.edit','cbt.jadwal.delete',
    'cbt.monitor', 'cbt.nilai.view', 'cbt.nilai.edit', 'cbt.rekap.view',
    // Akademik
    'rapor.view', 'rapor.edit', 'nilai.view', 'nilai.edit',
    // LMS
    'materi.view', 'materi.create', 'materi.edit', 'materi.delete',
    // Komunikasi
    'post.view', 'post.create', 'post.delete',
    // Buku Induk
    'buku_induk.view', 'buku_induk.edit',
    // Sistem
    'setting.view', 'setting.edit', 'log.view',
];

// Assign ke role sesuai tabel akses di atas
Role::findByName('superadmin')->syncPermissions($permissions); // semua
Role::findByName('guru')->syncPermissions([
    'cbt.bank.view','cbt.bank.create','cbt.bank.edit',
    'cbt.soal.view','cbt.soal.create','cbt.soal.edit',
    'cbt.jadwal.view','cbt.jadwal.create',
    'cbt.nilai.view','cbt.nilai.edit',
    'rapor.view','rapor.edit',
    'materi.view','materi.create','materi.edit','materi.delete',
    'master.siswa.view','master.kelas.view','master.mapel.view',
    'post.view','post.create',
]);
// ...dst per role
```

### Middleware Route
```php
// routes/web.php — contoh group per role
Route::middleware(['auth', 'verified', 'role:superadmin|operator'])
    ->prefix('master')->name('master.')
    ->group(base_path('routes/master.php'));

Route::middleware(['auth', 'verified', 'role:superadmin|guru|operator'])
    ->prefix('cbt')->name('cbt.')
    ->group(base_path('routes/cbt.php'));

Route::middleware(['auth', 'verified', 'role:siswa'])
    ->prefix('ujian')->name('ujian.')
    ->group(base_path('routes/ujian.php'));
```

---

## Struktur Direktori Laravel

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                    # Breeze default
│   │   ├── Master/
│   │   │   ├── SiswaController.php
│   │   │   ├── GuruController.php
│   │   │   ├── KelasController.php
│   │   │   ├── MapelController.php
│   │   │   └── JurusanController.php
│   │   ├── Cbt/
│   │   │   ├── BankSoalController.php
│   │   │   ├── SoalController.php
│   │   │   ├── JadwalController.php
│   │   │   ├── UjianController.php      # siswa: ambil soal, jawab
│   │   │   ├── MonitorController.php    # proktor: lihat status peserta
│   │   │   ├── NilaiController.php
│   │   │   └── RekapController.php
│   │   ├── Akademik/
│   │   │   ├── RaporController.php
│   │   │   ├── NilaiHarianController.php
│   │   │   └── KkmController.php
│   │   ├── Lms/
│   │   │   ├── MateriController.php
│   │   │   └── TugasController.php
│   │   ├── Komunikasi/
│   │   │   └── PostController.php
│   │   ├── BukuIndukController.php
│   │   ├── DashboardController.php
│   │   └── SettingController.php
│   └── Middleware/
│       └── CheckExamSession.php     # cegah multi-login saat ujian
├── Models/
│   ├── User.php
│   ├── Master/
│   │   ├── Siswa.php
│   │   ├── Guru.php
│   │   ├── Kelas.php
│   │   ├── Mapel.php
│   │   └── Jurusan.php
│   ├── Cbt/
│   │   ├── BankSoal.php
│   │   ├── Soal.php
│   │   ├── Jadwal.php
│   │   ├── SoalSiswa.php
│   │   ├── DurasiSiswa.php
│   │   ├── Nilai.php
│   │   └── Token.php
│   ├── Akademik/
│   │   ├── RaporNilaiAkhir.php
│   │   └── RaporKkm.php
│   └── ...
├── Services/
│   ├── CbtService.php               # logika distribusi soal, acak, skoring
│   ├── NilaiService.php             # hitung nilai akhir
│   └── RaporService.php
resources/
├── js/
│   ├── app.js                       # Inertia + Vue mount
│   ├── Pages/
│   │   ├── Dashboard.vue
│   │   ├── Auth/Login.vue
│   │   ├── Master/
│   │   │   ├── Siswa/Index.vue, Create.vue, Edit.vue
│   │   │   ├── Guru/...
│   │   │   └── Kelas/...
│   │   ├── Cbt/
│   │   │   ├── BankSoal/Index.vue, Create.vue, Edit.vue
│   │   │   ├── Soal/Index.vue, Editor.vue
│   │   │   ├── Jadwal/Index.vue, Form.vue
│   │   │   ├── Ujian/Start.vue, Soal.vue, Selesai.vue
│   │   │   ├── Monitor/Index.vue
│   │   │   └── Nilai/Index.vue, Koreksi.vue
│   │   ├── Akademik/Rapor/...
│   │   ├── Lms/Materi/...
│   │   └── BukuInduk/...
│   ├── Components/
│   │   ├── Layout/AppLayout.vue, Sidebar.vue, Navbar.vue
│   │   ├── Ui/Table.vue, Modal.vue, Pagination.vue, Badge.vue
│   │   ├── Cbt/TimerCountdown.vue, SoalRenderer.vue, JawabanPg.vue
│   │   └── Form/RichEditor.vue, FileUpload.vue
│   └── stores/
│       ├── ujian.js                 # Pinia — state sesi ujian siswa
│       └── auth.js
```

---

## Skema Database & Migrasi Laravel

> **Konvensi:** Semua tabel menggunakan nama baru (snake_case, plural Laravel-standard).
> Mapping dari nama tabel lama ke nama migration baru ada di bagian ini.

### Tabel Master Periode

**`master_tp` → `tahun_pelajaran`**
```php
Schema::create('tahun_pelajaran', function (Blueprint $table) {
    $table->id();
    $table->string('tahun', 20);       // contoh: "2025/2026"
    $table->boolean('active')->default(false);
    $table->timestamps();
});
```

**`master_smt` → `semesters`**
```php
Schema::create('semesters', function (Blueprint $table) {
    $table->id();
    $table->string('smt', 10);         // "1" atau "2"
    $table->string('nama_smt', 20);    // "Ganjil", "Genap"
    $table->boolean('active')->default(false);
    $table->timestamps();
});
```

### Tabel Master Data

**`master_siswa` → `siswa`**
```php
Schema::create('siswa', function (Blueprint $table) {
    $table->id();
    $table->string('nisn', 12)->unique();
    $table->string('nis', 20)->unique();
    $table->string('nama', 100);
    $table->char('jenis_kelamin', 1)->nullable();    // L / P
    $table->string('username', 50)->unique();
    $table->string('password');
    $table->unsignedBigInteger('kelas_awal')->nullable();
    $table->string('tahun_masuk', 10)->nullable();
    $table->string('sekolah_asal', 100)->nullable();
    $table->string('tempat_lahir', 100)->nullable();
    $table->string('tanggal_lahir', 30)->nullable();
    $table->string('agama', 15)->nullable();
    $table->string('hp', 15)->nullable();
    $table->string('email', 254)->nullable();
    $table->string('foto', 255)->default('siswa.png');
    $table->integer('anak_ke')->nullable();
    $table->char('status_keluarga', 1)->nullable();
    $table->text('alamat')->nullable();
    $table->string('rt', 5)->nullable();
    $table->string('rw', 5)->nullable();
    $table->string('kelurahan', 100)->nullable();
    $table->string('kecamatan', 100)->nullable();
    $table->string('kabupaten', 100)->nullable();
    $table->string('provinsi', 100)->nullable();
    $table->integer('kode_pos')->nullable();
    // Data Orang Tua
    $table->string('nama_ayah', 150)->nullable();
    $table->string('tgl_lahir_ayah', 50)->nullable();
    $table->string('pendidikan_ayah', 50)->nullable();
    $table->string('pekerjaan_ayah', 100)->nullable();
    $table->string('nohp_ayah', 20)->nullable();
    $table->text('alamat_ayah')->nullable();
    $table->string('nama_ibu', 100)->nullable();
    $table->string('tgl_lahir_ibu', 50)->nullable();
    $table->string('pendidikan_ibu', 50)->nullable();
    $table->string('pekerjaan_ibu', 100)->nullable();
    $table->string('nohp_ibu', 20)->nullable();
    $table->text('alamat_ibu')->nullable();
    $table->string('nama_wali', 150)->nullable();
    $table->string('tgl_lahir_wali', 50)->nullable();
    $table->string('pendidikan_wali', 50)->nullable();
    $table->string('pekerjaan_wali', 100)->nullable();
    $table->string('nohp_wali', 20)->nullable();
    $table->text('alamat_wali')->nullable();
    $table->string('nik', 30)->nullable();
    $table->string('warga_negara', 20)->default('WNI');
    $table->string('uid', 255)->nullable()->unique();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->timestamps();
});
```

**`master_guru` → `guru`**
```php
Schema::create('guru', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('nip', 30)->nullable();
    $table->string('nama_guru', 100);
    $table->string('email', 254)->nullable();
    $table->string('kode_guru', 10)->nullable()->unique();
    $table->string('username', 50)->nullable()->unique();
    $table->string('password')->nullable();
    $table->string('no_ktp', 16)->nullable();
    $table->string('tempat_lahir', 50)->nullable();
    $table->date('tgl_lahir')->nullable();
    $table->string('jenis_kelamin', 10)->nullable();
    $table->string('agama', 15)->nullable();
    $table->string('no_hp', 20)->nullable();
    $table->text('alamat')->nullable();
    $table->string('nuptk', 20)->nullable();
    $table->string('jenis_ptk', 50)->nullable();
    $table->string('status_pegawai', 50)->nullable();
    $table->string('status_aktif', 20)->nullable();
    $table->text('foto')->nullable();
    $table->timestamps();
});
```

**`master_jurusan` → `jurusan`**
```php
Schema::create('jurusan', function (Blueprint $table) {
    $table->id();
    $table->string('nama_jurusan', 80);
    $table->string('kode_jurusan', 10)->nullable()->unique();
    $table->json('mapel_peminatan')->nullable();
    $table->boolean('status')->default(true);
    $table->boolean('deletable')->default(true);
    $table->timestamps();
});
```

**`level_kelas` → `level_kelas`**
```php
Schema::create('level_kelas', function (Blueprint $table) {
    $table->id();
    $table->string('level', 5);    // "VII", "X", "1", dll
    $table->timestamps();
});
```

**`master_kelas` → `kelas`**
```php
Schema::create('kelas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->string('nama_kelas', 50);
    $table->string('kode_kelas', 20)->nullable();
    $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
    $table->foreignId('level_id')->constrained('level_kelas');
    $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete();
    $table->string('set_siswa', 1)->default('0');
    $table->timestamps();
});
```

**`kelas_siswa` → `kelas_siswa`** (pivot)
```php
Schema::create('kelas_siswa', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
    $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
    $table->timestamps();
    $table->unique(['tahun_pelajaran_id','semester_id','siswa_id']);
});
```

**`master_mapel` → `mapel`**
```php
Schema::create('mapel', function (Blueprint $table) {
    $table->id();
    $table->string('nama_mapel', 100);
    $table->string('kode', 20)->nullable();
    $table->string('kelompok', 10)->default('-');
    $table->integer('bobot_p')->default(0);
    $table->integer('bobot_k')->default(0);
    $table->integer('jenjang')->default(0);
    $table->integer('urutan')->default(0);
    $table->integer('urutan_tampil')->nullable();
    $table->boolean('status')->default(true);
    $table->boolean('deletable')->default(true);
    $table->timestamps();
});
```

### Tabel CBT

**`cbt_jenis` → `cbt_jenis`**
```php
Schema::create('cbt_jenis', function (Blueprint $table) {
    $table->id();
    $table->string('nama_jenis', 80);   // "Ulangan Harian", "PTS", "PAS", dll
    $table->string('kode_jenis', 15);
    $table->timestamps();
});
```

**`cbt_bank_soal` → `cbt_bank_soal`**
```php
Schema::create('cbt_bank_soal', function (Blueprint $table) {
    $table->id();
    $table->foreignId('jenis_id')->constrained('cbt_jenis');
    $table->string('kode', 50)->default('0');
    $table->string('level', 50);
    $table->json('kelas');                 // array id kelas yang dapat akses
    $table->foreignId('mapel_id')->nullable()->constrained('mapel')->nullOnDelete();
    $table->foreignId('jurusan_id')->nullable()->constrained('jurusan')->nullOnDelete();
    $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete();
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->string('nama', 255);
    $table->integer('kkm')->default(0);
    // Jumlah soal per tipe
    $table->integer('jml_pg')->default(0);
    $table->integer('tampil_pg')->default(0);
    $table->integer('bobot_pg')->default(0);
    $table->integer('jml_esai')->default(0);
    $table->integer('tampil_esai')->default(0);
    $table->integer('bobot_esai')->default(0);
    $table->integer('jml_kompleks')->default(0);
    $table->integer('tampil_kompleks')->default(0);
    $table->integer('bobot_kompleks')->default(0);
    $table->integer('jml_jodohkan')->default(0);
    $table->integer('tampil_jodohkan')->default(0);
    $table->integer('bobot_jodohkan')->default(0);
    $table->integer('jml_isian')->default(0);
    $table->integer('tampil_isian')->default(0);
    $table->integer('bobot_isian')->default(0);
    $table->integer('opsi')->default(4);   // 3=A-C, 4=A-D, 5=A-E
    $table->text('deskripsi')->nullable();
    $table->tinyInteger('status')->default(0);
    // 0=draft, 1=selesai dibuat
    $table->tinyInteger('status_soal')->default(0);
    $table->string('soal_agama', 20)->nullable();
    $table->timestamps();
});
```

**`cbt_soal` → `cbt_soal`**
```php
Schema::create('cbt_soal', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bank_id')->constrained('cbt_bank_soal')->cascadeOnDelete();
    $table->foreignId('mapel_id')->nullable()->constrained('mapel')->nullOnDelete();
    // Tipe: 1=PG, 2=Ganda Kompleks, 3=Menjodohkan, 4=Isian Singkat, 5=Uraian/Esai
    $table->tinyInteger('jenis');
    $table->integer('nomor_soal')->default(0);
    $table->string('file', 255)->nullable();      // path media lampiran soal
    $table->text('tipe_file')->nullable();
    $table->longText('soal')->nullable();          // HTML dari rich editor
    // Pilihan jawaban PG
    $table->longText('opsi_a')->nullable();
    $table->longText('opsi_b')->nullable();
    $table->longText('opsi_c')->nullable();
    $table->longText('opsi_d')->nullable();
    $table->longText('opsi_e')->nullable();
    // File media per opsi
    $table->string('file_a', 255)->nullable();
    $table->string('file_b', 255)->nullable();
    $table->string('file_c', 255)->nullable();
    $table->string('file_d', 255)->nullable();
    $table->string('file_e', 255)->nullable();
    $table->longText('jawaban')->nullable();       // kunci: "A","B", atau JSON untuk kompleks
    $table->longText('deskripsi')->nullable();     // pembahasan
    $table->tinyInteger('kesulitan')->default(1);  // 1-10
    $table->boolean('timer')->default(false);
    $table->integer('timer_menit')->default(0);
    $table->boolean('tampilkan')->default(false);
    $table->timestamps();
});
```

**`cbt_jadwal` → `cbt_jadwal`**
```php
Schema::create('cbt_jadwal', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->foreignId('bank_id')->nullable()->constrained('cbt_bank_soal')->nullOnDelete();
    $table->foreignId('jenis_id')->nullable()->constrained('cbt_jenis')->nullOnDelete();
    $table->string('tgl_mulai', 25);
    $table->string('tgl_selesai', 25);
    $table->integer('durasi_ujian');               // menit
    $table->json('pengawas')->nullable();          // array id guru
    $table->boolean('acak_soal')->default(false);
    $table->boolean('acak_opsi')->default(false);
    $table->boolean('hasil_tampil')->default(false); // tampil hasil langsung?
    $table->boolean('token')->default(false);      // pakai token?
    $table->tinyInteger('status')->default(0);     // 0=tutup, 1=buka
    $table->boolean('ulang')->default(false);      // boleh ulang?
    $table->boolean('reset_login')->default(false);
    $table->boolean('rekap')->default(false);
    $table->integer('jam_ke')->default(0);
    $table->integer('jarak')->default(0);          // menit jarak antar sesi
    $table->timestamps();
});
```

**`cbt_token` → `cbt_token`**
```php
Schema::create('cbt_token', function (Blueprint $table) {
    $table->id();
    $table->string('token', 10);
    $table->boolean('auto')->default(false);
    $table->timestamps();
});
```

**`cbt_soal_siswa` → `cbt_soal_siswa`** (distribusi soal per siswa)
```php
Schema::create('cbt_soal_siswa', function (Blueprint $table) {
    $table->string('id', 20)->primary();
    $table->foreignId('bank_id')->constrained('cbt_bank_soal');
    $table->foreignId('jadwal_id')->constrained('cbt_jadwal');
    $table->foreignId('soal_id')->constrained('cbt_soal');
    $table->foreignId('siswa_id')->constrained('siswa');
    $table->tinyInteger('jenis_soal');
    $table->integer('no_soal_alias');              // nomor tampil ke siswa
    // Alias acak opsi (mapping A→C, B→A, dst)
    $table->char('opsi_alias_a', 1)->nullable();
    $table->char('opsi_alias_b', 1)->nullable();
    $table->char('opsi_alias_c', 1)->nullable();
    $table->char('opsi_alias_d', 1)->nullable();
    $table->char('opsi_alias_e', 1)->nullable();
    $table->longText('jawaban_alias')->nullable(); // kunci setelah alias
    $table->longText('jawaban_siswa')->nullable(); // jawaban yang dipilih
    $table->longText('jawaban_benar')->nullable(); // cache kunci benar
    $table->integer('point_esai')->default(0);
    $table->boolean('soal_end')->default(false);   // soal terakhir?
    $table->string('point_soal', 5)->default('0');
    $table->string('nilai_koreksi', 5)->default('0');
    $table->boolean('nilai_otomatis')->default(true);
    $table->timestamps();
});
```

**`cbt_durasi_siswa` → `cbt_durasi_siswa`**
```php
Schema::create('cbt_durasi_siswa', function (Blueprint $table) {
    $table->id();
    $table->foreignId('siswa_id')->constrained('siswa');
    $table->foreignId('jadwal_id')->constrained('cbt_jadwal');
    // 0=belum, 1=sedang, 2=selesai
    $table->tinyInteger('status')->default(0);
    $table->time('lama_ujian')->nullable();
    $table->string('mulai', 25)->nullable();
    $table->string('selesai', 25)->nullable();
    // 0=tidak, 1=reset dari 0, 2=reset dari sisa, 3=ulangi semua
    $table->tinyInteger('reset')->default(0);
    $table->timestamps();
    $table->unique(['siswa_id','jadwal_id']);
});
```

**`cbt_nilai` → `cbt_nilai`**
```php
Schema::create('cbt_nilai', function (Blueprint $table) {
    $table->id();
    $table->foreignId('siswa_id')->constrained('siswa');
    $table->foreignId('jadwal_id')->constrained('cbt_jadwal');
    $table->integer('pg_benar')->default(0);
    $table->string('pg_nilai', 10)->default('0');
    $table->string('esai_nilai', 10)->default('0');
    $table->string('kompleks_nilai', 10)->default('0');
    $table->string('jodohkan_nilai', 10)->default('0');
    $table->string('isian_nilai', 10)->default('0');
    $table->boolean('dikoreksi')->default(false);
    $table->timestamps();
    $table->unique(['siswa_id','jadwal_id']);
});
```

**`cbt_ruang` → `cbt_ruang`**
```php
Schema::create('cbt_ruang', function (Blueprint $table) {
    $table->id();
    $table->string('nama_ruang', 80);
    $table->string('kode_ruang', 15)->unique();
    $table->timestamps();
});
```

**`cbt_sesi` → `cbt_sesi`**
```php
Schema::create('cbt_sesi', function (Blueprint $table) {
    $table->id();
    $table->string('nama_sesi', 80);
    $table->string('kode_sesi', 15)->unique();
    $table->time('waktu_mulai');
    $table->time('waktu_akhir');
    $table->boolean('aktif')->default(true);
    $table->timestamps();
});
```

**`cbt_rekap_nilai` → `cbt_rekap_nilai`**
```php
Schema::create('cbt_rekap_nilai', function (Blueprint $table) {
    $table->id();
    $table->foreignId('jadwal_id')->nullable()->constrained('cbt_jadwal');
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->foreignId('jenis_id')->constrained('cbt_jenis');
    $table->foreignId('bank_id')->nullable()->constrained('cbt_bank_soal');
    $table->foreignId('mapel_id')->nullable()->constrained('mapel');
    $table->foreignId('siswa_id')->nullable()->constrained('siswa');
    $table->foreignId('kelas_id')->nullable()->constrained('kelas')->default(0);
    $table->string('kelas', 30);
    $table->string('mulai', 25);
    $table->string('selesai', 25);
    $table->string('durasi', 25);
    $table->integer('bobot_pg');
    $table->longText('jawaban_pg');
    $table->string('nilai_pg', 10);
    $table->integer('bobot_esai');
    $table->longText('jawaban_esai');
    $table->string('nilai_esai', 10);
    $table->foreignId('guru_id')->nullable()->constrained('guru');
    $table->string('nama_siswa', 150)->nullable();
    $table->string('no_peserta', 50)->nullable();
    $table->json('soal_kompleks')->nullable();
    $table->json('soal_jodohkan')->nullable();
    $table->json('soal_isian')->nullable();
    $table->json('soal_essai')->nullable();
    $table->timestamps();
});
```

### Tabel LMS

**`kelas_materi` → `materi`**
```php
Schema::create('materi', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete();
    $table->foreignId('mapel_id')->nullable()->constrained('mapel')->nullOnDelete();
    $table->json('kelas');               // array id kelas tujuan
    $table->string('judul', 500);
    $table->longText('isi');
    $table->text('file')->nullable();    // JSON array path file
    $table->string('link_file', 255)->nullable();
    $table->string('youtube', 255)->nullable();
    $table->dateTime('tgl_mulai')->nullable();
    $table->tinyInteger('status')->default(0); // 0=draft, 1=publish
    // 1=materi, 2=tugas
    $table->tinyInteger('jenis')->default(1);
    $table->timestamps();
});
```

### Tabel Komunikasi

**`post` → `posts`**
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('dari_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->integer('dari_group')->nullable();  // id role group
    $table->string('kepada', 100);              // "all", "siswa", "guru", dll
    $table->longText('text');
    $table->timestamps();
});
```

### Tabel Akademik / Rapor

**`rapor_kkm` → `rapor_kkm`**
```php
Schema::create('rapor_kkm', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
    $table->foreignId('mapel_id')->nullable()->constrained('mapel')->nullOnDelete();
    $table->integer('kkm')->default(0);
    $table->integer('bobot_ph')->default(0);
    $table->integer('bobot_pts')->default(0);
    $table->integer('bobot_pas')->default(0);
    $table->integer('bobot_absen')->default(0);
    $table->integer('beban_jam')->default(0);
    $table->tinyInteger('jenis');
    $table->timestamps();
});
```

**`rapor_nilai_akhir` → `rapor_nilai_akhir`**
```php
Schema::create('rapor_nilai_akhir', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->foreignId('mapel_id')->nullable()->constrained('mapel');
    $table->foreignId('kelas_id')->nullable()->constrained('kelas');
    $table->foreignId('siswa_id')->nullable()->constrained('siswa');
    $table->integer('nilai')->default(0);
    $table->integer('akhir')->nullable();
    $table->char('predikat', 1)->nullable();
    $table->timestamps();
});
```

**`buku_induk` → `buku_induk`**
```php
Schema::create('buku_induk', function (Blueprint $table) {
    $table->foreignId('siswa_id')->primary()->constrained('siswa')->cascadeOnDelete();
    $table->string('uid', 100)->nullable();
    $table->string('nama_panggilan', 80)->nullable();
    $table->string('bahasa', 80)->nullable();
    $table->integer('jml_saudara_kandung')->default(0);
    $table->integer('jml_saudara_tiri')->default(0);
    $table->integer('jml_saudara_angkat')->default(0);
    // 0=ada orang-tua, 1=yatim, 2=yatim piatu
    $table->tinyInteger('yatim')->default(0);
    // 1=orang-tua, 2=saudara, 3=wali, 4=asrama, 5=kost, 6=lainnya
    $table->char('tinggal_bersama', 1)->default('1');
    $table->string('jarak', 15)->nullable();
    $table->string('gol_darah', 5)->nullable();
    $table->text('penyakit')->nullable();
    $table->string('kelainan_fisik', 150)->nullable();
    $table->text('kegemaran')->nullable();
    $table->text('beasiswa')->nullable();
    $table->string('no_ijazah_sebelumnya', 80)->nullable();
    $table->string('tahun_lulus_sebelumnya', 10)->nullable();
    $table->string('pindahan_dari', 150)->nullable();
    $table->string('alasan_kepindahan', 300)->nullable();
    // Status akhir: 1=aktif, 2=lulus, 3=pindah, 4=keluar
    $table->tinyInteger('status')->default(1);
    $table->integer('tahun_lulus')->nullable();
    $table->string('no_ijazah', 80)->nullable();
    $table->string('kelas_akhir', 80)->nullable();
    $table->text('catatan_penting')->nullable();
    $table->timestamps();
});
```

### Tabel Log & Auth

**`log` → `activity_log`** (atau gunakan Spatie Activity Log)
```php
Schema::create('activity_log', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');
    $table->integer('group_id');
    $table->string('group_name', 100);
    $table->tinyInteger('log_type');
    $table->text('log_desc');
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->string('device', 100)->nullable();
    $table->timestamps();
});
```

**`log_ujian` → `log_ujian`**
```php
Schema::create('log_ujian', function (Blueprint $table) {
    $table->id();
    $table->foreignId('siswa_id')->nullable()->constrained('siswa')->nullOnDelete();
    $table->foreignId('jadwal_id')->nullable()->constrained('cbt_jadwal')->nullOnDelete();
    $table->tinyInteger('log_type');
    $table->text('log_desc');
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->string('device', 100)->nullable();
    $table->boolean('reset')->default(false);
    $table->string('finish_time', 25)->nullable();
    $table->timestamps();
});
```

---

## Model Penting & Relasi

### `BankSoal` Model
```php
class BankSoal extends Model
{
    protected $table = 'cbt_bank_soal';
    protected $casts = ['kelas' => 'array'];

    public function soal(): HasMany
    {
        return $this->hasMany(Soal::class, 'bank_id');
    }
    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'bank_id');
    }
    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    // Hitung jumlah soal per tipe dari relasi
    public function syncJumlahSoal(): void
    {
        $this->update([
            'jml_pg'       => $this->soal()->where('jenis', 1)->count(),
            'jml_kompleks' => $this->soal()->where('jenis', 2)->count(),
            'jml_jodohkan' => $this->soal()->where('jenis', 3)->count(),
            'jml_isian'    => $this->soal()->where('jenis', 4)->count(),
            'jml_esai'     => $this->soal()->where('jenis', 5)->count(),
        ]);
    }
}
```

### `Jadwal` Model
```php
class Jadwal extends Model
{
    protected $table = 'cbt_jadwal';
    protected $casts = ['pengawas' => 'array'];

    public function isOpen(): bool
    {
        $now = now();
        return $this->status == 1
            && $now->between(
                Carbon::parse($this->tgl_mulai),
                Carbon::parse($this->tgl_selesai)
            );
    }

    public function durasiSiswa(): HasMany
    {
        return $this->hasMany(DurasiSiswa::class, 'jadwal_id');
    }
    public function soalSiswa(): HasMany
    {
        return $this->hasMany(SoalSiswa::class, 'jadwal_id');
    }
    public function nilai(): HasMany
    {
        return $this->hasMany(CbtNilai::class, 'jadwal_id');
    }
}
```

### `Siswa` Model
```php
class Siswa extends Model
{
    public function kelasSiswa(): HasMany
    {
        return $this->hasMany(KelasSiswa::class, 'siswa_id');
    }
    public function kelasAktif(): HasOne
    {
        return $this->hasOne(KelasSiswa::class, 'siswa_id')
            ->whereHas('tahunPelajaran', fn($q) => $q->where('active', true))
            ->whereHas('semester', fn($q) => $q->where('active', true));
    }
    public function bukuInduk(): HasOne
    {
        return $this->hasOne(BukuInduk::class, 'siswa_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## CbtService — Logika Inti Ujian

```php
// app/Services/CbtService.php
class CbtService
{
    /**
     * Distribusikan soal ke siswa (panggil saat siswa mulai ujian pertama kali)
     */
    public function distribusiSoal(Jadwal $jadwal, Siswa $siswa): void
    {
        $bank = $jadwal->bank;

        // Kumpulkan soal per tipe sesuai konfigurasi tampil
        $soalPool = collect();
        $tipeMap = [
            1 => 'tampil_pg',
            2 => 'tampil_kompleks',
            3 => 'tampil_jodohkan',
            4 => 'tampil_isian',
            5 => 'tampil_esai',
        ];

        foreach ($tipeMap as $jenis => $field) {
            if ($bank->$field > 0) {
                $soal = Soal::where('bank_id', $bank->id)
                    ->where('jenis', $jenis)
                    ->inRandomOrder()
                    ->take($bank->$field)
                    ->get();
                $soalPool = $soalPool->merge($soal);
            }
        }

        // Acak urutan soal jika diaktifkan
        if ($jadwal->acak_soal) {
            $soalPool = $soalPool->shuffle();
        }

        $records = [];
        foreach ($soalPool as $idx => $soal) {
            $aliasMap = $jadwal->acak_opsi
                ? $this->generateOpsiAlias($bank->opsi)
                : ['a'=>'a','b'=>'b','c'=>'c','d'=>'d','e'=>'e'];

            $records[] = [
                'id'           => uniqid('ss'),
                'bank_id'      => $bank->id,
                'jadwal_id'    => $jadwal->id,
                'soal_id'      => $soal->id,
                'siswa_id'     => $siswa->id,
                'jenis_soal'   => $soal->jenis,
                'no_soal_alias'=> $idx + 1,
                'opsi_alias_a' => $aliasMap['a'],
                'opsi_alias_b' => $aliasMap['b'],
                'opsi_alias_c' => $aliasMap['c'],
                'opsi_alias_d' => $aliasMap['d'],
                'opsi_alias_e' => $aliasMap['e'],
                'jawaban_alias'=> $this->remapJawaban($soal->jawaban, $aliasMap),
                'soal_end'     => $idx === $soalPool->count() - 1,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        SoalSiswa::insert($records);

        // Catat durasi mulai
        DurasiSiswa::updateOrCreate(
            ['siswa_id' => $siswa->id, 'jadwal_id' => $jadwal->id],
            ['status' => 1, 'mulai' => now()->toISOString()]
        );
    }

    /**
     * Hitung & simpan nilai otomatis
     */
    public function hitungNilai(Jadwal $jadwal, Siswa $siswa): CbtNilai
    {
        $bank   = $jadwal->bank;
        $soalSiswa = SoalSiswa::where('jadwal_id', $jadwal->id)
            ->where('siswa_id', $siswa->id)->get();

        $pgBenar = 0; $pgNilai = 0;
        $kompleksNilai = 0; $jodohkanNilai = 0; $isianNilai = 0;
        $esaiNilai = 0;

        foreach ($soalSiswa as $ss) {
            $point = match ((int)$ss->jenis_soal) {
                1 => $this->skorPg($ss, $bank),
                2 => $this->skorKompleks($ss, $bank),
                3 => $this->skorJodohkan($ss, $bank),
                4 => $this->skorIsian($ss, $bank),
                5 => 0, // esai harus manual
                default => 0,
            };

            $ss->update(['point_soal' => $point, 'nilai_otomatis' => true]);

            match ((int)$ss->jenis_soal) {
                1 => ($pgBenar += ($point > 0 ? 1 : 0)) && ($pgNilai += $point),
                2 => $kompleksNilai += $point,
                3 => $jodohkanNilai += $point,
                4 => $isianNilai += $point,
                5 => $esaiNilai += $ss->point_esai,
                default => null,
            };
        }

        return CbtNilai::updateOrCreate(
            ['siswa_id' => $siswa->id, 'jadwal_id' => $jadwal->id],
            [
                'pg_benar'       => $pgBenar,
                'pg_nilai'       => round($pgNilai, 2),
                'kompleks_nilai' => round($kompleksNilai, 2),
                'jodohkan_nilai' => round($jodohkanNilai, 2),
                'isian_nilai'    => round($isianNilai, 2),
                'esai_nilai'     => round($esaiNilai, 2),
                'dikoreksi'      => false,
            ]
        );
    }

    private function skorPg(SoalSiswa $ss, BankSoal $bank): float
    {
        if (empty($ss->jawaban_siswa)) return 0;
        $bobotPerSoal = $bank->bobot_pg / max($bank->tampil_pg, 1);
        return strtoupper($ss->jawaban_siswa) === strtoupper($ss->jawaban_benar)
            ? $bobotPerSoal : 0;
    }

    private function generateOpsiAlias(int $jmlOpsi): array
    {
        $opsi = array_slice(['a','b','c','d','e'], 0, $jmlOpsi);
        $shuffled = $opsi;
        shuffle($shuffled);
        return array_combine($opsi, $shuffled);
    }

    private function remapJawaban(?string $jawaban, array $aliasMap): ?string
    {
        if (!$jawaban) return null;
        $lower = strtolower($jawaban);
        return strtoupper($aliasMap[$lower] ?? $jawaban);
    }
}
```

---

## Controller Pattern (Inertia)

### Contoh: `BankSoalController`
```php
class BankSoalController extends Controller
{
    public function index(): Response
    {
        $this->authorize('cbt.bank.view');

        $banks = BankSoal::with(['mapel','guru','jenis'])
            ->when(auth()->user()->hasRole('guru'), function ($q) {
                $q->where('guru_id', auth()->user()->guru->id);
            })
            ->latest()->paginate(15)->withQueryString();

        return Inertia::render('Cbt/BankSoal/Index', [
            'banks'   => BankSoalResource::collection($banks),
            'filters' => request()->only('search', 'jenis', 'mapel'),
        ]);
    }

    public function store(StoreBankSoalRequest $request): RedirectResponse
    {
        $this->authorize('cbt.bank.create');

        $bank = BankSoal::create($request->validated() + [
            'guru_id' => auth()->user()->guru?->id,
        ]);

        return redirect()->route('cbt.bank.show', $bank)
            ->with('success', 'Bank soal berhasil dibuat.');
    }
}
```

### Contoh: `UjianController` (untuk siswa)
```php
class UjianController extends Controller
{
    public function start(Jadwal $jadwal, CbtService $cbtService): Response|RedirectResponse
    {
        $siswa = auth()->user()->siswa;
        abort_unless($siswa, 403, 'Akun bukan siswa.');
        abort_unless($jadwal->isOpen(), 403, 'Ujian belum/sudah ditutup.');

        $durasi = DurasiSiswa::firstOrNew(
            ['siswa_id' => $siswa->id, 'jadwal_id' => $jadwal->id]
        );

        // Jika belum pernah mulai, distribusi soal
        if ($durasi->status === 0) {
            $cbtService->distribusiSoal($jadwal, $siswa);
            $durasi->refresh();
        }

        if ($durasi->status === 2) {
            return redirect()->route('ujian.selesai', $jadwal)
                ->with('info', 'Anda sudah menyelesaikan ujian ini.');
        }

        $soal = SoalSiswa::with('soal')
            ->where('jadwal_id', $jadwal->id)
            ->where('siswa_id', $siswa->id)
            ->orderBy('no_soal_alias')
            ->get();

        $sisaDetik = $this->hitungSisaWaktu($durasi, $jadwal);

        return Inertia::render('Cbt/Ujian/Soal', [
            'jadwal'     => $jadwal->only('id','durasi_ujian','acak_soal','hasil_tampil'),
            'soalList'   => SoalSiswaResource::collection($soal),
            'sisaDetik'  => $sisaDetik,
            'durasi'     => $durasi,
        ]);
    }

    public function jawab(JawabRequest $request, Jadwal $jadwal): JsonResponse
    {
        // Endpoint API — dipanggil Axios tiap siswa klik jawaban
        $siswa = auth()->user()->siswa;
        SoalSiswa::where('id', $request->id_soal_siswa)
            ->where('siswa_id', $siswa->id)
            ->update(['jawaban_siswa' => $request->jawaban]);

        return response()->json(['ok' => true]);
    }

    public function selesai(Jadwal $jadwal, CbtService $cbtService): Response
    {
        $siswa = auth()->user()->siswa;
        $durasi = DurasiSiswa::where('siswa_id', $siswa->id)
            ->where('jadwal_id', $jadwal->id)->firstOrFail();

        if ($durasi->status !== 2) {
            $durasi->update(['status' => 2, 'selesai' => now()->toISOString()]);
            $nilai = $cbtService->hitungNilai($jadwal, $siswa);
        }

        $hasilTampil = $jadwal->hasil_tampil
            ? CbtNilai::where('siswa_id',$siswa->id)->where('jadwal_id',$jadwal->id)->first()
            : null;

        return Inertia::render('Cbt/Ujian/Selesai', compact('hasilTampil'));
    }
}
```

---

## Komponen Vue Penting

### `TimerCountdown.vue`
```vue
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ sisaDetik: Number, jadwalId: Number })
const sisa = ref(props.sisaDetik)
let interval = null

onMounted(() => {
  interval = setInterval(() => {
    sisa.value--
    if (sisa.value <= 0) {
      clearInterval(interval)
      // Auto-submit
      router.post(route('ujian.selesai', props.jadwalId), {}, {
        preserveScroll: false,
      })
    }
  }, 1000)
})

onUnmounted(() => clearInterval(interval))

const fmt = (s) => {
  const h = Math.floor(s / 3600)
  const m = Math.floor((s % 3600) / 60)
  const sec = s % 60
  return [h, m, sec].map(v => String(v).padStart(2,'0')).join(':')
}
</script>

<template>
  <div :class="sisa < 300 ? 'text-red-600 animate-pulse font-bold' : 'text-gray-700'"
       class="text-2xl font-mono">
    {{ fmt(sisa) }}
  </div>
</template>
```

### `SoalRenderer.vue` (render HTML soal dengan aman)
```vue
<script setup>
defineProps({ soal: Object, nomor: Number })
</script>

<template>
  <div class="soal-wrap">
    <p class="font-semibold mb-2">{{ nomor }}.</p>
    <!-- File lampiran -->
    <div v-if="soal.soal.file" class="mb-3">
      <img v-if="soal.soal.tipe_file === 'image'"
           :src="`/storage/${soal.soal.file}`" class="max-h-64 rounded" />
      <audio v-else-if="soal.soal.tipe_file === 'audio'" controls
             :src="`/storage/${soal.soal.file}`" />
    </div>
    <!-- Teks soal (HTML dari rich editor) -->
    <div class="prose max-w-none mb-4"
         v-html="soal.soal.soal" />
  </div>
</template>
```

### Pinia Store `ujian.js`
```js
// resources/js/stores/ujian.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useUjianStore = defineStore('ujian', {
  state: () => ({
    jawaban: {},       // { [id_soal_siswa]: 'A' }
    soalAktif: 1,
    saving: false,
  }),
  actions: {
    async simpanJawaban(idSoalSiswa, jawaban, jadwalId) {
      this.jawaban[idSoalSiswa] = jawaban
      this.saving = true
      try {
        await axios.post(route('ujian.jawab', jadwalId), {
          id_soal_siswa: idSoalSiswa,
          jawaban,
        })
      } finally {
        this.saving = false
      }
    },
  },
})
```

---

## Konvensi Koding

### Request Validation
```php
// app/Http/Requests/Cbt/StoreBankSoalRequest.php
public function rules(): array
{
    return [
        'nama'           => 'required|string|max:255',
        'jenis_id'       => 'required|exists:cbt_jenis,id',
        'mapel_id'       => 'required|exists:mapel,id',
        'kelas'          => 'required|array|min:1',
        'kelas.*'        => 'exists:kelas,id',
        'tampil_pg'      => 'required|integer|min:0',
        'bobot_pg'       => 'required|integer|min:0|max:100',
        'tampil_esai'    => 'required|integer|min:0',
        'bobot_esai'     => 'required|integer|min:0|max:100',
        // validasi: total bobot harus 100
        'bobot_pg'       => [
            'required','integer','min:0',
            function ($attr, $val, $fail) {
                $total = $val
                    + (int)request('bobot_esai')
                    + (int)request('bobot_kompleks')
                    + (int)request('bobot_jodohkan')
                    + (int)request('bobot_isian');
                if ($total !== 100) $fail('Total bobot semua tipe soal harus 100.');
            }
        ],
        'kkm'            => 'required|integer|min:0|max:100',
        'opsi'           => 'required|integer|in:3,4,5',
        'durasi'         => 'required|integer|min:1',
        'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
        'semester_id'    => 'required|exists:semesters,id',
    ];
}
```

### API Resource
```php
// app/Http/Resources/Cbt/SoalSiswaResource.php
// Jangan kirim jawaban_benar ke frontend saat ujian berlangsung!
public function toArray(Request $request): array
{
    $sedangUjian = $this->durasi_siswa?->status === 1;
    return [
        'id'            => $this->id,
        'no'            => $this->no_soal_alias,
        'jenis'         => $this->jenis_soal,
        'soal'          => [
            'soal'      => $this->soal->soal,
            'file'      => $this->soal->file,
            'tipe_file' => $this->soal->tipe_file,
            'timer'     => $this->soal->timer,
            'timer_menit'=> $this->soal->timer_menit,
            // Opsi dengan alias
            'opsi_a'    => $this->soalOpsi('a'),
            'opsi_b'    => $this->soalOpsi('b'),
            'opsi_c'    => $this->soalOpsi('c'),
            'opsi_d'    => $this->soalOpsi('d'),
            'opsi_e'    => $this->soalOpsi('e'),
        ],
        'jawaban_siswa' => $this->jawaban_siswa,
        // Kunci HANYA dikirim jika bukan sedang ujian
        'jawaban_benar' => $sedangUjian ? null : $this->jawaban_benar,
        'point_soal'    => $sedangUjian ? null : $this->point_soal,
    ];
}
```

### Inertia Shared Data (AppServiceProvider / HandleInertiaRequests)
```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    return array_merge(parent::share($request), [
        'auth' => [
            'user'        => $request->user()?->only('id','name','email'),
            'roles'       => $request->user()?->getRoleNames(),
            'permissions' => $request->user()?->getAllPermissions()->pluck('name'),
        ],
        'flash' => [
            'success' => session('success'),
            'error'   => session('error'),
            'info'    => session('info'),
        ],
        'tp_aktif' => TahunPelajaran::where('active', true)->first(['id','tahun']),
        'smt_aktif'=> Semester::where('active', true)->first(['id','nama_smt']),
    ]);
}
```

---

## Fitur Khusus & Catatan Implementasi

### Token Ujian
- `cbt_token` menyimpan token 4-6 karakter. Proktor membagikan token ke siswa.
- Siswa wajib input token sebelum bisa mulai jika `jadwal.token = true`.
- Token otomatis berganti tiap N menit jika `auto = true` (gunakan Laravel Scheduler).

```php
// routes/console.php (Laravel 11+)
Schedule::call(function () {
    if (CbtToken::where('auto', true)->exists()) {
        CbtToken::where('auto', true)->update([
            'token' => strtoupper(Str::random(5))
        ]);
    }
})->everyFiveMinutes();
```

### Monitor Ujian (Proktor)
- Proktor polling `/api/monitor/{jadwal}` tiap 30 detik (Axios + Vue).
- Response: array siswa + status (belum/sedang/selesai) + progress jawaban.
- **Bukan** pakai Inertia untuk endpoint ini, pakai `Route::get` di `api.php`.

```php
// routes/api.php
Route::middleware(['auth:web','role:proktor|guru|superadmin'])
    ->get('/monitor/{jadwal}', [MonitorController::class, 'status']);
```

### Acak Soal & Opsi
- Acak soal: `inRandomOrder()` saat distribusi, simpan `no_soal_alias`.
- Acak opsi: mapping alias (A→C dst) disimpan di `opsi_alias_*`, kunci diremapping.
- Jangan pernah re-distribusi soal jika sudah ada record di `cbt_soal_siswa`.

### Import Siswa (Excel)
- Gunakan `maatwebsite/excel` (Laravel Excel).
- Import via `SiswaImport` class, validasi NIS/NISN duplikat.
- Setelah import, otomatis buat record `buku_induk` default.

### Upload File Soal
- Simpan ke `storage/app/public/soal/` via `Storage::disk('public')`.
- Buat symlink: `php artisan storage:link`.
- Batasi tipe: `jpeg,png,gif,mp3,mp4,pdf` maks 5 MB.

### Koreksi Esai / Uraian
- Guru buka halaman koreksi: tampil jawaban siswa, input nilai manual.
- Setelah semua esai dikoreksi, tandai `cbt_nilai.dikoreksi = true`.
- Nilai akhir = PG + kompleks + jodohkan + isian + esai (sesuai bobot).

### Reset Ujian Siswa
- Proktor bisa reset: `cbt_durasi_siswa.reset` → 1 (reset dari 0) atau 2 (lanjut sisa).
- Reset total (nilai 3) → hapus `cbt_soal_siswa` + `cbt_nilai`, distribusi ulang.

---

## Perintah Artisan Penting

```bash
# Setup awal
composer require laravel/breeze inertiajs/inertia-laravel spatie/laravel-permission maatwebsite/excel
php artisan breeze:install vue --ssr=false
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate --seed
php artisan storage:link

# Development
php artisan make:model Cbt/BankSoal -mrc    # Model + Migration + Controller
php artisan make:request Cbt/StoreBankSoalRequest
php artisan make:resource Cbt/BankSoalResource

# Pest testing
php artisan make:test CbtUjianTest --pest
```

---

## Urutan Pengembangan (Roadmap)

1. **Setup & Auth** — Install stack, role/permission seeder, layout dasar (Sidebar, Navbar)
2. **Master Data** — Tahun Pelajaran, Semester, Level, Jurusan, Mapel, Guru, Siswa, Kelas
3. **CBT Core** — Bank Soal → Input Soal (5 tipe) → Jadwal → Token → Ujian Siswa
4. **Pasca Ujian** — Nilai otomatis, Koreksi esai, Rekap nilai, Export Excel
5. **Monitor & Log** — Monitor proktor, Log ujian, Log aktivitas
6. **LMS** — Materi & Tugas
7. **Akademik** — KKM, Nilai harian/PTS, Rapor
8. **Komunikasi** — Post & Pengumuman
9. **Buku Induk** — CRUD + Print
10. **Setting & Polish** — Setting sekolah, Running text, Kop surat, Import/Export massal

---

## Hal yang TIDAK Boleh Dilakukan

- **JANGAN** kirim `jawaban_benar` ke frontend saat ujian berlangsung (XSS/cheating).
- **JANGAN** buat endpoint REST `/api` untuk semua fitur — gunakan Inertia, API hanya untuk polling real-time.
- **JANGAN** simpan HTML soal mentah tanpa sanitasi — gunakan `HTMLPurifier` atau `strip_tags` selektif saat display.
- **JANGAN** gunakan `$table->timestamps()` + manual `created_on`/`updated_on` bersamaan — pilih satu.
- **JANGAN** distribusi ulang soal jika `cbt_soal_siswa` sudah terisi (data jawaban hilang).
- **JANGAN** hardcode `id_tp`/`id_smt` — selalu ambil dari `tahun_pelajaran` & `semesters` yang `active = true`.
# GarudaCBT — Navigasi Admin & Modul Tambahan

Dokumen ini adalah **suplemen** dari `SKILL.md` utama.
Berisi: struktur navigasi sidebar lengkap, komponen Vue Sidebar, route mapping,
dan modul yang belum tercakup di SKILL.md utama.

---

## Struktur Navigasi Admin (Lengkap)

Navigasi dikelompokkan dalam 4 section dengan separator label:

```
HOME
├── Dashboard

DATA UMUM (label separator)
├── Data Umum (group)
│   ├── Tahun Pelajaran      → /master/tahun-pelajaran
│   ├── Mata Pelajaran       → /master/mapel
│   ├── Jurusan              → /master/jurusan
│   ├── Siswa                → /master/siswa
│   ├── Kelas / Rombel       → /master/kelas
│   ├── Ekstrakurikuler      → /master/ekstra
│   └── Guru                 → /master/guru
├── Data Ujian (group)
│   ├── Jenis Ujian          → /cbt/jenis
│   ├── Sesi                 → /cbt/sesi
│   ├── Ruang                → /cbt/ruang
│   ├── Atur Ruang/Sesi      → /cbt/atur-ruang-sesi
│   ├── Nomor Peserta        → /cbt/nomor-peserta
│   ├── Bank Soal            → /cbt/bank-soal
│   ├── Jadwal               → /cbt/jadwal
│   ├── Alokasi Waktu        → /cbt/alokasi
│   ├── Pengawas             → /cbt/pengawas
│   └── Token                → /cbt/token
└── Pengumuman               → /pengumuman

PELAKSANAAN (label separator)
└── Pelaksanaan Ujian (group)
    ├── Cetak                → /cbt/cetak
    ├── Status Siswa         → /cbt/status
    ├── Hasil Ujian          → /cbt/nilai
    ├── Analisis Soal        → /cbt/analisis
    └── Rekap Nilai          → /cbt/rekap

RAPOR (label separator)
└── Setting Rapor            → /rapor/setting

PENGATURAN (label separator)
├── Profile Sekolah          → /setting/sekolah
├── User Management (group)
│   ├── Administrator        → /setting/user/admin
│   ├── Guru                 → /setting/user/guru
│   └── Siswa                → /setting/user/siswa
└── Database (group)
    ├── Backup               → /setting/database/backup
    └── Data Manager         → /setting/database/manager
```

---

## Komponen Vue: `AppSidebar.vue`

```vue
<!-- resources/js/Components/Layout/AppSidebar.vue -->
<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  HomeIcon, ChevronDownIcon,
  AcademicCapIcon, ClipboardDocumentListIcon,
  UserGroupIcon, Cog6ToothIcon, CircleStackIcon,
  BellAlertIcon, PrinterIcon, ChartBarIcon,
  DocumentTextIcon, ShieldCheckIcon,
} from '@heroicons/vue/24/outline'

const page   = usePage()
const roles  = computed(() => page.props.auth.roles ?? [])
const perms  = computed(() => page.props.auth.permissions ?? [])

// helper: cek apakah URL aktif
const isActive = (routeName) => route().current(routeName + '*')

// open state untuk accordion group
const open = ref({
  dataUmum:    false,
  dataUjian:   false,
  pelaksanaan: false,
  userMgmt:    false,
  database:    false,
})
const toggle = (key) => { open.value[key] = !open.value[key] }

// Visibility per role
const can = (permission) => perms.value.includes(permission)
const hasRole = (...r) => r.some(role => roles.value.includes(role))
</script>

<template>
  <aside class="fixed inset-y-0 left-0 w-64 bg-gray-900 text-gray-100 flex flex-col z-40 overflow-y-auto">

    <!-- Logo -->
    <div class="h-16 flex items-center px-5 border-b border-gray-700 shrink-0">
      <span class="text-xl font-bold tracking-wide text-white">GarudaCBT</span>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 text-sm">

      <!-- ── HOME ── -->
      <p class="px-3 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
        Home
      </p>

      <Link :href="route('dashboard')"
            :class="isActive('dashboard') ? 'bg-gray-700 text-white' : 'hover:bg-gray-800'"
            class="flex items-center gap-3 px-3 py-2 rounded-lg">
        <HomeIcon class="w-5 h-5" />
        Dashboard
      </Link>

      <!-- ── DATA UMUM ── -->
      <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
        Data Umum
      </p>

      <!-- Grup: Data Umum -->
      <div>
        <button @click="toggle('dataUmum')"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-800">
          <span class="flex items-center gap-3"><UserGroupIcon class="w-5 h-5" /> Data Umum</span>
          <ChevronDownIcon :class="open.dataUmum ? 'rotate-180' : ''"
                           class="w-4 h-4 transition-transform" />
        </button>
        <div v-show="open.dataUmum" class="ml-8 mt-1 space-y-1">
          <NavChild :href="route('master.tahun-pelajaran.index')" label="Tahun Pelajaran"
                    v-if="hasRole('superadmin','operator')" />
          <NavChild :href="route('master.mapel.index')"          label="Mata Pelajaran" />
          <NavChild :href="route('master.jurusan.index')"        label="Jurusan" />
          <NavChild :href="route('master.siswa.index')"          label="Siswa" />
          <NavChild :href="route('master.kelas.index')"          label="Kelas / Rombel" />
          <NavChild :href="route('master.ekstra.index')"         label="Ekstrakurikuler" />
          <NavChild :href="route('master.guru.index')"           label="Guru" />
        </div>
      </div>

      <!-- Grup: Data Ujian -->
      <div>
        <button @click="toggle('dataUjian')"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-800">
          <span class="flex items-center gap-3">
            <ClipboardDocumentListIcon class="w-5 h-5" /> Data Ujian
          </span>
          <ChevronDownIcon :class="open.dataUjian ? 'rotate-180' : ''"
                           class="w-4 h-4 transition-transform" />
        </button>
        <div v-show="open.dataUjian" class="ml-8 mt-1 space-y-1">
          <NavChild :href="route('cbt.jenis.index')"       label="Jenis Ujian" />
          <NavChild :href="route('cbt.sesi.index')"        label="Sesi" />
          <NavChild :href="route('cbt.ruang.index')"       label="Ruang" />
          <NavChild :href="route('cbt.atur-ruang.index')"  label="Atur Ruang/Sesi" />
          <NavChild :href="route('cbt.nomor-peserta.index')" label="Nomor Peserta" />
          <NavChild :href="route('cbt.bank-soal.index')"   label="Bank Soal" />
          <NavChild :href="route('cbt.jadwal.index')"      label="Jadwal" />
          <NavChild :href="route('cbt.alokasi.index')"     label="Alokasi Waktu" />
          <NavChild :href="route('cbt.pengawas.index')"    label="Pengawas" />
          <NavChild :href="route('cbt.token.index')"       label="Token" />
        </div>
      </div>

      <!-- Pengumuman -->
      <Link :href="route('pengumuman.index')"
            :class="isActive('pengumuman') ? 'bg-gray-700 text-white' : 'hover:bg-gray-800'"
            class="flex items-center gap-3 px-3 py-2 rounded-lg">
        <BellAlertIcon class="w-5 h-5" />
        Pengumuman
      </Link>

      <!-- ── PELAKSANAAN ── -->
      <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
        Pelaksanaan
      </p>

      <div>
        <button @click="toggle('pelaksanaan')"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-800">
          <span class="flex items-center gap-3">
            <AcademicCapIcon class="w-5 h-5" /> Pelaksanaan Ujian
          </span>
          <ChevronDownIcon :class="open.pelaksanaan ? 'rotate-180' : ''"
                           class="w-4 h-4 transition-transform" />
        </button>
        <div v-show="open.pelaksanaan" class="ml-8 mt-1 space-y-1">
          <NavChild :href="route('cbt.cetak.index')"    label="Cetak" />
          <NavChild :href="route('cbt.status.index')"   label="Status Siswa" />
          <NavChild :href="route('cbt.nilai.index')"    label="Hasil Ujian" />
          <NavChild :href="route('cbt.analisis.index')" label="Analisis Soal" />
          <NavChild :href="route('cbt.rekap.index')"    label="Rekap Nilai" />
        </div>
      </div>

      <!-- ── RAPOR ── -->
      <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
        Rapor
      </p>

      <Link :href="route('rapor.setting.index')"
            :class="isActive('rapor') ? 'bg-gray-700 text-white' : 'hover:bg-gray-800'"
            class="flex items-center gap-3 px-3 py-2 rounded-lg">
        <DocumentTextIcon class="w-5 h-5" />
        Setting Rapor
      </Link>

      <!-- ── PENGATURAN ── -->
      <p class="px-3 pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
        Pengaturan
      </p>

      <Link :href="route('setting.sekolah')"
            :class="isActive('setting.sekolah') ? 'bg-gray-700 text-white' : 'hover:bg-gray-800'"
            class="flex items-center gap-3 px-3 py-2 rounded-lg">
        <Cog6ToothIcon class="w-5 h-5" />
        Profile Sekolah
      </Link>

      <!-- User Management — hanya superadmin -->
      <div v-if="hasRole('superadmin')">
        <button @click="toggle('userMgmt')"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-800">
          <span class="flex items-center gap-3">
            <ShieldCheckIcon class="w-5 h-5" /> User Management
          </span>
          <ChevronDownIcon :class="open.userMgmt ? 'rotate-180' : ''"
                           class="w-4 h-4 transition-transform" />
        </button>
        <div v-show="open.userMgmt" class="ml-8 mt-1 space-y-1">
          <NavChild :href="route('setting.user.admin')" label="Administrator" />
          <NavChild :href="route('setting.user.guru')"  label="Guru" />
          <NavChild :href="route('setting.user.siswa')" label="Siswa" />
        </div>
      </div>

      <!-- Database — hanya superadmin -->
      <div v-if="hasRole('superadmin')">
        <button @click="toggle('database')"
                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-800">
          <span class="flex items-center gap-3">
            <CircleStackIcon class="w-5 h-5" /> Database
          </span>
          <ChevronDownIcon :class="open.database ? 'rotate-180' : ''"
                           class="w-4 h-4 transition-transform" />
        </button>
        <div v-show="open.database" class="ml-8 mt-1 space-y-1">
          <NavChild :href="route('setting.database.backup')"  label="Backup" />
          <NavChild :href="route('setting.database.manager')" label="Data Manager" />
        </div>
      </div>

    </nav>

    <!-- User Info Bottom -->
    <div class="px-4 py-3 border-t border-gray-700 shrink-0">
      <p class="text-xs text-gray-400">Login sebagai</p>
      <p class="font-semibold text-white truncate">{{ $page.props.auth.user?.name }}</p>
      <p class="text-xs text-blue-400 capitalize">{{ roles[0] }}</p>
    </div>
  </aside>
</template>
```

### `NavChild` Sub-Komponen (inline atau pisah)

```vue
<!-- resources/js/Components/Layout/NavChild.vue -->
<script setup>
import { Link, usePage } from '@inertiajs/vue3'
const { href, label } = defineProps(['href', 'label'])
const active = usePage().url.startsWith(new URL(href, location.origin).pathname)
</script>
<template>
  <Link :href="href"
        :class="active ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'"
        class="block px-3 py-1.5 rounded-md text-sm transition-colors">
    {{ label }}
  </Link>
</template>
```

---

## Route Mapping Lengkap

```php
// routes/web.php

// ── Master Data ──────────────────────────────────
Route::middleware(['auth','role:superadmin|operator|guru'])
    ->prefix('master')->name('master.')
    ->group(function () {
        Route::resource('tahun-pelajaran', Master\TahunPelajaranController::class);
        Route::resource('mapel',           Master\MapelController::class);
        Route::resource('jurusan',         Master\JurusanController::class);
        Route::resource('siswa',           Master\SiswaController::class);
        Route::resource('kelas',           Master\KelasController::class);
        Route::resource('ekstra',          Master\EkstraController::class);
        Route::resource('guru',            Master\GuruController::class);
    });

// ── CBT Data & Konfigurasi ───────────────────────
Route::middleware(['auth','role:superadmin|operator|guru|proktor'])
    ->prefix('cbt')->name('cbt.')
    ->group(function () {
        Route::resource('jenis',         Cbt\JenisController::class);
        Route::resource('sesi',          Cbt\SesiController::class);
        Route::resource('ruang',         Cbt\RuangController::class);
        Route::resource('atur-ruang',    Cbt\AturRuangSesiController::class);
        Route::resource('nomor-peserta', Cbt\NomorPesertaController::class);
        Route::resource('bank-soal',     Cbt\BankSoalController::class);
        Route::resource('jadwal',        Cbt\JadwalController::class);
        Route::resource('alokasi',       Cbt\AlokasiController::class);
        Route::resource('pengawas',      Cbt\PengawasController::class);
        Route::resource('token',         Cbt\TokenController::class);

        // Pelaksanaan
        Route::get('cetak',             [Cbt\CetakController::class,    'index'])->name('cetak.index');
        Route::get('cetak/{jadwal}/{type}', [Cbt\CetakController::class,'print'])->name('cetak.print');
        Route::get('status',            [Cbt\StatusSiswaController::class,'index'])->name('status.index');
        Route::get('nilai',             [Cbt\NilaiController::class,    'index'])->name('nilai.index');
        Route::get('nilai/{jadwal}/{siswa}/koreksi', [Cbt\NilaiController::class,'koreksi'])->name('nilai.koreksi');
        Route::put('nilai/{jadwal}/{siswa}/koreksi', [Cbt\NilaiController::class,'simpanKoreksi'])->name('nilai.simpan-koreksi');
        Route::get('analisis',          [Cbt\AnalisisController::class, 'index'])->name('analisis.index');
        Route::get('analisis/{jadwal}', [Cbt\AnalisisController::class, 'show'])->name('analisis.show');
        Route::get('rekap',             [Cbt\RekapController::class,    'index'])->name('rekap.index');
        Route::get('rekap/export/{jadwal}', [Cbt\RekapController::class,'export'])->name('rekap.export');
    });

// ── Ujian Siswa (route khusus) ───────────────────
Route::middleware(['auth','role:siswa'])
    ->prefix('ujian')->name('ujian.')
    ->group(base_path('routes/ujian.php'));

// ── Pengumuman ───────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::resource('pengumuman', PengumumanController::class);
});

// ── Rapor ────────────────────────────────────────
Route::middleware(['auth','role:superadmin|guru|operator'])
    ->prefix('rapor')->name('rapor.')
    ->group(function () {
        Route::get('setting',  [Akademik\RaporController::class,'setting'])->name('setting.index');
        Route::put('setting',  [Akademik\RaporController::class,'saveSetting'])->name('setting.save');
        Route::get('{kelas}',  [Akademik\RaporController::class,'index'])->name('index');
        Route::get('{kelas}/{siswa}', [Akademik\RaporController::class,'show'])->name('show');
    });

// ── Pengaturan ───────────────────────────────────
Route::middleware(['auth','role:superadmin'])
    ->prefix('setting')->name('setting.')
    ->group(function () {
        Route::get('sekolah',  [SettingController::class,'sekolah'])->name('sekolah');
        Route::put('sekolah',  [SettingController::class,'saveSekolah'])->name('sekolah.save');

        // User Management
        Route::get('user/admin',  [UserManagementController::class,'admin'])->name('user.admin');
        Route::get('user/guru',   [UserManagementController::class,'guru'])->name('user.guru');
        Route::get('user/siswa',  [UserManagementController::class,'siswa'])->name('user.siswa');
        Route::put('user/{user}/toggle', [UserManagementController::class,'toggle'])->name('user.toggle');
        Route::put('user/{user}/reset-password', [UserManagementController::class,'resetPassword'])->name('user.reset-password');

        // Database
        Route::get('database/backup',        [DatabaseController::class,'backup'])->name('database.backup');
        Route::post('database/backup/run',   [DatabaseController::class,'runBackup'])->name('database.backup.run');
        Route::get('database/manager',       [DatabaseController::class,'manager'])->name('database.manager');
        Route::post('database/manager/clear',[DatabaseController::class,'clear'])->name('database.manager.clear');
    });
```

---

## Modul yang Belum Ada di SKILL.md Utama

### 1. Alokasi Waktu (`cbt_jadwal` extension)

Alokasi waktu mengatur distribusi mata pelajaran ke slot waktu ujian.
Tidak ada tabel terpisah di DB asli — disimpan sebagai relasi jadwal + jam_ke.

```php
// AlokasiController.php — tampilkan grid alokasi per hari/sesi
class AlokasiController extends Controller
{
    public function index(): Response
    {
        $tp  = TahunPelajaran::where('active', true)->firstOrFail();
        $smt = Semester::where('active', true)->firstOrFail();

        $jadwal = Jadwal::with(['bank.mapel','bank.jenis'])
            ->where('tahun_pelajaran_id', $tp->id)
            ->where('semester_id', $smt->id)
            ->orderBy('tgl_mulai')
            ->get()
            ->groupBy(fn($j) => Carbon::parse($j->tgl_mulai)->format('Y-m-d'));

        return Inertia::render('Cbt/Alokasi/Index', [
            'jadwalPerHari' => $jadwal,
            'tp'            => $tp,
            'smt'           => $smt,
        ]);
    }
}
```

---

### 2. Nomor Peserta (`cbt_nomor_peserta`)

```php
// Migration
Schema::create('cbt_nomor_peserta', function (Blueprint $table) {
    $table->id();
    $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->string('nomor_peserta', 25)->unique();
    $table->timestamps();
});

// NomorPesertaController — generate otomatis
public function generate(Request $request): RedirectResponse
{
    $tp  = TahunPelajaran::where('active', true)->firstOrFail();
    $smt = Semester::where('active', true)->firstOrFail();

    // Ambil siswa aktif per kelas, generate nomor berurutan
    $siswaList = KelasSiswa::with('siswa','kelas')
        ->where('tahun_pelajaran_id', $tp->id)
        ->where('semester_id', $smt->id)
        ->orderBy('kelas_id')
        ->get();

    $counter = 1;
    foreach ($siswaList as $ks) {
        NomorPeserta::updateOrCreate(
            ['siswa_id' => $ks->siswa_id, 'tahun_pelajaran_id' => $tp->id, 'semester_id' => $smt->id],
            ['nomor_peserta' => str_pad($counter++, 4, '0', STR_PAD_LEFT)]
        );
    }
    return back()->with('success', 'Nomor peserta berhasil di-generate.');
}
```

---

### 3. Atur Ruang/Sesi (`cbt_kelas_ruang` + `cbt_sesi_siswa`)

```php
// Migration cbt_kelas_ruang
Schema::create('cbt_kelas_ruang', function (Blueprint $table) {
    $table->string('id', 60)->primary();
    $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
    $table->foreignId('ruang_id')->constrained('cbt_ruang');
    $table->foreignId('sesi_id')->constrained('cbt_sesi');
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->boolean('set_siswa')->default(false);
    $table->timestamps();
});

// Migration cbt_sesi_siswa
Schema::create('cbt_sesi_siswa', function (Blueprint $table) {
    $table->id();
    $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
    $table->foreignId('kelas_id')->nullable()->constrained('kelas');
    $table->foreignId('ruang_id')->constrained('cbt_ruang');
    $table->foreignId('sesi_id')->constrained('cbt_sesi');
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->timestamps();
    $table->unique(['siswa_id','tahun_pelajaran_id','semester_id']);
});
```

---

### 4. Pengawas (`cbt_pengawas`)

```php
// Migration
Schema::create('cbt_pengawas', function (Blueprint $table) {
    $table->string('id', 60)->primary();
    $table->foreignId('jadwal_id')->constrained('cbt_jadwal')->cascadeOnDelete();
    $table->foreignId('tahun_pelajaran_id')->constrained('tahun_pelajaran');
    $table->foreignId('semester_id')->constrained('semesters');
    $table->foreignId('ruang_id')->constrained('cbt_ruang');
    $table->foreignId('sesi_id')->constrained('cbt_sesi');
    $table->foreignId('guru_id')->constrained('guru');
    $table->timestamps();
});

// PengawasController
public function store(Request $request): RedirectResponse
{
    $request->validate([
        'jadwal_id' => 'required|exists:cbt_jadwal,id',
        'ruang_id'  => 'required|exists:cbt_ruang,id',
        'sesi_id'   => 'required|exists:cbt_sesi,id',
        'guru_id'   => 'required|exists:guru,id',
    ]);

    Pengawas::updateOrCreate(
        [
            'jadwal_id' => $request->jadwal_id,
            'ruang_id'  => $request->ruang_id,
            'sesi_id'   => $request->sesi_id,
        ],
        [
            'id'                 => uniqid('pgw'),
            'guru_id'            => $request->guru_id,
            'tahun_pelajaran_id' => TahunPelajaran::activeId(),
            'semester_id'        => Semester::activeId(),
        ]
    );
    return back()->with('success', 'Pengawas berhasil disimpan.');
}
```

---

### 5. Status Siswa (Monitor Detail)

```php
// StatusSiswaController — tampilan real-time via polling
class StatusSiswaController extends Controller
{
    public function index(): Response
    {
        $jadwalAktif = Jadwal::where('status', 1)
            ->where('tgl_mulai', '<=', now())
            ->where('tgl_selesai', '>=', now())
            ->with('bank.mapel')
            ->get();

        return Inertia::render('Cbt/Status/Index', [
            'jadwalAktif' => $jadwalAktif,
        ]);
    }
}

// API endpoint (polling Axios dari Vue)
// routes/api.php
Route::middleware(['auth:web','role:superadmin|proktor|guru'])
    ->get('/cbt/status/{jadwal}', function (Jadwal $jadwal) {
        $data = DurasiSiswa::with(['siswa:id,nama,nis', 'siswa.kelasAktif.kelas:id,nama_kelas'])
            ->where('jadwal_id', $jadwal->id)
            ->get()
            ->map(fn($d) => [
                'siswa'         => $d->siswa->nama,
                'nis'           => $d->siswa->nis,
                'kelas'         => $d->siswa->kelasAktif?->kelas?->nama_kelas,
                'status'        => $d->status,   // 0/1/2
                'status_label'  => ['Belum','Sedang','Selesai'][$d->status],
                'mulai'         => $d->mulai,
                'selesai'       => $d->selesai,
                // Progress: soal terjawab / total
                'progress'      => [
                    'jawab' => SoalSiswa::where('jadwal_id',$jadwal->id)
                                ->where('siswa_id',$d->siswa_id)
                                ->whereNotNull('jawaban_siswa')->count(),
                    'total' => SoalSiswa::where('jadwal_id',$jadwal->id)
                                ->where('siswa_id',$d->siswa_id)->count(),
                ],
            ]);
        return response()->json($data);
    });
```

**Vue component polling:**
```vue
<!-- resources/js/Pages/Cbt/Status/Index.vue -->
<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props    = defineProps({ jadwalAktif: Array })
const selected = ref(props.jadwalAktif[0]?.id ?? null)
const peserta  = ref([])
let timer      = null

const fetch = async () => {
  if (!selected.value) return
  const { data } = await axios.get(`/api/cbt/status/${selected.value}`)
  peserta.value = data
}

onMounted(() => { fetch(); timer = setInterval(fetch, 30_000) })
onUnmounted(() => clearInterval(timer))
</script>

<template>
  <div>
    <!-- Pilih jadwal aktif -->
    <select v-model="selected" @change="fetch" class="...">
      <option v-for="j in jadwalAktif" :key="j.id" :value="j.id">
        {{ j.bank?.mapel?.nama_mapel }}
      </option>
    </select>

    <!-- Tabel status peserta -->
    <table class="w-full text-sm mt-4">
      <thead><tr>
        <th>Nama</th><th>Kelas</th><th>Status</th><th>Progress</th><th>Mulai</th>
      </tr></thead>
      <tbody>
        <tr v-for="p in peserta" :key="p.nis">
          <td>{{ p.siswa }}</td>
          <td>{{ p.kelas }}</td>
          <td>
            <span :class="{
              'bg-gray-200': p.status === 0,
              'bg-green-100 text-green-700': p.status === 1,
              'bg-blue-100 text-blue-700':  p.status === 2,
            }" class="px-2 py-0.5 rounded text-xs font-medium">
              {{ p.status_label }}
            </span>
          </td>
          <td>{{ p.progress.jawab }} / {{ p.progress.total }}</td>
          <td>{{ p.mulai }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
```

---

### 6. Analisis Soal

Analisis tingkat kesulitan soal berdasarkan data jawaban siswa.

```php
// AnalisisController
class AnalisisController extends Controller
{
    public function show(Jadwal $jadwal): Response
    {
        $bank = $jadwal->bank;

        // Hitung statistik per soal
        $analisis = Soal::where('bank_id', $bank->id)
            ->where('jenis', 1) // PG saja yang bisa dianalisis otomatis
            ->get()
            ->map(function ($soal) use ($jadwal) {
                $total   = SoalSiswa::where('jadwal_id', $jadwal->id)
                               ->where('soal_id', $soal->id)->count();
                $benar   = SoalSiswa::where('jadwal_id', $jadwal->id)
                               ->where('soal_id', $soal->id)
                               ->whereColumn('jawaban_siswa', 'jawaban_benar')->count();
                $tingkat = $total > 0 ? round(($benar / $total) * 100, 1) : 0;

                return [
                    'no'           => $soal->nomor_soal,
                    'total_peserta'=> $total,
                    'benar'        => $benar,
                    'salah'        => $total - $benar,
                    'tingkat'      => $tingkat,    // % yang menjawab benar
                    'kategori'     => match(true) {
                        $tingkat >= 76 => 'Mudah',
                        $tingkat >= 26 => 'Sedang',
                        default        => 'Sulit',
                    },
                    'kesulitan_db' => $soal->kesulitan,
                ];
            });

        return Inertia::render('Cbt/Analisis/Show', [
            'jadwal'  => $jadwal->load('bank.mapel'),
            'analisis'=> $analisis,
            'ringkasan' => [
                'mudah'  => $analisis->where('kategori','Mudah')->count(),
                'sedang' => $analisis->where('kategori','Sedang')->count(),
                'sulit'  => $analisis->where('kategori','Sulit')->count(),
            ],
        ]);
    }
}
```

---

### 7. Cetak Dokumen (`CetakController`)

```php
class CetakController extends Controller
{
    public function index(): Response
    {
        $tp  = TahunPelajaran::where('active', true)->firstOrFail();
        $smt = Semester::where('active', true)->firstOrFail();

        $jadwal = Jadwal::with('bank.mapel','bank.jenis')
            ->where('tahun_pelajaran_id', $tp->id)
            ->where('semester_id', $smt->id)
            ->latest()->get();

        return Inertia::render('Cbt/Cetak/Index', compact('jadwal'));
    }

    // type: 'absensi' | 'kartu' | 'berita_acara' | 'nilai'
    public function print(Jadwal $jadwal, string $type): Response
    {
        return match($type) {
            'absensi'      => $this->cetakAbsensi($jadwal),
            'kartu'        => $this->cetakKartu($jadwal),
            'berita_acara' => $this->cetakBeritaAcara($jadwal),
            'nilai'        => $this->cetakNilai($jadwal),
            default        => abort(404),
        };
    }

    private function cetakAbsensi(Jadwal $jadwal): Response
    {
        // Gunakan kop dari cbt_kop_absensi
        $kop = DB::table('cbt_kop_absensi')->first();
        $peserta = SoalSiswa::select('siswa_id')->distinct()
            ->where('jadwal_id', $jadwal->id)
            ->with('siswa:id,nama,nis', 'siswa.nomorPeserta')
            ->get();

        return Inertia::render('Cbt/Cetak/Absensi', compact('jadwal','kop','peserta'));
    }
}
```

> **Catatan cetak:** Gunakan CSS `@media print` + tombol `window.print()` di Vue.
> Atau gunakan library `vue-html-to-paper` untuk print langsung dari browser.

---

### 8. User Management

```php
class UserManagementController extends Controller
{
    public function admin(): Response
    {
        $users = User::role(['superadmin','operator','kepsek'])
            ->latest()->paginate(20);
        return Inertia::render('Setting/User/Admin', ['users' => UserResource::collection($users)]);
    }

    public function guru(): Response
    {
        $users = User::role('guru')->with('guru:id,user_id,nama_guru,nip')
            ->latest()->paginate(20);
        return Inertia::render('Setting/User/Guru', ['users' => UserResource::collection($users)]);
    }

    public function siswa(): Response
    {
        $users = User::role('siswa')->with('siswa:id,user_id,nama,nis')
            ->latest()->paginate(20);
        return Inertia::render('Setting/User/Siswa', ['users' => UserResource::collection($users)]);
    }

    public function toggle(User $user): RedirectResponse
    {
        $user->update(['active' => !$user->active]);
        return back()->with('success', 'Status user diperbarui.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate(['password' => 'required|min:6|confirmed']);
        $user->update(['password' => bcrypt($request->password)]);
        return back()->with('success', 'Password berhasil direset.');
    }
}
```

---

### 9. Profile Sekolah & Setting

```php
// Migration tabel setting (dari tabel `setting` di DB asli)
Schema::create('setting', function (Blueprint $table) {
    $table->id();
    $table->string('nama_sekolah', 255)->nullable();
    $table->string('nss', 30)->nullable();
    $table->string('npsn', 15)->nullable();
    $table->string('alamat', 255)->nullable();
    $table->string('kecamatan', 100)->nullable();
    $table->string('kabupaten', 100)->nullable();
    $table->string('provinsi', 100)->nullable();
    $table->string('kode_pos', 10)->nullable();
    $table->string('telp', 20)->nullable();
    $table->string('email', 100)->nullable();
    $table->string('website', 100)->nullable();
    $table->string('logo', 255)->nullable();
    $table->string('kepala_sekolah', 100)->nullable();
    $table->string('nip_kepsek', 30)->nullable();
    $table->text('running_text')->nullable();
    $table->timestamps();
});

// SettingController
class SettingController extends Controller
{
    public function sekolah(): Response
    {
        $setting = Setting::firstOrCreate([]);
        return Inertia::render('Setting/Sekolah', compact('setting'));
    }

    public function saveSekolah(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'npsn'         => 'nullable|string|max:15',
            'alamat'       => 'nullable|string',
            'kepala_sekolah' => 'nullable|string|max:100',
            'nip_kepsek'   => 'nullable|string|max:30',
            'logo'         => 'nullable|image|max:2048',
            'running_text' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')
                ->store('setting', 'public');
        }

        Setting::updateOrCreate([], $data);
        return back()->with('success', 'Profile sekolah berhasil disimpan.');
    }
}
```

---

### 10. Database Backup & Data Manager

```php
// Require: spatie/db-dumper
// composer require spatie/db-dumper

class DatabaseController extends Controller
{
    public function runBackup(): RedirectResponse
    {
        $filename = 'backup_garudacbt_' . now()->format('Ymd_His') . '.sql';
        $path     = storage_path("app/backups/{$filename}");

        \Spatie\DbDumper\Databases\MySql::create()
            ->setDbName(config('database.connections.mysql.database'))
            ->setUserName(config('database.connections.mysql.username'))
            ->setPassword(config('database.connections.mysql.password'))
            ->dumpToFile($path);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    // Data Manager: clear data per modul (hati-hati!)
    public function clear(Request $request): RedirectResponse
    {
        $request->validate(['modul' => 'required|in:ujian,nilai,log,materi']);

        match($request->modul) {
            'ujian'  => DB::table('cbt_soal_siswa')->truncate()
                        && DB::table('cbt_durasi_siswa')->truncate(),
            'nilai'  => DB::table('cbt_nilai')->truncate()
                        && DB::table('cbt_rekap_nilai')->truncate(),
            'log'    => DB::table('activity_log')->truncate()
                        && DB::table('log_ujian')->truncate(),
            'materi' => DB::table('log_materi')->truncate(),
        };

        return back()->with('success', "Data {$request->modul} berhasil dibersihkan.");
    }
}
```

> ⚠️ **Data Manager harus dilindungi konfirmasi 2 langkah** di frontend (modal konfirmasi + ketik ulang nama modul).

---

## Tabel Tambahan yang Belum Ada di SKILL.md Utama

### `master_ekstra` → `ekstra`
```php
Schema::create('ekstra', function (Blueprint $table) {
    $table->id();
    $table->string('nama_ekstra', 150);
    $table->string('kode_ekstra', 25)->unique();
    $table->timestamps();
});
```

### `cbt_kop_absensi`, `cbt_kop_berita`, `cbt_kop_kartu`
```php
// Ketiga tabel kop dokumen — struktur mirip
Schema::create('cbt_kop_absensi', function (Blueprint $table) {
    $table->id();
    $table->string('header_1', 150)->nullable(); // Nama instansi / kementerian
    $table->string('header_2', 150)->nullable(); // Nama sekolah
    $table->string('header_3', 150)->nullable(); // Alamat
    $table->string('header_4', 150)->nullable(); // Kota, telp
    $table->string('proktor', 150)->nullable();
    $table->string('pengawas_1', 150)->nullable();
    $table->string('pengawas_2', 150)->nullable();
    $table->timestamps();
});
// cbt_kop_berita dan cbt_kop_kartu — struktur sama tanpa kolom proktor/pengawas
```

### `running_text`
```php
Schema::create('running_text', function (Blueprint $table) {
    $table->id();
    $table->text('text');
    $table->boolean('active')->default(true);
    $table->timestamps();
});
```

---

## Ringkasan Route Names

| Menu | Route Name |
|---|---|
| Dashboard | `dashboard` |
| Tahun Pelajaran | `master.tahun-pelajaran.index` |
| Mata Pelajaran | `master.mapel.index` |
| Jurusan | `master.jurusan.index` |
| Siswa | `master.siswa.index` |
| Kelas / Rombel | `master.kelas.index` |
| Ekstrakurikuler | `master.ekstra.index` |
| Guru | `master.guru.index` |
| Jenis Ujian | `cbt.jenis.index` |
| Sesi | `cbt.sesi.index` |
| Ruang | `cbt.ruang.index` |
| Atur Ruang/Sesi | `cbt.atur-ruang.index` |
| Nomor Peserta | `cbt.nomor-peserta.index` |
| Bank Soal | `cbt.bank-soal.index` |
| Jadwal | `cbt.jadwal.index` |
| Alokasi Waktu | `cbt.alokasi.index` |
| Pengawas | `cbt.pengawas.index` |
| Token | `cbt.token.index` |
| Pengumuman | `pengumuman.index` |
| Cetak | `cbt.cetak.index` |
| Status Siswa | `cbt.status.index` |
| Hasil Ujian | `cbt.nilai.index` |
| Analisis Soal | `cbt.analisis.index` |
| Rekap Nilai | `cbt.rekap.index` |
| Setting Rapor | `rapor.setting.index` |
| Profile Sekolah | `setting.sekolah` |
| User Admin | `setting.user.admin` |
| User Guru | `setting.user.guru` |
| User Siswa | `setting.user.siswa` |
| DB Backup | `setting.database.backup` |
| Data Manager | `setting.database.manager` |