<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\BukuInduk;
use App\Models\Master\Siswa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class BukuIndukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $siswa = Siswa::with(['bukuInduk', 'kelasAktif.kelas'])
            ->when($search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            })
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Master/BukuInduk/Index', [
            'siswa' => $siswa,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Siswa $buku_induk)
    {
        // Parameter name is $buku_induk because route is resource('buku-induk')
        // but we bind it to Siswa to get the full profile + relasi
        $buku_induk->load('bukuInduk');
        
        return Inertia::render('Master/BukuInduk/Edit', [
            'siswa' => $buku_induk
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Siswa $buku_induk)
    {
        $validatedData = $request->validate([
            // Data Diri Tambahan
            'buku_induk.uid' => 'nullable|string|max:100',
            'buku_induk.nama_panggilan' => 'nullable|string|max:80',
            'buku_induk.bahasa' => 'nullable|string|max:80',
            'buku_induk.jml_saudara_kandung' => 'nullable|integer',
            'buku_induk.jml_saudara_tiri' => 'nullable|integer',
            'buku_induk.jml_saudara_angkat' => 'nullable|integer',
            'buku_induk.yatim' => 'nullable|integer',
            'buku_induk.tinggal_bersama' => 'nullable|string|max:1',
            'buku_induk.jarak' => 'nullable|string|max:15',
            'buku_induk.gol_darah' => 'nullable|string|max:5',
            'buku_induk.penyakit' => 'nullable|string',
            'buku_induk.kelainan_fisik' => 'nullable|string|max:150',
            'buku_induk.kegemaran' => 'nullable|string',
            'buku_induk.beasiswa' => 'nullable|string',
            
            // Orang Tua (Siswa table)
            'siswa.nama_ayah' => 'nullable|string|max:150',
            'siswa.tgl_lahir_ayah' => 'nullable|string|max:50',
            'siswa.pendidikan_ayah' => 'nullable|string|max:50',
            'siswa.pekerjaan_ayah' => 'nullable|string|max:100',
            'siswa.nohp_ayah' => 'nullable|string|max:20',
            'siswa.alamat_ayah' => 'nullable|string',
            'siswa.nama_ibu' => 'nullable|string|max:100',
            'siswa.tgl_lahir_ibu' => 'nullable|string|max:50',
            'siswa.pendidikan_ibu' => 'nullable|string|max:50',
            'siswa.pekerjaan_ibu' => 'nullable|string|max:100',
            'siswa.nohp_ibu' => 'nullable|string|max:20',
            'siswa.alamat_ibu' => 'nullable|string',
            'siswa.nama_wali' => 'nullable|string|max:150',
            'siswa.tgl_lahir_wali' => 'nullable|string|max:50',
            'siswa.pendidikan_wali' => 'nullable|string|max:50',
            'siswa.pekerjaan_wali' => 'nullable|string|max:100',
            'siswa.nohp_wali' => 'nullable|string|max:20',
            'siswa.alamat_wali' => 'nullable|string',

            // Riwayat Sekolah (Buku Induk)
            'buku_induk.no_ijazah_sebelumnya' => 'nullable|string|max:80',
            'buku_induk.tahun_lulus_sebelumnya' => 'nullable|string|max:10',
            'buku_induk.pindahan_dari' => 'nullable|string|max:150',
            'buku_induk.alasan_kepindahan' => 'nullable|string|max:300',

            // Status Akhir (Buku Induk)
            'buku_induk.status' => 'required|integer',
            'buku_induk.tahun_lulus' => 'nullable|integer',
            'buku_induk.no_ijazah' => 'nullable|string|max:80',
            'buku_induk.kelas_akhir' => 'nullable|string|max:80',
            'buku_induk.catatan_penting' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validatedData, $buku_induk) {
            $oldStatus = $buku_induk->bukuInduk->status ?? 1;

            // Update data Siswa (Orang Tua)
            if (isset($validatedData['siswa'])) {
                $buku_induk->update($validatedData['siswa']);
            }

            // Update data Buku Induk
            if (isset($validatedData['buku_induk'])) {
                $buku_induk->bukuInduk()->updateOrCreate(
                    ['siswa_id' => $buku_induk->id],
                    $validatedData['buku_induk']
                );
            }

            $newStatus = $validatedData['buku_induk']['status'] ?? $oldStatus;
            
            // Trigger event if status changed to Lulus (2) or Pindah (3) or Keluar (4)
            if ($oldStatus !== $newStatus && in_array($newStatus, [2, 3, 4])) {
                event(new \App\Events\StatusSiswaChanged($buku_induk, $newStatus));
            }
        });

        return redirect()->route('master.buku-induk.index')->with('success', 'Buku Induk berhasil diperbarui.');
    }
}
