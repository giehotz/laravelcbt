<script setup lang="ts">
import { Head, useForm, Link, usePage, router } from '@inertiajs/vue3';
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
    MapPin,
    Users,
    BookOpen,
    Lock,
    Download,
    Upload,
    FileSpreadsheet,
    AlertTriangle,
    CheckCircle2,
    Loader2,
} from 'lucide-vue-next';
import { store, update, destroy } from '@/routes/setting/user/siswa';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pengaturan',
                href: '#',
            },
            {
                title: 'User Siswa',
                href: '/setting/user/siswa',
            },
        ],
    },
});

const props = defineProps<{
    siswas: {
        data: Array<{
            id: number;
            nisn: string;
            nis: string;
            nama: string;
            jenis_kelamin: string | null;
            tahun_masuk: string | null;
            sekolah_asal: string | null;
            tempat_lahir: string | null;
            tanggal_lahir: string | null;
            agama: string | null;
            hp: string | null;
            email: string | null;
            foto: string;
            anak_ke: number | null;
            status_keluarga: string | null;
            alamat: string | null;
            rt: string | null;
            rw: string | null;
            kelurahan: string | null;
            kecamatan: string | null;
            kabupaten: string | null;
            provinsi: string | null;
            kode_pos: number | null;
            nama_ayah: string | null;
            tgl_lahir_ayah: string | null;
            pendidikan_ayah: string | null;
            pekerjaan_ayah: string | null;
            nohp_ayah: string | null;
            alamat_ayah: string | null;
            nama_ibu: string | null;
            tgl_lahir_ibu: string | null;
            pendidikan_ibu: string | null;
            pekerjaan_ibu: string | null;
            nohp_ibu: string | null;
            alamat_ibu: string | null;
            nama_wali: string | null;
            tgl_lahir_wali: string | null;
            pendidikan_wali: string | null;
            pekerjaan_wali: string | null;
            nohp_wali: string | null;
            alamat_wali: string | null;
            nik: string | null;
            warga_negara: string;
            uid: string | null;
            user: {
                id: number;
                username: string | null;
                email: string | null;
            } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    imported_users?: Array<any> | null;
}>();

const page = usePage();

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingSiswaId = ref<number | null>(null);
const activeTab = ref<'identitas' | 'alamat' | 'orangtua' | 'lainnya'>(
    'identitas',
);
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
    nama: '',
    nisn: '',
    nis: '',
    jenis_kelamin: '',
    username: '',
    password: '',
    email: '',
    tahun_masuk: '',
    sekolah_asal: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    agama: '',
    hp: '',
    anak_ke: undefined as number | undefined,
    status_keluarga: '',
    alamat: '',
    rt: '',
    rw: '',
    kelurahan: '',
    kecamatan: '',
    kabupaten: '',
    provinsi: '',
    kode_pos: undefined as number | undefined,
    nama_ayah: '',
    tgl_lahir_ayah: '',
    pendidikan_ayah: '',
    pekerjaan_ayah: '',
    nohp_ayah: '',
    alamat_ayah: '',
    nama_ibu: '',
    tgl_lahir_ibu: '',
    pendidikan_ibu: '',
    pekerjaan_ibu: '',
    nohp_ibu: '',
    alamat_ibu: '',
    nama_wali: '',
    tgl_lahir_wali: '',
    pendidikan_wali: '',
    pekerjaan_wali: '',
    nohp_wali: '',
    alamat_wali: '',
    nik: '',
    warga_negara: 'WNI',
    uid: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingSiswaId.value = null;
    form.reset();
    activeTab.value = 'identitas';
    isModalOpen.value = true;
};

const openEditModal = (siswa: any) => {
    isEditing.value = true;
    editingSiswaId.value = siswa.id;
    form.reset();

    form.nama = siswa.nama;
    form.nisn = siswa.nisn;
    form.nis = siswa.nis;
    form.jenis_kelamin = siswa.jenis_kelamin ?? '';
    form.username = siswa.user?.username ?? '';
    form.email = siswa.user?.email ?? siswa.email ?? '';
    form.tahun_masuk = siswa.tahun_masuk ?? '';
    form.sekolah_asal = siswa.sekolah_asal ?? '';
    form.tempat_lahir = siswa.tempat_lahir ?? '';
    form.tanggal_lahir = siswa.tanggal_lahir ?? '';
    form.agama = siswa.agama ?? '';
    form.hp = siswa.hp ?? '';
    form.anak_ke = siswa.anak_ke ?? undefined;
    form.status_keluarga = siswa.status_keluarga ?? '';
    form.alamat = siswa.alamat ?? '';
    form.rt = siswa.rt ?? '';
    form.rw = siswa.rw ?? '';
    form.kelurahan = siswa.kelurahan ?? '';
    form.kecamatan = siswa.kecamatan ?? '';
    form.kabupaten = siswa.kabupaten ?? '';
    form.provinsi = siswa.provinsi ?? '';
    form.kode_pos = siswa.kode_pos ?? undefined;
    form.nama_ayah = siswa.nama_ayah ?? '';
    form.tgl_lahir_ayah = siswa.tgl_lahir_ayah ?? '';
    form.pendidikan_ayah = siswa.pendidikan_ayah ?? '';
    form.pekerjaan_ayah = siswa.pekerjaan_ayah ?? '';
    form.nohp_ayah = siswa.nohp_ayah ?? '';
    form.alamat_ayah = siswa.alamat_ayah ?? '';
    form.nama_ibu = siswa.nama_ibu ?? '';
    form.tgl_lahir_ibu = siswa.tgl_lahir_ibu ?? '';
    form.pendidikan_ibu = siswa.pendidikan_ibu ?? '';
    form.pekerjaan_ibu = siswa.pekerjaan_ibu ?? '';
    form.nohp_ibu = siswa.nohp_ibu ?? '';
    form.alamat_ibu = siswa.alamat_ibu ?? '';
    form.nama_wali = siswa.nama_wali ?? '';
    form.tgl_lahir_wali = siswa.tgl_lahir_wali ?? '';
    form.pendidikan_wali = siswa.pendidikan_wali ?? '';
    form.pekerjaan_wali = siswa.pekerjaan_wali ?? '';
    form.nohp_wali = siswa.nohp_wali ?? '';
    form.alamat_wali = siswa.alamat_wali ?? '';
    form.nik = siswa.nik ?? '';
    form.warga_negara = siswa.warga_negara ?? 'WNI';
    form.uid = siswa.uid ?? '';

    activeTab.value = 'identitas';
    isModalOpen.value = true;
};

const submit = () => {
    if (isEditing.value && editingSiswaId.value) {
        form.put(update.url(editingSiswaId.value), {
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

const deleteSiswa = (id: number) => {
    if (
        confirm(
            'Apakah Anda yakin ingin menghapus akun siswa ini? Semua data terkait (termasuk data Buku Induk) akan ikut terhapus.',
        )
    ) {
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

    router.post('/setting/user/siswa/import', formData, {
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
    window.location.href = '/setting/user/siswa/template';
};
</script>

<template>
    <Head title="User Siswa" />

    <div class="mx-auto max-w-6xl space-y-6 px-6 py-6">
        <div
            class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
        >
            <Heading
                title="User Siswa / Peserta Didik"
                description="Kelola akun Siswa beserta informasi lengkap biodata, alamat, dan data orang tua/wali untuk kebutuhan Buku Induk."
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
                    <span>Tambah Siswa</span>
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
                        {{ imported_users.length }} akun siswa berhasil diimpor.
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
                            >Segera minta setiap siswa untuk mengubah password
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
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50 text-xs font-semibold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-800/50"
                        >
                            <th class="px-6 py-4">Siswa</th>
                            <th class="px-6 py-4">NIS / NISN</th>
                            <th class="px-6 py-4">L/P</th>
                            <th class="px-6 py-4">Username Login</th>
                            <th class="px-6 py-4">UID Kartu</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                    >
                        <tr
                            v-for="siswa in siswas.data"
                            :key="siswa.id"
                            class="transition-colors hover:bg-neutral-50/50 dark:hover:bg-zinc-800/30"
                        >
                            <td
                                class="px-6 py-4 font-medium text-neutral-800 dark:text-neutral-200"
                            >
                                <div class="flex items-center gap-2">
                                    <User class="h-4 w-4 text-neutral-400" />
                                    <div>
                                        <div class="font-semibold">
                                            {{ siswa.nama }}
                                        </div>
                                        <div class="text-xs text-neutral-400">
                                            Tahun Masuk:
                                            {{ siswa.tahun_masuk || '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 text-neutral-600 dark:text-neutral-400"
                            >
                                <div>NIS: {{ siswa.nis }}</div>
                                <div class="text-xs">
                                    NISN: {{ siswa.nisn }}
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 text-neutral-600 dark:text-neutral-400"
                            >
                                <span
                                    class="rounded px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        siswa.jenis_kelamin === 'L'
                                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400'
                                            : 'bg-pink-50 text-pink-700 dark:bg-pink-950/30 dark:text-pink-400'
                                    "
                                >
                                    {{ siswa.jenis_kelamin || '-' }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 text-neutral-600 dark:text-neutral-400"
                            >
                                {{ siswa.user?.username || '-' }}
                            </td>
                            <td
                                class="px-6 py-4 font-mono text-xs text-neutral-600 dark:text-neutral-400"
                            >
                                {{ siswa.uid || '-' }}
                            </td>
                            <td
                                class="space-x-1 px-6 py-4 text-right whitespace-nowrap"
                            >
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    @click="openEditModal(siswa)"
                                >
                                    <Edit class="h-4 w-4 text-neutral-500" />
                                </Button>
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    @click="deleteSiswa(siswa.id)"
                                    class="text-rose-600 hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-950/20"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="siswas.data.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-12 text-center text-neutral-500"
                            >
                                Belum ada data siswa.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="siswas.links && siswas.links.length > 3"
                class="flex justify-end gap-1 border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
            >
                <template v-for="link in siswas.links" :key="link.label">
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
                            Import Data Siswa dari Excel
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
                                Isi data siswa sesuai format kolom (kolom
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
                                template_siswa.xlsx — Berisi header dan contoh
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
                                Semua akun siswa yang diimpor akan mendapat
                                password default
                                <code
                                    class="rounded bg-amber-100 px-1 py-0.5 font-mono dark:bg-amber-900/50"
                                    >password</code
                                >. Pastikan setiap siswa mengubah password
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
                class="flex max-h-[90vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
            >
                <!-- Modal Header -->
                <div
                    class="dark:bg-zinc-850 flex items-center justify-between border-b border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-zinc-800"
                >
                    <div>
                        <h3
                            class="text-lg font-bold text-neutral-800 dark:text-neutral-200"
                        >
                            {{
                                isEditing
                                    ? 'Edit Profil & Akun Siswa'
                                    : 'Tambah Akun Siswa Baru'
                            }}
                        </h3>
                        <p class="text-xs text-neutral-500">
                            Lengkapi data profil siswa di bawah ini untuk
                            terekam di buku induk.
                        </p>
                    </div>
                    <button
                        @click="isModalOpen = false"
                        class="cursor-pointer rounded-lg p-1 text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-zinc-800 dark:hover:text-neutral-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Tabs -->
                <div
                    class="flex gap-2 border-b border-neutral-200 bg-neutral-50/50 px-6 dark:border-zinc-800 dark:bg-zinc-900"
                >
                    <button
                        type="button"
                        @click="activeTab = 'identitas'"
                        class="flex cursor-pointer items-center gap-1.5 border-b-2 px-4 py-3 text-xs font-semibold tracking-wider uppercase transition-all"
                        :class="
                            activeTab === 'identitas'
                                ? 'border-zinc-900 text-zinc-900 dark:border-white dark:text-white'
                                : 'border-transparent text-neutral-500 hover:text-neutral-700'
                        "
                    >
                        <User class="h-3.5 w-3.5" />
                        Identitas & Akun
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'alamat'"
                        class="flex cursor-pointer items-center gap-1.5 border-b-2 px-4 py-3 text-xs font-semibold tracking-wider uppercase transition-all"
                        :class="
                            activeTab === 'alamat'
                                ? 'border-zinc-900 text-zinc-900 dark:border-white dark:text-white'
                                : 'border-transparent text-neutral-500 hover:text-neutral-700'
                        "
                    >
                        <MapPin class="h-3.5 w-3.5" />
                        Alamat Tinggal
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'orangtua'"
                        class="flex cursor-pointer items-center gap-1.5 border-b-2 px-4 py-3 text-xs font-semibold tracking-wider uppercase transition-all"
                        :class="
                            activeTab === 'orangtua'
                                ? 'border-zinc-900 text-zinc-900 dark:border-white dark:text-white'
                                : 'border-transparent text-neutral-500 hover:text-neutral-700'
                        "
                    >
                        <Users class="h-3.5 w-3.5" />
                        Orang Tua / Wali
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'lainnya'"
                        class="flex cursor-pointer items-center gap-1.5 border-b-2 px-4 py-3 text-xs font-semibold tracking-wider uppercase transition-all"
                        :class="
                            activeTab === 'lainnya'
                                ? 'border-zinc-900 text-zinc-900 dark:border-white dark:text-white'
                                : 'border-transparent text-neutral-500 hover:text-neutral-700'
                        "
                    >
                        <BookOpen class="h-3.5 w-3.5" />
                        Akademik & Lainnya
                    </button>
                </div>

                <!-- Form Content -->
                <form
                    @submit.prevent="submit"
                    class="flex h-full flex-1 flex-col overflow-y-auto bg-white dark:bg-zinc-900"
                >
                    <div class="flex-1 space-y-6 p-6">
                        <!-- Tab 1: Identitas & Akun -->
                        <div
                            v-show="activeTab === 'identitas'"
                            class="space-y-4"
                        >
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="nama"
                                        >Nama Lengkap Siswa
                                        <span class="text-rose-500"
                                            >*</span
                                        ></Label
                                    >
                                    <Input
                                        id="nama"
                                        v-model="form.nama"
                                        required
                                        placeholder="Nama Lengkap Tanpa Gelar"
                                    />
                                    <InputError :message="form.errors.nama" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="nik">Nomor NIK (KTP/KK)</Label>
                                    <Input
                                        id="nik"
                                        v-model="form.nik"
                                        placeholder="16 digit nomor induk kependudukan"
                                    />
                                    <InputError :message="form.errors.nik" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="nis"
                                        >NIS (Nomor Induk Siswa)
                                        <span class="text-rose-500"
                                            >*</span
                                        ></Label
                                    >
                                    <Input
                                        id="nis"
                                        v-model="form.nis"
                                        required
                                        placeholder="Nomor Induk Siswa Sekolah"
                                    />
                                    <InputError :message="form.errors.nis" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="nisn"
                                        >NISN (Nasional)
                                        <span class="text-rose-500"
                                            >*</span
                                        ></Label
                                    >
                                    <Input
                                        id="nisn"
                                        v-model="form.nisn"
                                        required
                                        placeholder="10 digit NISN Nasional"
                                    />
                                    <InputError :message="form.errors.nisn" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="jenis_kelamin"
                                        >Jenis Kelamin</Label
                                    >
                                    <select
                                        id="jenis_kelamin"
                                        v-model="form.jenis_kelamin"
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:border-zinc-800"
                                    >
                                        <option value="">
                                            -- Pilih Jenis Kelamin --
                                        </option>
                                        <option value="L">Laki-laki (L)</option>
                                        <option value="P">Perempuan (P)</option>
                                    </select>
                                    <InputError
                                        :message="form.errors.jenis_kelamin"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="warga_negara"
                                        >Kewarganegaraan</Label
                                    >
                                    <Input
                                        id="warga_negara"
                                        v-model="form.warga_negara"
                                        placeholder="Contoh: WNI"
                                    />
                                    <InputError
                                        :message="form.errors.warga_negara"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="tempat_lahir"
                                        >Tempat Lahir</Label
                                    >
                                    <Input
                                        id="tempat_lahir"
                                        v-model="form.tempat_lahir"
                                        placeholder="Kota Lahir"
                                    />
                                    <InputError
                                        :message="form.errors.tempat_lahir"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="tanggal_lahir"
                                        >Tanggal Lahir</Label
                                    >
                                    <Input
                                        id="tanggal_lahir"
                                        type="date"
                                        v-model="form.tanggal_lahir"
                                    />
                                    <InputError
                                        :message="form.errors.tanggal_lahir"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="agama">Agama</Label>
                                    <select
                                        id="agama"
                                        v-model="form.agama"
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:border-zinc-800"
                                    >
                                        <option value="">
                                            -- Pilih Agama --
                                        </option>
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen">Kristen</option>
                                        <option value="Katolik">Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Buddha">Buddha</option>
                                        <option value="Khonghucu">
                                            Khonghucu
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.agama" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="hp"
                                        >No Handphone / WA Siswa</Label
                                    >
                                    <Input
                                        id="hp"
                                        v-model="form.hp"
                                        placeholder="Contoh: 081234567890"
                                    />
                                    <InputError :message="form.errors.hp" />
                                </div>
                            </div>

                            <hr
                                class="border-neutral-200 dark:border-zinc-800"
                            />
                            <div
                                class="flex items-center gap-1.5 text-sm font-semibold text-zinc-900 dark:text-white"
                            >
                                <Lock class="h-4 w-4 text-neutral-400" />
                                <span>Konfigurasi Akun Pengguna</span>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="grid gap-2">
                                    <Label for="username"
                                        >Username Login
                                        <span class="text-rose-500"
                                            >*</span
                                        ></Label
                                    >
                                    <Input
                                        id="username"
                                        v-model="form.username"
                                        required
                                        placeholder="Username unik siswa"
                                    />
                                    <InputError
                                        :message="form.errors.username"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="email">Alamat Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        placeholder="siswa@sekolah.sch.id"
                                    />
                                    <InputError :message="form.errors.email" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="password"
                                        >Password Login
                                        <span
                                            v-if="!isEditing"
                                            class="text-rose-500"
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
                                    <InputError
                                        :message="form.errors.password"
                                    />
                                    <p
                                        v-if="isEditing"
                                        class="text-[10px] text-neutral-400"
                                    >
                                        Kosongkan password jika tidak ingin
                                        diubah.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Alamat Tinggal -->
                        <div v-show="activeTab === 'alamat'" class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="alamat"
                                    >Alamat Tempat Tinggal</Label
                                >
                                <textarea
                                    id="alamat"
                                    v-model="form.alamat"
                                    rows="2"
                                    class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:border-zinc-800"
                                    placeholder="Nama Jalan, Dukuh, RT/RW"
                                ></textarea>
                                <InputError :message="form.errors.alamat" />
                            </div>

                            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                                <div class="grid gap-2">
                                    <Label for="rt">RT</Label>
                                    <Input
                                        id="rt"
                                        v-model="form.rt"
                                        placeholder="RT"
                                    />
                                    <InputError :message="form.errors.rt" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="rw">RW</Label>
                                    <Input
                                        id="rw"
                                        v-model="form.rw"
                                        placeholder="RW"
                                    />
                                    <InputError :message="form.errors.rw" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="kode_pos">Kode Pos</Label>
                                    <Input
                                        id="kode_pos"
                                        type="number"
                                        v-model="form.kode_pos"
                                        placeholder="Kode Pos"
                                    />
                                    <InputError
                                        :message="form.errors.kode_pos"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="kelurahan"
                                        >Kelurahan / Desa</Label
                                    >
                                    <Input
                                        id="kelurahan"
                                        v-model="form.kelurahan"
                                        placeholder="Kelurahan"
                                    />
                                    <InputError
                                        :message="form.errors.kelurahan"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="kecamatan">Kecamatan</Label>
                                    <Input
                                        id="kecamatan"
                                        v-model="form.kecamatan"
                                        placeholder="Kecamatan"
                                    />
                                    <InputError
                                        :message="form.errors.kecamatan"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="kabupaten"
                                        >Kabupaten / Kota</Label
                                    >
                                    <Input
                                        id="kabupaten"
                                        v-model="form.kabupaten"
                                        placeholder="Kabupaten/Kota"
                                    />
                                    <InputError
                                        :message="form.errors.kabupaten"
                                    />
                                </div>
                                <div class="col-span-2 grid gap-2">
                                    <Label for="provinsi">Provinsi</Label>
                                    <Input
                                        id="provinsi"
                                        v-model="form.provinsi"
                                        placeholder="Provinsi"
                                    />
                                    <InputError
                                        :message="form.errors.provinsi"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Orang Tua / Wali -->
                        <div
                            v-show="activeTab === 'orangtua'"
                            class="space-y-6"
                        >
                            <!-- Data Ayah -->
                            <div class="space-y-3">
                                <h4
                                    class="border-b pb-1 text-sm font-bold text-neutral-800 dark:text-neutral-200"
                                >
                                    Biodata Ayah Kandung
                                </h4>
                                <div
                                    class="grid grid-cols-1 gap-4 md:grid-cols-3"
                                >
                                    <div class="grid gap-2">
                                        <Label for="nama_ayah">Nama Ayah</Label>
                                        <Input
                                            id="nama_ayah"
                                            v-model="form.nama_ayah"
                                            placeholder="Nama Lengkap Ayah"
                                        />
                                        <InputError
                                            :message="form.errors.nama_ayah"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="pekerjaan_ayah"
                                            >Pekerjaan Ayah</Label
                                        >
                                        <Input
                                            id="pekerjaan_ayah"
                                            v-model="form.pekerjaan_ayah"
                                            placeholder="Pekerjaan Ayah"
                                        />
                                        <InputError
                                            :message="
                                                form.errors.pekerjaan_ayah
                                            "
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="nohp_ayah"
                                            >No. HP Ayah</Label
                                        >
                                        <Input
                                            id="nohp_ayah"
                                            v-model="form.nohp_ayah"
                                            placeholder="No. HP / WhatsApp"
                                        />
                                        <InputError
                                            :message="form.errors.nohp_ayah"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="tgl_lahir_ayah"
                                            >Tanggal Lahir Ayah</Label
                                        >
                                        <Input
                                            id="tgl_lahir_ayah"
                                            v-model="form.tgl_lahir_ayah"
                                            placeholder="Tempat, Tgl Lahir"
                                        />
                                        <InputError
                                            :message="
                                                form.errors.tgl_lahir_ayah
                                            "
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="pendidikan_ayah"
                                            >Pendidikan Ayah</Label
                                        >
                                        <Input
                                            id="pendidikan_ayah"
                                            v-model="form.pendidikan_ayah"
                                            placeholder="Pendidikan Terakhir"
                                        />
                                        <InputError
                                            :message="
                                                form.errors.pendidikan_ayah
                                            "
                                        />
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="alamat_ayah"
                                        >Alamat Ayah (Kosongkan jika sama dengan
                                        siswa)</Label
                                    >
                                    <textarea
                                        id="alamat_ayah"
                                        v-model="form.alamat_ayah"
                                        rows="1"
                                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-1.5 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:border-zinc-800"
                                        placeholder="Alamat lengkap ayah"
                                    ></textarea>
                                    <InputError
                                        :message="form.errors.alamat_ayah"
                                    />
                                </div>
                            </div>

                            <!-- Data Ibu -->
                            <div class="space-y-3">
                                <h4
                                    class="border-b pb-1 text-sm font-bold text-neutral-800 dark:text-neutral-200"
                                >
                                    Biodata Ibu Kandung
                                </h4>
                                <div
                                    class="grid grid-cols-1 gap-4 md:grid-cols-3"
                                >
                                    <div class="grid gap-2">
                                        <Label for="nama_ibu">Nama Ibu</Label>
                                        <Input
                                            id="nama_ibu"
                                            v-model="form.nama_ibu"
                                            placeholder="Nama Lengkap Ibu"
                                        />
                                        <InputError
                                            :message="form.errors.nama_ibu"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="pekerjaan_ibu"
                                            >Pekerjaan Ibu</Label
                                        >
                                        <Input
                                            id="pekerjaan_ibu"
                                            v-model="form.pekerjaan_ibu"
                                            placeholder="Pekerjaan Ibu"
                                        />
                                        <InputError
                                            :message="form.errors.pekerjaan_ibu"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="nohp_ibu">No. HP Ibu</Label>
                                        <Input
                                            id="nohp_ibu"
                                            v-model="form.nohp_ibu"
                                            placeholder="No. HP / WhatsApp"
                                        />
                                        <InputError
                                            :message="form.errors.nohp_ibu"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="tgl_lahir_ibu"
                                            >Tanggal Lahir Ibu</Label
                                        >
                                        <Input
                                            id="tgl_lahir_ibu"
                                            v-model="form.tgl_lahir_ibu"
                                            placeholder="Tempat, Tgl Lahir"
                                        />
                                        <InputError
                                            :message="form.errors.tgl_lahir_ibu"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="pendidikan_ibu"
                                            >Pendidikan Ibu</Label
                                        >
                                        <Input
                                            id="pendidikan_ibu"
                                            v-model="form.pendidikan_ibu"
                                            placeholder="Pendidikan Terakhir"
                                        />
                                        <InputError
                                            :message="
                                                form.errors.pendidikan_ibu
                                            "
                                        />
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="alamat_ibu"
                                        >Alamat Ibu (Kosongkan jika sama dengan
                                        siswa)</Label
                                    >
                                    <textarea
                                        id="alamat_ibu"
                                        v-model="form.alamat_ibu"
                                        rows="1"
                                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-1.5 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:border-zinc-800"
                                        placeholder="Alamat lengkap ibu"
                                    ></textarea>
                                    <InputError
                                        :message="form.errors.alamat_ibu"
                                    />
                                </div>
                            </div>

                            <!-- Data Wali -->
                            <div class="space-y-3">
                                <h4
                                    class="border-b pb-1 text-sm font-bold text-neutral-800 dark:text-neutral-200"
                                >
                                    Biodata Wali (Opsional)
                                </h4>
                                <div
                                    class="grid grid-cols-1 gap-4 md:grid-cols-3"
                                >
                                    <div class="grid gap-2">
                                        <Label for="nama_wali">Nama Wali</Label>
                                        <Input
                                            id="nama_wali"
                                            v-model="form.nama_wali"
                                            placeholder="Nama Lengkap Wali"
                                        />
                                        <InputError
                                            :message="form.errors.nama_wali"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="pekerjaan_wali"
                                            >Pekerjaan Wali</Label
                                        >
                                        <Input
                                            id="pekerjaan_wali"
                                            v-model="form.pekerjaan_wali"
                                            placeholder="Pekerjaan Wali"
                                        />
                                        <InputError
                                            :message="
                                                form.errors.pekerjaan_wali
                                            "
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="nohp_wali"
                                            >No. HP Wali</Label
                                        >
                                        <Input
                                            id="nohp_wali"
                                            v-model="form.nohp_wali"
                                            placeholder="No. HP / WhatsApp"
                                        />
                                        <InputError
                                            :message="form.errors.nohp_wali"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="tgl_lahir_wali"
                                            >Tanggal Lahir Wali</Label
                                        >
                                        <Input
                                            id="tgl_lahir_wali"
                                            v-model="form.tgl_lahir_wali"
                                            placeholder="Tempat, Tgl Lahir"
                                        />
                                        <InputError
                                            :message="
                                                form.errors.tgl_lahir_wali
                                            "
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label for="pendidikan_wali"
                                            >Pendidikan Wali</Label
                                        >
                                        <Input
                                            id="pendidikan_wali"
                                            v-model="form.pendidikan_wali"
                                            placeholder="Pendidikan Terakhir"
                                        />
                                        <InputError
                                            :message="
                                                form.errors.pendidikan_wali
                                            "
                                        />
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="alamat_wali">Alamat Wali</Label>
                                    <textarea
                                        id="alamat_wali"
                                        v-model="form.alamat_wali"
                                        rows="1"
                                        class="flex w-full rounded-md border border-input bg-transparent px-3 py-1.5 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:border-zinc-800"
                                        placeholder="Alamat lengkap wali"
                                    ></textarea>
                                    <InputError
                                        :message="form.errors.alamat_wali"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Tab 4: Akademik & Lainnya -->
                        <div v-show="activeTab === 'lainnya'" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="tahun_masuk"
                                        >Tahun Masuk Sekolah
                                        <span class="text-rose-500"
                                            >*</span
                                        ></Label
                                    >
                                    <Input
                                        id="tahun_masuk"
                                        v-model="form.tahun_masuk"
                                        required
                                        placeholder="Contoh: 2024"
                                    />
                                    <InputError
                                        :message="form.errors.tahun_masuk"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="sekolah_asal"
                                        >Sekolah Asal (SMP/MTs)</Label
                                    >
                                    <Input
                                        id="sekolah_asal"
                                        v-model="form.sekolah_asal"
                                        placeholder="Nama sekolah asal siswa"
                                    />
                                    <InputError
                                        :message="form.errors.sekolah_asal"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="anak_ke"
                                        >Anak Ke- (Angka)</Label
                                    >
                                    <Input
                                        id="anak_ke"
                                        type="number"
                                        v-model="form.anak_ke"
                                        placeholder="Anak ke berapa"
                                    />
                                    <InputError
                                        :message="form.errors.anak_ke"
                                    />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="status_keluarga"
                                        >Status Hubungan Keluarga (Kode)</Label
                                    >
                                    <select
                                        id="status_keluarga"
                                        v-model="form.status_keluarga"
                                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:border-zinc-800"
                                    >
                                        <option value="">
                                            -- Pilih Status --
                                        </option>
                                        <option value="1">Anak Kandung</option>
                                        <option value="2">Anak Tiri</option>
                                        <option value="3">Anak Angkat</option>
                                    </select>
                                    <InputError
                                        :message="form.errors.status_keluarga"
                                    />
                                </div>
                                <div class="grid gap-2 md:col-span-2">
                                    <Label for="uid"
                                        >UID Kartu Ujian (RFID / Card
                                        Reader)</Label
                                    >
                                    <Input
                                        id="uid"
                                        v-model="form.uid"
                                        placeholder="Tempelkan kartu RFID atau isi dengan serial ID unik"
                                    />
                                    <InputError :message="form.errors.uid" />
                                    <p class="text-[10px] text-neutral-400">
                                        UID ini dapat digunakan siswa untuk tap
                                        login ujian menggunakan kartu fisik.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div
                        class="dark:bg-zinc-850 flex justify-between border-t border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-zinc-800"
                    >
                        <div class="flex gap-1.5">
                            <Button
                                type="button"
                                variant="outline"
                                @click="activeTab = 'identitas'"
                                :disabled="activeTab === 'identitas'"
                                size="sm"
                                >Kembali</Button
                            >
                            <Button
                                type="button"
                                variant="outline"
                                @click="
                                    activeTab =
                                        activeTab === 'identitas'
                                            ? 'alamat'
                                            : activeTab === 'alamat'
                                              ? 'orangtua'
                                              : 'lainnya'
                                "
                                :disabled="activeTab === 'lainnya'"
                                size="sm"
                                >Berikutnya</Button
                            >
                        </div>
                        <div class="flex gap-3">
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
                                {{
                                    isEditing
                                        ? 'Simpan Perubahan'
                                        : 'Tambah Siswa'
                                }}
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
