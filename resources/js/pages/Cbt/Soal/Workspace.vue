<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { index, store, update, destroy } from '@/routes/cbt/bank-soal/soal';
import RichEditor from '@/components/RichEditor.vue';
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-vue-next';
import { useConfirmStore } from '@/stores/confirm';
import { useToast } from '@/composables/useToast';

// Partials
import PgEditor from './Partials/PgEditor.vue';
import MenjodohkanEditor from './Partials/MenjodohkanEditor.vue';
import IsianEsaiEditor from './Partials/IsianEsaiEditor.vue';

const props = defineProps<{
    bank: any;
    soals: Array<any>;
    active_jenis?: number;
    active_soal_id?: number | null;
}>();

const confirmStore = useConfirmStore();
const { addToast } = useToast();

const jenisTabs = [
    { id: 1, label: 'Pilihan Ganda', key: 'pg' },
    { id: 2, label: 'Pilihan Ganda Kompleks', key: 'kompleks' },
    { id: 3, label: 'Menjodohkan', key: 'jodohkan' },
    { id: 4, label: 'Isian Singkat', key: 'isian' },
    { id: 5, label: 'Essai/Uraian', key: 'esai' },
];

// State
const activeJenis = ref<number>(props.active_jenis || 1);
const activeNomor = ref<number>(1);
const formMode = ref<'create' | 'edit'>('create');
const activeSoalId = ref<number | null>(props.active_soal_id || null);
const sessionMaxNomor = ref<number>(1);

// Helper: reset form fields based on question type without breaking data type for 'jawaban'
const resetFormByJenis = (jenis: number) => {
    form.jenis = jenis;
    form.soal = '';
    form.kesulitan = 1;
    form.timer = false;
    form.timer_menit = 0;
    form.tampilkan = true;
    form.opsi_a = '';
    form.opsi_b = '';
    form.opsi_c = '';
    form.opsi_d = '';
    form.opsi_e = '';
    if (jenis === 2 || jenis === 3) {
        form.jawaban = [];
    } else {
        form.jawaban = '';
    }
};

// Sync sessionMaxNomor on tab switch or database updates
watch(
    [activeJenis, () => props.soals],
    ([newJenis, newSoals], [oldJenis, oldSoals]) => {
        const key = jenisTabs.find((t) => t.id === newJenis)!.key;
        const tabTargetJml = props.bank[`tampil_${key}`] || 0;
        const dbMax = newSoals
            .filter((s: any) => s.jenis === newJenis)
            .reduce((acc, curr) => Math.max(acc, curr.nomor_soal), 0);

        if (newJenis !== oldJenis) {
            // Reset for the new tab
            sessionMaxNomor.value = Math.max(
                tabTargetJml,
                dbMax,
                activeNomor.value,
            );
        } else {
            // Same tab: grow only to prevent navigation shrinking
            sessionMaxNomor.value = Math.max(
                sessionMaxNomor.value,
                tabTargetJml,
                dbMax,
                activeNomor.value,
            );
        }
    },
    { immediate: true, deep: true },
);

// Ref to matching editor to call validation
const menjodohkanEditorRef = ref<InstanceType<typeof MenjodohkanEditor> | null>(
    null,
);

const currentTab = computed(
    () => jenisTabs.find((t) => t.id === activeJenis.value)!,
);

const targetJml = computed(() => {
    return props.bank[`tampil_${currentTab.value.key}`] || 0;
});

const currentSoals = computed(() => {
    return props.soals.filter((s) => s.jenis === activeJenis.value);
});

const getSoalByNomor = (nomor: number) => {
    return currentSoals.value.find((s) => s.nomor_soal === nomor);
};

// Calculate how many number circles to show
const numberCircles = computed(() => {
    let max = targetJml.value;
    const existingMax = currentSoals.value.reduce(
        (acc, curr) => Math.max(acc, curr.nomor_soal),
        0,
    );
    if (existingMax > max) max = existingMax;
    // Always show at least up to the sessionMaxNomor to prevent shrinking during unsaved additions
    if (sessionMaxNomor.value > max) max = sessionMaxNomor.value;

    // We want to return an array of objects
    return Array.from({ length: max }, (_, i) => {
        const no = i + 1;
        const exists = !!getSoalByNomor(no);
        return { no, exists };
    });
});

