<script setup lang="ts">
import { Head, useForm, router, usePage, Link } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    ArrowLeft,
    Search,
    Plus,
    Trash2,
    Save,
    GraduationCap,
    Download,
    Upload,
    FileSpreadsheet,
    AlertTriangle,
    CheckCircle2,
    Loader2,
    X,
} from 'lucide-vue-next';

import students from '@/routes/master/kelas/students';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Data Master',
                href: '#',
            },
            {
                title: 'Rombel & Kelas',
                href: '/master/kelas',
            },
            {
                title: 'Kelola Anggota Kelas',
                href: '#',
            },
        ],
    },
});

const props = defineProps<{
    kelas: {
        id: number;
        nama_kelas: string;
        kode_kelas: string;
        tahun_pelajaran_id: number;
        semester_id: number;
        level_kelas?: { level: string };
        jurusan?: { nama_jurusan: string; kode_jurusan: string };
        wali_kelas?: { nama_guru: string };
        tahun_pelajaran?: { tahun: string };
        semester?: { nama_smt: string };
    };
    assigned: Array<{
        id: number;
        nama: string;
        nis: string;
        nisn: string;
    }>;
    unassigned: {
        data: Array<{
            id: number;
            nama: string;
            nis: string;
            nisn: string;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    filters: {
        q: string;
    };
    imported_users?: Array<any> | null;
}>();

const localAssigned = ref([...props.assigned]);
const localUnassignedData = ref([...props.unassigned.data]);
const searchInput = ref(props.filters.q ?? '');

const isImportModalOpen = ref(false);
const importFile = ref<File | null>(null);
const importProcessing = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);

const page = usePage();

const importErrors = computed(() => {
    const errors = page.props.errors as Record<string, any>;
    if (errors?.import_errors) {
        return Array.isArray(errors.import_errors)
            ? errors.import_errors
            : [errors.import_errors];
    }
    return [];
});

const fileError = computed(() => {
    const errors = page.props.errors as Record<string, any>;
    return errors?.file || '';
});

// Track search input and reload unassigned list with debounce or search click
const handleSearch = () => {
    router.get(
        window.location.pathname,
        { q: searchInput.value },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['unassigned', 'filters'],
            onSuccess: () => {
                // Filter out students that are already in localAssigned from the new unassigned list
                const assignedIds = localAssigned.value.map((s) => s.id);
                localUnassignedData.value = props.unassigned.data.filter(
                    (s) => !assignedIds.includes(s.id),
                );
            },
        },
    );
};

// Sync local data if props change (e.g. on pagination)
watch(
    () => props.unassigned.data,
    (newData) => {
        const assignedIds = localAssigned.value.map((s) => s.id);
        localUnassignedData.value = newData.filter(
            (s) => !assignedIds.includes(s.id),
        );
    },
    { deep: true },
);

// Sync assigned if prop updates (e.g. on import success)
watch(
    () => props.assigned,
    (newAssigned) => {
        localAssigned.value = [...newAssigned];
    },
    { deep: true },
);

const openImportModal = () => {
    importFile.value = null;
    isImportModalOpen.value = true;
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        importFile.value = target.files[0];
    }
};

const submitImport = () => {
    if (!importFile.value) return;

    const formData = new FormData();
    formData.append('file', importFile.value);

    importProcessing.value = true;

    router.post(students.import.url(props.kelas.id), formData, {
        forceFormData: true,
        onSuccess: () => {
            isImportModalOpen.value = false;
            importFile.value = null;
            importProcessing.value = false;
            localAssigned.value = [...props.assigned];
        },
        onError: () => {
            importProcessing.value = false;
        },
    });
};

const downloadTemplate = () => {
    window.location.href = students.template.url(props.kelas.id);
};



const addStudent = (student: (typeof props.assigned)[0]) => {
    // Add to assigned
    if (!localAssigned.value.some((s) => s.id === student.id)) {
        localAssigned.value.push(student);
    }
    // Remove from unassigned list
    localUnassignedData.value = localUnassignedData.value.filter(
        (s) => s.id !== student.id,
    );
};

