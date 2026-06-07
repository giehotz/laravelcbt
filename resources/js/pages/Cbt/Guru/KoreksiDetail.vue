<template>
    <div class="flex min-h-screen flex-col bg-gray-50 dark:bg-zinc-950 text-foreground">
        <!-- Header -->
        <header
            class="border-b border-gray-200 bg-white px-4 py-4 sm:px-6 lg:px-8 dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="koreksiIndex().url"
                        class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-indigo-50 hover:text-indigo-600 dark:text-zinc-400 dark:hover:bg-indigo-950/40 dark:hover:text-indigo-400"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"
                            ></path>
                        </svg>
                    </Link>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-zinc-100">
                            Koreksi Essai: {{ siswa.nama }}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-zinc-400">
                            {{ siswa.nis }} | {{ jadwal.bank_soal?.nama }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-sm font-medium">
                    <div
                        class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-2 text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-400"
                    >
                        Nilai PG:
                        <span class="text-lg font-bold">{{
                            nilai?.pg_nilai || 0
                        }}</span>
                    </div>
                    <div
                        class="rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-2 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-400"
                    >
                        Nilai Essai:
                        <span class="text-lg font-bold">{{
                            nilai?.esai_nilai || 0
                        }}</span>
                    </div>
                </div>
            </div>
        </header>

        <main
            class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <form @submit.prevent="submitKoreksi">
                <div
                    v-if="!form.koreksi.length"
                    class="rounded-xl border border-gray-100 bg-white p-8 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <p class="text-gray-500 dark:text-zinc-400">
                        Tidak ada soal essai untuk jadwal ujian ini.
                    </p>
                </div>

                <div
                    v-for="(item, index) in form.koreksi"
                    :key="item.id"
                    class="mb-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div
                        class="mb-4 flex items-start justify-between border-b border-gray-100 pb-4 dark:border-zinc-800"
                    >
                        <h3 class="text-lg font-bold text-gray-800 dark:text-zinc-100">
                            Soal {{ item.no_soal_alias }}
                        </h3>

                        <!-- Input Nilai Essai -->
                        <div
                            class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-800 dark:bg-zinc-950/40"
                        >
                            <label
                                class="text-sm font-semibold whitespace-nowrap text-gray-700 dark:text-zinc-300"
                                >Point (0-100)</label
                            >
                            <input
                                type="number"
                                v-model="item.point_esai"
                                min="0"
                                max="100"
                                required
                                class="w-24 rounded-md border-gray-300 text-right text-lg font-bold text-indigo-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-indigo-400"
                            />
                        </div>
                    </div>

                    <!-- Pertanyaan -->
                    <div
                        class="prose dark:prose-invert mb-6 max-w-none rounded-lg border border-blue-100 bg-blue-50 p-4 text-gray-800 dark:border-blue-900/40 dark:bg-blue-950/20 dark:text-zinc-200"
                    >
                        <div
                            class="mb-2 text-xs font-bold text-blue-600 uppercase dark:text-blue-400"
                        >
                            Pertanyaan
                        </div>
                        <div v-html="item.soal_html"></div>
                    </div>

                    <!-- Jawaban Siswa -->
                    <div class="prose dark:prose-invert mb-6 max-w-none">
                        <div
                            class="mb-2 text-xs font-bold text-gray-500 uppercase dark:text-zinc-400"
                        >
                            Jawaban Siswa
                        </div>
                        <div
                            v-if="item.jawaban_siswa"
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-gray-800 dark:border-zinc-800 dark:bg-zinc-950/40 dark:text-zinc-200"
                            v-html="item.jawaban_siswa"
                        ></div>
                        <div
                            v-else
                            class="rounded-lg border border-red-100 bg-red-50 p-4 text-red-600 italic dark:border-rose-950/40 dark:bg-rose-950/20 dark:text-rose-450"
                        >
                            Siswa tidak menjawab soal ini.
                        </div>
                    </div>

                    <!-- Kunci Jawaban (Referensi Guru) -->
                    <div class="prose dark:prose-invert max-w-none">
                        <div
                            class="mb-2 text-xs font-bold text-emerald-600 uppercase dark:text-emerald-400"
                        >
                            Referensi Jawaban (Kunci)
                        </div>
                        <div
                            class="rounded-lg border border-emerald-100 bg-emerald-50 p-4 text-emerald-900 dark:border-emerald-950/40 dark:bg-emerald-950/20 dark:text-emerald-300"
                            v-html="item.jawaban_benar || '-'"
                        ></div>
                    </div>
                </div>

                <div
                    v-if="form.koreksi.length"
                    class="sticky bottom-6 z-10 flex items-center justify-between rounded-xl border border-gray-200 bg-white p-4 shadow-lg dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <div class="text-sm text-gray-500 dark:text-zinc-400">
                        Total Point Diberikan:
                        <span class="font-bold text-gray-900 dark:text-zinc-150">{{
                            totalPoint
                        }}</span>
                        / {{ form.koreksi.length * 100 }}
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2 rounded-xl bg-indigo-600 px-8 py-3 font-bold text-white shadow-sm transition-colors hover:bg-indigo-700 cursor-pointer"
                    >
                        <svg
                            v-if="form.processing"
                            class="h-5 w-5 animate-spin text-white"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        Simpan Nilai Essai
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { index as koreksiIndex, simpanKoreksi } from '@/routes/cbt/koreksi';

const props = defineProps({
    jadwal: Object,
    siswa: Object,
    soalSiswas: Array,
    nilai: Object,
});

const form = useForm({
    koreksi: props.soalSiswas.map((ss) => ({
        id: ss.id,
        no_soal_alias: ss.no_soal_alias,
        soal_html: ss.soal.soal,
        jawaban_siswa: ss.jawaban_siswa,
        jawaban_benar: ss.soal.jawaban, // Kunci referensi guru
        point_esai: ss.point_esai || 0,
    })),
});

const totalPoint = computed(() => {
    return form.koreksi.reduce(
        (acc, curr) => acc + Number(curr.point_esai || 0),
        0,
    );
});

const submitKoreksi = () => {
    form.post(
        simpanKoreksi({ jadwal: props.jadwal.id, siswa: props.siswa.id }).url,
        {
            preserveScroll: true,
            onSuccess: () => {
                alert('Nilai essai berhasil disimpan!');
            },
        },
    );
};
</script>
