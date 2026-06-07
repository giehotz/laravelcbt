<script setup lang="ts">
import { Head, useForm, usePage, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Trash2,
    Edit,
    Plus,
    X,
    User,
    Download,
    Upload,
    FileSpreadsheet,
    AlertTriangle,
    CheckCircle2,
    Loader2,
    ClipboardList,
} from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pengaturan',
                href: '#',
            },
            {
                title: 'User Guru',
                href: '/setting/user/guru',
            },
        ],
    },
});

const props = defineProps<{
    gurus: {
        data: Array<{
            id: number;
            nip: string | null;
            nama_guru: string;
            email: string | null;
            kode_guru: string | null;
            username: string | null;
            no_ktp: string | null;
            tempat_lahir: string | null;
            tgl_lahir: string | null;
            jenis_kelamin: string | null;
            agama: string | null;
            no_hp: string | null;
            alamat: string | null;
            nuptk: string | null;
            jenis_ptk: string | null;
            status_pegawai: string | null;
            status_aktif: string | null;
            user: {
                id: number;
                username: string | null;
                email: string;
            } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    imported_users?: Array<any> | null;
}>();

import { store, update, destroy } from '@/routes/setting/user/guru';

const page = usePage();

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingGuruId = ref<number | null>(null);
const isImportModalOpen = ref(false);
const importFile = ref<File | null>(null);
const importProcessing = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);

// Get errors from the page props
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

const form = useForm({
    nama_guru: '',
    email: '',
    username: '',
    password: '',
    nip: '',
    kode_guru: '',
    no_ktp: '',
    tempat_lahir: '',
    tgl_lahir: '',
    jenis_kelamin: '',
    agama: '',
    no_hp: '',
    alamat: '',
    nuptk: '',
    jenis_ptk: '',
    status_pegawai: '',
    status_aktif: 'Aktif',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingGuruId.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (guru: any) => {
    isEditing.value = true;
    editingGuruId.value = guru.id;
    form.reset();
    form.nama_guru = guru.nama_guru;
    form.email = guru.email ?? '';
    form.username = guru.user?.username ?? guru.username ?? '';
    form.nip = guru.nip ?? '';
    form.kode_guru = guru.kode_guru ?? '';
    form.no_ktp = guru.no_ktp ?? '';
    form.tempat_lahir = guru.tempat_lahir ?? '';
    form.tgl_lahir = guru.tgl_lahir ?? '';
    form.jenis_kelamin = guru.jenis_kelamin ?? '';
    form.agama = guru.agama ?? '';
    form.no_hp = guru.no_hp ?? '';
    form.alamat = guru.alamat ?? '';
    form.nuptk = guru.nuptk ?? '';
    form.jenis_ptk = guru.jenis_ptk ?? '';
    form.status_pegawai = guru.status_pegawai ?? '';
    form.status_aktif = guru.status_aktif ?? 'Aktif';
    isModalOpen.value = true;
};

const submit = () => {
    if (isEditing.value && editingGuruId.value) {
        form.put(update.url(editingGuruId.value), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(store.url(), {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
            },
        });
    }
};

const deleteGuru = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus akun guru ini?')) {
        form.delete(destroy.url(id));
    }
};

// Import functions
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

    router.post('/setting/user/guru/import', formData, {
        forceFormData: true,
        onSuccess: () => {
            isImportModalOpen.value = false;
            importFile.value = null;
            importProcessing.value = false;
        },
        onError: () => {
            importProcessing.value = false;
        },
    });
};

const downloadTemplate = () => {
    window.location.href = '/setting/user/guru/template';
};
</script>

