# Implementation Plan — Dashboard GarudaCBT

## Prinsip Arsitektur Dashboard

Dashboard ini melayani **5 role berbeda** dengan kebutuhan informasi yang berbeda.
Pendekatan: **satu controller, satu Vue page, konten dikontrol oleh role**.

Alasan tidak dibuat 5 halaman terpisah:
- Layout identik — hanya data dan widget yang berbeda
- Perubahan UI global cukup di satu file
- `DashboardController` branch berdasarkan role

---

## Strategi Layering — 3 Layer Berurutan

```
Layer 1 — Struktur & Stat Cards        (semua role, tidak ada dependensi baru)
Layer 2 — Jadwal Hari Ini & Notifikasi (semua role, computed di Vue)
Layer 3 — Grafik                        (kepsek + superadmin saja)
```

Setiap layer bisa diverifikasi secara independen sebelum lanjut ke layer berikutnya.

---

## Layer 1 — Struktur, Stat Cards, Waktu Server

### Backend

**`DashboardController@index`** — satu method, branch per role:

```php
public function index(): Response
{
    $user = auth()->user();
    $tp   = TahunPelajaran::aktif();
    $smt  = Semester::aktif();

    $data = match(true) {
        $user->hasRole('superadmin') => $this->dataAdmin($tp, $smt),
        $user->hasRole('operator')   => $this->dataOperator($tp, $smt),
        $user->hasRole('guru')       => $this->dataGuru($user, $tp, $smt),
        $user->hasRole('proktor')    => $this->dataProktor($tp, $smt),
        $user->hasRole('kepsek')     => $this->dataKepsek($tp, $smt),
        default => [],
    };

    return Inertia::render('Dashboard', array_merge($data, [
        'serverTime' => now()->toISOString(),
        'tp'         => $tp?->only('id', 'tahun'),
        'smt'        => $smt?->only('id', 'nama_smt'),
    ]));
}
```

**Stat cards per role:**

| Role | Card 1 | Card 2 | Card 3 | Card 4 |
|---|---|---|---|---|
| Superadmin | Total Siswa | Total Guru | Total Bank Soal | Jadwal Aktif Sekarang |
| Operator | Total Siswa | Total Kelas | Total Guru | Bank Soal |
| Guru | Bank Soal Saya | Soal Saya | Jadwal Saya | Nilai Belum Dikoreksi |
| Proktor | Jadwal Hari Ini | Total Peserta | Sedang Ujian | Sudah Selesai |
| Kepsek | Total Siswa | Total Guru | Rata-rata Nilai | Ujian Selesai Bulan Ini |

**Counter statis di-cache 5 menit, data real-time tidak di-cache:**

```php
private function dataAdmin(TahunPelajaran $tp, Semester $smt): array
{
    $stats = Cache::remember("dashboard_admin_{$tp->id}_{$smt->id}", 300, fn() => [
        'total_siswa' => Siswa::count(),
        'total_guru'  => Guru::count(),
        'total_bank'  => BankSoal::where('tp_id', $tp->id)->count(),
    ]);

    // Tidak di-cache — harus real-time
    $stats['jadwal_aktif_sekarang'] = CbtJadwal::aktifSekarang()->count();

    return ['stats' => $stats];
}
```

**Scope `aktifSekarang()` di `CbtJadwal`:**

```php
// tgl_mulai adalah string ISO — gunakan perbandingan string
public function scopeAktifSekarang(Builder $q): Builder
{
    $now = now()->toISOString();
    return $q->where('status', 1)
             ->where('tgl_mulai', '<=', $now)
             ->where('tgl_selesai', '>=', $now);
}
```

**Invalidasi cache saat data berubah:**
- `Cache::forget("dashboard_admin_{$tp->id}_{$smt->id}")` dipanggil di Observer
  `Siswa::created/deleted` dan `Guru::created/deleted`
