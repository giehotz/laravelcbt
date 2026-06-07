<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Layers,
    Plus,
    X,
    Edit2,
    Trash2,
    UserPlus,
    Users,
    ChevronDown,
} from 'lucide-vue-next';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import {
    store as storeLevel,
    update as updateLevel,
    destroy as destroyLevel,
    generate as generateLevel,
} from '@/routes/master/level-kelas';
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
                title: 'Level Kelas',
                href: '/master/level-kelas',
            },
        ],
    },
});

const props = defineProps<{
    levels: Array<{
        id: number;
        level: string;
        kelas?: Array<{
            id: number;
            nama_kelas: string;
            kode_kelas: string;
            tahun_pelajaran_id: number;
            semester_id: number;
            jurusan_id: number | null;
            level_id: number;
            guru_id: number | null;
            wali_kelas?: { id: number; nama_guru: string } | null;
            tahun_pelajaran?: { id: number; tahun: string } | null;
            semester?: { id: number; nama_smt: string } | null;
            siswa_count?: number;
        }>;
    }>;
    years?: Array<{ id: number; tahun: string }>;
    semesters?: Array<{ id: number; nama_smt: string; smt: string }>;
    jurusans?: Array<{
        id: number;
        nama_jurusan: string;
        kode_jurusan: string;
    }>;
    gurus?: Array<{ id: number; nama_guru: string }>;
}>();

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedLevel = ref<{ id: number; level: string } | null>(null);

const addForm = useForm({
    level: '',
});

const editForm = useForm({
    level: '',
});

// Class Management State
const isAddClassModalOpen = ref(false);
const isEditClassModalOpen = ref(false);
const selectedClass = ref<any>(null);

const addClassForm = useForm({
    nama_kelas: '',
    kode_kelas: '',
    tahun_pelajaran_id: ((props.years && props.years[0]?.id) ?? '') as
        | number
        | '',
    semester_id: ((props.semesters && props.semesters[0]?.id) ?? '') as
        | number
        | '',
    jurusan_id: '' as number | '',
    level_id: '' as number | '',
    guru_id: '' as number | '',
    set_siswa: '0',
});

const editClassForm = useForm({
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
    addForm.post(storeLevel.url(), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (lvl: { id: number; level: string }) => {
    selectedLevel.value = lvl;
    editForm.level = lvl.level;
    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    if (!selectedLevel.value) return;
    editForm.put(updateLevel.url(selectedLevel.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
            selectedLevel.value = null;
        },
    });
};

const handleDelete = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus level kelas ini?')) {
        router.delete(destroyLevel.url(id));
    }
};

const handleGenerate = (type: string) => {
    if (
        confirm(
            `Apakah Anda yakin ingin men-generate otomatis level untuk jenjang ${type.toUpperCase()}?`,
        )
    ) {
        router.post(generateLevel.url(), { type });
    }
};

// Class Form Handlers
const openAddClassModal = (levelId: number) => {
    addClassForm.reset();
    addClassForm.level_id = levelId;
    addClassForm.tahun_pelajaran_id = ((props.years && props.years[0]?.id) ??
        '') as number | '';
    addClassForm.semester_id = ((props.semesters && props.semesters[0]?.id) ??
        '') as number | '';
    addClassForm.clearErrors();
    isAddClassModalOpen.value = true;
};

const submitAddClass = () => {
    addClassForm.post(storeKelas.url(), {
        onSuccess: () => {
            isAddClassModalOpen.value = false;
            addClassForm.reset();
        },
    });
};

const openEditClassModal = (kls: any) => {
    selectedClass.value = kls;
    editClassForm.nama_kelas = kls.nama_kelas;
    editClassForm.kode_kelas = kls.kode_kelas;
    editClassForm.tahun_pelajaran_id = kls.tahun_pelajaran_id;
    editClassForm.semester_id = kls.semester_id;
    editClassForm.jurusan_id = kls.jurusan_id ?? '';
    editClassForm.level_id = kls.level_id;
    editClassForm.guru_id = kls.guru_id ?? '';
    editClassForm.clearErrors();
    isEditClassModalOpen.value = true;
};

const submitEditClass = () => {
    if (!selectedClass.value) return;
    editClassForm.put(updateKelas.url(selectedClass.value.id), {
        onSuccess: () => {
            isEditClassModalOpen.value = false;
            editClassForm.reset();
            selectedClass.value = null;
        },
    });
};

