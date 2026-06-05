<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { BookOpen, Plus, X, Edit2, Trash2 } from 'lucide-vue-next';

import { store as storeMapel, update as updateMapel, destroy as destroyMapel } from '@/routes/master/mapel';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Data Master',
                href: '#',
            },
            {
                title: 'Mata Pelajaran',
                href: '/master/mapel',
            },
        ],
    },
});

const props = defineProps<{
    mapels: Array<{
        id: number;
        nama_mapel: string;
        kode: string;
        kelompok: string;
        bobot_p: number;
        bobot_k: number;
        jenjang: number | null;
        urutan: number | null;
        status: boolean;
        deletable: boolean;
    }>;
}>();

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedMapel = ref<typeof props.mapels[0] | null>(null);

const addForm = useForm({
    nama_mapel: '',
    kode: '',
    kelompok: '-',
    bobot_p: 0,
    bobot_k: 0,
    jenjang: 0,
    urutan: 0,
    status: true,
});

const editForm = useForm({
    nama_mapel: '',
    kode: '',
    kelompok: '',
    bobot_p: 0,
    bobot_k: 0,
    jenjang: 0,
    urutan: 0,
    status: true,
});

const submitAdd = () => {
    addForm.post(storeMapel.url(), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (mapel: typeof props.mapels[0]) => {
    selectedMapel.value = mapel;
    editForm.nama_mapel = mapel.nama_mapel;
    editForm.kode = mapel.kode;
    editForm.kelompok = mapel.kelompok;
    editForm.bobot_p = mapel.bobot_p;
    editForm.bobot_k = mapel.bobot_k;
    editForm.jenjang = mapel.jenjang ?? 0;
    editForm.urutan = mapel.urutan ?? 0;
    editForm.status = mapel.status;
    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    if (!selectedMapel.value) return;
    editForm.put(updateMapel.url(selectedMapel.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
            selectedMapel.value = null;
        },
    });
};

const handleDelete = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini?')) {
        addForm.delete(destroyMapel.url(id));
    }
};
</script>

