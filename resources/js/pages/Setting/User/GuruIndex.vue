<script setup lang="ts">
import { Head, useForm, usePage, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Trash2, Edit, Plus, X, User, Download, Upload, FileSpreadsheet, AlertTriangle, CheckCircle2, Loader2, ClipboardList } from 'lucide-vue-next';

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
        return Array.isArray(errors.import_errors) ? errors.import_errors : [errors.import_errors];
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

    <div class="px-6 py-6 max-w-6xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <Heading
                title="User Guru & Pendidik"
                description="Kelola akun Guru dan Wali Kelas beserta informasi biodatanya untuk keperluan pembuatan soal dan pengisian rapor."
            />
            <div class="flex items-center gap-2 flex-shrink-0">
                <Button variant="outline" @click="downloadTemplate" class="flex items-center gap-1.5 text-sm">
                    <Download class="w-4 h-4" />
                    <span class="hidden sm:inline">Template</span>
                </Button>
                <Button variant="outline" @click="openImportModal" class="flex items-center gap-1.5 text-sm border-emerald-300 text-emerald-700 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-400 dark:hover:bg-emerald-950/30">
                    <Upload class="w-4 h-4" />
                    <span class="hidden sm:inline">Import Excel</span>
                </Button>
                <Button @click="openCreateModal" class="bg-zinc-900 hover:bg-zinc-800 text-white font-semibold flex items-center gap-1.5 shadow">
                    <Plus class="w-4 h-4" />
                    <span>Tambah Guru</span>
                </Button>
            </div>
        </div>

        <!-- Import Success Banner -->
        <div v-if="imported_users && imported_users.length > 0" class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <CheckCircle2 class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0" />
                <div class="flex-1">
                    <h4 class="font-semibold text-emerald-800 dark:text-emerald-200 text-sm">Import Berhasil</h4>
                    <p class="text-emerald-700 dark:text-emerald-300 text-xs mt-1">
                        {{ imported_users.length }} akun guru berhasil diimpor. Password default untuk semua akun baru adalah <code class="bg-emerald-100 dark:bg-emerald-900/50 px-1.5 py-0.5 rounded font-mono text-xs">password</code>.
                    </p>
                    <div class="mt-2 flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                        <AlertTriangle class="w-3.5 h-3.5" />
                        <span class="text-[11px] font-medium">Segera minta setiap guru untuk mengubah password mereka setelah login pertama.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Errors Banner -->
        <div v-if="importErrors.length > 0" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <AlertTriangle class="w-5 h-5 text-rose-600 dark:text-rose-400 mt-0.5 flex-shrink-0" />
                <div class="flex-1">
                    <h4 class="font-semibold text-rose-800 dark:text-rose-200 text-sm">Gagal Import — {{ importErrors.length }} Error Ditemukan</h4>
                    <p class="text-rose-700 dark:text-rose-300 text-xs mt-1">Tidak ada data yang diimpor. Perbaiki kesalahan berikut, lalu upload ulang file Anda.</p>
                    <div class="mt-3 max-h-40 overflow-y-auto bg-rose-100/50 dark:bg-rose-950/50 rounded-lg border border-rose-200 dark:border-rose-800">
                        <ul class="divide-y divide-rose-200/50 dark:divide-rose-800/50">
                            <li v-for="(error, idx) in importErrors" :key="idx" class="px-3 py-2 text-xs text-rose-700 dark:text-rose-300 font-mono">
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- File Error Banner -->
        <div v-if="fileError" class="bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <AlertTriangle class="w-5 h-5 text-rose-600 dark:text-rose-400 mt-0.5 flex-shrink-0" />
                <div class="flex-1">
                    <h4 class="font-semibold text-rose-800 dark:text-rose-200 text-sm">Error Upload</h4>
                    <p class="text-rose-700 dark:text-rose-300 text-xs mt-1">{{ fileError }}</p>
                </div>
            </div>
        </div>

        <!-- Table List -->
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-neutral-50 dark:bg-zinc-800/50 border-b border-neutral-200 dark:border-zinc-800 text-xs font-semibold uppercase text-neutral-500 tracking-wider">
                        <th class="px-6 py-4">Guru</th>
                        <th class="px-6 py-4">Kode Guru / NIP</th>
                        <th class="px-6 py-4">Username Login</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                    <tr v-for="guru in gurus.data" :key="guru.id" class="hover:bg-neutral-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                        <td class="px-6 py-4 font-medium text-neutral-800 dark:text-neutral-200">
                            <div class="flex items-center gap-2">
                                <User class="w-4 h-4 text-neutral-400" />
                                <div>
                                    <div class="font-semibold">{{ guru.nama_guru }}</div>
                                    <div class="text-xs text-neutral-400">{{ guru.email || 'Tidak ada email' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400">
                            <div>Kode: {{ guru.kode_guru || '-' }}</div>
                            <div class="text-xs">NIP: {{ guru.nip || '-' }}</div>
                        </td>
                        <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400">
                            {{ guru.user?.username || '-' }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-1">
                            <Button
                                size="sm"
                                variant="ghost"
                                as-child
                                title="Atur Penugasan Mengajar"
                            >
                                <Link :href="`/setting/user/guru/${guru.id}/assignment`" class="text-indigo-600 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-950/20">
                                    <ClipboardList class="w-4 h-4" />
                                </Link>
                            </Button>
                            <Button size="sm" variant="ghost" @click="openEditModal(guru)">
                                <Edit class="w-4 h-4 text-neutral-500" />
                            </Button>
                            <Button size="sm" variant="ghost" @click="deleteGuru(guru.id)" class="text-rose-600 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20">
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </td>
                    </tr>
                    <tr v-if="gurus.data.length === 0">
                        <td colspan="4" class="px-6 py-12 text-center text-neutral-500">
                            Belum ada data guru.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="gurus.links && gurus.links.length > 3" class="px-6 py-4 bg-neutral-50 dark:bg-zinc-800/30 border-t border-neutral-200 dark:border-zinc-800 flex justify-end gap-1">
                <template v-for="link in gurus.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        v-html="link.label"
                        class="px-3 py-1.5 rounded text-xs font-medium transition-colors"
                        :class="link.active ? 'bg-zinc-900 text-white dark:bg-zinc-50 dark:text-zinc-900' : 'bg-white border border-neutral-200 text-neutral-600 hover:bg-neutral-50 dark:bg-zinc-900 dark:border-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-800'"
                    />
                    <span v-else v-html="link.label" class="px-3 py-1.5 rounded text-xs font-medium text-neutral-400 border border-neutral-200 bg-neutral-100/50 dark:border-zinc-800 dark:bg-zinc-800/30" />
                </template>
            </div>
        </div>

        <!-- Import Modal -->
        <div v-if="isImportModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
            <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <FileSpreadsheet class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Import Data Guru dari Excel</h3>
                    </div>
                    <button @click="isImportModalOpen = false" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 cursor-pointer">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Instructions -->
                    <div class="bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                        <h4 class="font-semibold text-blue-800 dark:text-blue-200 text-xs mb-2">Panduan Import:</h4>
                        <ol class="text-[11px] text-blue-700 dark:text-blue-300 space-y-1 list-decimal list-inside">
                            <li>Unduh template Excel terlebih dahulu menggunakan tombol di bawah.</li>
                            <li>Isi data guru sesuai format kolom (kolom bertanda <strong>*</strong> wajib diisi).</li>
                            <li>Maksimal <strong>1.000 baris</strong> data per file (ukuran maks <strong>2 MB</strong>).</li>
                            <li>Upload file yang sudah dilengkapi lalu klik <strong>Mulai Import</strong>.</li>
                        </ol>
                    </div>

                    <!-- Download Template -->
                    <button @click="downloadTemplate" type="button" class="w-full flex items-center gap-3 p-3 rounded-lg border-2 border-dashed border-neutral-300 dark:border-zinc-700 hover:border-emerald-400 dark:hover:border-emerald-600 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20 transition-all cursor-pointer group">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center group-hover:bg-emerald-200 dark:group-hover:bg-emerald-800/60 transition-colors">
                            <Download class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Unduh Template Excel</p>
                            <p class="text-[11px] text-neutral-500">template_guru.xlsx — Berisi header dan contoh pengisian</p>
                        </div>
                    </button>

                    <!-- File Upload -->
                    <div class="space-y-2">
                        <Label>Pilih File Excel (.xlsx / .xls)</Label>
                        <div
                            class="relative border-2 border-dashed rounded-lg p-6 text-center transition-all"
                            :class="importFile
                                ? 'border-emerald-400 bg-emerald-50/50 dark:border-emerald-600 dark:bg-emerald-950/20'
                                : 'border-neutral-300 dark:border-zinc-700 hover:border-neutral-400 dark:hover:border-zinc-600'"
                        >
                            <input
                                ref="fileInputRef"
                                type="file"
                                accept=".xlsx,.xls"
                                @change="handleFileChange"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            />
                            <div v-if="!importFile" class="space-y-2">
                                <Upload class="w-8 h-8 text-neutral-400 mx-auto" />
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">Klik atau seret file ke sini</p>
                                <p class="text-[11px] text-neutral-400">Format: .xlsx atau .xls (Maks. 2MB)</p>
                            </div>
                            <div v-else class="flex items-center justify-center gap-2">
                                <FileSpreadsheet class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ importFile.name }}</span>
                                <span class="text-[11px] text-neutral-400">({{ (importFile.size / 1024).toFixed(1) }} KB)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Default Password Warning -->
                    <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-lg p-3 flex items-start gap-2">
                        <AlertTriangle class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="text-xs font-semibold text-amber-800 dark:text-amber-200">Perhatian: Password Default</p>
                            <p class="text-[11px] text-amber-700 dark:text-amber-300 mt-0.5">
                                Semua akun guru yang diimpor akan mendapat password default <code class="bg-amber-100 dark:bg-amber-900/50 px-1 py-0.5 rounded font-mono">password</code>. Pastikan setiap guru mengubah password setelah login pertama.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-neutral-200 dark:border-zinc-800 flex justify-end gap-3 bg-neutral-50 dark:bg-zinc-850">
                    <Button type="button" variant="outline" @click="isImportModalOpen = false">Batal</Button>
                    <Button
                        @click="submitImport"
                        :disabled="!importFile || importProcessing"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold flex items-center gap-1.5"
                    >
                        <Loader2 v-if="importProcessing" class="w-4 h-4 animate-spin" />
                        <Upload v-else class="w-4 h-4" />
                        {{ importProcessing ? 'Memproses...' : 'Mulai Import' }}
                    </Button>
                </div>
            </div>
        </div>

        <!-- Modal Dialog Form -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
            <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-2xl rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                    <h3 class="font-bold text-neutral-800 dark:text-neutral-200">{{ isEditing ? 'Edit Akun Guru' : 'Tambah Akun Guru' }}</h3>
                    <button @click="isModalOpen = false" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 cursor-pointer">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-6 overflow-y-auto space-y-4 flex-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="nama_guru">Nama Lengkap Guru <span class="text-rose-500">*</span></Label>
                            <Input id="nama_guru" v-model="form.nama_guru" required placeholder="Nama Lengkap dengan Gelar" />
                            <InputError :message="form.errors.nama_guru" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="form.email" placeholder="email@domain.com" />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="username">Username Login <span class="text-rose-500">*</span></Label>
                            <Input id="username" v-model="form.username" required placeholder="Username unik" />
                            <InputError :message="form.errors.username" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password">Password <span v-if="!isEditing" class="text-rose-500">*</span></Label>
                            <Input id="password" type="password" v-model="form.password" :required="!isEditing" placeholder="Minimal 8 karakter" />
                            <InputError :message="form.errors.password" />
                            <p v-if="isEditing" class="text-xs text-neutral-400">Kosongkan password jika tidak ingin diubah.</p>
                        </div>

                        <div class="grid gap-2">
                            <Label for="nip">NIP</Label>
                            <Input id="nip" v-model="form.nip" placeholder="NIP Guru" />
                            <InputError :message="form.errors.nip" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="kode_guru">Kode Guru</Label>
                            <Input id="kode_guru" v-model="form.kode_guru" placeholder="Contoh: GR01" />
                            <InputError :message="form.errors.kode_guru" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="no_hp">No HP / WhatsApp</Label>
                            <Input id="no_hp" v-model="form.no_hp" placeholder="Contoh: 0812345678" />
                            <InputError :message="form.errors.no_hp" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="nuptk">NUPTK</Label>
                            <Input id="nuptk" v-model="form.nuptk" placeholder="Nomor NUPTK" />
                            <InputError :message="form.errors.nuptk" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="status_aktif">Status Aktif</Label>
                            <select id="status_aktif" v-model="form.status_aktif" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 dark:border-zinc-800">
                                <option value="Aktif">Aktif</option>
                                <option value="Non-Aktif">Non-Aktif</option>
                            </select>
                            <InputError :message="form.errors.status_aktif" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="alamat">Alamat</Label>
                        <textarea id="alamat" v-model="form.alamat" class="flex min-h-[60px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring dark:border-zinc-800" placeholder="Alamat rumah lengkap"></textarea>
                        <InputError :message="form.errors.alamat" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-zinc-800">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Batal</Button>
                        <Button type="submit" :disabled="form.processing" class="bg-zinc-900 hover:bg-zinc-800 text-white font-semibold">
                            {{ isEditing ? 'Simpan' : 'Tambah' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
