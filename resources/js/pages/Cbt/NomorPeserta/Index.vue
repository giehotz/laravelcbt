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
    router.get(
        indexNomor.url(),
        { kelas_id: newVal },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const submitGenerate = () => {
    generateForm.post(generate.url(), {
        onSuccess: () => {
            isGenerateModalOpen.value = false;
        },
    });
};
</script>

<template>
    <Head title="Nomor Peserta CBT" />

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Nomor Peserta Ujian"
                description="Kelola dan hasilkan nomor peserta secara otomatis untuk ujian."
            />
            <Button
                @click="isGenerateModalOpen = true"
                class="flex items-center gap-2 bg-amber-500 font-semibold text-white shadow-sm transition-colors hover:bg-amber-600"
            >
                <RefreshCcw class="h-4 w-4" />
                <span>Generate Otomatis</span>
            </Button>
        </div>

        <!-- Filter Section -->
        <div
            class="flex items-center gap-4 rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center gap-2 text-sm font-semibold text-neutral-600 dark:text-neutral-400"
            >
                <Search class="h-4 w-4" />
                Filter Kelas:
            </div>
            <select
                v-model="selectedKelas"
                class="rounded-lg border border-neutral-300 bg-neutral-50 px-3 py-1.5 text-sm text-neutral-800 transition-all outline-none focus:ring-2 focus:ring-amber-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-neutral-200"
            >
                <option value="">Semua Kelas</option>
                <option v-for="k in kelas" :key="k.id" :value="k.id">
                    {{ k.nama_kelas }}
                </option>
            </select>
        </div>

        <!-- Data Table -->
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
                            <th class="px-6 py-3">Nama Siswa</th>
                            <th class="px-6 py-3">NISN</th>
                            <th class="px-6 py-3">Kelas</th>
                            <th class="px-6 py-3 text-center">Nomor Peserta</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                    >
                        <tr
                            v-for="(item, idx) in peserta.data"
                            :key="item.id"
                            class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20"
                        >
                            <td
                                class="px-6 py-3.5 text-center font-medium text-neutral-400 dark:text-neutral-500"
                            >
                                {{
                                    (peserta.current_page - 1) *
                                        peserta.per_page +
                                    Number(idx) +
                                    1
                                }}
                            </td>
                            <td
                                class="px-6 py-3.5 font-bold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ item.siswa.nama }}
                            </td>
                            <td
                                class="px-6 py-3.5 font-mono text-xs text-neutral-500"
                            >
                                {{ item.siswa.nisn }}
                            </td>
                            <td
                                class="px-6 py-3.5 font-medium text-neutral-600 dark:text-neutral-400"
                            >
                                {{ item.nama_kelas }}
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span
                                    class="rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 font-mono text-xs font-bold tracking-widest text-indigo-700 shadow-sm dark:border-indigo-800/50 dark:bg-indigo-900/30 dark:text-indigo-400"
                                >
                                    {{ item.nomor_peserta }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="peserta.data.length === 0">
                            <td
                                colspan="5"
                                class="px-6 py-8 text-center text-neutral-500"
                            >
                                Belum ada data nomor peserta. Silakan klik
                                Generate Otomatis.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="peserta.links && peserta.links.length > 3"
                class="flex justify-center border-t border-neutral-200 bg-neutral-50/30 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/30"
            >
                <div class="flex gap-1">
                    <template v-for="(link, key) in peserta.links" :key="key">
                        <div
                            v-if="link.url === null"
                            class="cursor-not-allowed rounded border border-neutral-200 bg-neutral-50 px-3 py-1 text-sm text-neutral-400 dark:border-zinc-800 dark:bg-zinc-900"
                            v-html="link.label"
                        ></div>
                        <a
                            v-else
                            :href="link.url"
                            :class="[
                                'rounded border px-3 py-1 text-sm transition-colors',
                                link.active
                                    ? 'border-zinc-900 bg-zinc-900 font-bold text-white dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900'
                                    : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-neutral-300 dark:hover:bg-zinc-800',
                            ]"
                            v-html="link.label"
                            @click.prevent="
                                router.visit(link.url, { preserveState: true })
                            "
                        ></a>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form: Generate -->
    <div
        v-if="isGenerateModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
            >
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">
                    Konfirmasi Generate Otomatis
                </h3>
                <button
                    type="button"
                    @click="isGenerateModalOpen = false"
                    class="hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer rounded-lg p-1 text-neutral-400"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitGenerate" class="space-y-4 p-6">
                <div
                    class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900/50 dark:bg-rose-900/20 dark:text-rose-300"
                >
                    <p class="mb-1 flex items-center gap-1 font-bold">
                        ⚠️ PERINGATAN DESTRUKTIF
                    </p>
                    <p>
                        Aksi ini akan menghapus semua nomor peserta yang ada
                        pada Semester Aktif ini dan men-generate ulang urutan
                        nomor peserta untuk seluruh kelas.
                    </p>
                    <p
                        class="mt-2 font-semibold text-rose-900 dark:text-rose-200"
                    >
                        Lanjutkan hanya jika ujian belum dimulai!
                    </p>
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-zinc-800"
                >
                    <Button
                        type="button"
                        variant="outline"
                        @click="isGenerateModalOpen = false"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="generateForm.processing"
                        class="bg-rose-600 font-semibold text-white hover:bg-rose-700"
                    >
                        Ya, Generate Sekarang
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