<template>
    <Head title="Mata Pelajaran" />

    <div class="px-6 py-6 max-w-6xl mx-auto space-y-6">
        <Heading
            title="Mata Pelajaran"
            description="Atur mata pelajaran sekolah, kelompok kurikulum, serta bobot nilai pengetahuan (P) dan keterampilan (K)."
        />

        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col transition-all">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center bg-neutral-50/50 dark:bg-zinc-800/30">
                <div class="flex items-center gap-2">
                    <BookOpen class="w-5 h-5 text-zinc-500" />
                    <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-sm uppercase tracking-wider">Daftar Mata Pelajaran</h3>
                </div>
                <Button @click="isAddModalOpen = true" size="sm" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold flex items-center gap-1 shadow-sm transition-colors">
                    <Plus class="w-4 h-4" />
                    <span>Tambah Mapel</span>
                </Button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                            <th class="px-4 py-3.5 w-14 text-center">No</th>
                            <th class="px-4 py-3.5 w-32">Kode</th>
                            <th class="px-4 py-3.5">Nama Mapel</th>
                            <th class="px-4 py-3.5 w-24 text-center">Kelompok</th>
                            <th class="px-4 py-3.5 w-20 text-center">Bobot P</th>
                            <th class="px-4 py-3.5 w-20 text-center">Bobot K</th>
                            <th class="px-4 py-3.5 w-20 text-center">Status</th>
                            <th class="px-4 py-3.5 text-right w-44">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                        <tr v-for="(mapel, idx) in mapels" :key="mapel.id" class="hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                            <td class="px-4 py-4 text-center font-medium text-neutral-400 dark:text-neutral-500">{{ idx + 1 }}</td>
                            <td class="px-4 py-4 font-mono font-semibold text-zinc-600 dark:text-zinc-400">{{ mapel.kode }}</td>
                            <td class="px-4 py-4 font-bold text-neutral-800 dark:text-neutral-200">{{ mapel.nama_mapel }}</td>
                            <td class="px-4 py-4 text-center font-semibold text-neutral-500">{{ mapel.kelompok }}</td>
                            <td class="px-4 py-4 text-center font-medium text-zinc-700 dark:text-zinc-300">{{ mapel.bobot_p }}%</td>
                            <td class="px-4 py-4 text-center font-medium text-zinc-700 dark:text-zinc-300">{{ mapel.bobot_k }}%</td>
                            <td class="px-4 py-4 text-center">
                                <span v-if="mapel.status" class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/30">
                                    AKTIF
                                </span>
                                <span v-else class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-neutral-100 text-neutral-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    NON-AKTIF
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right space-x-1.5 whitespace-nowrap">
                                <Button
                                    size="sm"
                                    @click="openEditModal(mapel)"
                                    class="bg-amber-500 hover:bg-amber-600 text-white dark:bg-amber-600 dark:hover:bg-amber-700 font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                >
                                    <Edit2 class="w-3.5 h-3.5 mr-1" />
                                    Edit
                                </Button>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="handleDelete(mapel.id)"
                                    :disabled="!mapel.deletable"
                                    class="bg-rose-600 hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800 text-white font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    <Trash2 class="w-3.5 h-3.5 mr-1" />
                                    Hapus
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="mapels.length === 0">
                            <td colspan="8" class="px-6 py-8 text-center text-neutral-500">
                                Belum ada data mata pelajaran.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah Mapel -->
    <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Tambah Mata Pelajaran</h3>
                <button @click="isAddModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitAdd" class="p-6 space-y-4 overflow-y-auto flex-1">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="kode">Kode Mapel <span class="text-rose-500">*</span></Label>
                        <Input id="kode" v-model="addForm.kode" required placeholder="Contoh: MTK, INDO, IPA" />
                        <InputError :message="addForm.errors.kode" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="kelompok">Kelompok <span class="text-rose-500">*</span></Label>
                        <Input id="kelompok" v-model="addForm.kelompok" required placeholder="Contoh: A, B, C, Peminatan" />
                        <InputError :message="addForm.errors.kelompok" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="nama_mapel">Nama Mata Pelajaran <span class="text-rose-500">*</span></Label>
                    <Input id="nama_mapel" v-model="addForm.nama_mapel" required placeholder="Contoh: Matematika" />
                    <InputError :message="addForm.errors.nama_mapel" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="bobot_p">Bobot Pengetahuan (%) <span class="text-rose-500">*</span></Label>
                        <Input id="bobot_p" type="number" v-model.number="addForm.bobot_p" min="0" max="100" required />
                        <InputError :message="addForm.errors.bobot_p" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="bobot_k">Bobot Keterampilan (%) <span class="text-rose-500">*</span></Label>
                        <Input id="bobot_k" type="number" v-model.number="addForm.bobot_k" min="0" max="100" required />
                        <InputError :message="addForm.errors.bobot_k" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="jenjang">Jenjang Kelas (Pilihan)</Label>
                        <Input id="jenjang" type="number" v-model.number="addForm.jenjang" min="0" />
                        <InputError :message="addForm.errors.jenjang" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="urutan">Urutan Tampil (Pilihan)</Label>
                        <Input id="urutan" type="number" v-model.number="addForm.urutan" min="0" />
                        <InputError :message="addForm.errors.urutan" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input id="status" type="checkbox" v-model="addForm.status" class="rounded border-neutral-300 dark:border-zinc-700 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                    <Label for="status" class="text-sm font-medium text-neutral-750 dark:text-neutral-300 cursor-pointer">Status Aktif</Label>
                    <InputError :message="addForm.errors.status" />
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

    <!-- Modal Form: Edit Mapel -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Edit Mata Pelajaran</h3>
                <button @click="isEditModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitEdit" class="p-6 space-y-4 overflow-y-auto flex-1">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_kode">Kode Mapel <span class="text-rose-500">*</span></Label>
                        <Input id="edit_kode" v-model="editForm.kode" required placeholder="Contoh: MTK, INDO, IPA" />
                        <InputError :message="editForm.errors.kode" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_kelompok">Kelompok <span class="text-rose-500">*</span></Label>
                        <Input id="edit_kelompok" v-model="editForm.kelompok" required placeholder="Contoh: A, B, C, Peminatan" />
                        <InputError :message="editForm.errors.kelompok" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="edit_nama_mapel">Nama Mata Pelajaran <span class="text-rose-500">*</span></Label>
                    <Input id="edit_nama_mapel" v-model="editForm.nama_mapel" required placeholder="Contoh: Matematika" />
                    <InputError :message="editForm.errors.nama_mapel" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_bobot_p">Bobot Pengetahuan (%) <span class="text-rose-500">*</span></Label>
                        <Input id="edit_bobot_p" type="number" v-model.number="editForm.bobot_p" min="0" max="100" required />
                        <InputError :message="editForm.errors.bobot_p" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_bobot_k">Bobot Keterampilan (%) <span class="text-rose-500">*</span></Label>
                        <Input id="edit_bobot_k" type="number" v-model.number="editForm.bobot_k" min="0" max="100" required />
                        <InputError :message="editForm.errors.bobot_k" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_jenjang">Jenjang Kelas (Pilihan)</Label>
                        <Input id="edit_jenjang" type="number" v-model.number="editForm.jenjang" min="0" />
                        <InputError :message="editForm.errors.jenjang" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_urutan">Urutan Tampil (Pilihan)</Label>
                        <Input id="edit_urutan" type="number" v-model.number="editForm.urutan" min="0" />
                        <InputError :message="editForm.errors.urutan" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input id="edit_status" type="checkbox" v-model="editForm.status" class="rounded border-neutral-300 dark:border-zinc-700 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                    <Label for="edit_status" class="text-sm font-medium text-neutral-750 dark:text-neutral-300 cursor-pointer">Status Aktif</Label>
                    <InputError :message="editForm.errors.status" />
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