- Jangan andalkan TTL saja untuk data yang bisa berubah karena aksi user

### Frontend

**Struktur komponen Dashboard:**

```
Dashboard.vue
├── WelcomeBanner.vue     — sapaan + TP/SMT + jam real-time
├── StatCards.vue         — 4 kartu metrik (props: cards[])
├── QuickActions.vue      — shortcut tombol per role
├── JadwalHariIni.vue     — tabel jadwal (Layer 2)
├── NotifikasiPanel.vue   — notifikasi computed (Layer 2)
└── GrafikSection.vue     — grafik (Layer 3, conditional render)
```

**`WelcomeBanner.vue` — jam real-time tanpa polling:**

```vue
<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  serverTime: String,
  user: Object,
  tp: Object,
  smt: Object,
})

const now   = ref(new Date(props.serverTime))
let   timer = null

onMounted(() => {
  timer = setInterval(() => {
    now.value = new Date(now.value.getTime() + 1000)
  }, 1000)
})
onUnmounted(() => clearInterval(timer))

const jamTampil = computed(() =>
  now.value.toLocaleTimeString('id-ID', {
    hour: '2-digit', minute: '2-digit', second: '2-digit'
  })
)
</script>
```

**`StatCards.vue` — menerima array, tidak hardcode konten:**

```vue
<!-- cards: [{ label, value, icon, color, route? }] -->
<script setup>
defineProps({ cards: { type: Array, required: true } })
</script>
```

Definisi array `cards` dibuat di `Dashboard.vue` parent berdasarkan role,
bukan di dalam `StatCards.vue`. Komponen ini murni presentational.

**Quick Actions per role:**

| Role | Actions |
|---|---|
| Superadmin | ➕ Jadwal Baru · 📋 Bank Soal · 👥 Kelola Siswa · 🔑 Token |
| Operator | ➕ Jadwal Baru · 👥 Kelola Siswa · 📋 Bank Soal · 🏫 Kelola Kelas |
| Guru | 📝 Buat Bank Soal · ➕ Jadwal Baru · 📊 Lihat Nilai |
| Proktor | 🔑 Kelola Token · 👁️ Monitor Ujian · 📋 Status Peserta |
| Kepsek | 📊 Rekap Nilai · 📄 Lihat Laporan |

> "Reset Login Peserta" **tidak** masuk Quick Actions dashboard — dipindah ke
> halaman Monitor Ujian (Fase 4.2) karena membutuhkan konteks jadwal spesifik.

---

## Layer 2 — Jadwal Hari Ini & Notifikasi

### Jadwal Hari Ini

**Query di controller — eager load sekaligus, tidak ada lazy load:**

```php
private function jadwalHariIni(TahunPelajaran $tp): Collection
{
    $start = now()->startOfDay()->toISOString();
    $end   = now()->endOfDay()->toISOString();

    return CbtJadwal::with(['bank.mapel', 'bank.jenis', 'token'])
        ->where('tp_id', $tp->id)
        ->where('tgl_mulai', '>=', $start)
        ->where('tgl_mulai', '<=', $end)
        ->orderBy('tgl_mulai')
        ->get();
}
```

**Kolom per role — dikontrol di `JadwalHariIni.vue` via prop `showColumns`:**

| Kolom | Admin | Operator | Guru | Proktor | Kepsek |
|---|---|---|---|---|---|
| Mata Pelajaran | ✓ | ✓ | ✓ | ✓ | ✓ |
| Jenis Ujian | ✓ | ✓ | ✓ | ✓ | ✓ |
| Waktu | ✓ | ✓ | ✓ | ✓ | ✓ |
| Status badge | ✓ | ✓ | ✓ | ✓ | ✓ |
| Token | ✓ | — | — | ✓ | — |
| Peserta / Selesai | ✓ | — | — | ✓ | ✓ |
| Link Monitor | ✓ | — | — | ✓ | — |
| Link Bank Soal | — | — | ✓ | — | — |

