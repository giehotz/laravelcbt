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
    sesi: Array<{ id: number; nama_sesi: string; waktu_mulai: string; waktu_akhir: string }>;
}>();

// We will track the unsaved state of each row
const formData = ref(props.kelasData.map(k => ({
    ...k,
    isDirty: false,
    isSaving: false
})));

const saveRow = (idx: number) => {
    const row = formData.value[idx];
    if (!row.ruang_id || !row.sesi_id) {
        alert('Pilih Ruang dan Sesi terlebih dahulu!');
        return;
    }

    row.isSaving = true;
    router.post(store.url(), {
        kelas_id: row.kelas_id,
        ruang_id: row.ruang_id,
        sesi_id: row.sesi_id,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            row.isDirty = false;
        },
        onFinish: () => {
            row.isSaving = false;
        }
    });
};

const syncRow = (idx: number) => {
    const row = formData.value[idx];
    if (!row.id) return;
    
    row.isSaving = true;
    router.post(sync.url(row.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            row.isSaving = false;
        }
    });
};

const markDirty = (idx: number) => {
    formData.value[idx].isDirty = true;
};
</script>

<template>
    <Head title="Atur Ruang & Sesi CBT" />

    <div class="px-6 py-6 max-w-7xl mx-auto space-y-6">
        <Heading
            title="Atur Ruang & Sesi Ujian"
            description="Petakan tiap Kelas ke dalam Ruang Ujian dan Sesi Ujian secara bulk."
        />

        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                            <th class="px-6 py-3 w-16 text-center">No</th>
                            <th class="px-6 py-3">Nama Kelas</th>
                            <th class="px-6 py-3 w-48">Pilih Ruang</th>
                            <th class="px-6 py-3 w-64">Pilih Sesi</th>
                            <th class="px-6 py-3 w-32 text-center">Status</th>
                            <th class="px-6 py-3 text-right w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                        <tr v-for="(item, idx) in formData" :key="item.kelas_id" class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20" :class="{ 'bg-green-50/30 dark:bg-emerald-900/10': item.id && !item.isDirty, 'bg-amber-50/30 dark:bg-amber-900/10': item.isDirty }">
                            <td class="px-6 py-3.5 text-center font-medium text-neutral-400 dark:text-neutral-500">{{ idx + 1 }}</td>
                            <td class="px-6 py-3.5 font-bold text-neutral-800 dark:text-neutral-200">{{ item.nama_kelas }}</td>
                            
                            <td class="px-6 py-2.5">
                                <select v-model="item.ruang_id" @change="markDirty(idx)" class="w-full text-sm border border-neutral-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-950 text-neutral-800 dark:text-neutral-200 px-3 py-1.5 outline-none focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 transition-all">
                                    <option :value="null" disabled>-- Pilih Ruang --</option>
                                    <option v-for="r in ruang" :key="r.id" :value="r.id">{{ r.nama_ruang }}</option>
                                </select>
                            </td>
                            
                            <td class="px-6 py-2.5">
                                <select v-model="item.sesi_id" @change="markDirty(idx)" class="w-full text-sm border border-neutral-300 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-950 text-neutral-800 dark:text-neutral-200 px-3 py-1.5 outline-none focus:ring-2 focus:ring-zinc-900 dark:focus:ring-zinc-100 transition-all">
                                    <option :value="null" disabled>-- Pilih Sesi --</option>
                                    <option v-for="s in sesi" :key="s.id" :value="s.id">{{ s.nama_sesi }} ({{ s.waktu_mulai }} - {{ s.waktu_akhir }})</option>
                                </select>
                            </td>
                            
                            <td class="px-6 py-3.5 text-center">
                                <div v-if="!item.id && !item.isDirty" class="inline-flex items-center gap-1.5 text-neutral-400 dark:text-neutral-500 text-xs font-semibold px-2 py-1 rounded bg-neutral-100 dark:bg-zinc-800">
                                    <AlertCircle class="w-3.5 h-3.5" />
                                    Belum
                                </div>
                                <div v-else-if="item.isDirty" class="inline-flex items-center gap-1.5 text-amber-600 dark:text-amber-500 text-xs font-semibold px-2 py-1 rounded bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800/50">
                                    <AlertCircle class="w-3.5 h-3.5" />
                                    Beriubah
                                </div>
                                <div v-else class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 text-xs font-semibold px-2 py-1 rounded bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/50">
                                    <CheckCircle2 class="w-3.5 h-3.5" />
                                    Ter-assign
                                </div>
                            </td>

                            <td class="px-6 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <Button
                                    v-if="item.isDirty || !item.id"
                                    size="sm"
                                    @click="saveRow(idx)"
                                    :disabled="item.isSaving || !item.ruang_id || !item.sesi_id"
                                    class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-100 dark:hover:bg-white text-white dark:text-zinc-950 font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                >
                                    <Save class="w-3.5 h-3.5 mr-1" />
                                    {{ item.isSaving ? 'Menyimpan...' : 'Simpan' }}
                                </Button>
                                <Button
                                    v-if="item.id && !item.isDirty"
                                    size="sm"
                                    variant="outline"
                                    @click="syncRow(idx)"
                                    :disabled="item.isSaving"
                                    class="text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                    title="Jalankan ulang sinkronisasi siswa ke tabel SesiSiswa"
                                >
                                    <RefreshCw class="w-3.5 h-3.5 mr-1" :class="{'animate-spin': item.isSaving}" />
                                    Sync Ulang
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="formData.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-neutral-500">
                                Belum ada kelas yang terdaftar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
