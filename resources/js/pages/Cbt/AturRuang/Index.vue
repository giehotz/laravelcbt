<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { index as indexAtur, store, sync } from '@/routes/cbt/atur-ruang';
import { CheckCircle2, AlertCircle, Save, RefreshCw } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'CBT', href: '#' },
            { title: 'Atur Ruang & Sesi', href: indexAtur.url() },
        ],
    },
});

const props = defineProps<{
    kelasData: Array<{
        kelas_id: number;
        nama_kelas: string;
        ruang_id: number | null;
        sesi_id: number | null;
        id: number | null;
    }>;
    ruang: Array<{ id: number; nama_ruang: string }>;
    sesi: Array<{
        id: number;
        nama_sesi: string;
        waktu_mulai: string;
        waktu_akhir: string;
    }>;
}>();

// We will track the unsaved state of each row
const formData = ref(
    props.kelasData.map((k) => ({
        ...k,
        isDirty: false,
        isSaving: false,
    })),
);

const saveRow = (idx: number) => {
    const row = formData.value[idx];
    if (!row.ruang_id || !row.sesi_id) {
        alert('Pilih Ruang dan Sesi terlebih dahulu!');
        return;
    }

    row.isSaving = true;
    router.post(
        store.url(),
        {
            kelas_id: row.kelas_id,
            ruang_id: row.ruang_id,
            sesi_id: row.sesi_id,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                row.isDirty = false;
            },
            onFinish: () => {
                row.isSaving = false;
            },
        },
    );
};

const syncRow = (idx: number) => {
    const row = formData.value[idx];
    if (!row.id) return;

    row.isSaving = true;
    router.post(
        sync.url(row.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                row.isSaving = false;
            },
        },
    );
};

const markDirty = (idx: number) => {
    formData.value[idx].isDirty = true;
};
</script>

<template>
    <Head title="Atur Ruang & Sesi CBT" />

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">
        <Heading
            title="Atur Ruang & Sesi Ujian"
            description="Petakan tiap Kelas ke dalam Ruang Ujian dan Sesi Ujian secara bulk."
        />

        <div
            class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50/30 text-[11px] font-bold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-900/50"
                        >
                            <th class="w-16 px-6 py-3 text-center">No</th>
                            <th class="px-6 py-3">Nama Kelas</th>
                            <th class="w-48 px-6 py-3">Pilih Ruang</th>
                            <th class="w-64 px-6 py-3">Pilih Sesi</th>
                            <th class="w-32 px-6 py-3 text-center">Status</th>
                            <th class="w-48 px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                    >
                        <tr
                            v-for="(item, idx) in formData"
                            :key="item.kelas_id"
                            class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20"
                            :class="{
                                'bg-green-50/30 dark:bg-emerald-900/10':
                                    item.id && !item.isDirty,
                                'bg-amber-50/30 dark:bg-amber-900/10':
                                    item.isDirty,
                            }"
                        >
                            <td
                                class="px-6 py-3.5 text-center font-medium text-neutral-400 dark:text-neutral-500"
                            >
                                {{ idx + 1 }}
                            </td>
                            <td
                                class="px-6 py-3.5 font-bold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ item.nama_kelas }}
                            </td>

                            <td class="px-6 py-2.5">
                                <select
                                    v-model="item.ruang_id"
                                    @change="markDirty(idx)"
                                    class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-sm text-neutral-800 transition-all outline-none focus:ring-2 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-neutral-200 dark:focus:ring-zinc-100"
                                >
                                    <option :value="null" disabled>
                                        -- Pilih Ruang --
                                    </option>
                                    <option
                                        v-for="r in ruang"
                                        :key="r.id"
                                        :value="r.id"
                                    >
                                        {{ r.nama_ruang }}
                                    </option>
                                </select>
                            </td>

                            <td class="px-6 py-2.5">
                                <select
                                    v-model="item.sesi_id"
                                    @change="markDirty(idx)"
                                    class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-sm text-neutral-800 transition-all outline-none focus:ring-2 focus:ring-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-neutral-200 dark:focus:ring-zinc-100"
                                >
                                    <option :value="null" disabled>
                                        -- Pilih Sesi --
                                    </option>
                                    <option
                                        v-for="s in sesi"
                                        :key="s.id"
                                        :value="s.id"
                                    >
                                        {{ s.nama_sesi }} ({{ s.waktu_mulai }} -
                                        {{ s.waktu_akhir }})
                                    </option>
                                </select>
                            </td>

                            <td class="px-6 py-3.5 text-center">
                                <div
                                    v-if="!item.id && !item.isDirty"
                                    class="inline-flex items-center gap-1.5 rounded bg-neutral-100 px-2 py-1 text-xs font-semibold text-neutral-400 dark:bg-zinc-800 dark:text-neutral-500"
                                >
                                    <AlertCircle class="h-3.5 w-3.5" />
                                    Belum
                                </div>
                                <div
                                    v-else-if="item.isDirty"
                                    class="inline-flex items-center gap-1.5 rounded border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-600 dark:border-amber-800/50 dark:bg-amber-900/30 dark:text-amber-500"
                                >
                                    <AlertCircle class="h-3.5 w-3.5" />
                                    Beriubah
                                </div>
                                <div
                                    v-else
                                    class="inline-flex items-center gap-1.5 rounded border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-600 dark:border-emerald-800/50 dark:bg-emerald-900/30 dark:text-emerald-400"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    Ter-assign
                                </div>
                            </td>

                            <td
                                class="space-x-1.5 px-6 py-3.5 text-right whitespace-nowrap"
                            >
                                <Button
                                    v-if="item.isDirty || !item.id"
                                    size="sm"
                                    @click="saveRow(idx)"
                                    :disabled="
                                        item.isSaving ||
                                        !item.ruang_id ||
                                        !item.sesi_id
                                    "
                                    class="h-8 rounded bg-zinc-900 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-950 dark:hover:bg-white"
                                >
                                    <Save class="mr-1 h-3.5 w-3.5" />
                                    {{
                                        item.isSaving
                                            ? 'Menyimpan...'
                                            : 'Simpan'
                                    }}
                                </Button>
                                <Button
                                    v-if="item.id && !item.isDirty"
                                    size="sm"
                                    variant="outline"
                                    @click="syncRow(idx)"
                                    :disabled="item.isSaving"
                                    class="h-8 rounded px-2.5 py-1.5 text-xs shadow-sm transition-colors"
                                    title="Jalankan ulang sinkronisasi siswa ke tabel SesiSiswa"
                                >
                                    <RefreshCw
                                        class="mr-1 h-3.5 w-3.5"
                                        :class="{
                                            'animate-spin': item.isSaving,
                                        }"
                                    />
                                    Sync Ulang
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="formData.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-8 text-center text-neutral-500"
                            >
                                Belum ada kelas yang terdaftar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
