<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Kelas {{ $kelas->nama }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .kop-surat { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        .kop-surat h2, .kop-surat h3, .kop-surat p { margin: 2px 0; }
        .identitas { width: 100%; margin-bottom: 20px; }
        .identitas td { padding: 2px; }
        .table-nilai { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-nilai th, .table-nilai td { border: 1px solid #000; padding: 6px; text-align: center; }
        .table-nilai th { background-color: #f3f3f3; }
        .table-nilai .left { text-align: left; }
        .ttd { width: 100%; margin-top: 50px; }
        .ttd td { width: 50%; text-align: center; }
        .spacer { height: 80px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @foreach($data as $item)
        @php
            $siswa = $item['siswa'];
            $nilai = $item['nilai'];
        @endphp
        
        <div class="kop-surat">
            <h2>{{ $sekolah->nama_sekolah ?? 'NAMA SEKOLAH' }}</h2>
            <p>{{ $sekolah->alamat_sekolah ?? 'Alamat Sekolah' }}</p>
        </div>

        <h3 style="text-align: center; margin-bottom: 20px;">LAPORAN HASIL BELAJAR PESERTA DIDIK</h3>

        <table class="identitas">
            <tr>
                <td width="20%">Nama Peserta Didik</td>
                <td width="2%">:</td>
                <td width="48%"><strong>{{ $siswa->nama }}</strong></td>
                <td width="15%">Kelas</td>
                <td width="2%">:</td>
                <td width="13%">{{ $kelas->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>NIS / NISN</td>
                <td>:</td>
                <td>{{ $siswa->nis }} / {{ $siswa->nisn }}</td>
                <td>Semester</td>
                <td>:</td>
                <td>{{ $smt->nama_semester ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nama Madrasah</td>
                <td>:</td>
                <td>{{ $sekolah->nama_sekolah ?? '-' }}</td>
                <td>Tahun Pelajaran</td>
                <td>:</td>
                <td>{{ $tp->nama_tahun_pelajaran ?? '-' }}</td>
            </tr>
        </table>

        <table class="table-nilai">
            <thead>
                <tr>
                    <th width="5%" rowspan="2">No</th>
                    <th width="35%" rowspan="2">Mata Pelajaran</th>
                    <th width="10%" rowspan="2">KKM</th>
                    <th colspan="3">Nilai Hasil Belajar</th>
                </tr>
                <tr>
                    <th width="15%">Angka</th>
                    <th width="15%">Predikat</th>
                    <th width="20%">Deskripsi (Opsional)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($nilai as $index => $n)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="left">{{ $n->mapel->nama_mapel ?? '-' }}</td>
                    <td>
                        @php
                            $kkm = \App\Models\Akademik\RaporKkm::where('tp_id', $tp->id)
                                    ->where('smt_id', $smt->id)
                                    ->where('kelas_id', $n->kelas_id)
                                    ->where('mapel_id', $n->mapel_id)
                                    ->first();
                        @endphp
                        {{ $kkm->kkm ?? '-' }}
                    </td>
                    <td>{{ number_format($n->nilai_akhir, 0) }}</td>
                    <td>{{ $n->predikat }}</td>
                    <td></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">Belum ada nilai yang dimasukkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <table class="ttd">
            <tr>
                <td>
                    Mengetahui,<br>
                    Kepala Sekolah
                    <div class="spacer"></div>
                    <strong>{{ $sekolah->kepala_sekolah ?? '..............................' }}</strong><br>
                    NIP. {{ $sekolah->nip_kepala_sekolah ?? '..............................' }}
                </td>
                <td>
                    Wali Kelas
                    <div class="spacer"></div>
                    <strong>..............................</strong><br>
                    NIP. ..............................
                </td>
            </tr>
        </table>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
