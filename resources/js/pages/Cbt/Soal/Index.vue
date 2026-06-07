<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { index as bankSoalIndex } from '@/routes/cbt/bank-soal';
import { create, edit, destroy } from '@/routes/cbt/bank-soal/soal';
import { ArrowLeft, Plus, Edit2, Trash2, HelpCircle } from 'lucide-vue-next';
import MatchingPreview from '@/components/Cbt/MatchingPreview.vue';
import { useConfirmStore } from '@/stores/confirm';

const props = defineProps<{
    bank: any;
    soals: Array<any>;
}>();

const confirmStore = useConfirmStore();

const confirmDelete = (id: number) => {
    confirmStore.requireConfirm({
        title: 'Hapus Soal',
        message: 'Apakah Anda yakin ingin menghapus soal ini?',
        onConfirm: () => {
            router.delete(destroy.url({ bank: props.bank.id, soal: id }));
        },
    });
};

const isJawabanBenar = (item: any, opt: string): boolean => {
    if (Array.isArray(item.jawaban)) {
        return item.jawaban.includes(opt);
    }
    return item.jawaban === opt;
};

const getJenisLabel = (jenis: number) => {
    const map: Record<number, string> = {
        1: 'Pilihan Ganda',
        2: 'Ganda Kompleks',
        3: 'Menjodohkan',
        4: 'Isian Singkat',
        5: 'Uraian/Esai',
    };
    return map[jenis] || 'Unknown';
};

const activeJenisFilter = ref<number | null>(null);

const jenisTabs = [
    { id: null, label: 'Semua Soal', key: 'all' },
    { id: 1, label: 'Pilihan Ganda', key: 'pg' },
    { id: 2, label: 'Pilihan Ganda Kompleks', key: 'kompleks' },
    { id: 3, label: 'Menjodohkan', key: 'jodohkan' },
    { id: 4, label: 'Isian Singkat', key: 'isian' },
    { id: 5, label: 'Essai/Uraian', key: 'esai' },
];

const getCountForJenis = (jenis: number | null) => {
    if (jenis === null) return props.soals.length;
    return props.soals.filter((s) => s.jenis === jenis).length;
};

const filteredSoals = computed(() => {
    let list = [...props.soals];
    if (activeJenisFilter.value !== null) {
        list = list.filter((s) => s.jenis === activeJenisFilter.value);
    }
    return list.sort((a, b) => {
        if (a.jenis !== b.jenis) return a.jenis - b.jenis;
        return a.nomor_soal - b.nomor_soal;
    });
});
</script>

