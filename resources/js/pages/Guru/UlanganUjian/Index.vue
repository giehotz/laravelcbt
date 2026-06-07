<script setup lang="ts">
import { Calendar, ClipboardList, Users, Trophy } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { ulanganUjian } from '@/routes/guru';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: ulanganUjian() },
            { title: 'Ulangan / Ujian', href: ulanganUjian() },
        ],
    },
});

const props = defineProps<{
    willCome: { id: number; bank_soal: { nama: string } | null; tgl_mulai: string; tgl_selesai: string }[];
    completed: { id: number; bank_soal: { nama: string } | null; tgl_mulai: string; tgl_selesai: string }[];
    stats: { total_jadwal: number; total_selesai: number; total_akan_datang: number; total_siswa: number };
}>();
</script>

<template>
    <div class="flex flex-col gap-6">
        <Heading
            title="Ulangan / Ujian"
            description="Dashboard terpadu semua jadwal ulangan dan ujian."
        />

        <div class="grid grid-cols-4 gap-4">
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-blue-100 p-2.5">
                        <Calendar class="h-5 w-5 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Total Jadwal</p>
                        <p class="text-2xl font-bold">{{ stats.total_jadwal }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-green-100 p-2.5">
                        <Trophy class="h-5 w-5 text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Selesai</p>
                        <p class="text-2xl font-bold">{{ stats.total_selesai }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-yellow-100 p-2.5">
                        <ClipboardList class="h-5 w-5 text-yellow-600" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Akan Datang</p>
                        <p class="text-2xl font-bold">{{ stats.total_akan_datang }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border bg-card p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-purple-100 p-2.5">
                        <Users class="h-5 w-5 text-purple-600" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Total Siswa</p>
                        <p class="text-2xl font-bold">{{ stats.total_siswa }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="willCome.length" class="rounded-xl border bg-card p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold">Akan Datang</h3>
            <div class="divide-y divide-border">
                <div v-for="j in willCome" :key="j.id" class="flex items-center justify-between py-3">
                    <div>
                        <p class="font-medium">{{ j.bank_soal?.nama || '—' }}</p>
                        <p class="text-sm text-muted-foreground">{{ j.tgl_mulai }} — {{ j.tgl_selesai }}</p>
                    </div>
                    <span class="rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-700">Akan Datang</span>
                </div>
            </div>
        </div>

        <div
            v-if="completed.length"
            class="rounded-xl border bg-card p-6 shadow-sm"
        >
            <h3 class="mb-4 text-lg font-semibold">Riwayat Selesai</h3>
            <div class="divide-y divide-border">
                <div v-for="j in completed" :key="j.id" class="flex items-center justify-between py-3">
                    <div>
                        <p class="font-medium">{{ j.bank_soal?.nama || '—' }}</p>
                        <p class="text-sm text-muted-foreground">{{ j.tgl_mulai }} — {{ j.tgl_selesai }}</p>
                    </div>
                    <span class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">Selesai</span>
                </div>
            </div>
        </div>

        <div
            v-if="!willCome.length && !completed.length"
            class="rounded-xl border bg-card p-12 text-center shadow-sm"
        >
            <ClipboardList class="mx-auto h-12 w-12 text-muted-foreground" />
            <h3 class="mt-2 text-sm font-medium text-card-foreground">Belum Ada Jadwal</h3>
            <p class="mt-1 text-sm text-muted-foreground">
                Belum ada jadwal ujian untuk guru ini.
            </p>
        </div>
    </div>
</template>
