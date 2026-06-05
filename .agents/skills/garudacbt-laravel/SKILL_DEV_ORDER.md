---
name: garudacbt-dev-order
description: >
  Gunakan skill ini SETIAP KALI menentukan urutan pengerjaan fitur GarudaCBT,
  membuat implementation plan, atau memutuskan modul mana yang dikerjakan berikutnya.
  Aktif saat pengguna menyebut: "urutan pengerjaan", "mau mulai dari mana",
  "fase pengembangan", "implementation plan", "apa yang harus dibuat dulu",
  "prioritas fitur", atau sebelum memulai coding fitur baru apapun di GarudaCBT.
  Skill ini adalah KOMPAS — selalu dikonsultasikan sebelum menulis kode apapun.
---

# Skill: GarudaCBT — Urutan & Logika Pengembangan

## Prinsip Dasar Urutan

Setiap keputusan urutan pengerjaan mengikuti **tiga aturan keras**:

1. **Dependensi database lebih dulu** — tabel yang menjadi FK target harus ada sebelum tabel yang mereferensikannya. Jangan pernah balik urutan ini.
2. **Backend sebelum frontend** — migration → model → observer/action → form request → controller → resource → baru Vue page. Jangan buat komponen Vue sebelum endpoint Inertia siap.
3. **Blokir lebih dulu** — fitur yang mem-blokir fitur lain dikerjakan pertama, meski terasa "kecil". Setting aktif TP/Semester mem-blokir hampir semua modul lain.

---

## Hierarki Dependensi Tabel (Acuan FK)

Urutan ini tidak bisa dinegosiasi — tabel di atas harus ada sebelum tabel di bawahnya:

```
users
  └── tahun_pelajaran          (aktif → dipakai semua tabel)
  └── semesters                (aktif → dipakai semua tabel)
  └── setting                  (singleton → dipakai layout)
  └── level_kelas
  └── jurusan
  └── mapel
  └── ekstra
  └── guru         (→ FK ke users)
  └── siswa        (→ FK ke users)
       └── buku_induk
  └── kelas        (→ FK ke tp, smt, jurusan, level, guru)
       └── kelas_siswa  (→ FK ke kelas, siswa, tp, smt)
  └── cbt_jenis
  └── cbt_sesi
  └── cbt_ruang
       └── cbt_kelas_ruang   (→ FK ke kelas, ruang, sesi)
       └── cbt_sesi_siswa    (→ FK ke siswa, ruang, sesi)
       └── cbt_pengawas      (→ FK ke jadwal, ruang, sesi, guru)
  └── cbt_nomor_peserta       (→ FK ke siswa, tp, smt)
  └── cbt_bank_soal           (→ FK ke jenis, mapel, jurusan, guru, tp, smt)
       └── cbt_soal           (→ FK ke bank_soal, mapel)
       └── cbt_jadwal         (→ FK ke bank_soal, tp, smt, jenis)
            └── cbt_token
            └── cbt_durasi_siswa
            └── cbt_soal_siswa
            └── cbt_nilai
            └── cbt_rekap_nilai
            └── log_ujian
```

---

## FASE 1 — Fondasi

**Kriteria selesai:** Login berjalan, sidebar tampil sesuai role, TP/Semester aktif tersedia di semua halaman, setting sekolah tersimpan.

**Mengapa fase ini tidak bisa dilewati:** `HandleInertiaRequests` yang membagi `tp_aktif`, `smt_aktif`, `auth.roles`, dan `setting` ke semua halaman harus jalan dulu. Tanpanya, setiap komponen Vue yang bergantung pada data ini akan error.

### Urutan dalam Fase 1:

**1.1 Infrastruktur Auth**
- Modifikasi migration `users` → tambah kolom `username` (unique, nullable) **sebelum** `migrate:fresh`
- Install Breeze + Spatie Permission
- Jalankan migration awal
- Buat `RolePermissionSeeder` — definisikan hierarki role sebagai konstanta numerik:
  ```
  superadmin=100, kepsek=80, operator=60, proktor=50, guru=40, siswa=10
  ```
