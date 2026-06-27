<template>
    <div class="flex min-h-screen flex-col bg-gray-50 dark:bg-zinc-950">
        <!-- Header Ujian -->
        <header
            class="sticky top-0 z-10 flex flex-col items-center justify-between gap-4 border-b border-gray-200 bg-white px-4 py-4 sm:flex-row sm:px-6 lg:px-8 dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div class="flex items-center gap-4">
                <div class="rounded-lg bg-blue-600 p-2">
                    <svg
                        class="h-6 w-6 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
                        ></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-zinc-100">
                        {{ jadwal?.bank_soal?.mapel?.nama_mapel || 'Ujian' }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-zinc-400">
                        {{ jadwal?.bank_soal?.nama }}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-zinc-500">
                        Soal {{ currentQuestionPosition }} / {{ totalQuestionCount }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div
                    v-show="!examStore.isOnline"
                    class="flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-sm text-red-700"
                >
                    <div
                        class="h-2 w-2 animate-pulse rounded-full bg-red-500"
                    ></div>
                    Offline (Menunggu Koneksi)
                </div>
                <div
                    v-show="examStore.hasOfflineQueue"
                    class="text-sm font-medium text-amber-600"
                >
                    Menyimpan...
                </div>

                <button
                    @click="showNavigationModal = true"
                    class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:border-gray-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700/80"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="hidden sm:inline">Navigasi</span>
                </button>

                <div
                    class="rounded-lg bg-gray-900 px-4 py-2 font-mono text-lg font-bold text-white"
                >
                    {{ remainingTimeDisplay }}
                </div>
            </div>
        </header>

        <div
            class="relative mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- Area Soal -->
            <div
                class="flex min-h-[500px] flex-1 flex-col rounded-xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    v-if="isLoading"
                    class="flex flex-1 items-center justify-center"
                >
                    <div
                        class="h-12 w-12 animate-spin rounded-full border-b-2 border-blue-600"
                    ></div>
                </div>
                <div v-else-if="activeSoal" class="flex flex-1 flex-col">
                    <div
                        class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4 dark:border-zinc-800"
                    >
                        <span class="text-lg font-bold text-gray-800 dark:text-zinc-100"
                            >Soal Nomor {{ activeSoal.no_soal_alias }}</span
                        >
                        <label
                            class="flex cursor-pointer items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 dark:border-amber-900/30 dark:bg-amber-950/20"
                        >
                            <input
                                type="checkbox"
                                v-model="isRaguRagu"
                                @change="saveAnswer"
                                class="rounded border-amber-300 text-amber-500 focus:ring-amber-500 dark:border-amber-900 dark:bg-zinc-900"
                            />
                            <span class="text-sm font-medium text-amber-700 dark:text-amber-400"
                                >Ragu-ragu</span
                            >
                        </label>
                    </div>

                    <div
                        class="prose dark:prose-invert mb-8 max-w-none flex-1 text-gray-800 dark:text-zinc-200"
                        v-html="activeSoal.soal?.soal || ''"
                    ></div>

                    <!-- Opsi Jawaban (Jika PG) -->
                    <div
                        v-if="activeSoal.jenis_soal === 1"
                        class="mt-auto space-y-3"
                    >
                        <label
                            v-for="(
                                aliasOpsi, originalOpsi
                            ) in pgOptionsMapping"
                            :key="originalOpsi"
                            class="flex cursor-pointer items-start gap-4 rounded-xl border-2 p-4 transition-all hover:bg-gray-50 dark:hover:bg-zinc-800/40"
                            :class="
                                activeSoal.jawaban_siswa === aliasOpsi
                                    ? 'border-blue-500 bg-blue-50 hover:bg-blue-50 dark:border-blue-900/30 dark:bg-blue-950/20'
                                    : 'border-gray-100 dark:border-zinc-800'
                            "
                        >
                            <div class="pt-1">
                                <input
                                    type="radio"
                                    :value="aliasOpsi"
                                    v-model="selectedAnswer"
                                    @change="saveAnswer"
                                    class="mt-0.5 h-5 w-5 border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                            </div>
                            <div class="min-w-[24px] text-lg font-medium text-gray-900 dark:text-zinc-100">
                                {{ aliasOpsi }}.
                            </div>
                            <div
                                class="prose dark:prose-invert max-w-none flex-1 text-gray-700 dark:text-zinc-300"
                                v-html="getOptionHtml(originalOpsi)"
                            ></div>
                        </label>
                    </div>

                    <!-- Opsi Jawaban (Jika PG Kompleks) -->
                    <div
                        v-else-if="activeSoal.jenis_soal === 2"
                        class="mt-auto space-y-3"
                    >
                        <label
                            v-for="(
                                aliasOpsi, originalOpsi
                            ) in pgOptionsMapping"
                            :key="originalOpsi"
                            class="flex cursor-pointer items-start gap-4 rounded-xl border-2 p-4 transition-all hover:bg-gray-50 dark:hover:bg-zinc-800/40"
                            :class="
                                selectedComplexAnswers.includes(aliasOpsi)
                                    ? 'border-blue-500 bg-blue-50 hover:bg-blue-50 dark:border-blue-900/30 dark:bg-blue-950/20'
                                    : 'border-gray-100 dark:border-zinc-800'
                            "
                        >
                            <div class="pt-1">
                                <input
                                    type="checkbox"
                                    :value="aliasOpsi"
                                    :checked="
                                        selectedComplexAnswers.includes(
                                            aliasOpsi,
                                        )
                                    "
                                    @change="
                                        toggleStudentComplexAnswer(aliasOpsi)
                                    "
                                    class="mt-0.5 h-5 w-5 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                            </div>
                            <div class="min-w-[24px] text-lg font-medium text-gray-900 dark:text-zinc-100">
                                {{ aliasOpsi }}.
                            </div>
                            <div
                                class="prose dark:prose-invert max-w-none flex-1 text-gray-700 dark:text-zinc-300"
                                v-html="getOptionHtml(originalOpsi)"
                            ></div>
                        </label>
                    </div>

                    <!-- Opsi Jawaban (Jika Menjodohkan) -->
                    <div
                        v-else-if="activeSoal.jenis_soal === 3"
                        class="mt-auto"
                    >
                        <p
                            class="mb-6 flex items-center gap-2 rounded-lg border border-blue-100 bg-blue-50 p-3 text-sm font-semibold text-blue-600 dark:border-blue-900/30 dark:bg-blue-950/20 dark:text-blue-400"
                        >
                            <span
                                class="h-2 w-2 animate-ping rounded-full bg-blue-500"
                            ></span>
                            Cara menjawab: Klik satu baris di kolom kiri, lalu
                            klik baris pasangannya di kolom kanan.
                        </p>

                        <div
                            ref="studentContainerRef"
                            class="relative grid grid-cols-12 gap-2 sm:gap-8 rounded-xl border border-gray-100 bg-gray-50/50 p-2 sm:p-4 dark:border-zinc-800 dark:bg-zinc-950/40"
                        >
                            <!-- Connecting SVG Overlay -->
                            <svg
                                class="pointer-events-none absolute inset-0 z-20 h-full w-full"
                            >
                                <defs>
                                    <marker
                                        v-for="(line, idx) in studentLines"
                                        :key="'marker-' + idx"
                                        :id="'student-arrow-' + idx"
                                        viewBox="0 0 10 10"
                                        refX="6"
                                        refY="5"
                                        markerWidth="6"
                                        markerHeight="6"
                                        orient="auto-start-reverse"
                                    >
                                        <path
                                            d="M 0 2 L 8 5 L 0 8 z"
                                            :fill="line.color"
                                        />
                                    </marker>
                                </defs>
                                <path
                                    v-for="(line, idx) in studentLines"
                                    :key="'line-' + idx"
                                    :d="line.d"
                                    fill="none"
                                    :stroke="line.color"
                                    stroke-width="2.5"
                                    :marker-end="
                                        'url(#student-arrow-' + idx + ')'
                                    "
                                    class="opacity-80"
                                />
                            </svg>

                            <!-- Left Column: Statements -->
                            <div class="z-10 col-span-5 space-y-3">
                                <div
                                    class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase"
                                >
                                    Kolom Kiri
                                </div>
                                <div
                                    v-for="(item, idx) in matchingLeftItems"
                                    :key="'left-' + item.id"
                                    @click="handleLeftItemClick(item.id)"
                                    class="relative flex min-h-[50px] cursor-pointer items-center justify-between rounded-lg border-2 bg-white p-2 sm:p-4 shadow-sm transition-all hover:shadow-md dark:bg-zinc-900"
                                    :class="[
                                        selectedLeftId === item.id
                                            ? 'border-blue-500 bg-blue-50/30 ring-2 ring-blue-100 dark:border-blue-600 dark:bg-blue-950/20 dark:ring-blue-900/40'
                                            : 'border-gray-200 dark:border-zinc-800',
                                        studentConnections[item.id] !==
                                        undefined
                                            ? 'border-emerald-200 bg-emerald-50/10 dark:border-emerald-800/40 dark:bg-emerald-950/10'
                                            : '',
                                    ]"
                                >
                                    <div class="flex items-center gap-1.5 sm:gap-3 min-w-0">
                                        <span
                                            class="flex h-5 w-5 sm:h-6 sm:w-6 shrink-0 items-center justify-center rounded border border-blue-100 bg-blue-50 text-[10px] sm:text-xs font-bold text-blue-600 dark:border-blue-900/30 dark:bg-blue-950/20 dark:text-blue-400"
                                        >
                                            {{ idx + 1 }}
                                        </span>
                                        <div
                                            class="prose dark:prose-invert max-w-none text-xs sm:text-sm leading-relaxed font-medium text-gray-700 dark:text-zinc-300"
                                            v-html="item.text"
                                        ></div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button
                                            v-if="
                                                studentConnections[item.id] !==
                                                undefined
                                            "
                                            @click.stop="
                                                removeConnection(item.id)
                                            "
                                            class="rounded p-1 text-gray-300 transition-colors hover:bg-red-50 hover:text-red-500"
                                            title="Hapus Koneksi"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                ></path>
                                            </svg>
                                        </button>
                                        <!-- Connector Dot -->
                                        <div
                                            :class="[
                                                'student-left-dot-' + item.id,
                                            ]"
                                            class="h-3.5 w-3.5 cursor-pointer rounded-full border-2 border-white bg-blue-500 shadow-md transition-transform hover:scale-110"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Middle Column: Spacing for SVG Lines -->
                            <div class="pointer-events-none col-span-2"></div>

                            <!-- Right Column: Choices -->
                            <div class="z-10 col-span-5 space-y-3">
                                <div
                                    class="mb-2 text-xs font-bold tracking-wider text-gray-400 uppercase"
                                >
                                    Kolom Kanan
                                </div>
                                 <div
                                    v-for="(item, idx) in matchingRightItems"
                                    :key="'right-' + item.id"
                                    @click="handleRightItemClick(item.id)"
                                    class="relative flex min-h-[50px] cursor-pointer items-center rounded-lg border-2 bg-white p-2 sm:p-4 shadow-sm transition-all hover:shadow-md dark:bg-zinc-900"
                                    :class="[
                                        selectedLeftId !== null
                                            ? 'border-blue-200 hover:border-blue-400 dark:border-blue-800/60 dark:hover:border-blue-600'
                                            : 'border-gray-200 dark:border-zinc-800',
                                    ]"
                                 >
                                    <!-- Connector Dot -->
                                    <div
                                        :class="[
                                            'student-right-dot-' + item.id,
                                        ]"
                                        class="absolute top-1/2 -left-[7px] h-3.5 w-3.5 -translate-y-1/2 cursor-pointer rounded-full border-2 border-white bg-emerald-500 shadow-md transition-transform hover:scale-110"
                                    ></div>
                                    <div class="flex items-center gap-1.5 sm:gap-3 pl-1 sm:pl-3 min-w-0">
                                        <span
                                            class="flex h-5 w-5 sm:h-6 sm:w-6 shrink-0 items-center justify-center rounded border border-emerald-100 bg-emerald-50 text-[10px] sm:text-xs font-bold text-emerald-600 dark:border-emerald-900/30 dark:bg-emerald-950/20 dark:text-emerald-400"
                                        >
                                            {{ String.fromCharCode(65 + idx) }}
                                        </span>
                                        <div
                                            class="prose dark:prose-invert max-w-none text-xs sm:text-sm leading-relaxed font-medium text-gray-700 dark:text-zinc-300"
                                            v-html="item.text"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="mt-8 flex gap-3 border-t border-gray-100 pt-6 dark:border-zinc-800">
                    <button
                        @click="goToPreviousQuestion"
                        :disabled="isFirstQuestion"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl border-2 border-gray-200 px-4 py-3 font-bold text-gray-700 transition-all hover:border-gray-300 hover:bg-gray-50 disabled:cursor-not-allowed disabled:border-gray-100 disabled:bg-gray-50 disabled:text-gray-400 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:disabled:border-zinc-805 dark:disabled:bg-zinc-900/45 dark:disabled:text-zinc-600"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            ></path>
                        </svg>
                        Sebelumnya
                    </button>
                    <button
                        v-if="!isLastQuestion"
                        @click="goToNextQuestion"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl border-2 border-blue-600 bg-blue-600 px-4 py-3 font-bold text-white transition-all hover:border-blue-700 hover:bg-blue-700"
                    >
                        Selanjutnya
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            ></path>
                        </svg>
                    </button>
                    <button
                        v-else
                        @click="confirmSelesai"
                        class="flex flex-1 items-center justify-center gap-2 rounded-xl border-2 border-red-600 bg-red-600 px-4 py-3 font-bold text-white transition-all hover:border-red-700 hover:bg-red-700"
                    >
                        Selesai
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            ></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigasi Soal Modal -->
    <Dialog v-model:open="showNavigationModal">
        <DialogContent class="max-h-[80vh] max-w-2xl overflow-y-auto">
            <DialogHeader>
                <DialogTitle>Navigasi Soal</DialogTitle>
                <DialogDescription>
                    <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-normal text-gray-600">
                        {{ examStore.unansweredCount }} belum dijawab
                    </span>
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-6">
                <div class="grid grid-cols-6 gap-3 sm:grid-cols-8">
                    <button
                        v-for="soal in examStore.soalList"
                        :key="soal.id"
                        @click="examStore.setActiveSoal(soal.id); showNavigationModal = false"
                        class="relative flex aspect-square items-center justify-center rounded-lg border-2 text-sm font-bold transition-colors"
                        :class="[
                            soal.id === examStore.activeSoalId
                                ? 'border-blue-600 bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400'
                                : 'border-gray-200 text-gray-600 hover:border-gray-300 dark:border-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-700',
                            soal.jawaban_siswa && !soal.ragu_ragu
                                ? 'border-blue-600 bg-blue-600 text-white dark:border-blue-600 dark:bg-blue-600'
                                : '',
                            soal.jawaban_siswa && soal.ragu_ragu
                                ? 'border-amber-400 bg-amber-400 text-white dark:border-amber-500 dark:bg-amber-500'
                                : '',
                        ]"
                    >
                        {{ soal.no_soal_alias }}
                        <div
                            v-if="soal.ragu_ragu"
                            class="absolute -top-1 -right-1 h-3 w-3 rounded-full border-2 border-white bg-amber-500"
                        ></div>
                    </button>
                </div>

                <div class="border-t border-gray-100 pt-6 dark:border-zinc-850">
                    <button
                        @click="confirmSelesai(); showNavigationModal = false"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-3 font-bold text-white shadow-sm transition-all hover:bg-red-700"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                        Selesai Ujian
                    </button>
                </div>
            </div>

            <DialogFooter>
                <DialogClose as-child>
                    <button class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700/80">
                        Tutup
                    </button>
                </DialogClose>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Confirm Selesai Dialog -->
    <Dialog v-model:open="showConfirmDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Konfirmasi Selesai Ujian</DialogTitle>
                <DialogDescription>{{ confirmMessage }}</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <DialogClose as-child>
                    <button
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700/80"
                    >
                        Batal
                    </button>
                </DialogClose>
                <button
                    v-if="unansweredNumbers.length === 0"
                    @click="handleSelesai"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                >
                    Ya, Selesaikan Ujian
                </button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { mulai, soal, selesai } from '@/routes/ujian';