### Notifikasi — Computed di Vue, Tanpa Request Tambahan

Notifikasi dihitung dari data `jadwalHariIni` yang sudah ada di props.
Tidak ada endpoint notifikasi terpisah, tidak ada polling.

```js
// Di Dashboard.vue — computed dari jadwalHariIni
const notifikasi = computed(() => {
  const hasil = []
  const now   = new Date()

  props.jadwalHariIni?.forEach(jadwal => {
    const mulai   = new Date(jadwal.tgl_mulai)
    const selisih = (mulai - now) / 60000 // dalam menit

    // Ujian akan dimulai dalam 15 menit
    if (selisih > 0 && selisih <= 15) {
      hasil.push({
        type : 'warning',
        pesan: `${jadwal.bank.mapel.nama_mapel} dimulai ${Math.round(selisih)} mnt lagi`,
        href : route('cbt.jadwal.show', jadwal.id),
      })
    }

    // Jadwal pakai token tapi token belum ada
    if (jadwal.token_required && !jadwal.token?.token) {
      hasil.push({
        type : 'danger',
        pesan: `Token belum dibuat: ${jadwal.bank.mapel.nama_mapel}`,
        href : route('cbt.token.index'),
      })
    }

    // Jadwal sudah lewat tapi status masih buka
    const selesai = new Date(jadwal.tgl_selesai)
    if (selesai < now && jadwal.status === 1) {
      hasil.push({
        type : 'info',
        pesan: `Jadwal ${jadwal.bank.mapel.nama_mapel} belum ditutup`,
        href : route('cbt.jadwal.index'),
      })
    }
  })

  return hasil
})
```

**Badge di `AppNavbar.vue`** — sync via Pinia store agar tidak perlu prop drilling:

```js
// stores/notifikasi.js
export const useNotifikasiStore = defineStore('notifikasi', {
  state: () => ({ items: [] }),
  getters: { total: (s) => s.items.length },
  actions: {
    set(items) { this.items = items }
  }
})
```

Di `Dashboard.vue`, setelah `notifikasi` computed berubah:
```js
watch(notifikasi, (val) => useNotifikasiStore().set(val), { immediate: true })
```

---

## Layer 3 — Grafik (Kepsek + Superadmin)

> **Catatan penting sebelum eksekusi Layer 3:**
> Recharts adalah library React. Di Vue 3 tidak bisa dipakai langsung.
> Gunakan **`vue-chartjs`** (wrapper Chart.js untuk Vue 3) yang lebih native.
> Install: `npm install vue-chartjs chart.js`

### Tiga grafik yang dibangun:

**Grafik 1 — Rata-rata Nilai per Mata Pelajaran (Bar Chart)**

Query harus menggunakan raw aggregate, bukan load semua record ke Collection:

```php
$distribusiNilai = DB::table('cbt_nilai')
    ->join('cbt_jadwal', 'cbt_nilai.jadwal_id', '=', 'cbt_jadwal.id')
    ->join('cbt_bank_soal', 'cbt_jadwal.bank_id', '=', 'cbt_bank_soal.id')
    ->join('mapel', 'cbt_bank_soal.mapel_id', '=', 'mapel.id')
    ->where('cbt_jadwal.tp_id', $tp->id)
    ->where('cbt_jadwal.smt_id', $smt->id)
    ->groupBy('mapel.id', 'mapel.nama_mapel')
    ->select(
        'mapel.nama_mapel',
        DB::raw('ROUND(AVG(pg_nilai + esai_nilai + kompleks_nilai + jodohkan_nilai + isian_nilai), 1) as rata_rata'),
        DB::raw('COUNT(*) as total_peserta')
    )
    ->get();
```

**Grafik 2 — Status Peserta Ujian (Donut Chart)**