- Buat seeder default accounts per role
- Modifikasi `User` model — tambah `HasRoles`, relasi `hasOne(Guru)`, `hasOne(Siswa)`, `scopeWithGuru()`, `scopeWithSiswa()`

**1.2 Model & Migration Fondasi**
Urutan migration harus tepat karena `HandleInertiaRequests` akan query dua tabel ini:
- Migration + Model `TahunPelajaran` — fillable eksplisit, cast `active` boolean
- Migration + Model `Semester` — fillable eksplisit, cast `active` boolean
- Migration + Model `Setting` — fillable eksplisit, method `Setting::get()` dengan cache, invalidasi cache di event `saved()`

**1.3 Action Classes** (sebelum controller)
- `ActivateTahunPelajaranAction` — bungkus `DB::transaction()`, nonaktifkan semua, aktifkan satu, dispatch event `TahunPelajaranActivated`
- `ActivateSemesterAction` — pola sama

**1.4 Form Requests**
- `StoreTahunPelajaranRequest`, `UpdateTahunPelajaranRequest`
- `StoreSemesterRequest`
- `UpdateSettingRequest` — validasi logo: `nullable|sometimes|mimes:jpg,jpeg,png,webp|max:2048|dimensions:min_width=100`

**1.5 Policy**
- `UserPolicy` — gunakan konstanta hierarki numerik dari seeder, bukan perbandingan string role

**1.6 Controllers**
- `TahunPelajaranController` — thin, delegasikan aktivasi ke Action
- `SemesterController` — thin, delegasikan aktivasi ke Action
- `SettingController` — handle upload logo ke `storage/public/setting`, flush cache setelah update

**1.7 Middleware & Route**
- Update `HandleInertiaRequests` — share `auth.user`, `auth.roles` (bukan permissions), `flash`, `tp_aktif`, `smt_aktif`, `setting_sekolah` (hanya field yang dibutuhkan frontend: `nama_sekolah`, `logo`)
- Daftarkan route dengan middleware `role:` yang sesuai

**1.8 Frontend**
Urutan komponen:
1. `AppLayout.vue` — shell utama, terima slot
2. `AppSidebar.vue` — accordion per group, visibility berbasis `auth.roles`
3. `AppNavbar.vue` — tampil `tp_aktif`, `smt_aktif`, dropdown user, badge peringatan jika tidak ada TP/Semester aktif
4. `Toast.vue` — Pinia store + Vue Transition, auto-dismiss dengan `clearTimeout` saat unmount
5. Pages: `TahunPelajaran/Index.vue`, `Semester/Index.vue`, `Setting/School.vue`

---

## FASE 2 — Master Data

**Kriteria selesai:** Semua data referensi tersedia, siswa dan guru punya akun, kelas terbentuk dengan anggota.

**Mengapa harus urut dari kiri ke kanan:** `Kelas` bergantung pada Level, Jurusan, Mapel, Guru, dan Siswa. Satu saja tidak ada, form Kelas tidak bisa diisi.

### Urutan dalam Fase 2:

**2.1 Data referensi sederhana (tidak saling bergantung, bisa paralel)**
- Level Kelas — CRUD sederhana
- Jurusan — CRUD + field JSON `mapel_peminatan`
- Mapel — CRUD + urutan, bobot PH/PTS/PAS
- Ekstrakurikuler — CRUD sederhana

**2.2 Guru** (bergantung pada: users)
- Observer `GuruObserver` — event `created`: buat `User`, assign role `guru`; event `deleting`: hapus user jika tidak ada relasi lain
- `CreateGuruAction` — bungkus `DB::transaction()`: simpan Guru → buat User → link `user_id`
- `GuruUserController` — thin, delegasikan ke Action
- Form Request `StoreGuruRequest`, `UpdateGuruRequest` — `username` wajib ada, `user_id` tidak boleh di fillable