import { dashboard } from '@/routes';
import { useExamStore, type SoalSiswa, type Jadwal } from '@/stores/exam';

const props = defineProps<{
    jadwal?: Jadwal;
    sisa_waktu?: number;
}>();

const examStore = useExamStore();
const isLoading = ref(true);
const selectedAnswer = ref<string | null>(null);
const selectedComplexAnswers = ref<string[]>([]);
const isRaguRagu = ref(false);
const showConfirmDialog = ref(false);
const confirmMessage = ref('');
const showNavigationModal = ref(false);

const remainingTimeDisplay = ref('00:00:00');

const activeSoal = computed<SoalSiswa | null>(() => examStore.activeSoal);

// Helper to safely parse JSON answers for Ganda Kompleks and Menjodohkan
const parseJawaban = (raw: string | null): string[] => {
    if (!raw) {
        return [];
    }

    try {
        const parsed = JSON.parse(raw);

        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
};

// Mapping original A,B,C,D,E to what user sees (alias)
const pgOptionsMapping = computed<Record<string, string>>(() => {
    if (!activeSoal.value) {
        return {};
    }

    const s = activeSoal.value;
    const map: Record<string, string> = {};
    const limit = s.opsi ?? 5;

    if (limit >= 1 && s.opsi_alias_a) {
        map['A'] = s.opsi_alias_a;
    }

    if (limit >= 2 && s.opsi_alias_b) {
        map['B'] = s.opsi_alias_b;
    }

    if (limit >= 3 && s.opsi_alias_c) {
        map['C'] = s.opsi_alias_c;
    }

    if (limit >= 4 && s.opsi_alias_d) {
        map['D'] = s.opsi_alias_d;
    }

    if (limit >= 5 && s.opsi_alias_e) {
        map['E'] = s.opsi_alias_e;
    }

    return Object.fromEntries(
        Object.entries(map).sort(([, a], [, b]) => a.localeCompare(b)),
    );
});

const currentQuestionPosition = computed(() => {
    const active = activeSoal.value;
    if (!active || examStore.soalList.length === 0) {
        return 0;
    }

    return (
        examStore.soalList.findIndex((s) => s.id === active.id) + 1
    );
});

const totalQuestionCount = computed(() => examStore.soalList.length);

const getOptionHtml = (originalOpsi: string): string => {
    if (!activeSoal.value) {
        return '';
    }

    const key = originalOpsi.toLowerCase();
    const soalData = (activeSoal.value.soal || {}) as Record<string, any>;

    return (
        soalData[`file_${key}`] ||
        soalData[`file${originalOpsi}`] ||
        soalData[`opsi_${key}`] ||
        soalData[`opsi${originalOpsi}`] ||
        ''
    );
};

// Reset local v-model whenever active soal changes
watch(activeSoal, (newSoal) => {
    if (newSoal) {
        isRaguRagu.value = newSoal.ragu_ragu;

        if (newSoal.jenis_soal === 2) {
            selectedComplexAnswers.value = parseJawaban(newSoal.jawaban_siswa ?? null);
            selectedAnswer.value =
                selectedComplexAnswers.value.length > 0
                    ? JSON.stringify(selectedComplexAnswers.value)
                    : null;
        } else {
            selectedAnswer.value = newSoal.jawaban_siswa ?? null;
            selectedComplexAnswers.value = [];
        }
    }
});

const toggleStudentComplexAnswer = (aliasOpsi: string) => {
    const current = [...selectedComplexAnswers.value];
    const idx = current.indexOf(aliasOpsi);

    if (idx > -1) {
        current.splice(idx, 1);
    } else {
        current.push(aliasOpsi);
        current.sort();
    }

    selectedComplexAnswers.value = current;
    // Serialize to JSON string for saving, or null if nothing is selected
    selectedAnswer.value = current.length > 0 ? JSON.stringify(current) : null;
    saveAnswer();
};

// Interactive matching (Menjodohkan) state & methods
const selectedLeftId = ref<number | null>(null);
const studentConnections = ref<Record<number, number>>({});
const studentContainerRef = ref<HTMLDivElement | null>(null);

interface StudentLine {
    d: string;
    color: string;
}
const studentLines = ref<StudentLine[]>([]);

const matchingLeftItems = computed<Array<{ id: number; text: string }>>(() => {
    if (!activeSoal.value || activeSoal.value.jenis_soal !== 3) {
        return [];
    }

    return activeSoal.value.soal?.matching_left || [];
});

const matchingRightItems = computed<Array<{ id: number; text: string }>>(() => {
    if (!activeSoal.value || activeSoal.value.jenis_soal !== 3) {
        return [];
    }

    return activeSoal.value.soal?.matching_right || [];
});

watch(
    () => activeSoal.value?.id,
    () => {
        selectedLeftId.value = null;
        studentConnections.value = {};
        studentLines.value = [];

        if (
            activeSoal.value &&
            activeSoal.value.jenis_soal === 3 &&
            activeSoal.value.jawaban_siswa
        ) {
            try {
                const savedAns = JSON.parse(activeSoal.value.jawaban_siswa);

                if (Array.isArray(savedAns)) {
                    savedAns.forEach((conn) => {
                        const kiriId = conn.kiri_id;
                        const kananId = conn.kanan_id;

                        if (kiriId !== undefined && kananId !== undefined) {
                            studentConnections.value[kiriId] = kananId;
                        }
                    });
                }
            } catch (error) {
                console.error('Gagal memuat jawaban menjodohkan', error);
            }
        }
    },
    { immediate: true },
);

const updateStudentLines = () => {
    if (!studentContainerRef.value) {
        return;
    }

    const containerRect = studentContainerRef.value.getBoundingClientRect();
    const tempLines: StudentLine[] = [];

    const colors = [
        '#3B82F6', // Blue
        '#10B981', // Emerald
        '#8B5CF6', // Violet
        '#F59E0B', // Amber
        '#EC4899', // Pink
        '#06B6D4', // Cyan
        '#EF4444', // Red
    ];

    Object.entries(studentConnections.value).forEach(
        ([leftIdxStr, rightIdx]) => {
            const leftIdx = parseInt(leftIdxStr);
            const leftEl = studentContainerRef.value?.querySelector(
                `.student-left-dot-${leftIdx}`,
            );
            const rightEl = studentContainerRef.value?.querySelector(
                `.student-right-dot-${rightIdx}`,
            );

            if (leftEl && rightEl) {
                const leftRect = leftEl.getBoundingClientRect();
                const rightRect = rightEl.getBoundingClientRect();

                const x1 =
                    leftRect.left + leftRect.width / 2 - containerRect.left;
                const y1 =
                    leftRect.top + leftRect.height / 2 - containerRect.top;

                const x2 =
                    rightRect.left + rightRect.width / 2 - containerRect.left;
                const y2 =
                    rightRect.top + rightRect.height / 2 - containerRect.top;

                const controlX = x1 + (x2 - x1) / 2;
                const d = `M ${x1} ${y1} C ${controlX} ${y1}, ${controlX} ${y2}, ${x2} ${y2}`;
                const color = colors[leftIdx % colors.length];

                tempLines.push({ d, color });
            }
        },
    );

    studentLines.value = tempLines;
};

// Update lines when connections change
watch(
    studentConnections,
    () => {
        nextTick(() => {
            setTimeout(updateStudentLines, 50);
        });
    },
    { deep: true },
);

// Mutation observer for student container to handle size updates
let studentObserver: MutationObserver | null = null;
watch(studentContainerRef, (newRef) => {
    if (newRef) {
        if (studentObserver) {
            studentObserver.disconnect();
        }

        studentObserver = new MutationObserver(updateStudentLines);
        studentObserver.observe(newRef, {
            childList: true,
            subtree: true,
            attributes: true,
        });
        setTimeout(updateStudentLines, 200);
    } else {
        if (studentObserver) {
            studentObserver.disconnect();
            studentObserver = null;
        }
    }
});

const handleLeftItemClick = (leftId: number) => {
    if (selectedLeftId.value === leftId) {
        selectedLeftId.value = null;
    } else {
        selectedLeftId.value = leftId;
    }
};

const handleRightItemClick = (rightId: number) => {
    if (selectedLeftId.value === null) {
        return;
    }

    const leftId = selectedLeftId.value;
    studentConnections.value[leftId] = rightId;
    selectedLeftId.value = null;

    saveMatchingAnswer();
};

const removeConnection = (leftId: number) => {
    delete studentConnections.value[leftId];
    saveMatchingAnswer();
};

const saveMatchingAnswer = () => {
    interface MatchingConnection {
        kiri_id: number;
        kanan_id: number;
    }
    const connectionsArray: MatchingConnection[] = [];
    Object.entries(studentConnections.value).forEach(([kiriId, kananId]) => {
        connectionsArray.push({
            kiri_id: parseInt(kiriId),
            kanan_id: kananId,
        });
    });

    selectedAnswer.value = JSON.stringify(connectionsArray);
    saveAnswer();
};

const enterFullscreen = () => {
    const elem = document.documentElement;
    if (elem.requestFullscreen) {
        elem.requestFullscreen().catch(err => {
            console.warn(`Error attempting to enable fullscreen: ${err.message}`);
        });
    }
};

onMounted(async () => {
    enterFullscreen();

    if (!props.jadwal) {
        console.error('Jadwal is undefined');
        return;
    }
    examStore.setJadwal(props.jadwal);
    examStore.initNetworkListeners();

    window.addEventListener('resize', updateStudentLines);
    window.addEventListener('scroll', updateStudentLines, true);

    try {
        // 1. Mulai ujian (Backend generate soal)
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';

        await fetch(mulai({ jadwal: props.jadwal.id }).url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
        });

        // 2. Fetch list soal
        const response = await fetch(soal({ jadwal: props.jadwal.id }).url, {
            headers: { Accept: 'application/json' },
        });

        const data = await response.json();
        examStore.setSoalList(data.data);
        isLoading.value = false;

        // Start timer using remaining seconds from server
        startTimer(Number(props.sisa_waktu || 0));
    } catch (error) {
        console.error('Gagal memuat ujian', error);
        alert('Gagal memuat soal ujian. Harap periksa koneksi internet.');
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', updateStudentLines);
    window.removeEventListener('scroll', updateStudentLines, true);
});

const saveAnswer = () => {
    if (!activeSoal.value) {
        return;
    }

    examStore.simpanJawaban(
        activeSoal.value.id,
        selectedAnswer.value,
        isRaguRagu.value,
    );
};

const unansweredNumbers = ref<string[]>([]);
const confirmSelesai = () => {
    unansweredNumbers.value = examStore.soalList
        .filter(s => !s.jawaban_siswa)
        .map(s => String(s.no_soal_alias));
        
    if (unansweredNumbers.value.length > 0) {
        confirmMessage.value = `Masih ada ${unansweredNumbers.value.length} soal yang belum dijawab, yaitu nomor: ${unansweredNumbers.value.join(', ')}. Anda tidak bisa menyelesaikan ujian sebelum semua soal dijawab.`;
    } else {
        confirmMessage.value = 'Apakah Anda yakin ingin menyelesaikan ujian ini?';
    }
    showConfirmDialog.value = true;
};

const goToPreviousQuestion = () => {
    const active = activeSoal.value;
    if (!active || examStore.soalList.length === 0) {
        return;
    }

    const currentIndex = examStore.soalList.findIndex(
        (s) => s.id === active.id,
    );
    if (currentIndex > 0) {
        examStore.setActiveSoal(examStore.soalList[currentIndex - 1].id);
    }
};

const goToNextQuestion = () => {
    const active = activeSoal.value;
    if (!active || examStore.soalList.length === 0) {
        return;
    }

    const currentIndex = examStore.soalList.findIndex(
        (s) => s.id === active.id,
    );
    if (currentIndex < examStore.soalList.length - 1) {
        examStore.setActiveSoal(examStore.soalList[currentIndex + 1].id);
    }
};

const isFirstQuestion = computed(() => {
    const active = activeSoal.value;
    if (!active || examStore.soalList.length === 0) {
        return true;
    }

    return examStore.soalList[0].id === active.id;
});

const isLastQuestion = computed(() => {
    const active = activeSoal.value;
    if (!active || examStore.soalList.length === 0) {
        return true;
    }

    return (
        examStore.soalList[examStore.soalList.length - 1].id ===
        active.id
    );
});

const handleSelesai = async () => {
    if (!props.jadwal) {
        return;
    }
    showConfirmDialog.value = false;


    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';
        const response = await fetch(selesai({ jadwal: props.jadwal.id }).url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
        });

        if (response.ok) {
            router.visit(dashboard().url);
        } else {
            const data = await response.json();
            alert(data.message || 'Gagal mengakhiri ujian.');
        }
    } catch {
        alert('Gagal mengakhiri ujian. Periksa koneksi internet Anda.');
    }
};

const startTimer = (durationSeconds: number) => {
    if (!Number.isFinite(durationSeconds) || durationSeconds <= 0) {
        remainingTimeDisplay.value = '00:00:00';
        return;
    }

    let timer = Math.floor(durationSeconds);
    setInterval(() => {
        const hVal = Math.floor(timer / 3600);
        const mVal = Math.floor((timer % 3600) / 60);
        const sVal = Math.floor(timer % 60);

        const h = hVal < 10 ? '0' + hVal : String(hVal);
        const m = mVal < 10 ? '0' + mVal : String(mVal);
        const s = sVal < 10 ? '0' + sVal : String(sVal);

        remainingTimeDisplay.value = `${h}:${m}:${s}`;

        if (--timer < 0) {
            timer = 0;
            // TODO: Auto submit
        }
    }, 1000);
};
</script>
