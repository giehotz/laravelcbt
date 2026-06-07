<script setup lang="ts">
import { School, LogOut } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps<{
    namaSekolah: string;
    tpAktif: string | null;
    smtAktif: string | null;
    siswa: {
        nama: string;
        nis: string;
        kelas: string;
        foto: string | null;
    };
}>();
</script>

<template>
    <div class="flex flex-col gap-4">
        <!-- Baris header sekolah -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                    <School class="h-6 w-6" />
                </div>
                <div>
                    <h1 class="text-lg font-bold leading-tight">{{ namaSekolah }}</h1>
                    <p v-if="tpAktif && smtAktif" class="text-xs text-muted-foreground">
                        Tahun Pelajaran: {{ tpAktif }} Smt: {{ smtAktif }}
                    </p>
                </div>
            </div>
            <Link
                href="/logout"
                method="post"
                as="button"
                class="inline-flex items-center gap-1.5 rounded-lg bg-red-500 px-3 py-2 text-xs font-semibold text-white shadow-xs transition-colors hover:bg-red-600 active:scale-95"
            >
                <LogOut class="h-4 w-4" />
                <span class="hidden sm:inline">Logout</span>
            </Link>
        </div>

        <!-- Profil siswa -->
        <div class="flex items-center gap-3 rounded-xl border bg-card p-4 shadow-xs">
            <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-blue-100 text-lg font-bold text-blue-600"
            >
                {{ siswa.nama.charAt(0).toUpperCase() }}
            </div>
            <div class="min-w-0">
                <h2 class="truncate text-base font-bold text-card-foreground">{{ siswa.nama }}</h2>
                <div class="flex flex-wrap gap-x-4 text-xs text-muted-foreground">
                    <span>NIS: {{ siswa.nis }}</span>
                    <span>Kelas {{ siswa.kelas }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