**2.3 Siswa** (bergantung pada: users)
- Migration `buku_induk` harus ada **sebelum** Observer dibuat
- Observer `SiswaObserver` — event `created`: buat `User` + assign role `siswa` + buat `BukuInduk` kosong; seluruh blok dalam `DB::transaction()` atau gunakan Action
- `CreateSiswaAction` — pola sama dengan Guru, tambah pembuatan `BukuInduk`
- `SiswaImport` — reuse `CreateSiswaAction`, tangkap `Failure` per baris, kembalikan laporan error ke frontend
- Controller: `SiswaUserController`, `SiswaController` (master data)
- Endpoint `GET /master/siswa/template-excel` untuk download template import

**2.4 Kelas / Rombel** (bergantung pada: TP, Semester, Level, Jurusan, Mapel, Guru, Siswa semua harus ada)
- Migration `kelas` → `kelas_siswa`
- Fitur "Set Siswa" — assign siswa ke kelas, buat record `kelas_siswa`
- Fitur "Copy ke Semester Baru" — salin kelas beserta anggotanya ke TP/Semester aktif baru

**2.5 User Management UI** (bergantung pada: Guru dan Siswa sudah ada)
- `AdminUserController`, `GuruUserController`, `SiswaUserController`
- Semua operasi wajib melewati `UserPolicy` — tidak ada bypass via route langsung
- Reset password, toggle aktif/nonaktif

---

## FASE 3 — Setup CBT

**Kriteria selesai:** Bank soal terisi, jadwal terkonfigurasi, token tersedia, sistem siap dijalankan ujian.

**Urutan ketat — tidak ada yang bisa dilewati:**

**3.1 Referensi CBT sederhana (paralel)**
- Jenis Ujian — CRUD
- Sesi — CRUD + waktu mulai/akhir
- Ruang — CRUD

**3.2 Nomor Peserta** (bergantung pada: Siswa + Kelas sudah terisi)
- Migration `cbt_nomor_peserta`
- Fitur generate otomatis — ambil semua siswa aktif berurut per kelas, generate nomor berurutan

**3.3 Atur Ruang/Sesi** (bergantung pada: Ruang, Sesi, Kelas, Siswa)
- Migration `cbt_kelas_ruang` + `cbt_sesi_siswa`
- Distribusi siswa ke ruang dan sesi

**3.4 Bank Soal + Input Soal** — modul paling kompleks di seluruh aplikasi
- Urutan internal:
  1. Migration `cbt_bank_soal` → `cbt_soal`
  2. Model `BankSoal` dengan method `syncJumlahSoal()` — dipanggil setiap kali soal ditambah/hapus
  3. Install TipTap v2: `@tiptap/vue-3`, `@tiptap/starter-kit`, `@tiptap/extension-image`, `@tiptap/extension-table`, `katex`
  4. Buat ekstensi KaTeX custom (bukan paket pro) — ~50 baris
  5. Endpoint upload gambar soal → `storage/public/soal/` — kembalikan URL, bukan base64
  6. Komponen `RichEditor.vue` — reusable, dipakai untuk teks soal dan setiap opsi jawaban
  7. `SoalSanitizer` service — HTMLPurifier, izinkan class KaTeX, blokir tag berbahaya
  8. Form `StoreSoalRequest` — validasi total bobot semua tipe = 100, validasi `tampil_*` tidak melebihi `jml_*`
  9. Controller per tipe soal atau satu `SoalController` dengan branching di method `store`

- **Catatan penting:** Jangan pernah simpan HTML soal tanpa melewati `SoalSanitizer`. Panggil sanitizer di Form Request, bukan di controller.

**3.5 Token** (bergantung pada: Jadwal belum ada, tapi tabel independen)
- Migration `cbt_token`
- Fitur auto-rotate via Laravel Scheduler — jalankan tiap 5 menit jika ada token dengan `auto = true`

