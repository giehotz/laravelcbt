<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Layers, Plus, X, Edit2, Trash2, UserPlus, Users, ChevronDown } from 'lucide-vue-next';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import { store as storeLevel, update as updateLevel, destroy as destroyLevel, generate as generateLevel } from '@/routes/master/level-kelas';
import { store as storeKelas, update as updateKelas, destroy as destroyKelas } from '@/routes/master/kelas';
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
    jurusans?: Array<{ id: number; nama_jurusan: string; kode_jurusan: string }>;
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
    tahun_pelajaran_id: ((props.years && props.years[0]?.id) ?? '') as number | '',
    semester_id: ((props.semesters && props.semesters[0]?.id) ?? '') as number | '',
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
    if (confirm(`Apakah Anda yakin ingin men-generate otomatis level untuk jenjang ${type.toUpperCase()}?`)) {
        router.post(generateLevel.url(), { type });
    }
};

// Class Form Handlers
const openAddClassModal = (levelId: number) => {
    addClassForm.reset();
    addClassForm.level_id = levelId;
    addClassForm.tahun_pelajaran_id = ((props.years && props.years[0]?.id) ?? '') as number | '';
    addClassForm.semester_id = ((props.semesters && props.semesters[0]?.id) ?? '') as number | '';
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

    <div class="px-6 py-6 max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <Heading
                title="Level Kelas"
                description="Atur tingkatan kelas untuk pembagian rombel siswa (contoh: VII, VIII, IX, X, XI, XII)."
            />
            <div class="flex gap-2">
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="outline" class="flex items-center gap-1 shadow-sm font-semibold">
                            <Layers class="w-4 h-4" />
                            <span>Template Level</span>
                            <ChevronDown class="w-4 h-4 ml-1 opacity-50" />
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

                <Button @click="isAddModalOpen = true" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold flex items-center gap-1 shadow-sm transition-colors">
                    <Plus class="w-4 h-4" />
                    <span>Tambah Level</span>
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div v-for="(lvl, idx) in levels" :key="lvl.id" class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col transition-all">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center bg-neutral-50/50 dark:bg-zinc-800/30">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-zinc-700 dark:text-zinc-300 text-sm">
                            {{ idx + 1 }}
                        </div>
                        <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-base">Level {{ lvl.level }}</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            size="sm"
                            @click="openAddClassModal(lvl.id)"
                            class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold flex items-center gap-1 shadow-sm transition-colors"
                        >
                            <Plus class="w-3.5 h-3.5" />
                            <span>Tambah Rombel</span>
                        </Button>
                        <Button
                            size="sm"
                            @click="openEditModal(lvl)"
                            class="bg-amber-555 hover:bg-amber-600 text-white dark:bg-amber-600 dark:hover:bg-amber-700 font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                        >
                            <Edit2 class="w-3.5 h-3.5 mr-1" />
                            Edit Level
                        </Button>
                        <Button
                            size="sm"
                            variant="destructive"
                            @click="handleDelete(lvl.id)"
                            class="bg-rose-600 hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800 text-white font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                        >
                            <Trash2 class="w-3.5 h-3.5 mr-1" />
                            Hapus
                        </Button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                                <th class="px-6 py-3 w-16 text-center">No</th>
                                <th class="px-6 py-3">Nama Kelas</th>
                                <th class="px-6 py-3">Kode Kelas</th>
                                <th class="px-6 py-3">Wali Kelas</th>
                                <th class="px-6 py-3 w-32 text-center">Jumlah Siswa</th>
                                <th class="px-6 py-3 text-right w-64">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                            <tr v-for="(kls, kIdx) in lvl.kelas" :key="kls.id" class="hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                                <td class="px-6 py-3.5 text-center font-medium text-neutral-400 dark:text-neutral-500">{{ kIdx + 1 }}</td>
                                <td class="px-6 py-3.5 font-bold text-neutral-800 dark:text-neutral-200">{{ kls.nama_kelas }}</td>
                                <td class="px-6 py-3.5 font-mono text-xs font-semibold text-neutral-500 dark:text-neutral-405">{{ kls.kode_kelas }}</td>
                                <td class="px-6 py-3.5 font-semibold text-neutral-600 dark:text-neutral-400">{{ kls.wali_kelas?.nama_guru ?? '-' }}</td>
                                <td class="px-6 py-3.5 text-center font-bold text-neutral-700 dark:text-neutral-305">{{ kls.siswa_count ?? 0 }}</td>
                                <td class="px-6 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                    <Link
                                        :href="students.edit.url(kls.id)"
                                        class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                    >
                                        <UserPlus class="w-3.5 h-3.5 mr-1" />
                                        Siswa
                                    </Link>
                                    <Button
                                        size="sm"
                                        @click="openEditClassModal(kls)"
                                        class="bg-amber-500 hover:bg-amber-600 text-white dark:bg-amber-600 dark:hover:bg-amber-700 font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                    >
                                        <Edit2 class="w-3.5 h-3.5 mr-1" />
                                        Edit
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        @click="handleDeleteClass(kls.id)"
                                        class="bg-rose-600 hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800 text-white font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                    >
                                        <Trash2 class="w-3.5 h-3.5 mr-1" />
                                        Hapus
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="!lvl.kelas || lvl.kelas.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-neutral-500">
                                    Belum ada data rombel/kelas pada level ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="levels.length === 0" class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl p-8 text-center text-neutral-500">
                Belum ada data level kelas. Silakan tambah level kelas baru.
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah Level -->
    <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Tambah Level Kelas</h3>
                <button @click="isAddModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitAdd" class="p-6 space-y-4">
                <div class="grid gap-2">
                    <Label for="level">Level Kelas <span class="text-rose-500">*</span></Label>
                    <Input id="level" v-model="addForm.level" required placeholder="Contoh: VII, VIII, X, XI" />
                    <InputError :message="addForm.errors.level" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-zinc-800">
                    <Button type="button" variant="outline" @click="isAddModalOpen = false">Batal</Button>
                    <Button type="submit" :disabled="addForm.processing" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold">
                        Simpan
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Edit Level -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Edit Level Kelas</h3>
                <button @click="isEditModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                <div class="grid gap-2">
                    <Label for="edit_level">Level Kelas <span class="text-rose-500">*</span></Label>
                    <Input id="edit_level" v-model="editForm.level" required placeholder="Contoh: VII, VIII, X, XI" />
                    <InputError :message="editForm.errors.level" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-zinc-800">
                    <Button type="button" variant="outline" @click="isEditModalOpen = false">Batal</Button>
                    <Button type="submit" :disabled="editForm.processing" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold">
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Tambah Rombel / Kelas -->
    <div v-if="isAddClassModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Tambah Rombel / Kelas</h3>
                <button @click="isAddClassModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitAddClass" class="p-6 space-y-4 overflow-y-auto flex-1">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="level_id">Level Tingkat <span class="text-rose-500">*</span></Label>
                        <select id="level_id" v-model="addClassForm.level_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-800 cursor-pointer">
                            <option v-for="lvl in levels" :key="lvl.id" :value="lvl.id">{{ lvl.level }}</option>
                        </select>
                        <InputError :message="addClassForm.errors.level_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="jurusan_id">Jurusan / Peminatan</Label>
                        <select id="jurusan_id" v-model="addClassForm.jurusan_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-800 cursor-pointer">
                            <option value="">Umum (Tanpa Jurusan)</option>
                            <option v-for="jur in jurusans" :key="jur.id" :value="jur.id">{{ jur.nama_jurusan }} ({{ jur.kode_jurusan }})</option>
                        </select>
                        <InputError :message="addClassForm.errors.jurusan_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-rose-500">*</span></Label>
                        <select id="tahun_pelajaran_id" v-model="addClassForm.tahun_pelajaran_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-800 cursor-pointer">
                            <option v-for="y in years" :key="y.id" :value="y.id">{{ y.tahun }}</option>
                        </select>
                        <InputError :message="addClassForm.errors.tahun_pelajaran_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="semester_id">Semester <span class="text-rose-500">*</span></Label>
                        <select id="semester_id" v-model="addClassForm.semester_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-800 cursor-pointer">
                            <option v-for="s in semesters" :key="s.id" :value="s.id">{{ s.nama_smt }} ({{ s.smt }})</option>
                        </select>
                        <InputError :message="addClassForm.errors.semester_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="nama_kelas">Nama Rombel / Kelas <span class="text-rose-500">*</span></Label>
                        <Input id="nama_kelas" v-model="addClassForm.nama_kelas" required placeholder="Contoh: X IPA 1" />
                        <InputError :message="addClassForm.errors.nama_kelas" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="kode_kelas">Kode Kelas <span class="text-rose-500">*</span></Label>
                        <Input id="kode_kelas" v-model="addClassForm.kode_kelas" required placeholder="Contoh: X-IPA-1" />
                        <InputError :message="addClassForm.errors.kode_kelas" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="guru_id">Wali Kelas / Homeroom Teacher</Label>
                    <select id="guru_id" v-model="addClassForm.guru_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-800 cursor-pointer">
                        <option value="">Pilih Wali Kelas (Pilihan)</option>
                        <option v-for="g in gurus" :key="g.id" :value="g.id">{{ g.nama_guru }}</option>
                    </select>
                    <InputError :message="addClassForm.errors.guru_id" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-zinc-800">
                    <Button type="button" variant="outline" @click="isAddClassModalOpen = false">Batal</Button>
                    <Button type="submit" :disabled="addClassForm.processing" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold">
                        Simpan
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Edit Rombel / Kelas -->
    <div v-if="isEditClassModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Edit Rombel / Kelas</h3>
                <button @click="isEditClassModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitEditClass" class="p-6 space-y-4 overflow-y-auto flex-1">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_level_id">Level Tingkat <span class="text-rose-500">*</span></Label>
                        <select id="edit_level_id" v-model="editClassForm.level_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-800 cursor-pointer">
                            <option v-for="lvl in levels" :key="lvl.id" :value="lvl.id">{{ lvl.level }}</option>
                        </select>
                        <InputError :message="editClassForm.errors.level_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_jurusan_id">Jurusan / Peminatan</Label>
                        <select id="edit_jurusan_id" v-model="editClassForm.jurusan_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-800 cursor-pointer">
                            <option value="">Umum (Tanpa Jurusan)</option>
                            <option v-for="jur in jurusans" :key="jur.id" :value="jur.id">{{ jur.nama_jurusan }} ({{ jur.kode_jurusan }})</option>
                        </select>
                        <InputError :message="editClassForm.errors.jurusan_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_tahun_pelajaran_id">Tahun Pelajaran <span class="text-rose-500">*</span></Label>
                        <select id="edit_tahun_pelajaran_id" v-model="editClassForm.tahun_pelajaran_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-800 cursor-pointer">
                            <option v-for="y in years" :key="y.id" :value="y.id">{{ y.tahun }}</option>
                        </select>
                        <InputError :message="editClassForm.errors.tahun_pelajaran_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_semester_id">Semester <span class="text-rose-500">*</span></Label>
                        <select id="edit_semester_id" v-model="editClassForm.semester_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-800 cursor-pointer">
                            <option v-for="s in semesters" :key="s.id" :value="s.id">{{ s.nama_smt }} ({{ s.smt }})</option>
                        </select>
                        <InputError :message="editClassForm.errors.semester_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_nama_kelas">Nama Rombel / Kelas <span class="text-rose-500">*</span></Label>
                        <Input id="edit_nama_kelas" v-model="editClassForm.nama_kelas" required placeholder="Contoh: X IPA 1" />
                        <InputError :message="editClassForm.errors.nama_kelas" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_kode_kelas">Kode Kelas <span class="text-rose-500">*</span></Label>
                        <Input id="edit_kode_kelas" v-model="editClassForm.kode_kelas" required placeholder="Contoh: X-IPA-1" />
                        <InputError :message="editClassForm.errors.kode_kelas" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="edit_guru_id">Wali Kelas / Homeroom Teacher</Label>
                    <select id="edit_guru_id" v-model="editClassForm.guru_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-800 cursor-pointer">
                        <option value="">Pilih Wali Kelas (Pilihan)</option>
                        <option v-for="g in gurus" :key="g.id" :value="g.id">{{ g.nama_guru }}</option>
                    </select>
                    <InputError :message="editClassForm.errors.guru_id" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-zinc-800">
                    <Button type="button" variant="outline" @click="isEditClassModalOpen = false">Batal</Button>
                    <Button type="submit" :disabled="editClassForm.processing" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold">
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