const form = useForm({
    jenis: 1,
    nomor_soal: 1,
    soal: '',
    opsi_a: '',
    opsi_b: '',
    opsi_c: '',
    opsi_d: '',
    opsi_e: '',
    jawaban: '' as any,
    kesulitan: 1,
    timer: false,
    timer_menit: 0,
    tampilkan: true,
});

const loadForm = (nomor: number) => {
    activeNomor.value = nomor;
    sessionMaxNomor.value = Math.max(sessionMaxNomor.value, nomor);
    const existing = getSoalByNomor(nomor);

    if (existing) {
        formMode.value = 'edit';
        activeSoalId.value = existing.id;
        resetFormByJenis(existing.jenis);
        form.jenis = existing.jenis;
        form.nomor_soal = existing.nomor_soal;
        form.soal = existing.soal || '';
        form.opsi_a = existing.opsi_a || '';
        form.opsi_b = existing.opsi_b || '';
        form.opsi_c = existing.opsi_c || '';
        form.opsi_d = existing.opsi_d || '';
        form.opsi_e = existing.opsi_e || '';

        if (existing.jenis === 2 || existing.jenis === 3) {
            form.jawaban = existing.jawaban || [];
        } else {
            form.jawaban = existing.jawaban || '';
        }
        form.kesulitan = existing.kesulitan || 1;
        form.timer = existing.timer || false;
        form.timer_menit = existing.timer_menit || 0;
        form.tampilkan = existing.tampilkan ?? true;
    } else {
        formMode.value = 'create';
        activeSoalId.value = null;
        resetFormByJenis(activeJenis.value);
        form.nomor_soal = nomor;
    }
    form.clearErrors();
};

const switchTab = (jenisId: number) => {
    activeJenis.value = jenisId;
    // Find the first empty number, or 1 if full
    let firstEmpty = 1;
    for (let i = 1; i <= targetJml.value; i++) {
        if (
            !props.soals.find((s) => s.jenis === jenisId && s.nomor_soal === i)
        ) {
            firstEmpty = i;
            break;
        }
    }
    loadForm(firstEmpty);
};

const submitForm = () => {
    if (activeJenis.value === 3) {
        const isValid = menjodohkanEditorRef.value?.validate();
        if (isValid === false) return;
    }

    if (formMode.value === 'edit' && activeSoalId.value) {
        form.put(
            update.url({ bank: props.bank.id, soal: activeSoalId.value }),
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    addToast('Soal berhasil diperbarui!', 'success');
                },
            },
        );
    } else {
        form.post(store.url({ bank: props.bank.id }), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                addToast('Soal berhasil disimpan!', 'success');
                loadForm(activeNomor.value); // Reload to get ID and switch mode to edit
            },
        });
    }
};

const deleteSoal = () => {
    if (!activeSoalId.value) return;
    confirmStore.requireConfirm({
        title: 'Hapus Soal',
        message: 'Apakah Anda yakin ingin menghapus soal ini?',
        onConfirm: () => {
            router.delete(
                destroy.url({ bank: props.bank.id, soal: activeSoalId.value! }),
                {
                    preserveScroll: true,
                    preserveState: true,
                    onSuccess: () => {
                        // Recalculate max to adjust sessionMaxNomor and activeNomor on deletion success
                        const tabTargetJml =
                            props.bank[`tampil_${currentTab.value.key}`] || 0;
                        const dbMax = props.soals
                            .filter((s: any) => s.jenis === activeJenis.value)
                            .reduce(
                                (acc, curr) => Math.max(acc, curr.nomor_soal),
                                0,
                            );
                        const newMax = Math.max(tabTargetJml, dbMax);

                        const targetNomor =
                            activeNomor.value > newMax
                                ? Math.max(1, newMax)
                                : activeNomor.value;
                        sessionMaxNomor.value = newMax; // Shrink sessionMaxNomor to allow circles list to contract
                        loadForm(targetNomor);
                    },
                },
            );
        },
    });
};

const tambahNomor = () => {
    const nextNo = numberCircles.value.length + 1;
    loadForm(nextNo);
};

