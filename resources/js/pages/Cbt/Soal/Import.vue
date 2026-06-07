<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { importMethod } from '@/routes/cbt/bank-soal/soal';
import { confirm, template } from '@/routes/cbt/bank-soal/soal/import';
import {
    ArrowLeft,
    Upload,
    Download,
    FileSpreadsheet,
    AlertTriangle,
    CheckCircle2,
    XCircle,
    HelpCircle,
} from 'lucide-vue-next';

interface PreviewRow {
    nomor_soal: number;
    soal: string;
    jawaban: string;
    valid: boolean;
    error_msg: string | null;
}

interface PreviewSheet {
    name: string;
    jenis: number;
    count: number;
    errors: Array<{ row: number | string; field: string; message: string }>;
    rows: PreviewRow[];
}

interface PreviewData {
    sheets: PreviewSheet[];
    total_rows: number;
    errors: Array<{ row: number | string; field: string; message: string }>;
    warnings: string[];
}

const props = defineProps<{
    bank: any;
    preview: PreviewData | null;
    cache_key?: string | null;
}>();

const form = useForm({ file: null as File | null });
const confirmProcessing = ref(false);
const activeSheet = ref<string | null>(null);

watch(
    () => props.preview,
    (val) => {
        if (val?.sheets?.length && !activeSheet.value) {
            activeSheet.value = val.sheets[0].name;
        }
    },
    { immediate: true },
);

const activeSheetData = computed<PreviewSheet>(() => {
    if (!props.preview || !props.preview.sheets.length)
        return {} as PreviewSheet;
    const found = props.preview.sheets.find(
        (s) => s.name === activeSheet.value,
    );
    return found || props.preview.sheets[0];
});

const activeSheetColumns = computed(() => {
    const cols: Array<{ key: string; label: string }> = [
        { key: 'nomor_soal', label: 'No' },
        { key: 'soal', label: 'Soal' },
        { key: 'jawaban', label: 'Jawaban' },
    ];
    if (activeSheetData.value?.errors?.length) {
        cols.push({ key: 'error', label: 'Error' });
    }
    return cols;
});

const hasErrors = computed(() => {
    return props.preview ? props.preview.errors.length > 0 : false;
});

const backToForm = () => {
    router.get(importMethod.url({ bank: props.bank.id }));
};

const submitImport = () => {
    form.post(importMethod.url({ bank: props.bank.id }));
};

const confirmImport = () => {
    if (confirmProcessing.value) return;
    confirmProcessing.value = true;
    router.post(
        confirm.url({ bank: props.bank.id }),
        { cache_key: props.cache_key },
        {
            preserveScroll: true,
            onFinish: () => {
                confirmProcessing.value = false;
            },
        },
    );
};

const downloadTemplate = () => {
    window.location.href = template.url({ bank: props.bank.id });
};

const getJenisLabel = (jenis: number): string => {
    const map: Record<number, string> = {
        1: 'Pilihan Ganda',
        2: 'Ganda Kompleks',
        3: 'Menjodohkan',
        4: 'Isian Singkat',
        5: 'Uraian/Esai',
    };
    return map[jenis] || 'Unknown';
};

const totalErrors = computed(() => {
    if (!props.preview) return 0;
    return props.preview.sheets.reduce(
        (sum, s) => sum + (s.errors?.length || 0),
        0,
    );
});
</script>

