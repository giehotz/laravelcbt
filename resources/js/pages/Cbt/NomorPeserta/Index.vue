<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { index as indexNomor, generate } from '@/routes/cbt/nomor-peserta';
import { RefreshCcw, Search, X } from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'CBT', href: '#' },
            { title: 'Nomor Peserta', href: indexNomor.url() },
        ],
    },
});

const props = defineProps<{
    peserta: any;
    kelas: Array<{ id: number; nama_kelas: string }>;
    filters: { kelas_id?: number | string };
}>();

const selectedKelas = ref(props.filters.kelas_id || '');
const isGenerateModalOpen = ref(false);
const generateForm = useForm({});

// Handle Filter by Class
watch(selectedKelas, (newVal) => {
    router.get(indexNomor.url(), { kelas_id: newVal }, {
        preserveState: true,
        replace: true,
    });
});

const submitGenerate = () => {
    generateForm.post(generate.url(), {
        onSuccess: () => {
            isGenerateModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Nomor Peserta CBT" />

    <div class="px-6 py-6 max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <Heading
                title="Nomor Peserta Ujian"
                description="Kelola dan hasilkan nomor peserta secara otomatis untuk ujian."
            />
            <Button @click="isGenerateModalOpen = true" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold flex items-center gap-2 shadow-sm transition-colors">
                <RefreshCcw class="w-4 h-4" />
                <span>Generate Otomatis</span>
            </Button>
        </div>

        <!-- Filter Section -->
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl p-4 shadow-sm flex items-center gap-4">
            <div class="flex items-center text-sm font-semibold text-neutral-600 dark:text-neutral-400 gap-2">
                <Search class="w-4 h-4" />
                Filter Kelas:
            </div>
            <select v-model="selectedKelas" class="text-sm border border-neutral-300 dark:border-zinc-700 rounded-lg bg-neutral-50 dark:bg-zinc-950 text-neutral-800 dark:text-neutral-200 px-3 py-1.5 outline-none focus:ring-2 focus:ring-amber-500 transition-all">
                <option value="">Semua Kelas</option>
                <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
            </select>
        </div>

        <!-- Data Table -->
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                            <th class="px-6 py-3 w-16 text-center">No</th>
                            <th class="px-6 py-3">Nama Siswa</th>
                            <th class="px-6 py-3">NISN</th>
                            <th class="px-6 py-3">Kelas</th>
                            <th class="px-6 py-3 text-center">Nomor Peserta</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                        <tr v-for="(item, idx) in peserta.data" :key="item.id" class="hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                            <td class="px-6 py-3.5 text-center font-medium text-neutral-400 dark:text-neutral-500">
                                {{ (peserta.current_page - 1) * peserta.per_page + Number(idx) + 1 }}
                            </td>
                            <td class="px-6 py-3.5 font-bold text-neutral-800 dark:text-neutral-200">{{ item.siswa.nama }}</td>
                            <td class="px-6 py-3.5 font-mono text-xs text-neutral-500">{{ item.siswa.nisn }}</td>
                            <td class="px-6 py-3.5 font-medium text-neutral-600 dark:text-neutral-400">{{ item.nama_kelas }}</td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/50 px-3 py-1 rounded-full font-mono text-xs font-bold tracking-widest shadow-sm">
                                    {{ item.nomor_peserta }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="peserta.data.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-neutral-500">
                                Belum ada data nomor peserta. Silakan klik Generate Otomatis.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="peserta.links && peserta.links.length > 3" class="px-6 py-4 border-t border-neutral-200 dark:border-zinc-800 bg-neutral-50/30 dark:bg-zinc-900/30 flex justify-center">
                <div class="flex gap-1">
                    <template v-for="(link, key) in peserta.links" :key="key">
                        <div v-if="link.url === null" class="px-3 py-1 text-sm text-neutral-400 border border-neutral-200 dark:border-zinc-800 rounded bg-neutral-50 dark:bg-zinc-900 cursor-not-allowed" v-html="link.label"></div>
                        <a v-else :href="link.url" :class="['px-3 py-1 text-sm border rounded transition-colors', link.active ? 'bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 border-zinc-900 dark:border-zinc-100 font-bold' : 'bg-white dark:bg-zinc-900 text-neutral-700 dark:text-neutral-300 border-neutral-200 dark:border-zinc-800 hover:bg-neutral-50 dark:hover:bg-zinc-800']" v-html="link.label" @click.prevent="router.visit(link.url, { preserveState: true })"></a>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form: Generate -->
    <div v-if="isGenerateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Konfirmasi Generate Otomatis</h3>
                <button type="button" @click="isGenerateModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitGenerate" class="p-6 space-y-4">
                <div class="bg-rose-50 dark:bg-rose-900/20 text-rose-800 dark:text-rose-300 p-4 rounded-lg text-sm border border-rose-200 dark:border-rose-900/50">
                    <p class="font-bold mb-1 flex items-center gap-1">
                        ⚠️ PERINGATAN DESTRUKTIF
                    </p>
                    <p>
                        Aksi ini akan menghapus semua nomor peserta yang ada pada Semester Aktif ini dan men-generate ulang urutan nomor peserta untuk seluruh kelas. 
                    </p>
                    <p class="mt-2 font-semibold text-rose-900 dark:text-rose-200">
                        Lanjutkan hanya jika ujian belum dimulai!
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-zinc-800">
                    <Button type="button" variant="outline" @click="isGenerateModalOpen = false">Batal</Button>
                    <Button type="submit" :disabled="generateForm.processing" class="bg-rose-600 hover:bg-rose-700 text-white font-semibold">
                        Ya, Generate Sekarang
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