<template>
    <Head :title="`Preview Soal - ${bank.kode}`" />

    <div
        class="min-h-screen bg-gray-100 pb-12 transition-colors duration-200 dark:bg-zinc-950"
    >
        <!-- Header -->
        <div
            class="sticky top-0 z-30 border-b border-gray-200 bg-white shadow-sm transition-colors duration-200 dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div class="mx-auto max-w-[1200px] px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="$inertia.visit(bankSoalIndex.url())"
                            class="h-9 shrink-0 border-neutral-200 px-3 text-neutral-700 dark:border-zinc-800 dark:text-zinc-300"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" /> Kembali
                        </Button>
                        <div>
                            <h1
                                class="text-xl leading-tight font-bold text-gray-900 dark:text-zinc-100"
                            >
                                Daftar Soal (Preview)
                            </h1>
                            <p
                                class="text-sm font-medium text-gray-500 dark:text-zinc-400"
                            >
                                <span
                                    class="font-bold text-gray-700 dark:text-zinc-300"
                                    >Bank Soal:</span
                                >
                                {{ bank.kode }}
                                <span class="mx-2">|</span>
                                PG: {{ bank.jml_pg }} / {{ bank.tampil_pg }}
                                <span class="mx-2">|</span> Kompleks:
                                {{ bank.jml_kompleks }} /
                                {{ bank.tampil_kompleks }}
                                <span class="mx-2">|</span> Jodohkan:
                                {{ bank.jml_jodohkan }} /
                                {{ bank.tampil_jodohkan }}
                                <span class="mx-2">|</span> Isian:
                                {{ bank.jml_isian }} / {{ bank.tampil_isian }}
                                <span class="mx-2">|</span> Esai:
                                {{ bank.jml_esai }} / {{ bank.tampil_esai }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto mt-6 max-w-[1200px] px-4 sm:px-6 lg:px-8">
            <!-- Tambah Soal Actions -->
            <div
                class="mb-6 flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-colors duration-200 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <span
                    class="mr-2 text-sm font-bold text-gray-700 dark:text-zinc-300"
                    >Tambah Soal:</span
                >
                <Button
                    v-if="bank.jml_pg < bank.tampil_pg || bank.tampil_pg > 0"
                    @click="
                        router.get(create.url({ bank: bank.id }), { jenis: 1 })
                    "
                    variant="outline"
                    class="border-blue-200 text-blue-700 hover:bg-blue-50 dark:border-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-950/20"
                >
                    <Plus class="mr-1 h-4 w-4" /> Pilihan Ganda
                </Button>
                <Button
                    v-if="
                        bank.jml_kompleks < bank.tampil_kompleks ||
                        bank.tampil_kompleks > 0
                    "
                    @click="
                        router.get(create.url({ bank: bank.id }), { jenis: 2 })
                    "
                    variant="outline"
                    class="border-indigo-200 text-indigo-700 hover:bg-indigo-50 dark:border-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-950/20"
                >
                    <Plus class="mr-1 h-4 w-4" /> Kompleks
                </Button>
                <Button
                    v-if="
                        bank.jml_jodohkan < bank.tampil_jodohkan ||
                        bank.tampil_jodohkan > 0
                    "
                    @click="
                        router.get(create.url({ bank: bank.id }), { jenis: 3 })
                    "
                    variant="outline"
                    class="border-purple-200 text-purple-700 hover:bg-purple-50 dark:border-purple-900/30 dark:text-purple-400 dark:hover:bg-purple-950/20"
                >
                    <Plus class="mr-1 h-4 w-4" /> Jodohkan
                </Button>
                <Button
                    v-if="
                        bank.jml_isian < bank.tampil_isian ||
                        bank.tampil_isian > 0
                    "
                    @click="
                        router.get(create.url({ bank: bank.id }), { jenis: 4 })
                    "
                    variant="outline"
                    class="border-amber-200 text-amber-700 hover:bg-amber-50 dark:border-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-950/20"
                >
                    <Plus class="mr-1 h-4 w-4" /> Isian Singkat
                </Button>
                <Button
                    v-if="
                        bank.jml_esai < bank.tampil_esai || bank.tampil_esai > 0
                    "
                    @click="
                        router.get(create.url({ bank: bank.id }), { jenis: 5 })
                    "
                    variant="outline"
                    class="border-rose-200 text-rose-700 hover:bg-rose-50 dark:border-rose-900/30 dark:text-rose-400 dark:hover:bg-rose-950/20"
                >
                    <Plus class="mr-1 h-4 w-4" /> Esai / Uraian
                </Button>
            </div>

            <!-- Tab Navigation Filters -->
            <div
                class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-colors duration-200 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex overflow-x-auto border-b border-gray-100 dark:border-zinc-800"
                >
                    <button
                        v-for="tab in jenisTabs"
                        :key="tab.key"
                        @click="activeJenisFilter = tab.id"
                        class="flex items-center gap-2 border-b-2 px-5 py-3 text-xs font-bold whitespace-nowrap transition-colors"
                        :class="
                            activeJenisFilter === tab.id
                                ? 'border-blue-500 bg-blue-50/50 text-blue-600 dark:bg-zinc-800/40 dark:text-blue-400'
                                : 'border-transparent text-gray-500 hover:bg-gray-50/50 hover:text-gray-700 dark:text-zinc-400 dark:hover:bg-zinc-800/30 dark:hover:text-zinc-200'
                        "
                    >
                        {{ tab.label }}
                        <span
                            class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                            :class="
                                activeJenisFilter === tab.id
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400'
                                    : 'bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-zinc-400'
                            "
                        >
                            {{ getCountForJenis(tab.id) }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="filteredSoals.length === 0"
                class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <HelpCircle
                    class="mx-auto mb-4 h-16 w-16 text-gray-300 dark:text-zinc-700"
                />
                <h3 class="text-lg font-bold text-gray-700 dark:text-zinc-300">
                    Belum Ada Soal
                </h3>
                <p class="mt-2 text-gray-500 dark:text-zinc-400">
                    {{
                        activeJenisFilter === null
                            ? 'Silakan klik salah satu tombol Tambah Soal di atas untuk mulai membuat soal di Bank ini.'
                            : 'Tidak ada soal dengan tipe ini di dalam Bank Soal.'
                    }}
                </p>
            </div>

            <!-- List Soal -->
            <div class="space-y-6">
                <div
                    v-for="item in filteredSoals"
                    :key="item.id"
                    class="group relative rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition-colors hover:border-blue-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
                >
                    <!-- Toolbar (Edit/Hapus) -->
                    <div class="absolute top-4 right-4 flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 border-gray-200 px-2 text-gray-600 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-zinc-800 dark:text-zinc-400 dark:hover:bg-blue-950/20"
                            @click="
                                router.get(
                                    edit.url({ bank: bank.id, soal: item.id }),
                                )
                            "
                        >
                            <Edit2 class="h-4 w-4" />
                            <span class="ml-1 text-xs">Edit</span>
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 border-gray-200 px-2 text-gray-600 hover:border-red-300 hover:bg-red-50 hover:text-red-600 dark:border-zinc-800 dark:text-zinc-400 dark:hover:bg-red-950/20"
                            @click="confirmDelete(item.id)"
                        >
                            <Trash2 class="h-4 w-4" />
                            <span class="ml-1 text-xs">Hapus</span>
                        </Button>
                    </div>

                    <!-- Header Soal -->
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-blue-200 bg-blue-100 text-lg font-bold text-blue-700 shadow-sm dark:border-blue-800/40 dark:bg-blue-900/30 dark:text-blue-400"
                        >
                            {{ item.nomor_soal }}
                        </div>
                        <div>
                            <span
                                class="rounded border border-gray-200 bg-gray-100 px-2 py-1 text-xs font-bold tracking-wider text-gray-700 uppercase dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
                            >
                                {{ getJenisLabel(item.jenis) }}
                            </span>
                        </div>
                    </div>

                    <!-- Teks Soal -->
                    <div
                        class="prose dark:prose-invert mb-6 max-w-none text-gray-800 dark:text-zinc-200"
                        v-html="item.soal"
                    ></div>

                    <!-- Pilihan Ganda -->
                    <div
                        v-if="item.jenis === 1 || item.jenis === 2"
                        class="space-y-3 pl-2"
                    >
                        <div
                            v-for="opt in ['A', 'B', 'C', 'D', 'E'].slice(
                                0,
                                bank.opsi,
                            )"
                            :key="opt"
                            class="flex gap-4"
                        >
                            <div class="pt-1">
                                <div
                                    class="flex h-7 w-7 items-center justify-center rounded-full border-2 text-sm font-bold"
                                    :class="
                                        isJawabanBenar(item, opt)
                                            ? 'border-green-500 bg-green-500 text-white dark:border-green-600 dark:bg-green-600'
                                            : 'border-gray-300 text-gray-500 dark:border-zinc-700 dark:text-zinc-400'
                                    "
                                >
                                    {{ opt }}
                                </div>
                            </div>
                            <div
                                class="prose dark:prose-invert max-w-none flex-1 pt-1 text-gray-700 dark:text-zinc-300"
                                :class="
                                    isJawabanBenar(item, opt)
                                        ? 'font-medium'
                                        : ''
                                "
                                v-html="item[`opsi_${opt.toLowerCase()}`]"
                            ></div>
                        </div>
                    </div>

                    <!-- Menjodohkan -->
                    <div v-else-if="item.jenis === 3" class="mt-4">
                        <div
                            class="mb-2 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-zinc-400"
                        >
                            Kunci Pasangan Menjodohkan (Garis Penghubung):
                        </div>
                        <MatchingPreview :jawaban="item.pairs" />
                    </div>

                    <!-- Isian Singkat / Esai -->
                    <div
                        v-else-if="item.jenis === 4 || item.jenis === 5"
                        class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-zinc-800 dark:bg-zinc-950"
                    >
                        <div
                            class="mb-2 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-zinc-400"
                        >
                            Kunci Jawaban / Pedoman Penilaian:
                        </div>
                        <div
                            class="text-sm whitespace-pre-wrap text-gray-700 dark:text-zinc-300"
                        >
                            {{ item.jawaban || '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