const handleDeleteClass = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus kelas ini?')) {
        router.delete(destroyKelas.url(id));
    }
};
</script>

<template>
    <Head title="Level Kelas" />

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Level Kelas"
                description="Atur tingkatan kelas untuk pembagian rombel siswa (contoh: VII, VIII, IX, X, XI, XII)."
            />
            <div class="flex gap-3">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="outline"
                            class="flex items-center gap-2 border-zinc-200 font-bold shadow-sm transition-all hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800"
                        >
                            <Layers class="h-4 w-4 text-zinc-500" />
                            <span>Template Level</span>
                            <ChevronDown class="h-4 w-4 opacity-50" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuLabel>Pilih Jenjang</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="handleGenerate('sd')">
                            SD / MI (1-6)
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="handleGenerate('smp')">
                            SMP / MTs (7-9)
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="handleGenerate('sma')">
                            SMA / MA (10-12)
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <Button
                    @click="isAddModalOpen = true"
                    class="flex items-center gap-2 bg-indigo-600 font-bold text-white shadow-md transition-all hover:bg-indigo-700 active:scale-95 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                >
                    <Plus class="h-4 w-4" />
                    <span>Tambah Level</span>
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8">
            <div
                v-for="(lvl, idx) in levels"
                :key="lvl.id"
                class="flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition-all dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-zinc-200 bg-zinc-50/50 px-6 py-5 dark:border-zinc-800 dark:bg-zinc-800/30"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-100 text-sm font-black text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400"
                        >
                            {{ idx + 1 }}
                        </div>
                        <h3
                            class="text-lg font-black text-zinc-900 dark:text-zinc-100"
                        >
                            Level {{ lvl.level }}
                        </h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <Button
                            size="sm"
                            @click="openAddClassModal(lvl.id)"
                            class="flex items-center gap-1.5 bg-zinc-900 font-bold text-white shadow-sm transition-all hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                        >
                            <Plus class="h-4 w-4" />
                            <span>Tambah Rombel</span>
                        </Button>
                        <Button
                            size="sm"
                            variant="outline"
                            @click="openEditModal(lvl)"
                            class="flex items-center gap-1.5 border-amber-200 bg-amber-50 font-bold text-amber-700 shadow-sm transition-all hover:bg-amber-100 hover:text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-400 dark:hover:bg-amber-950/30"
                        >
                            <Edit2 class="h-3.5 w-3.5" />
                            <span>Edit Level</span>
                        </Button>
                        <Button
                            size="sm"
                            variant="outline"
                            @click="handleDelete(lvl.id)"
                            class="flex items-center gap-1.5 border-rose-200 bg-rose-50 font-bold text-rose-700 shadow-sm transition-all hover:bg-rose-100 hover:text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/20 dark:text-rose-400 dark:hover:bg-rose-950/30"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                            <span>Hapus</span>
                        </Button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-zinc-200 bg-zinc-50/30 text-[10px] font-black tracking-[0.1em] text-zinc-500 uppercase dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <th class="w-16 px-6 py-4 text-center">No</th>
                                <th class="px-6 py-4">Nama Rombel / Kelas</th>
                                <th class="px-6 py-4">Kode Kelas</th>
                                <th class="px-6 py-4">Wali Kelas</th>
                                <th class="w-32 px-6 py-4 text-center">
                                    Siswa
                                </th>
                                <th class="w-64 px-6 py-4 text-right">Manajemen</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-zinc-200 text-sm dark:divide-zinc-800"
                        >
                            <tr
                                v-for="(kls, kIdx) in lvl.kelas"
                                :key="kls.id"
                                class="transition-colors hover:bg-zinc-50/30 dark:hover:bg-zinc-800/20"
                            >
                                <td
                                    class="px-6 py-4 text-center font-bold text-zinc-400 dark:text-zinc-500"
                                >
                                    {{ kIdx + 1 }}
                                </td>
                                <td
                                    class="px-6 py-4 font-black text-zinc-900 dark:text-zinc-100"
                                >
                                    {{ kls.nama_kelas }}
                                </td>
                                <td
                                    class="px-6 py-4 font-mono text-[11px] font-black tracking-tight text-zinc-500 dark:text-zinc-400"
                                >
                                    {{ kls.kode_kelas }}
                                </td>
                                <td
                                    class="px-6 py-4 font-bold text-zinc-700 dark:text-zinc-300"
                                >
                                    {{ kls.wali_kelas?.nama_guru ?? '-' }}
                                </td>
                                <td
                                    class="px-6 py-4 text-center"
                                >
                                    <span class="inline-flex h-6 min-w-[2rem] items-center justify-center rounded-full bg-zinc-100 px-2 text-[11px] font-black text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ kls.siswa_count ?? 0 }}
                                    </span>
                                </td>
                                <td
                                    class="space-x-2 px-6 py-4 text-right whitespace-nowrap"
                                >
                                    <Link
                                        :href="students.edit.url(kls.id)"
                                        class="inline-flex h-8 items-center justify-center rounded-lg bg-indigo-600 px-3 py-1.5 text-[11px] font-black uppercase tracking-wider text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95"
                                    >
                                        <Users class="mr-1.5 h-3.5 w-3.5" />
                                        Siswa
                                    </Link>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openEditClassModal(kls)"
                                        class="h-8 border-amber-200 bg-amber-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-wider text-amber-700 transition-all hover:bg-amber-100 hover:text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-400 dark:hover:bg-amber-950/30"
                                    >
                                        <Edit2 class="mr-1.5 h-3.5 w-3.5" />
                                        Edit
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="handleDeleteClass(kls.id)"
                                        class="h-8 border-rose-200 bg-rose-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-wider text-rose-700 transition-all hover:bg-rose-100 hover:text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/20 dark:text-rose-400 dark:hover:bg-rose-950/30"
                                    >
                                        <Trash2 class="mr-1.5 h-3.5 w-3.5" />
                                        Hapus
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="!lvl.kelas || lvl.kelas.length === 0">
                                <td
                                    colspan="6"
                                    class="px-6 py-12 text-center text-zinc-500 italic dark:text-zinc-500"
                                >
                                    Belum ada data rombel/kelas pada level ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                v-if="levels.length === 0"
                class="rounded-2xl border border-zinc-200 bg-white p-16 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <Layers class="h-6 w-6 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-sm font-bold text-zinc-900 dark:text-zinc-100">Belum Ada Data Level Kelas</h3>
                <p class="mt-1 text-xs text-zinc-500">Silakan gunakan tombol "Tambah Level" atau "Template Level" di atas.</p>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah Level -->
    <div
        v-if="isAddModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
            >
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">
                    Tambah Level Kelas
                </h3>
                <button
                    @click="isAddModalOpen = false"
                    class="hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer rounded-lg p-1 text-neutral-400"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitAdd" class="space-y-4 p-6">
                <div class="grid gap-2">
                    <Label for="level"
                        >Level Kelas <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="level"
                        v-model="addForm.level"
                        required
                        placeholder="Contoh: VII, VIII, X, XI"
                    />
                    <InputError :message="addForm.errors.level" />
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
                        class="bg-indigo-600 font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:text-white dark:hover:bg-indigo-600 shadow-md transition-all active:scale-95"
                    >
                        Simpan Level
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Edit Level -->
    <div
        v-if="isEditModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-zinc-200 px-6 py-5 dark:border-zinc-800"
            >
                <h3 class="font-black text-zinc-900 dark:text-zinc-100">
                    Edit Level Kelas
                </h3>
                <button
                    @click="isEditModalOpen = false"
                    class="hover:text-zinc-600 dark:hover:text-zinc-300 cursor-pointer rounded-lg p-1 text-zinc-400 transition-colors"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitEdit" class="space-y-5 p-6">
                <div class="grid gap-2">
                    <Label for="edit_level" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                        >Level Kelas <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="edit_level"
                        v-model="editForm.level"
                        required
                        placeholder="Contoh: VII, VIII, X, XI"
                        class="h-11 font-bold focus:ring-4 focus:ring-indigo-500/10"
                    />
                    <InputError :message="editForm.errors.level" />
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-zinc-100 pt-5 dark:border-zinc-800"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        @click="isEditModalOpen = false"
                        class="font-bold text-zinc-500 hover:text-zinc-900"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="editForm.processing"
                        class="bg-indigo-600 font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:text-white dark:hover:bg-indigo-600 shadow-md transition-all active:scale-95"
                    >
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Tambah Rombel / Kelas -->
    <div
        v-if="isAddClassModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-zinc-200 px-6 py-5 dark:border-zinc-800"
            >
                <h3 class="font-black text-zinc-900 dark:text-zinc-100">
                    Tambah Rombel / Kelas
                </h3>
                <button
                    @click="isAddClassModalOpen = false"
                    class="hover:text-zinc-600 dark:hover:text-zinc-300 cursor-pointer rounded-lg p-1 text-zinc-400 transition-colors"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form
                @submit.prevent="submitAddClass"
                class="flex-1 space-y-5 overflow-y-auto p-6"
            >
                <div class="grid grid-cols-2 gap-5">
                    <div class="grid gap-2">
                        <Label for="level_id" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >Level Tingkat
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="level_id"
                            v-model="addClassForm.level_id"
                            required
                            class="flex h-11 w-full cursor-pointer rounded-lg border-2 border-zinc-100 bg-zinc-50/50 px-3 py-2 text-sm font-bold shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                            <option
                                v-for="lvl in levels"
                                :key="lvl.id"
                                :value="lvl.id"
                            >
                                {{ lvl.level }}
                            </option>
                        </select>
                        <InputError :message="addClassForm.errors.level_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="jurusan_id" class="text-xs font-bold uppercase tracking-wider text-zinc-500">Jurusan / Peminatan</Label>
                        <select
                            id="jurusan_id"
                            v-model="addClassForm.jurusan_id"
                            class="flex h-11 w-full cursor-pointer rounded-lg border-2 border-zinc-100 bg-zinc-50/50 px-3 py-2 text-sm font-bold shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
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
                        <InputError :message="addClassForm.errors.jurusan_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div class="grid gap-2">
                        <Label for="tahun_pelajaran_id" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >Tahun Pelajaran
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="tahun_pelajaran_id"
                            v-model="addClassForm.tahun_pelajaran_id"
                            required
                            class="flex h-11 w-full cursor-pointer rounded-lg border-2 border-zinc-100 bg-zinc-50/50 px-3 py-2 text-sm font-bold shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
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
                            :message="addClassForm.errors.tahun_pelajaran_id"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="semester_id" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >Semester
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="semester_id"
                            v-model="addClassForm.semester_id"
                            required
                            class="flex h-11 w-full cursor-pointer rounded-lg border-2 border-zinc-100 bg-zinc-50/50 px-3 py-2 text-sm font-bold shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                            <option
                                v-for="s in semesters"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.nama_smt }} ({{ s.smt }})
                            </option>
                        </select>
                        <InputError
                            :message="addClassForm.errors.semester_id"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div class="grid gap-2">
                        <Label for="nama_kelas" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >Nama Rombel / Kelas
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="nama_kelas"
                            v-model="addClassForm.nama_kelas"
                            required
                            placeholder="Contoh: X IPA 1"
                            class="h-11 font-bold focus:ring-4 focus:ring-indigo-500/10"
                        />
                        <InputError :message="addClassForm.errors.nama_kelas" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="kode_kelas" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >Kode Kelas
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="kode_kelas"
                            v-model="addClassForm.kode_kelas"
                            required
                            placeholder="Contoh: X-IPA-1"
                            class="h-11 font-bold focus:ring-4 focus:ring-indigo-500/10"
                        />
                        <InputError :message="addClassForm.errors.kode_kelas" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="guru_id" class="text-xs font-bold uppercase tracking-wider text-zinc-500">Wali Kelas / Homeroom Teacher</Label>
                    <select
                        id="guru_id"
                        v-model="addClassForm.guru_id"
                        class="flex h-11 w-full cursor-pointer rounded-lg border-2 border-zinc-100 bg-zinc-50/50 px-3 py-2 text-sm font-bold shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        <option value="">Pilih Wali Kelas (Pilihan)</option>
                        <option v-for="g in gurus" :key="g.id" :value="g.id">
                            {{ g.nama_guru }}
                        </option>
                    </select>
                    <InputError :message="addClassForm.errors.guru_id" />
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-zinc-100 pt-5 dark:border-zinc-800"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        @click="isAddClassModalOpen = false"
                        class="font-bold text-zinc-500 hover:text-zinc-900"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="addClassForm.processing"
                        class="bg-indigo-600 font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:text-white dark:hover:bg-indigo-600 shadow-md transition-all active:scale-95"
                    >
                        Simpan Rombel
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Edit Rombel / Kelas -->
    <div
        v-if="isEditClassModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-zinc-200 px-6 py-5 dark:border-zinc-800"
            >
                <h3 class="font-black text-zinc-900 dark:text-zinc-100">
                    Edit Rombel / Kelas
                </h3>
                <button
                    @click="isEditClassModalOpen = false"
                    class="hover:text-zinc-600 dark:hover:text-zinc-300 cursor-pointer rounded-lg p-1 text-zinc-400 transition-colors"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form
                @submit.prevent="submitEditClass"
                class="flex-1 space-y-5 overflow-y-auto p-6"
            >
                <div class="grid grid-cols-2 gap-5">
                    <div class="grid gap-2">
                        <Label for="edit_level_id" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >Level Tingkat
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="edit_level_id"
                            v-model="editClassForm.level_id"
                            required
                            class="flex h-11 w-full cursor-pointer rounded-lg border-2 border-zinc-100 bg-zinc-50/50 px-3 py-2 text-sm font-bold shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                            <option
                                v-for="lvl in levels"
                                :key="lvl.id"
                                :value="lvl.id"
                            >
                                {{ lvl.level }}
                            </option>
                        </select>
                        <InputError :message="editClassForm.errors.level_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_jurusan_id" class="text-xs font-bold uppercase tracking-wider text-zinc-500">Jurusan / Peminatan</Label>
                        <select
                            id="edit_jurusan_id"
                            v-model="editClassForm.jurusan_id"
                            class="flex h-11 w-full cursor-pointer rounded-lg border-2 border-zinc-100 bg-zinc-50/50 px-3 py-2 text-sm font-bold shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
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
                        <InputError
                            :message="editClassForm.errors.jurusan_id"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div class="grid gap-2">
                        <Label for="edit_tahun_pelajaran_id" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >Tahun Pelajaran
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="edit_tahun_pelajaran_id"
                            v-model="editClassForm.tahun_pelajaran_id"
                            required
                            class="flex h-11 w-full cursor-pointer rounded-lg border-2 border-zinc-100 bg-zinc-50/50 px-3 py-2 text-sm font-bold shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
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
                            :message="editClassForm.errors.tahun_pelajaran_id"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_semester_id" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >Semester
                            <span class="text-rose-500">*</span></Label
                        >
                        <select
                            id="edit_semester_id"
                            v-model="editClassForm.semester_id"
                            required
                            class="flex h-11 w-full cursor-pointer rounded-lg border-2 border-zinc-100 bg-zinc-50/50 px-3 py-2 text-sm font-bold shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                        >
                            <option
                                v-for="s in semesters"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.nama_smt }} ({{ s.smt }})
                            </option>
                        </select>
                        <InputError
                            :message="editClassForm.errors.semester_id"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div class="grid gap-2">
                        <Label for="edit_nama_kelas" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >Nama Rombel / Kelas
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="edit_nama_kelas"
                            v-model="editClassForm.nama_kelas"
                            required
                            placeholder="Contoh: X IPA 1"
                            class="h-11 font-bold focus:ring-4 focus:ring-indigo-500/10"
                        />
                        <InputError
                            :message="editClassForm.errors.nama_kelas"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_kode_kelas" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                            >Kode Kelas
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="edit_kode_kelas"
                            v-model="editClassForm.kode_kelas"
                            required
                            placeholder="Contoh: X-IPA-1"
                            class="h-11 font-bold focus:ring-4 focus:ring-indigo-500/10"
                        />
                        <InputError
                            :message="editClassForm.errors.kode_kelas"
                        />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="edit_guru_id" class="text-xs font-bold uppercase tracking-wider text-zinc-500"
                        >Wali Kelas / Homeroom Teacher</Label
                    >
                    <select
                        id="edit_guru_id"
                        v-model="editClassForm.guru_id"
                        class="flex h-11 w-full cursor-pointer rounded-lg border-2 border-zinc-100 bg-zinc-50/50 px-3 py-2 text-sm font-bold shadow-sm transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                        <option value="">Pilih Wali Kelas (Pilihan)</option>
                        <option v-for="g in gurus" :key="g.id" :value="g.id">
                            {{ g.nama_guru }}
                        </option>
                    </select>
                    <InputError :message="editClassForm.errors.guru_id" />
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-zinc-100 pt-5 dark:border-zinc-800"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        @click="isEditClassModalOpen = false"
                        class="font-bold text-zinc-500 hover:text-zinc-900"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="editClassForm.processing"
                        class="bg-indigo-600 font-bold text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:text-white dark:hover:bg-indigo-600 shadow-md transition-all active:scale-95"
                    >
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
