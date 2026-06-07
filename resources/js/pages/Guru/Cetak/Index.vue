<script setup lang="ts">
import { ref } from 'vue';
import { Printer, ClipboardList, FileText, BarChart3 } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import {
    kartu,
    daftarHadir,
    beritaAcara,
    rekapNilai,
} from '@/routes/guru/cetak';
import { cetak } from '@/routes/guru';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: cetak() },
            { title: 'Cetak', href: cetak() },
        ],
    },
});

const props = defineProps<{
    jadwals: {
        id: number;
        tgl_mulai: string;
        bank_soal?: { nama: string };
    }[];
}>();

const selectedJadwalId = ref('');

const docs = [
    {
        key: 'kartu',
        label: 'Kartu Peserta',
        desc: 'Cetak kartu ujian untuk siswa yang terdaftar.',
        icon: ClipboardList,
        color: 'indigo',
        href: kartu,
    },
    {
        key: 'daftarHadir',
        label: 'Daftar Hadir',
        desc: 'Absensi siswa per ruang dan sesi.',
        icon: Printer,
        color: 'blue',
        href: daftarHadir,
    },
    {
        key: 'beritaAcara',
        label: 'Berita Acara',
        desc: 'Dokumen resmi pelaksanaan ujian.',
        icon: FileText,
        color: 'amber',
        href: beritaAcara,
    },
    {
        key: 'rekapNilai',
        label: 'Rekap Nilai',
        desc: 'Nilai akhir seluruh siswa per jadwal.',
        icon: BarChart3,
        color: 'emerald',
        href: rekapNilai,
    },
] as const;

function docUrl(doc: (typeof docs)[number]): string {
    return doc.href({ jadwal_id: selectedJadwalId.value }).url;
}

function iconBg(color: string): string {
    const map: Record<string, string> = {
        indigo: 'bg-indigo-50 text-indigo-600 group-hover:bg-indigo-100',
        blue: 'bg-blue-50 text-blue-600 group-hover:bg-blue-100',
        amber: 'bg-amber-50 text-amber-600 group-hover:bg-amber-100',
        emerald: 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100',
    };
    return map[color] ?? '';
}

function borderHover(color: string): string {
    const map: Record<string, string> = {
        indigo: 'hover:border-indigo-500',
        blue: 'hover:border-blue-500',
        amber: 'hover:border-amber-500',
        emerald: 'hover:border-emerald-500',
    };
    return map[color] ?? '';
}

function textHover(color: string): string {
    const map: Record<string, string> = {
        indigo: 'group-hover:text-indigo-600',
        blue: 'group-hover:text-blue-600',
        amber: 'group-hover:text-amber-600',
        emerald: 'group-hover:text-emerald-600',
    };
    return map[color] ?? '';
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <Heading
            title="Cetak Dokumen"
            description="Pilih jadwal dan jenis dokumen yang akan dicetak."
        />

        <div class="rounded-xl border bg-card p-6 shadow-sm">
            <div class="max-w-xl">
                <label
                    class="mb-2 block text-sm font-medium text-foreground"
                >
                    Pilih Jadwal Ujian
                </label>
                <select
                    v-model="selectedJadwalId"
                    class="block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-xs focus:border-ring focus:outline-hidden focus:ring-1 focus:ring-ring"
                >
                    <option value="">-- Pilih Jadwal --</option>
                    <option
                        v-for="jadwal in jadwals"
                        :key="jadwal.id"
                        :value="jadwal.id"
                    >
                        {{ jadwal.bank_soal?.nama ?? '-' }} ({{
                            jadwal.tgl_mulai
                        }})
                    </option>
                </select>
            </div>
        </div>

        <div
            v-if="selectedJadwalId"
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
        >
            <a
                v-for="doc in docs"
                :key="doc.key"
                :href="docUrl(doc)"
                target="_blank"
                class="group flex flex-col items-center rounded-xl border bg-card p-6 text-center shadow-sm transition-all hover:shadow-md"
                :class="borderHover(doc.color)"
            >
                <div class="mb-4 rounded-full p-4 transition-colors" :class="iconBg(doc.color)">
                    <component :is="doc.icon" class="h-8 w-8" />
                </div>
                <h3 class="font-semibold text-card-foreground" :class="textHover(doc.color)">
                    {{ doc.label }}
                </h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ doc.desc }}
                </p>
            </a>
        </div>

        <div
            v-else
            class="rounded-xl border bg-card p-12 text-center shadow-sm"
        >
            <Printer class="mx-auto h-12 w-12 text-muted-foreground" />
            <h3 class="mt-2 text-sm font-medium text-card-foreground">
                Pilih Jadwal
            </h3>
            <p class="mt-1 text-sm text-muted-foreground">
                Silakan pilih jadwal terlebih dahulu untuk memunculkan menu
                cetak.
            </p>
        </div>
    </div>
</template>