```php
$statusPeserta = DB::table('cbt_durasi_siswa')
    ->join('cbt_jadwal', 'cbt_durasi_siswa.jadwal_id', '=', 'cbt_jadwal.id')
    ->where('cbt_jadwal.tp_id', $tp->id)
    ->where('cbt_jadwal.smt_id', $smt->id)
    ->selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->get()
    ->mapWithKeys(fn($row) => [
        ['0' => 'Belum', '1' => 'Sedang', '2' => 'Selesai'][$row->status] => $row->total
    ]);
```

**Grafik 3 — Jumlah Ujian per Bulan (Line Chart)**

```php
// tgl_mulai adalah string — gunakan SUBSTRING untuk extract bulan
$trenUjian = DB::table('cbt_jadwal')
    ->where('tp_id', $tp->id)
    ->selectRaw("SUBSTRING(tgl_mulai, 1, 7) as bulan, COUNT(*) as total")
    ->groupByRaw("SUBSTRING(tgl_mulai, 1, 7)")
    ->orderBy('bulan')
    ->get();
```

**Cache grafik 30 menit** — data historis tidak berubah per menit:

```php
$grafik = Cache::remember("dashboard_grafik_{$tp->id}_{$smt->id}", 1800, fn() => [
    'distribusi_nilai' => $distribusiNilai,
    'status_peserta'   => $statusPeserta,
    'tren_ujian'       => $trenUjian,
]);
```

---

## Proposed Changes (Ringkasan)

### Backend
| File | Aksi | Keterangan |
|---|---|---|
| `routes/web.php` | MODIFY | Ubah dari `Route::inertia` ke `DashboardController@index` |
| `DashboardController.php` | NEW | Method branching per role + private methods |
| `CbtJadwal.php` | MODIFY | Tambah scope `scopeAktifSekarang()` |
| `stores/notifikasi.js` | NEW | Pinia store untuk badge navbar |

### Frontend
| File | Aksi | Keterangan |
|---|---|---|
| `Pages/Dashboard.vue` | MODIFY | Rebuild dari placeholder |
| `Components/Dashboard/WelcomeBanner.vue` | NEW | Jam real-time, sapaan, TP/SMT |
| `Components/Dashboard/StatCards.vue` | NEW | 4 kartu metrik, presentational |
| `Components/Dashboard/QuickActions.vue` | NEW | Shortcut per role |
| `Components/Dashboard/JadwalHariIni.vue` | NEW | Tabel jadwal, kolom per role |
| `Components/Dashboard/NotifikasiPanel.vue` | NEW | Computed dari jadwal, tanpa polling |
| `Components/Dashboard/GrafikSection.vue` | NEW | Lazy render, kepsek/admin only |
| `Components/Layout/AppNavbar.vue` | MODIFY | Badge notifikasi dari Pinia store |

---

## Verification Plan

**Layer 1:**
- [ ] Login 5 role berbeda → stat cards menampilkan data yang tepat per role
- [ ] Guru tidak melihat stat "Total Siswa Sistem" (hanya miliknya)
- [ ] Jam berjalan tanpa request ke server (Network tab = 0 request per detik)
- [ ] Cache bekerja: query COUNT tidak muncul di Telescope setelah refresh kedua

**Layer 2:**
- [ ] Jadwal hari ini hanya dari TP aktif
- [ ] Notifikasi "15 menit lagi" muncul jika ada jadwal yang akan mulai
- [ ] Badge navbar sinkron dengan panel notifikasi
- [ ] Token hanya tampil untuk Admin dan Proktor

**Layer 3:**
- [ ] `GrafikSection` tidak di-render di DOM untuk role Guru/Operator/Proktor
- [ ] Grafik akurat vs query langsung ke database
- [ ] Empty state grafik tampil informatif saat belum ada data nilai

**Automated Test:**
- `DashboardTest` — setiap role mendapat struktur data yang tepat
- Guru tidak bisa akses data role lain via manipulasi request