const removeStudent = (student: (typeof props.assigned)[0]) => {
    // Remove from assigned
    localAssigned.value = localAssigned.value.filter(
        (s) => s.id !== student.id,
    );
    // Put back to unassigned if it matches search
    const matchesSearch =
        !searchInput.value ||
        student.nama.toLowerCase().includes(searchInput.value.toLowerCase()) ||
        student.nis.includes(searchInput.value) ||
        student.nisn.includes(searchInput.value);

    if (
        matchesSearch &&
        !localUnassignedData.value.some((s) => s.id === student.id)
    ) {
        localUnassignedData.value.unshift(student);
    }
};

const submitAssignment = () => {
    const form = useForm({
        siswa_ids: localAssigned.value.map((s) => s.id),
    });

    form.put(students.update.url(props.kelas.id), {
        onSuccess: () => {
            // Success redirect is handled by controller
        },
    });
};
</script>

<template>
    <Head :title="'Kelola Siswa - ' + kelas.nama_kelas" />

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex items-center gap-3">
                <Link
                    href="/master/kelas"
                    class="rounded-lg border border-neutral-200 p-2 text-neutral-500 transition-colors hover:bg-neutral-50 dark:border-zinc-800 dark:text-neutral-400 dark:hover:bg-zinc-800"
                >
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <Heading
                    :title="'Kelola Roster Siswa — ' + kelas.nama_kelas"
                    :description="
                        'Atur siswa terdaftar pada kelas tingkat ' +
                        (kelas.level_kelas?.level ?? '-') +
                        ' (' +
                        (kelas.jurusan?.nama_jurusan ?? 'Umum') +
                        ') untuk tahun pelajaran ' +
                        (kelas.tahun_pelajaran?.tahun ?? '-') +
                        ' / ' +
                        (kelas.semester?.nama_smt ?? '-')
                    "
                />
            </div>
            <div class="flex flex-shrink-0 items-center gap-2">
                <Button
                    variant="outline"
                    @click="downloadTemplate"
                    class="flex items-center gap-1.5 text-sm"
                >
                    <Download class="h-4 w-4" />
                    <span class="hidden sm:inline">Template</span>
                </Button>
                <Button
                    variant="outline"
                    @click="openImportModal"
                    class="flex items-center gap-1.5 border-emerald-300 text-sm text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-400 dark:hover:bg-emerald-950/30"
                >
                    <Upload class="h-4 w-4" />
                    <span class="hidden sm:inline">Import Excel</span>
                </Button>
            </div>
        </div>

        <!-- Import Success Banner -->
        <div
            v-if="imported_users && imported_users.length > 0"
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950/30"
        >
            <div class="flex items-start gap-3">
                <CheckCircle2
                    class="mt-0.5 h-5 w-5 flex-shrink-0 text-emerald-600 dark:text-emerald-400"
                />
                <div class="flex-1">
                    <h4
                        class="text-sm font-semibold text-emerald-800 dark:text-emerald-200"
                    >
                        Import Berhasil
                    </h4>
                    <p
                        class="mt-1 text-xs text-emerald-700 dark:text-emerald-300"
                    >
                        {{ imported_users.length }} akun siswa berhasil diimpor dan dimasukkan ke kelas ini.
                        Password default untuk semua akun baru adalah
                        <code
                            class="rounded bg-emerald-100 px-1.5 py-0.5 font-mono text-xs dark:bg-emerald-900/50"
                            >password</code
                        >.
                    </p>
                    <div
                        class="mt-2 flex items-center gap-1.5 text-amber-600 dark:text-amber-400"
                    >
                        <AlertTriangle class="h-3.5 w-3.5" />
                        <span class="text-[11px] font-medium"
                            >Segera minta setiap siswa untuk mengubah password mereka setelah login pertama.</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Errors Banner -->
        <div
            v-if="importErrors.length > 0"
            class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-800 dark:bg-rose-950/30"
        >
            <div class="flex items-start gap-3">
                <AlertTriangle
                    class="mt-0.5 h-5 w-5 flex-shrink-0 text-rose-600 dark:text-rose-400"
                />
                <div class="flex-1">
                    <h4
                        class="text-sm font-semibold text-rose-800 dark:text-rose-200"
                    >
                        Gagal Import — {{ importErrors.length }} Error Ditemukan
                    </h4>
                    <p class="mt-1 text-xs text-rose-700 dark:text-rose-300">
                        Tidak ada data yang diimpor. Perbaiki kesalahan berikut, lalu upload ulang file Anda.
                    </p>
                    <div
                        class="mt-3 max-h-40 overflow-y-auto rounded-lg border border-rose-200 bg-rose-100/50 dark:border-rose-800 dark:bg-rose-950/50"
                    >
                        <ul
                            class="divide-y divide-rose-200/50 dark:divide-rose-800/50"
                        >
                            <li
                                v-for="(error, idx) in importErrors"
                                :key="idx"
                                class="px-3 py-2 font-mono text-xs text-rose-700 dark:text-rose-300"
                            >
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- File Error Banner -->
        <div
            v-if="fileError"
            class="rounded-xl border border-rose-200 bg-rose-50 p-4 dark:border-rose-800 dark:bg-rose-950/30"
        >
            <div class="flex items-start gap-3">
                <AlertTriangle
                    class="mt-0.5 h-5 w-5 flex-shrink-0 text-rose-600 dark:text-rose-400"
                />
                <div class="flex-1">
                    <h4
                        class="text-sm font-semibold text-rose-800 dark:text-rose-200"
                    >
                        Error Upload
                    </h4>
                    <p class="mt-1 text-xs text-rose-700 dark:text-rose-300">
                        {{ fileError }}
                    </p>
                </div>
            </div>
        </div>


        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            <!-- Left Pane: Unassigned Students (takes 7 cols) -->
            <div
                class="flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition-all lg:col-span-7 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="border-b border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
                >
                    <div
                        class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-2">
                            <GraduationCap class="h-5 w-5 text-zinc-500" />
                            <h3
                                class="text-sm font-bold tracking-wider text-neutral-800 uppercase dark:text-neutral-200"
                            >
                                Siswa Belum Punya Kelas
                            </h3>
                        </div>
                        <div class="relative w-full max-w-xs">
                            <Input
                                v-model="searchInput"
                                @keyup.enter="handleSearch"
                                placeholder="Cari nama, NIS..."
                                class="h-8 pl-8 text-xs"
                            />
                            <Search
                                class="absolute top-2.5 left-2.5 h-3.5 w-3.5 text-neutral-400"
                            />
                        </div>
                    </div>
                </div>

                <div class="min-h-[300px] overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-neutral-200 bg-neutral-50/30 text-[11px] font-bold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <th class="w-14 px-4 py-3 text-center">No</th>
                                <th class="w-32 px-4 py-3">NIS / NISN</th>
                                <th class="px-4 py-3">Nama Lengkap</th>
                                <th class="w-20 px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                        >
                            <tr
                                v-for="(student, idx) in localUnassignedData"
                                :key="student.id"
                                class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20"
                            >
                                <td
                                    class="px-4 py-3 text-center font-medium text-neutral-400 dark:text-neutral-500"
                                >
                                    {{ idx + 1 }}
                                </td>
                                <td
                                    class="px-4 py-3 font-mono text-xs text-neutral-500 dark:text-neutral-400"
                                >
                                    <div>{{ student.nis }}</div>
                                    <div class="text-[10px] text-neutral-400">
                                        {{ student.nisn }}
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 font-semibold text-neutral-800 dark:text-neutral-200"
                                >
                                    {{ student.nama }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Button
                                        size="sm"
                                        @click="addStudent(student)"
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 p-0 font-bold text-white shadow hover:bg-blue-700"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="localUnassignedData.length === 0">
                                <td
                                    colspan="4"
                                    class="px-6 py-12 text-center text-neutral-400"
                                >
                                    Tidak ada siswa yang belum terdaftar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination for Unassigned list -->
                <div
                    v-if="unassigned.links && unassigned.links.length > 3"
                    class="flex items-center justify-between border-t border-neutral-200 bg-neutral-50/20 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/20"
                >
                    <div class="flex items-center gap-1">
                        <template
                            v-for="link in unassigned.links"
                            :key="link.label"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="rounded border px-2.5 py-1 text-[10px] font-bold transition-all"
                                :class="
                                    link.active
                                        ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-50 dark:bg-zinc-50 dark:text-zinc-950'
                                        : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800'
                                "
                                v-html="link.label"
                                preserve-state
                                preserve-scroll
                                :only="['unassigned']"
                            />
                            <span
                                v-else
                                class="dark:text-zinc-655 cursor-not-allowed rounded border border-neutral-100 px-2.5 py-1 text-[10px] font-bold text-neutral-300 select-none dark:border-zinc-800/45"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right Pane: Currently Assigned (takes 5 cols) -->
            <div
                class="flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition-all lg:col-span-5 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
                >
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-5 w-5 text-emerald-600" />
                        <h3
                            class="text-sm font-bold tracking-wider text-neutral-800 uppercase dark:text-neutral-200"
                        >
                            Anggota Kelas ({{ localAssigned.length }})
                        </h3>
                    </div>
                    <Button
                        @click="submitAssignment"
                        size="sm"
                        class="flex items-center gap-1 bg-emerald-600 font-bold text-white shadow-sm transition-colors hover:bg-emerald-700"
                    >
                        <Save class="h-4 w-4" />
                        <span>Simpan</span>
                    </Button>
                </div>

                <div
                    class="max-h-[500px] min-h-[300px] divide-y divide-neutral-200 overflow-y-auto dark:divide-zinc-800"
                >
                    <div
                        v-for="student in localAssigned"
                        :key="student.id"
                        class="dark:hover:bg-zinc-850/20 flex items-center justify-between px-6 py-3 transition-colors hover:bg-neutral-50/30"
                    >
                        <div class="space-y-0.5">
                            <div
                                class="text-sm font-bold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ student.nama }}
                            </div>
                            <div class="font-mono text-xs text-neutral-400">
                                NIS: {{ student.nis }}
                            </div>
                        </div>
                        <Button
                            size="sm"
                            variant="ghost"
                            @click="removeStudent(student)"
                            class="h-8 w-8 rounded-full p-0 text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-950/20"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                    <div
                        v-if="localAssigned.length === 0"
                        class="px-6 py-12 text-center text-neutral-400"
                    >
                        Belum ada siswa di kelas ini.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div
        v-if="isImportModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="w-full max-w-lg overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
            >
                <div class="flex items-center gap-2">
                    <FileSpreadsheet
                        class="h-5 w-5 text-emerald-600 dark:text-emerald-400"
                    />
                    <h3
                        class="font-bold text-neutral-800 dark:text-neutral-200"
                    >
                        Import Data Siswa ke Kelas
                    </h3>
                </div>
                <button
                    @click="isImportModalOpen = false"
                    class="cursor-pointer text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="space-y-5 p-6">
                <!-- Instructions -->
                <div
                    class="rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-950/30"
                >
                    <h4
                        class="mb-2 text-xs font-semibold text-blue-800 dark:text-blue-200"
                    >
                        Panduan Import:
                    </h4>
                    <ol
                        class="list-inside list-decimal space-y-1 text-[11px] text-blue-700 dark:text-blue-300"
                    >
                        <li>
                            Unduh template Excel terlebih dahulu menggunakan tombol di bawah.
                        </li>
                        <li>
                            Isi data siswa sesuai format kolom (kolom bertanda <strong>*</strong> wajib diisi).
                        </li>
                        <li>
                            Siswa yang diimpor akan langsung dimasukkan ke kelas <strong>{{ kelas.nama_kelas }}</strong>.
                        </li>
                        <li>
                            Maksimal <strong>1.000 baris</strong> data per file (ukuran maks <strong>2 MB</strong>).
                        </li>
                        <li>
                            Upload file yang sudah dilengkapi lalu klik <strong>Mulai Import</strong>.
                        </li>
                    </ol>
                </div>

                <!-- Download Template -->
                <button
                    @click="downloadTemplate"
                    type="button"
                    class="group flex w-full cursor-pointer items-center gap-3 rounded-lg border-2 border-dashed border-neutral-300 p-3 transition-all hover:border-emerald-400 hover:bg-emerald-50/50 dark:border-zinc-700 dark:hover:border-emerald-600 dark:hover:bg-emerald-950/20"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 transition-colors group-hover:bg-emerald-200 dark:bg-emerald-900/40 dark:group-hover:bg-emerald-800/60"
                    >
                        <Download
                            class="h-5 w-5 text-emerald-600 dark:text-emerald-400"
                        />
                    </div>
                    <div class="text-left">
                        <p
                            class="text-sm font-semibold text-neutral-800 dark:text-neutral-200"
                        >
                            Unduh Template Excel Kelas
                        </p>
                        <p class="text-[11px] text-neutral-500">
                            template_siswa_kelas_{{ kelas.nama_kelas }}.xlsx
                        </p>
                    </div>
                </button>

                <!-- File Upload -->
                <div class="space-y-2">
                    <Label>Pilih File Excel (.xlsx / .xls)</Label>
                    <div
                        class="relative rounded-lg border-2 border-dashed p-6 text-center transition-all"
                        :class="
                            importFile
                                ? 'border-emerald-400 bg-emerald-50/50 dark:border-emerald-600 dark:bg-emerald-950/20'
                                : 'border-neutral-300 hover:border-neutral-400 dark:border-zinc-700 dark:hover:border-zinc-600'
                        "
                    >
                        <input
                            ref="fileInputRef"
                            type="file"
                            accept=".xlsx,.xls"
                            @change="handleFileChange"
                            class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                        />
                        <div v-if="!importFile" class="space-y-2">
                            <Upload
                                class="mx-auto h-8 w-8 text-neutral-400"
                            />
                            <p
                                class="text-sm text-neutral-600 dark:text-neutral-400"
                            >
                                Klik atau seret file ke sini
                            </p>
                            <p class="text-[11px] text-neutral-400">
                                Format: .xlsx atau .xls (Maks. 2MB)
                            </p>
                        </div>
                        <div
                            v-else
                            class="flex items-center justify-center gap-2"
                        >
                            <FileSpreadsheet
                                class="h-5 w-5 text-emerald-600 dark:text-emerald-400"
                            />
                            <span
                                class="text-sm font-medium text-emerald-700 dark:text-emerald-300"
                                >{{ importFile.name }}</span
                            >
                            <span class="text-[11px] text-neutral-400"
                                >({{ (importFile.size / 1024).toFixed(1) }} KB)</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Default Password Warning -->
                <div
                    class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-950/30"
                >
                    <AlertTriangle
                        class="mt-0.5 h-4 w-4 flex-shrink-0 text-amber-600 dark:text-amber-400"
                    />
                    <div>
                        <p
                            class="text-xs font-semibold text-amber-800 dark:text-amber-200"
                        >
                            Perhatian: Password Default
                        </p>
                        <p
                            class="mt-0.5 text-[11px] text-amber-700 dark:text-amber-300"
                        >
                            Semua akun siswa yang diimpor akan mendapat password default
                            <code
                                class="rounded bg-amber-100 px-1 py-0.5 font-mono dark:bg-amber-900/50"
                                >password</code
                            >. Pastikan setiap siswa mengubah password setelah login pertama.
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="dark:bg-zinc-850 flex justify-end gap-3 border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-zinc-800"
            >
                <Button
                    type="button"
                    variant="outline"
                    @click="isImportModalOpen = false"
                    >Batal</Button
                >
                <Button
                    @click="submitImport"
                    :disabled="!importFile || importProcessing"
                    class="flex items-center gap-1.5 bg-emerald-600 font-semibold text-white hover:bg-emerald-700"
                >
                    <Loader2
                        v-if="importProcessing"
                        class="h-4 w-4 animate-spin"
                    />
                    <Upload v-else class="h-4 w-4" />
                    {{ importProcessing ? 'Memproses...' : 'Mulai Import' }}
                </Button>
            </div>
        </div>
    </div>
</template>