**3.6 Jadwal** (bergantung pada: BankSoal, Jenis, TP, Semester, Kelas, Sesi, Ruang semua harus ada)
- Migration `cbt_jadwal`
- Konfigurasi kompleks: acak soal, acak opsi, durasi, token, hasil tampil, ulang, reset login
- Validasi: `tgl_selesai` harus setelah `tgl_mulai`, durasi harus positif

**3.7 Pengawas** (bergantung pada: Jadwal + Ruang + Sesi + Guru)
- Migration `cbt_pengawas`
- Assign guru per kombinasi jadwal+ruang+sesi

**3.8 Alokasi Waktu**
- Tidak punya tabel sendiri — baca dari `cbt_jadwal` yang sudah ada
- Tampilkan sebagai grid per hari/sesi

---

## FASE 4 — Pelaksanaan Ujian

**Kriteria selesai:** Siswa bisa ujian, proktor bisa monitor, nilai tersimpan.

**Catatan keamanan paling kritis di seluruh aplikasi:** Endpoint yang melayani soal ke siswa **tidak boleh mengirim `jawaban_benar`** dalam response. Ini harus diverifikasi di `SoalSiswaResource` sebelum fase ini dianggap selesai.

**Urutan:**

**4.1 Alur Ujian Siswa**
- Migration `cbt_durasi_siswa` + `cbt_soal_siswa`
- `CbtService::distribusiSoal()` — distribusi soal per siswa, acak urutan, acak alias opsi, simpan mapping alias, **jangan re-distribusi jika record sudah ada**
- `CbtService::hitungNilai()` — skoring otomatis PG/Kompleks/Jodohkan/Isian, esai = 0 dulu
- `CheckExamSession` middleware — cegah multi-login saat ujian berlangsung
- `UjianController`: `start()`, `jawab()` (API endpoint, Axios), `selesai()`
- Pinia store `ujian.js` — state jawaban lokal, sinkronisasi ke server tiap jawaban berubah
- Komponen: `TimerCountdown.vue` (auto-submit + `clearInterval` saat unmount), `SoalRenderer.vue`, `JawabanPg.vue`

**4.2 Monitor Proktor** (bergantung pada: ujian sudah bisa berjalan)
- API endpoint polling `/api/cbt/status/{jadwal}` — di `routes/api.php`, bukan web
- Response: status per siswa (belum/sedang/selesai) + progress jawaban
- Komponen `StatusSiswa/Index.vue` — polling `setInterval` 30 detik, `clearInterval` saat unmount
- Fitur reset siswa: reset=1 (dari awal), reset=2 (lanjut sisa waktu), reset=3 (hapus semua, distribusi ulang)

**4.3 Hasil Ujian & Koreksi**
- Migration `cbt_nilai`
- Halaman koreksi esai — tampil jawaban siswa, input nilai manual per soal
- Setelah semua esai dikoreksi, set `cbt_nilai.dikoreksi = true`
- Nilai akhir = akumulasi semua tipe sesuai bobot

**4.4 Analisis Soal**
- Hitung persentase benar per soal dari `cbt_soal_siswa`
- Kategorisasi: ≥76% = Mudah, 26-75% = Sedang, ≤25% = Sulit
- Bandingkan dengan `kesulitan` yang di-set guru saat input soal

**4.5 Rekap Nilai**
- Migration `cbt_rekap_nilai`
- Agregat per kelas/mapel/jenis ujian
- Export Excel via `maatwebsite/excel`

**4.6 Cetak Dokumen**
- Kop surat dari tabel `cbt_kop_absensi`, `cbt_kop_berita`, `cbt_kop_kartu`
- 4 jenis: absensi, kartu peserta, berita acara, rekap nilai
- Implementasi: render Vue page khusus print + `window.print()` + CSS `@media print`

---

## FASE 5 — Pendukung

