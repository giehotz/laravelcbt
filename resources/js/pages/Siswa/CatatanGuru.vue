<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { UserCheck, ArrowLeft, Frown } from 'lucide-vue-next';
import { dashboard } from '@/routes/siswa';

defineProps<{
    siswa: { kelas: string };
}>();
</script>

<template>
    <div class="mx-auto flex w-full max-w-3xl flex-col gap-4 sm:gap-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-pink-100 text-pink-600">
                <UserCheck class="h-6 w-6" />
            </div>
            <div>
                <h1 class="text-lg font-bold leading-tight">Catatan Guru</h1>
                <p class="text-xs text-muted-foreground">Kelas {{ siswa.kelas }}</p>
            </div>
        </div>

        <div class="rounded-xl border bg-card p-12 text-center shadow-xs">
            <Frown class="mx-auto h-12 w-12 text-muted-foreground/40" />
            <h3 class="mt-3 text-sm font-medium text-card-foreground">Fitur dalam Pengembangan</h3>
            <p class="mt-1 text-xs text-muted-foreground/60">
                Catatan dan pesan dari wali kelas akan segera tersedia.
            </p>
            <Link
                :href="dashboard()"
                class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:underline"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali ke Dashboard
            </Link>
        </div>
    </div>
</template>