const backToIndex = () => {
    router.visit(index.url({ bank: props.bank.id }));
};

// Initialization
if (props.active_soal_id) {
    const existing = props.soals.find((s) => s.id === props.active_soal_id);
    if (existing) {
        loadForm(existing.nomor_soal);
    } else {
        loadForm(1);
    }
} else {
    // If it's a new create, find next empty for the active jenis
    let firstEmpty = 1;
    for (let i = 1; i <= targetJml.value; i++) {
        if (
            !props.soals.find(
                (s) => s.jenis === activeJenis.value && s.nomor_soal === i,
            )
        ) {
            firstEmpty = i;
            break;
        }
    }
    loadForm(firstEmpty);
}
</script>

<template>
    <Head :title="`Editor Soal - ${bank.kode}`" />

    <div
        class="min-h-screen bg-gray-100 pb-12 transition-colors duration-200 dark:bg-zinc-950"
    >
        <!-- Header section -->
        <div
            class="sticky top-0 z-30 border-b border-gray-200 bg-white shadow-sm transition-colors duration-200 dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div class="mx-auto max-w-[1600px] px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="backToIndex"
                            class="h-9 shrink-0 border-neutral-200 px-3 text-neutral-700 dark:border-zinc-800 dark:text-zinc-300"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" /> Kembali
                        </Button>
                        <div>
                            <h1
                                class="text-xl leading-tight font-bold text-gray-900 dark:text-zinc-100"
                            >
                                Buat Soal
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
                                PG: {{ bank.jml_pg }}
                                <span class="mx-2">|</span> Kompleks:
                                {{ bank.jml_kompleks }}
                                <span class="mx-2">|</span> Jodohkan:
                                {{ bank.jml_jodohkan }}
                                <span class="mx-2">|</span> Isian:
                                {{ bank.jml_isian }}
                                <span class="mx-2">|</span> Esai:
                                {{ bank.jml_esai }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto mt-6 max-w-[1600px] px-4 sm:px-6 lg:px-8">
            <!-- Tab Navigation -->
            <div
                class="overflow-x-auto rounded-t-xl border border-b-0 border-gray-200 bg-white transition-colors duration-200 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="flex">
                    <button
                        v-for="tab in jenisTabs"
                        :key="tab.id"
                        @click="switchTab(tab.id)"
                        class="border-b-2 px-6 py-4 text-sm font-semibold whitespace-nowrap transition-colors"
                        :class="
                            activeJenis === tab.id
                                ? 'border-blue-500 bg-blue-50 text-blue-600 dark:bg-zinc-800/40 dark:text-blue-400'
                                : 'border-transparent text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:text-zinc-400 dark:hover:bg-zinc-800/60 dark:hover:text-zinc-200'
                        "
                    >
                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <!-- Main Editor Area -->
            <div
                class="rounded-b-xl border border-gray-200 bg-white p-6 shadow-sm transition-colors duration-200 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <!-- Alert Target -->
                <div
                    class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-emerald-800/30 dark:bg-emerald-950/20 dark:text-emerald-400"
                >
                    {{ currentTab.label }}, jumlah soal seharusnya:
                    <strong>{{ targetJml }}</strong>
                </div>

                <!-- Number Navigation -->
                <div class="mb-8 flex items-center gap-4">
                    <div
                        class="text-sm font-bold whitespace-nowrap text-gray-700 dark:text-zinc-300"
                    >
                        {{
                            currentTab.key === 'pg'
                                ? 'PG'
                                : currentTab.key.toUpperCase()
                        }}
                        NOMOR:
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-for="circle in numberCircles"
                            :key="circle.no"
                            @click="loadForm(circle.no)"
                            class="flex h-9 w-9 items-center justify-center rounded-full border-2 text-sm font-bold transition-all"
                            :class="[
                                activeNomor === circle.no
                                    ? 'border-blue-500 bg-blue-500 text-white shadow-md ring-4 ring-blue-200 dark:ring-blue-900/40' // Active
                                    : circle.exists
                                      ? 'border-green-500 bg-green-500 text-white hover:bg-green-600 dark:border-green-600 dark:bg-green-600 dark:hover:bg-green-700' // Exists
                                      : 'border-gray-300 bg-white text-gray-500 hover:border-blue-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-500', // Empty
                            ]"
                        >
                            {{ circle.no }}
                        </button>

                        <button
                            @click="tambahNomor"
                            class="ml-2 flex h-9 items-center gap-1 rounded-full border border-green-500 px-4 text-sm font-semibold text-green-600 transition-colors hover:bg-green-50 dark:border-green-600 dark:text-green-400 dark:hover:bg-green-950/20"
                        >
                            <Plus class="h-4 w-4" /> Tambah
                            {{
                                currentTab.key === 'pg'
                                    ? 'PG'
                                    : currentTab.key.toUpperCase()
                            }}
                        </button>
                    </div>
                </div>

                <!-- Editor Header -->
                <div
                    class="mb-6 flex items-center justify-between border-b border-gray-200 pb-4 dark:border-zinc-800"
                >
                    <h2
                        class="text-xl font-bold text-gray-800 dark:text-zinc-100"
                    >
                        Soal
                        {{
                            currentTab.key === 'pg'
                                ? 'PG'
                                : currentTab.key.toUpperCase()
                        }}
                        Nomor:
                        <span class="text-blue-600 dark:text-blue-400">{{
                            activeNomor
                        }}</span>
                        <span
                            v-if="formMode === 'create'"
                            class="ml-2 rounded bg-amber-50 px-2 py-1 text-sm font-normal text-amber-600 dark:bg-amber-950/30 dark:text-amber-400"
                            >(Baru)</span
                        >
                    </h2>
                    <div class="flex gap-2">
                        <Button
                            v-if="formMode === 'edit'"
                            variant="outline"
                            class="border-red-200 text-red-600 hover:bg-red-50 dark:border-red-800/30 dark:text-red-400 dark:hover:bg-red-950/20"
                            @click="deleteSoal"
                        >
                            <Trash2 class="mr-2 h-4 w-4" /> Hapus
                        </Button>
                        <Button
                            @click="submitForm"
                            :disabled="form.processing"
                            class="bg-blue-600 px-6 font-bold text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                        >
                            <Save class="mr-2 h-4 w-4" /> Simpan
                        </Button>
                    </div>
                </div>

                <!-- Split Layout Editor -->
                <div class="flex flex-col gap-8 lg:flex-row">
                    <!-- Left Panel: Main Question -->
                    <div class="flex w-full flex-col lg:w-1/2">
                        <div class="mb-2 flex items-end justify-between">
                            <Label
                                class="text-sm font-bold tracking-wider text-gray-700 uppercase dark:text-zinc-300"
                                >Soal</Label
                            >
                            <span
                                v-if="form.errors.soal"
                                class="text-xs text-red-500 dark:text-red-400"
                                >{{ form.errors.soal }}</span
                            >
                        </div>
                        <div
                            class="min-h-[300px] flex-1 overflow-hidden rounded-lg border border-gray-300 dark:border-zinc-700"
                        >
                            <RichEditor
                                v-model="form.soal"
                                :minimal="false"
                                class="h-full border-0"
                            />
                        </div>
                    </div>

                    <!-- Right Panel: Answers -->
                    <div class="flex w-full flex-col gap-6 lg:w-1/2">
                        <!-- PG/Kompleks Answers -->
                        <PgEditor
                            v-if="activeJenis === 1 || activeJenis === 2"
                            :form="form"
                            :bank="bank"
                        />

                        <!-- Menjodohkan Editor (Relational & Manual Drawing) -->
                        <MenjodohkanEditor
                            v-else-if="activeJenis === 3"
                            ref="menjodohkanEditorRef"
                            :form="form"
                            :soal="getSoalByNomor(activeNomor) || null"
                        />

                        <!-- Isian / Esai -->
                        <IsianEsaiEditor
                            v-else-if="activeJenis === 4 || activeJenis === 5"
                            :form="form"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Optional specific overrides if RichEditor needs height adjustments in split view */
:deep(.ql-container) {
    min-height: 200px;
    height: auto;
}
:deep(.ql-editor) {
    min-height: 200px;
}
</style>
