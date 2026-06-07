<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { FileText, Clock, CheckCircle2, Play, Frown } from 'lucide-vue-next';

defineProps<{
    ujians: {
        id: number;
        nama: string;
        mapel: string;
        tgl_mulai: string;
        tgl_selesai: string;
        durasi: number;
        status: number;
        status_siswa: string;
    }[];
    siswa: {
        kelas: string;
    };
}>();
</script>

<template>
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-4 sm:gap-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600">
                <FileText class="h-6 w-6" />
            </div>
            <div>
                <h1 class="text-lg font-bold leading-tight">Ujian / Ulangan</h1>
                <p class="text-xs text-muted-foreground">Daftar ujian tersedia untuk kelas {{ siswa.kelas }}</p>
            </div>
        </div>

        <div v-if="ujians.length" class="flex flex-col gap-3">
            <div
                v-for="u in ujians"
                :key="u.id"
                class="rounded-xl border bg-card p-4 shadow-xs transition-all hover:shadow-md"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-card-foreground">{{ u.nama }}</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">{{ u.mapel }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                            <span class="inline-flex items-center gap-1">
                                <Clock class="h-3.5 w-3.5" />
                                {{ u.durasi }} menit
                            </span>
                            <span>{{ u.tgl_mulai }} — {{ u.tgl_selesai }}</span>
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-2">
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                            :class="{
                                'bg-green-100 text-green-700': u.status_siswa === 'selesai',
                                'bg-yellow-100 text-yellow-700': u.status_siswa === 'sedang',
                                'bg-blue-100 text-blue-700': u.status_siswa === 'baru',
                            }"
                        >
                            {{ { baru: 'Baru', sedang: 'Sedang', selesai: 'Selesai' }[u.status_siswa] }}
                        </span>
                        <Link
                            v-if="u.status_siswa !== 'selesai' && u.status"
                            :href="`/ujian/${u.id}`"
                            class="inline-flex items-center gap-1 rounded-lg bg-primary px-3 py-1.5 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90 active:scale-95"
                        >
                            <Play class="h-3.5 w-3.5" />
                            {{ u.status_siswa === 'sedang' ? 'Lanjutkan' : 'Mulai' }}
                        </Link>
                        <span
                            v-else-if="!u.status"
                            class="text-xs text-muted-foreground/60"
                        >
                            Ditutup
                        </span>
                        <div
                            v-else
                            class="inline-flex items-center gap-1 text-xs text-green-600"
                        >
                            <CheckCircle2 class="h-3.5 w-3.5" />
                            Selesai
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="rounded-xl border bg-card p-12 text-center shadow-xs">
            <Frown class="mx-auto h-12 w-12 text-muted-foreground/40" />
            <h3 class="mt-3 text-sm font-medium text-card-foreground">Belum Ada Ujian</h3>
            <p class="mt-1 text-xs text-muted-foreground/60">
                Belum ada ujian tersedia untuk kelas {{ siswa.kelas }}
            </p>
        </div>
    </div>
</template>
