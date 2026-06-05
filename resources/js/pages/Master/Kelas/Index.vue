<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Users, Plus, X, Edit2, Trash2, UserPlus } from 'lucide-vue-next';

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
const selectedKelas = ref<typeof props.kelas.data[0] | null>(null);

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

const openEditModal = (kls: typeof props.kelas.data[0]) => {
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

    <div class="px-6 py-6 max-w-7xl mx-auto space-y-6">
        <Heading
            title="Rombel / Kelas"
            description="Atur rombongan belajar (rombel) siswa, tingkat kelas, jurusan, wali kelas, serta pembagian siswa."
        />

        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col transition-all">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center bg-neutral-50/50 dark:bg-zinc-800/30">
                <div class="flex items-center gap-2">
                    <Users class="w-5 h-5 text-zinc-500" />
                    <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-sm uppercase tracking-wider">Daftar Rombel / Kelas</h3>
                </div>
                <Button @click="isAddModalOpen = true" size="sm" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold flex items-center gap-1 shadow-sm transition-colors">
                    <Plus class="w-4 h-4" />
                    <span>Tambah Rombel</span>
                </Button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                            <th class="px-4 py-3.5 w-14 text-center">No</th>
                            <th class="px-4 py-3.5">Nama Kelas</th>
                            <th class="px-4 py-3.5 w-24">Tingkat</th>
                            <th class="px-4 py-3.5 w-32">Jurusan</th>
                            <th class="px-4 py-3.5">Wali Kelas</th>
                            <th class="px-4 py-3.5 w-32">Periode</th>
                            <th class="px-4 py-3.5 text-right w-64">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                        <tr v-for="(kls, idx) in kelas.data" :key="kls.id" class="hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                            <td class="px-4 py-4 text-center font-medium text-neutral-400 dark:text-neutral-500">{{ idx + 1 }}</td>
                            <td class="px-4 py-4 font-bold text-neutral-800 dark:text-neutral-200">{{ kls.nama_kelas }} <span class="font-mono text-xs font-semibold text-neutral-400">({{ kls.kode_kelas }})</span></td>
                            <td class="px-4 py-4 text-neutral-600 dark:text-neutral-400 font-semibold">{{ kls.level_kelas?.level ?? '-' }}</td>
                            <td class="px-4 py-4 text-neutral-600 dark:text-neutral-400 font-semibold">{{ kls.jurusan?.nama_jurusan ?? 'Umum' }}</td>
                            <td class="px-4 py-4 text-neutral-600 dark:text-neutral-400 font-semibold">{{ kls.wali_kelas?.nama_guru ?? '-' }}</td>
                            <td class="px-4 py-4 text-xs text-neutral-500 dark:text-neutral-400">
                                <div>{{ kls.tahun_pelajaran?.tahun ?? '-' }}</div>
                                <div class="font-semibold">{{ kls.semester?.nama_smt ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-4 text-right space-x-1.5 whitespace-nowrap">
                                <Link
                                    :href="students.edit.url(kls.id)"
                                    class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                >
                                    <UserPlus class="w-3.5 h-3.5 mr-1" />
                                    Siswa
                                </Link>
                                <Button
                                    size="sm"
                                    @click="openEditModal(kls)"
                                    class="bg-amber-500 hover:bg-amber-600 text-white dark:bg-amber-600 dark:hover:bg-amber-700 font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                >
                                    <Edit2 class="w-3.5 h-3.5 mr-1" />
                                    Edit
                                </Button>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="handleDelete(kls.id)"
                                    class="bg-rose-600 hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800 text-white font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                >
                                    <Trash2 class="w-3.5 h-3.5 mr-1" />
                                    Hapus
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="kelas.data.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-neutral-500">
                                Belum ada data rombel/kelas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Simple Pagination -->
            <div v-if="kelas.links && kelas.links.length > 3" class="px-6 py-4 border-t border-neutral-200 dark:border-zinc-800 flex justify-between items-center bg-neutral-50/20 dark:bg-zinc-900/20">
                <div class="flex items-center gap-1">
                    <template v-for="link in kelas.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-1 rounded text-xs font-semibold border transition-all"
                            :class="link.active ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-50 dark:border-zinc-50 dark:text-zinc-950' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50 dark:bg-zinc-900 dark:border-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-800'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-3 py-1 rounded text-xs font-semibold border border-neutral-100 text-neutral-300 dark:border-zinc-800/40 dark:text-zinc-600 cursor-not-allowed select-none"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah Rombel -->
    <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Tambah Rombel / Kelas</h3>
                <button @click="isAddModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitAdd" class="p-6 space-y-4 overflow-y-auto flex-1">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="level_id">Level Tingkat <span class="text-rose-500">*</span></Label>
                        <select id="level_id" v-model="addForm.level_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-850 cursor-pointer">
                            <option v-for="lvl in levels" :key="lvl.id" :value="lvl.id">{{ lvl.level }}</option>
                        </select>
                        <InputError :message="addForm.errors.level_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="jurusan_id">Jurusan / Peminatan</Label>
                        <select id="jurusan_id" v-model="addForm.jurusan_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-850 cursor-pointer">
                            <option value="">Umum (Tanpa Jurusan)</option>
                            <option v-for="jur in jurusans" :key="jur.id" :value="jur.id">{{ jur.nama_jurusan }} ({{ jur.kode_jurusan }})</option>
                        </select>
                        <InputError :message="addForm.errors.jurusan_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="tahun_pelajaran_id">Tahun Pelajaran <span class="text-rose-500">*</span></Label>
                        <select id="tahun_pelajaran_id" v-model="addForm.tahun_pelajaran_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-850 cursor-pointer">
                            <option v-for="y in years" :key="y.id" :value="y.id">{{ y.tahun }}</option>
                        </select>
                        <InputError :message="addForm.errors.tahun_pelajaran_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="semester_id">Semester <span class="text-rose-500">*</span></Label>
                        <select id="semester_id" v-model="addForm.semester_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-850 cursor-pointer">
                            <option v-for="s in semesters" :key="s.id" :value="s.id">{{ s.nama_smt }} ({{ s.smt }})</option>
                        </select>
                        <InputError :message="addForm.errors.semester_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="nama_kelas">Nama Rombel / Kelas <span class="text-rose-500">*</span></Label>
                        <Input id="nama_kelas" v-model="addForm.nama_kelas" required placeholder="Contoh: X IPA 1" />
                        <InputError :message="addForm.errors.nama_kelas" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="kode_kelas">Kode Kelas <span class="text-rose-500">*</span></Label>
                        <Input id="kode_kelas" v-model="addForm.kode_kelas" required placeholder="Contoh: X-IPA-1" />
                        <InputError :message="addForm.errors.kode_kelas" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="guru_id">Wali Kelas / Homeroom Teacher</Label>
                    <select id="guru_id" v-model="addForm.guru_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-850 cursor-pointer">
                        <option value="">Pilih Wali Kelas (Pilihan)</option>
                        <option v-for="g in gurus" :key="g.id" :value="g.id">{{ g.nama_guru }}</option>
                    </select>
                    <InputError :message="addForm.errors.guru_id" />
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

    <!-- Modal Form: Edit Rombel -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Edit Rombel / Kelas</h3>
                <button @click="isEditModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitEdit" class="p-6 space-y-4 overflow-y-auto flex-1">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_level_id">Level Tingkat <span class="text-rose-500">*</span></Label>
                        <select id="edit_level_id" v-model="editForm.level_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-850 cursor-pointer">
                            <option v-for="lvl in levels" :key="lvl.id" :value="lvl.id">{{ lvl.level }}</option>
                        </select>
                        <InputError :message="editForm.errors.level_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_jurusan_id">Jurusan / Peminatan</Label>
                        <select id="edit_jurusan_id" v-model="editForm.jurusan_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-850 cursor-pointer">
                            <option value="">Umum (Tanpa Jurusan)</option>
                            <option v-for="jur in jurusans" :key="jur.id" :value="jur.id">{{ jur.nama_jurusan }} ({{ jur.kode_jurusan }})</option>
                        </select>
                        <InputError :message="editForm.errors.jurusan_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_tahun_pelajaran_id">Tahun Pelajaran <span class="text-rose-500">*</span></Label>
                        <select id="edit_tahun_pelajaran_id" v-model="editForm.tahun_pelajaran_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-850 cursor-pointer">
                            <option v-for="y in years" :key="y.id" :value="y.id">{{ y.tahun }}</option>
                        </select>
                        <InputError :message="editForm.errors.tahun_pelajaran_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_semester_id">Semester <span class="text-rose-500">*</span></Label>
                        <select id="edit_semester_id" v-model="editForm.semester_id" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-850 cursor-pointer">
                            <option v-for="s in semesters" :key="s.id" :value="s.id">{{ s.nama_smt }} ({{ s.smt }})</option>
                        </select>
                        <InputError :message="editForm.errors.semester_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_nama_kelas">Nama Rombel / Kelas <span class="text-rose-500">*</span></Label>
                        <Input id="edit_nama_kelas" v-model="editForm.nama_kelas" required placeholder="Contoh: X IPA 1" />
                        <InputError :message="editForm.errors.nama_kelas" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_kode_kelas">Kode Kelas <span class="text-rose-500">*</span></Label>
                        <Input id="edit_kode_kelas" v-model="editForm.kode_kelas" required placeholder="Contoh: X-IPA-1" />
                        <InputError :message="editForm.errors.kode_kelas" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="edit_guru_id">Wali Kelas / Homeroom Teacher</Label>
                    <select id="edit_guru_id" v-model="editForm.guru_id" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-zinc-900 dark:border-zinc-850 cursor-pointer">
                        <option value="">Pilih Wali Kelas (Pilihan)</option>
                        <option v-for="g in gurus" :key="g.id" :value="g.id">{{ g.nama_guru }}</option>
                    </select>
                    <InputError :message="editForm.errors.guru_id" />
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
</template>
