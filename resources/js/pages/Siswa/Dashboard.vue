<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileHeader from '@/components/Siswa/Dashboard/ProfileHeader.vue';
import MenuGrid from '@/components/Siswa/Dashboard/MenuGrid.vue';
import InfoPanel from '@/components/Siswa/Dashboard/InfoPanel.vue';
import JadwalHariIni from '@/components/Siswa/Dashboard/JadwalHariIni.vue';
import { dashboard, ujian, jadwal, tugas, nilai, absensi, catatanGuru } from '@/routes/siswa';

const page = usePage();
const tpAktif = computed(() => page.props.tp_aktif as { id: number; tahun: string } | null);
const smtAktif = computed(() => page.props.smt_aktif as { id: number; nama_smt: string; smt: string } | null);
const namaSekolah = computed(() => (page.props.setting_sekolah as any)?.nama_sekolah ?? 'CBT MIN 2 TANGGAMUS');

defineProps<{
    siswa: {
        nama: string;
        nis: string;
        nisn: string;
        kelas: string;
        foto: string | null;
    };
}>();

const menus = [
    { label: 'Jadwal Pelajaran', icon: 'BookOpen', href: jadwal().url, color: 'green', bgClass: 'bg-green-100', textClass: 'text-green-600' },
    { label: 'Materi', icon: 'Ruler', href: '/lms/materi', color: 'blue', bgClass: 'bg-blue-100', textClass: 'text-blue-600' },
    { label: 'Tugas', icon: 'Globe', href: tugas().url, color: 'purple', bgClass: 'bg-purple-100', textClass: 'text-purple-600' },
    { label: 'Ujian / Ulangan', icon: 'FileText', href: ujian().url, color: 'red', bgClass: 'bg-red-100', textClass: 'text-red-600' },
    { label: 'Nilai Hasil', icon: 'Trophy', href: nilai().url, color: 'amber', bgClass: 'bg-amber-100', textClass: 'text-amber-600' },
    { label: 'Absensi', icon: 'ClipboardList', href: absensi().url, color: 'cyan', bgClass: 'bg-cyan-100', textClass: 'text-cyan-600' },
    { label: 'Catatan Guru', icon: 'UserCheck', href: catatanGuru().url, color: 'pink', bgClass: 'bg-pink-100', textClass: 'text-pink-600' },
];
</script>

<template>
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-4 sm:gap-6">
        <ProfileHeader
            :nama-sekolah="namaSekolah"
            :tp-aktif="tpAktif?.tahun ?? null"
            :smt-aktif="smtAktif?.nama_smt ?? null"
            :siswa="siswa"
        />

        <MenuGrid :menus="menus" />

        <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">
            <InfoPanel />
            <JadwalHariIni :kelas="siswa.kelas" />
        </div>
    </div>
</template>