**Kriteria selesai:** LMS, pengumuman, rapor, buku induk, dan utilitas admin berjalan.

**Urutan (tidak saling blokir, bisa paralel setelah Fase 4 selesai):**

**5.1 LMS — Materi & Tugas**
- Migration `materi`
- Upload file: simpan sebagai JSON array path di kolom `file`
- Embed YouTube: ekstrak ID dari URL, tampilkan via iframe
- Log baca materi: catat siswa yang sudah membuka

**5.2 Pengumuman**
- Migration `posts`
- Target audience: "all", "siswa", "guru", atau kelas spesifik
- Komentar opsional

**5.3 Rapor**
- Migration `rapor_kkm`, `rapor_nilai_akhir`
- Input KKM per mapel per kelas
- Kalkulasi nilai akhir dari komponen PH + PTS + PAS berdasarkan bobot
- Template cetak rapor

**5.4 Buku Induk**
- Sudah dibuat kosong oleh `SiswaObserver` — ini hanya UI untuk melengkapi data
- Form edit lengkap: data fisik, riwayat sekolah, status akhir (lulus/pindah/keluar)

**5.5 Database Utilities**
- Backup: `spatie/db-dumper`, download langsung, hapus file setelah download
- Data Manager: konfirmasi 2 langkah di frontend (modal + ketik ulang nama modul)
- `truncate` hanya untuk tabel yang aman dibersihkan — **jangan izinkan truncate tabel master**

---

## Checklist Sebelum Pindah Fase

Sebelum memulai fase berikutnya, verifikasi:

**Sebelum Fase 2:**
- [ ] Login berhasil untuk semua role default
- [ ] Sidebar tampil menu yang tepat per role
- [ ] TP aktif dan Semester aktif muncul di navbar
- [ ] Setting sekolah tersimpan dan logo tampil
- [ ] Akses halaman yang tidak sesuai role → redirect 403

**Sebelum Fase 3:**
- [ ] Semua tabel master terisi minimal data dummy
- [ ] Siswa punya akun user dengan role `siswa`
- [ ] Guru punya akun user dengan role `guru`
- [ ] Minimal satu kelas terbentuk dengan siswa di dalamnya
- [ ] Import Excel siswa berjalan dan laporan error per baris tampil

**Sebelum Fase 4:**
- [ ] Bank soal terisi minimal 10 soal PG
- [ ] Jadwal terkonfigurasi dengan bank soal, kelas, dan durasi
- [ ] Token tersedia (manual atau auto)
- [ ] Akun siswa bisa login dan melihat daftar jadwal ujian aktif

**Sebelum Fase 5:**
- [ ] Ujian end-to-end selesai: siswa mulai → jawab → selesai → nilai tersimpan
- [ ] Koreksi esai berjalan
- [ ] Rekap nilai bisa diexport
- [ ] Cetak absensi menghasilkan output yang benar

---

## Aturan yang Tidak Boleh Dilanggar Selama Pengembangan

- **JANGAN** buat halaman Vue sebelum controller Inertia-nya ada — akan error blank page
- **JANGAN** distribusi ulang soal ke siswa yang sudah punya record di `cbt_soal_siswa`
- **JANGAN** kirim `jawaban_benar` di response API saat ujian berlangsung
- **JANGAN** simpan HTML soal tanpa `SoalSanitizer`
- **JANGAN** hardcode `id` TP atau Semester — selalu query yang `active = true`
- **JANGAN** gunakan `$guarded = []` di model manapun — selalu `$fillable` eksplisit
- **JANGAN** izinkan `user_id`, `password`, `uid` masuk `$fillable` di model Siswa/Guru
- **JANGAN** bypass `UserPolicy` — operasi user selalu lewat `$this->authorize()`
- **JANGAN** truncate tabel master (siswa, guru, kelas, mapel) dari Data Manager
- **JANGAN** lupa `Cache::forget('setting')` setiap kali Setting diupdate