<template>
    <Head title="User Guru" />

    <div class="mx-auto max-w-6xl space-y-6 px-6 py-6">
        <div
            class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
        >
            <Heading
                title="User Guru & Pendidik"
                description="Kelola akun Guru dan Wali Kelas beserta informasi biodatanya untuk keperluan pembuatan soal dan pengisian rapor."
            />
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
                <Button
                    @click="openCreateModal"
                    class="flex items-center gap-1.5 bg-zinc-900 font-semibold text-white shadow hover:bg-zinc-800"
                >
                    <Plus class="h-4 w-4" />
                    <span>Tambah Guru</span>
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
                        {{ imported_users.length }} akun guru berhasil diimpor.
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
                            >Segera minta setiap guru untuk mengubah password
                            mereka setelah login pertama.</span
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
                        Tidak ada data yang diimpor. Perbaiki kesalahan berikut,
                        lalu upload ulang file Anda.
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

        <!-- Table List -->
        <div
            class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
        >
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr
                        class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-800/50"
                    >
                        <th class="px-6 py-4">Guru</th>
                        <th class="px-6 py-4">Kode Guru / NIP</th>
                        <th class="px-6 py-4">Username Login</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody
                    class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                >
                    <tr
                        v-for="guru in gurus.data"
                        :key="guru.id"
                        class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-zinc-800/30"
                    >
                        <td
                            class="px-6 py-4 font-medium text-neutral-800 dark:text-neutral-200"
                        >
                            <div class="flex items-center gap-2">
                                <User class="h-4 w-4 text-neutral-400" />
                                <div>
                                    <div class="font-semibold">
                                        {{ guru.nama_guru }}
                                    </div>
                                    <div class="text-xs text-neutral-400">
                                        {{ guru.email || 'Tidak ada email' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 text-neutral-600 dark:text-neutral-400"
                        >
                            <div>Kode: {{ guru.kode_guru || '-' }}</div>
                            <div class="text-xs">
                                NIP: {{ guru.nip || '-' }}
                            </div>
                        </td>
                        <td
                            class="px-6 py-4 text-neutral-600 dark:text-neutral-400"
                        >
                            {{ guru.user?.username || '-' }}
                        </td>
                        <td class="space-x-1 px-6 py-4 text-right">
                            <Button
                                size="sm"
                                variant="ghost"
                                as-child
                                title="Atur Penugasan Mengajar"
                            >
                                <Link
                                    :href="`/setting/user/guru/${guru.id}/assignment`"
                                    class="text-indigo-600 hover:bg-indigo-50 hover:text-indigo-500 dark:hover:bg-indigo-950/20"
                                >
                                    <ClipboardList class="h-4 w-4" />
                                </Link>
                            </Button>
                            <Button
                                size="sm"
                                variant="ghost"
                                @click="openEditModal(guru)"
                            >
                                <Edit class="h-4 w-4 text-neutral-500" />
                            </Button>
                            <Button
                                size="sm"
                                variant="ghost"
                                @click="deleteGuru(guru.id)"
                                class="text-rose-600 hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/20"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </td>
                    </tr>
                    <tr v-if="gurus.data.length === 0">
                        <td
                            colspan="4"
                            class="px-6 py-12 text-center text-neutral-500"
                        >
                            Belum ada data guru.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div
                v-if="gurus.links && gurus.links.length > 3"
                class="flex justify-end gap-1 border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
            >
                <template v-for="link in gurus.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        class="rounded px-3 py-1.5 text-xs font-medium transition-colors"
                        :class="
                            link.active
                                ? 'bg-zinc-900 text-white dark:bg-zinc-50 dark:text-zinc-900'
                                : 'border border-neutral-200 bg-white text-neutral-600 hover:bg-neutral-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800'
                        "
                    />
                    <span
                        v-else
                        v-html="link.label"
                        class="rounded border border-neutral-200 bg-neutral-100/50 px-3 py-1.5 text-xs font-medium text-neutral-400 dark:border-zinc-800 dark:bg-zinc-800/30"
                    />
                </template>
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
                            Import Data Guru dari Excel
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
                                Unduh template Excel terlebih dahulu menggunakan
                                tombol di bawah.
                            </li>
                            <li>
                                Isi data guru sesuai format kolom (kolom
                                bertanda <strong>*</strong> wajib diisi).
                            </li>
                            <li>
                                Maksimal <strong>1.000 baris</strong> data per
                                file (ukuran maks <strong>2 MB</strong>).
                            </li>
                            <li>
                                Upload file yang sudah dilengkapi lalu klik
                                <strong>Mulai Import</strong>.
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
                                Unduh Template Excel
                            </p>
                            <p class="text-[11px] text-neutral-500">
                                template_guru.xlsx — Berisi header dan contoh
                                pengisian
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
                                    >({{
                                        (importFile.size / 1024).toFixed(1)
                                    }}
                                    KB)</span
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
                                Semua akun guru yang diimpor akan mendapat
                                password default
                                <code
                                    class="rounded bg-amber-100 px-1 py-0.5 font-mono dark:bg-amber-900/50"
                                    >password</code
                                >. Pastikan setiap guru mengubah password
                                setelah login pertama.
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

        <!-- Modal Dialog Form -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
                >
                    <h3
                        class="font-bold text-neutral-800 dark:text-neutral-200"
                    >
                        {{ isEditing ? 'Edit Akun Guru' : 'Tambah Akun Guru' }}
                    </h3>
                    <button
                        @click="isModalOpen = false"
                        class="cursor-pointer text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form
                    @submit.prevent="submit"
                    class="flex-1 space-y-4 overflow-y-auto p-6"
                >
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="nama_guru"
                                >Nama Lengkap Guru
                                <span class="text-rose-500">*</span></Label
                            >
                            <Input
                                id="nama_guru"
                                v-model="form.nama_guru"
                                required
                                placeholder="Nama Lengkap dengan Gelar"
                            />
                            <InputError :message="form.errors.nama_guru" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                placeholder="email@domain.com"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="username"
                                >Username Login
                                <span class="text-rose-500">*</span></Label
                            >
                            <Input
                                id="username"
                                v-model="form.username"
                                required
                                placeholder="Username unik"
                            />
                            <InputError :message="form.errors.username" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password"
                                >Password
                                <span v-if="!isEditing" class="text-rose-500"
                                    >*</span
                                ></Label
                            >
                            <Input
                                id="password"
                                type="password"
                                v-model="form.password"
                                :required="!isEditing"
                                placeholder="Minimal 8 karakter"
                            />
                            <InputError :message="form.errors.password" />
                            <p
                                v-if="isEditing"
                                class="text-xs text-neutral-400"
                            >
                                Kosongkan password jika tidak ingin diubah.
                            </p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="nip">NIP</Label>
                            <Input
                                id="nip"
                                v-model="form.nip"
                                placeholder="NIP Guru"
                            />
                            <InputError :message="form.errors.nip" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="kode_guru">Kode Guru</Label>
                            <Input
                                id="kode_guru"
                                v-model="form.kode_guru"
                                placeholder="Contoh: GR01"
                            />
                            <InputError :message="form.errors.kode_guru" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="no_hp">No HP / WhatsApp</Label>
                            <Input
                                id="no_hp"
                                v-model="form.no_hp"
                                placeholder="Contoh: 0812345678"
                            />
                            <InputError :message="form.errors.no_hp" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="nuptk">NUPTK</Label>
                            <Input
                                id="nuptk"
                                v-model="form.nuptk"
                                placeholder="Nomor NUPTK"
                            />
                            <InputError :message="form.errors.nuptk" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="status_aktif">Status Aktif</Label>
                            <select
                                id="status_aktif"
                                v-model="form.status_aktif"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-800"
                            >
                                <option value="Aktif">Aktif</option>
                                <option value="Non-Aktif">Non-Aktif</option>
                            </select>
                            <InputError :message="form.errors.status_aktif" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="alamat">Alamat</Label>
                        <textarea
                            id="alamat"
                            v-model="form.alamat"
                            class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:border-zinc-800"
                            placeholder="Alamat rumah lengkap"
                        ></textarea>
                        <InputError :message="form.errors.alamat" />
                    </div>

                    <div
                        class="flex justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-zinc-800"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="isModalOpen = false"
                            >Batal</Button
                        >
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-zinc-900 font-semibold text-white hover:bg-zinc-800"
                        >
                            {{ isEditing ? 'Simpan' : 'Tambah' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