<template>
    <Head :title="`Import Soal - ${bank.kode}`" />

    <div
        class="min-h-screen bg-gray-100 pb-12 transition-colors duration-200 dark:bg-zinc-950"
    >
        <div
            class="sticky top-0 z-30 border-b border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div class="mx-auto max-w-[1200px] px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="backToForm"
                            class="h-9 shrink-0 border-neutral-200 px-3 text-neutral-700 dark:border-zinc-800 dark:text-zinc-300"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" /> Kembali
                        </Button>
                        <div>
                            <h1
                                class="text-xl leading-tight font-bold text-gray-900 dark:text-zinc-100"
                            >
                                Import Soal
                            </h1>
                            <p
                                class="text-sm font-medium text-gray-500 dark:text-zinc-400"
                            >
                                <span
                                    class="font-bold text-gray-700 dark:text-zinc-300"
                                    >Bank Soal:</span
                                >
                                {{ bank.kode }} <span class="mx-2">|</span>
                                {{ bank.mapel?.nama_mapel || '-' }}
                                <span class="mx-2">|</span> Opsi:
                                {{ bank.opsi }} ({{
                                    ['A', 'B', 'C', 'D', 'E']
                                        .slice(0, bank.opsi)
                                        .join(', ')
                                }})
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto mt-6 max-w-[1200px] px-4 sm:px-6 lg:px-8">
            <!-- Mode: Upload Form -->
            <div
                v-if="!preview"
                class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="mx-auto max-w-2xl text-center">
                    <FileSpreadsheet
                        class="mx-auto mb-4 h-20 w-20 text-emerald-500 dark:text-emerald-400"
                    />
                    <h2
                        class="mb-2 text-xl font-bold text-gray-800 dark:text-zinc-100"
                    >
                        Upload File Excel
                    </h2>
                    <p class="mb-6 text-gray-500 dark:text-zinc-400">
                        Unggah file Excel (.xlsx) dengan sheet:
                        <strong>PG</strong>, <strong>Kompleks</strong>,
                        <strong>Jodohkan</strong>, <strong>Isian</strong>,
                        <strong>Esai</strong>
                    </p>

                    <div
                        class="mb-6 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50/50 p-8 transition-colors hover:border-emerald-400 dark:border-zinc-700 dark:bg-zinc-900/50 dark:hover:border-emerald-600"
                    >
                        <input
                            type="file"
                            accept=".xlsx,.xls"
                            @change="
                                (e: any) =>
                                    (form.file = e.target?.files?.[0] || null)
                            "
                            class="block w-full cursor-pointer text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-6 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 dark:text-zinc-400 dark:file:bg-emerald-950/30 dark:file:text-emerald-400 dark:hover:file:bg-emerald-950/50"
                        />
                    </div>

                    <div
                        v-if="form.errors?.file"
                        class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-500 dark:border-red-900/30 dark:bg-red-950/20 dark:text-red-400"
                    >
                        {{ form.errors.file }}
                    </div>

                    <div class="flex items-center justify-center gap-4">
                        <Button
                            variant="outline"
                            class="border-gray-200 text-gray-600 dark:border-zinc-800 dark:text-zinc-400"
                            @click="downloadTemplate"
                        >
                            <Download class="mr-2 h-4 w-4" /> Download Template
                        </Button>
                        <Button
                            @click="submitImport"
                            :disabled="!form.file || form.processing"
                            class="bg-emerald-600 px-8 font-bold text-white hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600"
                        >
                            <Upload class="mr-2 h-4 w-4" /> Upload & Preview
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Mode: Preview -->
            <div v-else>
                <!-- Summary Card -->
                <div
                    class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <h2
                                class="flex items-center gap-2 text-lg font-bold text-gray-800 dark:text-zinc-100"
                            >
                                <FileSpreadsheet
                                    class="h-5 w-5 text-emerald-500"
                                />
                                Preview Import
                            </h2>
                            <p
                                class="mt-1 text-sm text-gray-500 dark:text-zinc-400"
                            >
                                {{ preview.total_rows }} baris ditemukan di
                                {{ preview.sheets.length }} sheet
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                v-if="preview.errors.length"
                                class="flex items-center gap-1.5 font-semibold text-red-600 dark:text-red-400"
                            >
                                <XCircle class="h-5 w-5" />
                                <span>{{ preview.errors.length }} error</span>
                            </div>
                            <div
                                v-else
                                class="flex items-center gap-1.5 font-semibold text-emerald-600 dark:text-emerald-400"
                            >
                                <CheckCircle2 class="h-5 w-5" />
                                <span>Valid</span>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="preview.warnings.length"
                        class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/30 dark:bg-amber-950/10"
                    >
                        <p
                            v-for="(w, i) in preview.warnings"
                            :key="i"
                            class="flex items-start gap-2 text-sm text-amber-700 dark:text-amber-400"
                        >
                            <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
                            {{ w }}
                        </p>
                    </div>
                </div>

                <!-- Sheet Tabs + Table -->
                <div
                    class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div
                        class="flex overflow-x-auto border-b border-gray-200 dark:border-zinc-800"
                    >
                        <button
                            v-for="sheet in preview.sheets"
                            :key="sheet.name"
                            @click="activeSheet = sheet.name"
                            class="border-b-2 px-5 py-3 text-sm font-semibold whitespace-nowrap transition-colors"
                            :class="
                                activeSheet === sheet.name
                                    ? 'border-blue-500 bg-blue-50/50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-zinc-400'
                            "
                        >
                            {{ sheet.name }}
                            <span
                                class="ml-1.5 rounded-full px-2 py-0.5 text-xs font-bold"
                                :class="
                                    sheet.errors?.length
                                        ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'
                                        : 'bg-gray-100 text-gray-600 dark:bg-zinc-800 dark:text-zinc-400'
                                "
                            >
                                {{ sheet.count }}
                            </span>
                        </button>
                    </div>

                    <div
                        class="overflow-x-auto"
                        v-if="activeSheetData?.rows?.length"
                    >
                        <table class="w-full text-sm">
                            <thead>
                                <tr
                                    class="border-b border-gray-200 bg-gray-50 dark:border-zinc-800 dark:bg-zinc-900/50"
                                >
                                    <th
                                        v-for="col in activeSheetColumns"
                                        :key="col.key"
                                        class="px-4 py-3 text-left text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-zinc-400"
                                    >
                                        {{ col.label }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-100 dark:divide-zinc-800"
                            >
                                <tr
                                    v-for="(row, idx) in activeSheetData.rows"
                                    :key="idx"
                                    :class="
                                        row.valid
                                            ? ''
                                            : 'bg-red-50 dark:bg-red-950/10'
                                    "
                                >
                                    <td
                                        class="px-4 py-3 font-mono text-gray-500 dark:text-zinc-400"
                                    >
                                        {{ row.nomor_soal }}
                                    </td>
                                    <td
                                        class="max-w-md truncate px-4 py-3 text-gray-700 dark:text-zinc-300"
                                        :title="row.soal"
                                    >
                                        {{ row.soal }}
                                    </td>
                                    <td
                                        class="px-4 py-3 font-mono text-gray-600 dark:text-zinc-400"
                                    >
                                        {{ row.jawaban }}
                                    </td>
                                    <td
                                        v-if="
                                            !row.valid &&
                                            activeSheetData?.errors?.length
                                        "
                                        class="max-w-xs px-4 py-3 text-xs text-red-600 dark:text-red-400"
                                    >
                                        <span class="flex items-start gap-1.5">
                                            <AlertTriangle
                                                class="mt-0.5 h-3.5 w-3.5 shrink-0"
                                            />
                                            {{ row.error_msg }}
                                        </span>
                                    </td>
                                    <td
                                        v-else-if="
                                            activeSheetData?.errors?.length
                                        "
                                        class="px-4 py-3 text-xs text-emerald-600 dark:text-emerald-400"
                                    >
                                        OK
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-else
                        class="p-12 text-center text-gray-400 dark:text-zinc-500"
                    >
                        <HelpCircle class="mx-auto mb-3 h-12 w-12 opacity-50" />
                        <p>Tidak ada data di sheet ini</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                    <Button
                        variant="outline"
                        class="border-gray-200 text-gray-600 dark:border-zinc-800 dark:text-zinc-400"
                        @click="backToForm"
                    >
                        <ArrowLeft class="mr-2 h-4 w-4" /> Upload Ulang
                    </Button>

                    <Button
                        @click="confirmImport"
                        :disabled="hasErrors || confirmProcessing"
                        class="bg-blue-600 px-8 font-bold text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                    >
                        <span v-if="confirmProcessing">Menyimpan...</span>
                        <span v-else
                            >Konfirmasi Import ({{
                                preview.total_rows
                            }}
                            soal)</span
                        >
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
