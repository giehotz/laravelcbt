<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Users, Plus, X, Edit2, Trash2, UserPlus } from 'lucide-vue-next';

import {
    store as storeKelas,
    update as updateKelas,
    destroy as destroyKelas,
} from '@/routes/master/kelas';
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
        ],
    },
});

const props = defineProps<{
    kelas: {
        data: Array<{
            id: number;
            nama_kelas: string;
            kode_kelas: string;
            tahun_pelajaran_id: number;
            semester_id: number;
            jurusan_id: number | null;
            level_id: number;
            guru_id: number | null;
            level_kelas?: { level: string };
            jurusan?: { nama_jurusan: string; kode_jurusan: string };
            wali_kelas?: { nama_guru: string };
            tahun_pelajaran?: { tahun: string };
            semester?: { nama_smt: string };
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    years: Array<{ id: number; tahun: string }>;
    semesters: Array<{ id: number; nama_smt: string; smt: string }>;
    jurusans: Array<{ id: number; nama_jurusan: string; kode_jurusan: string }>;
    levels: Array<{ id: number; level: string }>;
    gurus: Array<{ id: number; nama_guru: string }>;
}>();

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedKelas = ref<(typeof props.kelas.data)[0] | null>(null);

const addForm = useForm({
    nama_kelas: '',
    kode_kelas: '',
    tahun_pelajaran_id: (props.years[0]?.id ?? '') as number | '',
    semester_id: (props.semesters[0]?.id ?? '') as number | '',
    jurusan_id: '' as number | '',
    level_id: (props.levels[0]?.id ?? '') as number | '',
    guru_id: '' as number | '',
    set_siswa: '0',
});

const editForm = useForm({
    nama_kelas: '',
    kode_kelas: '',
    tahun_pelajaran_id: '' as number | '',
    semester_id: '' as number | '',
    jurusan_id: '' as number | '',
    level_id: '' as number | '',
    guru_id: '' as number | '',
    set_siswa: '0',
});

const submitAdd = () => {
    addForm.post(storeKelas.url(), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (kls: (typeof props.kelas.data)[0]) => {
    selectedKelas.value = kls;
    editForm.nama_kelas = kls.nama_kelas;
    editForm.kode_kelas = kls.kode_kelas;
    editForm.tahun_pelajaran_id = kls.tahun_pelajaran_id;
    editForm.semester_id = kls.semester_id;
    editForm.jurusan_id = kls.jurusan_id ?? '';
    editForm.level_id = kls.level_id;
    editForm.guru_id = kls.guru_id ?? '';
    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    if (!selectedKelas.value) return;
    editForm.put(updateKelas.url(selectedKelas.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
            selectedKelas.value = null;
        },
    });
};

const handleDelete = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus kelas ini?')) {
        router.delete(destroyKelas.url(id));
    }
};
</script>

<template>
    <Head title="Rombel & Kelas" />

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">
        <Heading
            title="Rombel / Kelas"
            description="Atur rombongan belajar (rombel) siswa, tingkat kelas, jurusan, wali kelas, serta pembagian siswa."
        />

        <div
            class="flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition-all dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
            >
                <div class="flex items-center gap-2">
                    <Users class="h-5 w-5 text-zinc-500" />
                    <h3
                        class="text-sm font-bold tracking-wider text-neutral-800 uppercase dark:text-neutral-200"
                    >
                        Daftar Rombel / Kelas
                    </h3>
                </div>
                <Button
                    @click="isAddModalOpen = true"
                    size="sm"
                    class="flex items-center gap-1 bg-zinc-900 font-semibold text-white shadow-sm transition-colors hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                >
                    <Plus class="h-4 w-4" />
                    <span>Tambah Rombel</span>
                </Button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50/30 text-[11px] font-bold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-900/50"
                        >
                            <th class="w-14 px-4 py-3.5 text-center">No</th>
                            <th class="px-4 py-3.5">Nama Kelas</th>
                            <th class="w-24 px-4 py-3.5">Tingkat</th>
                            <th class="w-32 px-4 py-3.5">Jurusan</th>
                            <th class="px-4 py-3.5">Wali Kelas</th>
                            <th class="w-32 px-4 py-3.5">Periode</th>
                            <th class="w-64 px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                    >
                        <tr
                            v-for="(kls, idx) in kelas.data"
                            :key="kls.id"
                            class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20"
                        >
                            <td
                                class="px-4 py-4 text-center font-medium text-neutral-400 dark:text-neutral-500"
                            >
                                {{ idx + 1 }}
                            </td>
                            <td
                                class="px-4 py-4 font-bold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ kls.nama_kelas }}
                                <span
                                    class="font-mono text-xs font-semibold text-neutral-400"
                                    >({{ kls.kode_kelas }})</span
                                >
                            </td>
                            <td
                                class="px-4 py-4 font-semibold text-neutral-600 dark:text-neutral-400"
                            >
                                {{ kls.level_kelas?.level ?? '-' }}
                            </td>
                            <td
                                class="px-4 py-4 font-semibold text-neutral-600 dark:text-neutral-400"
                            >
                                {{ kls.jurusan?.nama_jurusan ?? 'Umum' }}
                            </td>
                            <td
                                class="px-4 py-4 font-semibold text-neutral-600 dark:text-neutral-400"
                            >
                                {{ kls.wali_kelas?.nama_guru ?? '-' }}
                            </td>
                            <td
                                class="px-4 py-4 text-xs text-neutral-500 dark:text-neutral-400"
                            >
                                <div>
                                    {{ kls.tahun_pelajaran?.tahun ?? '-' }}
                                </div>
                                <div class="font-semibold">
                                    {{ kls.semester?.nama_smt ?? '-' }}
                                </div>
                            </td>
                            <td
                                class="space-x-1.5 px-4 py-4 text-right whitespace-nowrap"
                            >
                                <Link
                                    :href="students.edit.url(kls.id)"
                                    class="inline-flex h-8 items-center justify-center rounded bg-blue-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-blue-700"
                                >
                                    <UserPlus class="mr-1 h-3.5 w-3.5" />
                                    Siswa
                                </Link>
                                <Button
                                    size="sm"
                                    @click="openEditModal(kls)"
                                    class="h-8 rounded bg-amber-500 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700"
                                >
                                    <Edit2 class="mr-1 h-3.5 w-3.5" />
                                    Edit
                                </Button>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="handleDelete(kls.id)"
                                    class="h-8 rounded bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800"
                                >
                                    <Trash2 class="mr-1 h-3.5 w-3.5" />
                                    Hapus
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="kelas.data.length === 0">
                            <td
                                colspan="7"
                                class="px-6 py-8 text-center text-neutral-500"
                            >
                                Belum ada data rombel/kelas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Simple Pagination -->
            <div
                v-if="kelas.links && kelas.links.length > 3"
                class="flex items-center justify-between border-t border-neutral-200 bg-neutral-50/20 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/20"
            >
                <div class="flex items-center gap-1">
                    <template v-for="link in kelas.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded border px-3 py-1 text-xs font-semibold transition-all"
                            :class="
                                link.active
                                    ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-50 dark:bg-zinc-50 dark:text-zinc-950'
                                    : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800'
                            "
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="cursor-not-allowed rounded border border-neutral-100 px-3 py-1 text-xs font-semibold text-neutral-300 select-none dark:border-zinc-800/40 dark:text-zinc-600"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah Rombel -->
    <div
        v-if="isAddModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
            >
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">
                    Tambah Rombel / Kelas
                </h3>
                <button
                    @click="isAddModalOpen = false"
                    class="hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer rounded-lg p-1 text-neutral-400"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form
                @submit.prevent="submitAdd"
                class="flex-1 space-y-4 overflow-y-auto p-6"
            >
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="level_id"
                            >Level Tingkat
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="level_id"
                            v-model="addForm.level_id"
                            required
                            class="dark:border-zinc-850 flex h-10 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900"
                        >
                            <option
                                v-for="lvl in levels"
                                :key="lvl.id"
                                :value="lvl.id"
                            >
                                {{ lvl.level }}
                            </option>
                        </select>
                        <InputError :message="addForm.errors.level_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="jurusan_id">Jurusan / Peminatan</Label>
                        <select
                            id="jurusan_id"
                            v-model="addForm.jurusan_id"
                            class="dark:border-zinc-850 flex h-10 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900"
                        >
                            <option value="">Umum (Tanpa Jurusan)</option>
                            <option
                                v-for="jur in jurusans"
                                :key="jur.id"
                                :value="jur.id"
                            >
                                {{ jur.nama_jurusan }} ({{ jur.kode_jurusan }})
                            </option>
                        </select>
                        <InputError :message="addForm.errors.jurusan_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="tahun_pelajaran_id"
                            >Tahun Pelajaran
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="tahun_pelajaran_id"
                            v-model="addForm.tahun_pelajaran_id"
                            required
                            class="dark:border-zinc-850 flex h-10 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900"
                        >
                            <option
                                v-for="y in years"
                                :key="y.id"
                                :value="y.id"
                            >
                                {{ y.tahun }}
                            </option>
                        </select>
                        <InputError
                            :message="addForm.errors.tahun_pelajaran_id"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="semester_id"
                            >Semester
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="semester_id"
                            v-model="addForm.semester_id"
                            required
                            class="dark:border-zinc-850 flex h-10 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900"
                        >
                            <option
                                v-for="s in semesters"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.nama_smt }} ({{ s.smt }})
                            </option>
                        </select>
                        <InputError :message="addForm.errors.semester_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="nama_kelas"
                            >Nama Rombel / Kelas
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="nama_kelas"
                            v-model="addForm.nama_kelas"
                            required
                            placeholder="Contoh: X IPA 1"
                        />
                        <InputError :message="addForm.errors.nama_kelas" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="kode_kelas"
                            >Kode Kelas
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="kode_kelas"
                            v-model="addForm.kode_kelas"
                            required
                            placeholder="Contoh: X-IPA-1"
                        />
                        <InputError :message="addForm.errors.kode_kelas" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="guru_id">Wali Kelas / Homeroom Teacher</Label>
                    <select
                        id="guru_id"
                        v-model="addForm.guru_id"
                        class="dark:border-zinc-850 flex h-10 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900"
                    >
                        <option value="">Pilih Wali Kelas (Pilihan)</option>
                        <option v-for="g in gurus" :key="g.id" :value="g.id">
                            {{ g.nama_guru }}
                        </option>
                    </select>
                    <InputError :message="addForm.errors.guru_id" />
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-zinc-800"
                >
                    <Button
                        type="button"
                        variant="outline"
                        @click="isAddModalOpen = false"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="addForm.processing"
                        class="bg-zinc-900 font-semibold text-white hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                    >
                        Simpan
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Edit Rombel -->
    <div
        v-if="isEditModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
            >
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">
                    Edit Rombel / Kelas
                </h3>
                <button
                    @click="isEditModalOpen = false"
                    class="hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer rounded-lg p-1 text-neutral-400"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form
                @submit.prevent="submitEdit"
                class="flex-1 space-y-4 overflow-y-auto p-6"
            >
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_level_id"
                            >Level Tingkat
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="edit_level_id"
                            v-model="editForm.level_id"
                            required
                            class="dark:border-zinc-850 flex h-10 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900"
                        >
                            <option
                                v-for="lvl in levels"
                                :key="lvl.id"
                                :value="lvl.id"
                            >
                                {{ lvl.level }}
                            </option>
                        </select>
                        <InputError :message="editForm.errors.level_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_jurusan_id">Jurusan / Peminatan</Label>
                        <select
                            id="edit_jurusan_id"
                            v-model="editForm.jurusan_id"
                            class="dark:border-zinc-850 flex h-10 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900"
                        >
                            <option value="">Umum (Tanpa Jurusan)</option>
                            <option
                                v-for="jur in jurusans"
                                :key="jur.id"
                                :value="jur.id"
                            >
                                {{ jur.nama_jurusan }} ({{ jur.kode_jurusan }})
                            </option>
                        </select>
                        <InputError :message="editForm.errors.jurusan_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_tahun_pelajaran_id"
                            >Tahun Pelajaran
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="edit_tahun_pelajaran_id"
                            v-model="editForm.tahun_pelajaran_id"
                            required
                            class="dark:border-zinc-850 flex h-10 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900"
                        >
                            <option
                                v-for="y in years"
                                :key="y.id"
                                :value="y.id"
                            >
                                {{ y.tahun }}
                            </option>
                        </select>
                        <InputError
                            :message="editForm.errors.tahun_pelajaran_id"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_semester_id"
                            >Semester
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="edit_semester_id"
                            v-model="editForm.semester_id"
                            required
                            class="dark:border-zinc-850 flex h-10 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900"
                        >
                            <option
                                v-for="s in semesters"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.nama_smt }} ({{ s.smt }})
                            </option>
                        </select>
                        <InputError :message="editForm.errors.semester_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_nama_kelas"
                            >Nama Rombel / Kelas
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="edit_nama_kelas"
                            v-model="editForm.nama_kelas"
                            required
                            placeholder="Contoh: X IPA 1"
                        />
                        <InputError :message="editForm.errors.nama_kelas" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_kode_kelas"
                            >Kode Kelas
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="edit_kode_kelas"
                            v-model="editForm.kode_kelas"
                            required
                            placeholder="Contoh: X-IPA-1"
                        />
                        <InputError :message="editForm.errors.kode_kelas" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="edit_guru_id"
                        >Wali Kelas / Homeroom Teacher</Label
                    >
                    <select
                        id="edit_guru_id"
                        v-model="editForm.guru_id"
                        class="dark:border-zinc-850 flex h-10 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900"
                    >
                        <option value="">Pilih Wali Kelas (Pilihan)</option>
                        <option v-for="g in gurus" :key="g.id" :value="g.id">
                            {{ g.nama_guru }}
                        </option>
                    </select>
                    <InputError :message="editForm.errors.guru_id" />
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-zinc-800"
                >
                    <Button
                        type="button"
                        variant="outline"
                        @click="isEditModalOpen = false"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="editForm.processing"
                        class="bg-zinc-900 font-semibold text-white hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                    >
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
