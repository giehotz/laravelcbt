<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Milestone, Plus, X, Edit2, Trash2, CheckCircle, AlertTriangle } from 'lucide-vue-next';

import { store as storeJurusan, update as updateJurusan, destroy as destroyJurusan } from '@/routes/master/jurusan';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Data Master',
                href: '#',
            },
            {
                title: 'Jurusan',
                href: '/master/jurusan',
            },
        ],
    },
});

const props = defineProps<{
    jurusans: Array<{
        id: number;
        nama_jurusan: string;
        kode_jurusan: string;
        mapel_peminatan: number[] | null;
        status: boolean;
        deletable: boolean;
    }>;
    mapels: Array<{
        id: number;
        nama_mapel: string;
        kode: string;
    }>;
}>();

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedJurusan = ref<typeof props.jurusans[0] | null>(null);

const addForm = useForm({
    nama_jurusan: '',
    kode_jurusan: '',
    mapel_peminatan: [] as number[],
    status: true,
});

const editForm = useForm({
    nama_jurusan: '',
    kode_jurusan: '',
    mapel_peminatan: [] as number[],
    status: true,
});

const getMapelNames = (ids: number[] | null) => {
    if (!ids || ids.length === 0) return '-';
    return props.mapels
        .filter(m => ids.includes(m.id))
        .map(m => m.nama_mapel)
        .join(', ');
};

const submitAdd = () => {
    addForm.post(storeJurusan.url(), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (jur: typeof props.jurusans[0]) => {
    selectedJurusan.value = jur;
    editForm.nama_jurusan = jur.nama_jurusan;
    editForm.kode_jurusan = jur.kode_jurusan;
    editForm.mapel_peminatan = jur.mapel_peminatan ?? [];
    editForm.status = jur.status;
    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    if (!selectedJurusan.value) return;
    editForm.put(updateJurusan.url(selectedJurusan.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
            selectedJurusan.value = null;
        },
    });
};

const handleDelete = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus jurusan ini?')) {
        addForm.delete(destroyJurusan.url(id));
    }
};
</script>

<template>
    <Head title="Jurusan Sekolah" />

    <div class="px-6 py-6 max-w-6xl mx-auto space-y-6">
        <Heading
            title="Jurusan / Peminatan"
            description="Atur program keahlian atau kelompok peminatan kurikulum di sekolah (contoh: IPA, IPS, RPL)."
        />

        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col transition-all">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center bg-neutral-50/50 dark:bg-zinc-800/30">
                <div class="flex items-center gap-2">
                    <Milestone class="w-5 h-5 text-zinc-500" />
                    <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-sm uppercase tracking-wider">Daftar Jurusan</h3>
                </div>
                <Button @click="isAddModalOpen = true" size="sm" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold flex items-center gap-1 shadow-sm transition-colors">
                    <Plus class="w-4 h-4" />
                    <span>Tambah Jurusan</span>
                </Button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                            <th class="px-6 py-3.5 w-16 text-center">No</th>
                            <th class="px-6 py-3.5 w-32">Kode</th>
                            <th class="px-6 py-3.5">Nama Jurusan</th>
                            <th class="px-6 py-3.5">Mapel Peminatan</th>
                            <th class="px-6 py-3.5 text-center w-24">Status</th>
                            <th class="px-6 py-3.5 text-right w-44">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                        <tr v-for="(jur, idx) in jurusans" :key="jur.id" class="hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                            <td class="px-6 py-4 text-center font-medium text-neutral-400 dark:text-neutral-500">{{ idx + 1 }}</td>
                            <td class="px-6 py-4 font-mono font-semibold text-zinc-600 dark:text-zinc-400">{{ jur.kode_jurusan }}</td>
                            <td class="px-6 py-4 font-bold text-neutral-800 dark:text-neutral-200">{{ jur.nama_jurusan }}</td>
                            <td class="px-6 py-4 text-xs text-neutral-500 dark:text-neutral-400 max-w-xs truncate" :title="getMapelNames(jur.mapel_peminatan)">
                                {{ getMapelNames(jur.mapel_peminatan) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="jur.status" class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/30">
                                    AKTIF
                                </span>
                                <span v-else class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-neutral-100 text-neutral-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    NON-AKTIF
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <Button
                                    size="sm"
                                    @click="openEditModal(jur)"
                                    class="bg-amber-500 hover:bg-amber-600 text-white dark:bg-amber-600 dark:hover:bg-amber-700 font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                >
                                    <Edit2 class="w-3.5 h-3.5 mr-1" />
                                    Edit
                                </Button>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="handleDelete(jur.id)"
                                    :disabled="!jur.deletable"
                                    class="bg-rose-600 hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800 text-white font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    <Trash2 class="w-3.5 h-3.5 mr-1" />
                                    Hapus
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="jurusans.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-neutral-500">
                                Belum ada data jurusan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah Jurusan -->
    <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Tambah Jurusan Baru</h3>
                <button @click="isAddModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitAdd" class="p-6 space-y-4 overflow-y-auto flex-1">
                <div class="grid gap-2">
                    <Label for="kode_jurusan">Kode Jurusan <span class="text-rose-500">*</span></Label>
                    <Input id="kode_jurusan" v-model="addForm.kode_jurusan" required placeholder="Contoh: IPA, IPS, RPL" />
                    <InputError :message="addForm.errors.kode_jurusan" />
                </div>

                <div class="grid gap-2">
                    <Label for="nama_jurusan">Nama Jurusan <span class="text-rose-500">*</span></Label>
                    <Input id="nama_jurusan" v-model="addForm.nama_jurusan" required placeholder="Contoh: Ilmu Pengetahuan Alam" />
                    <InputError :message="addForm.errors.nama_jurusan" />
                </div>

                <div class="grid gap-2">
                    <Label>Mata Pelajaran Peminatan / Pilihan</Label>
                    <div class="border border-neutral-250 dark:border-zinc-800 rounded-lg p-3 space-y-2 max-h-40 overflow-y-auto bg-neutral-50/50 dark:bg-zinc-900/50">
                        <div v-for="mapel in mapels" :key="mapel.id" class="flex items-center gap-2">
                            <input
                                :id="'add_mapel_' + mapel.id"
                                type="checkbox"
                                :value="mapel.id"
                                v-model="addForm.mapel_peminatan"
                                class="rounded border-neutral-300 dark:border-zinc-700 text-blue-600 focus:ring-blue-500 cursor-pointer"
                            />
                            <label :for="'add_mapel_' + mapel.id" class="text-sm text-neutral-700 dark:text-neutral-300 cursor-pointer select-none">
                                {{ mapel.nama_mapel }} <span class="text-xs text-neutral-400">({{ mapel.kode }})</span>
                            </label>
                        </div>
                        <div v-if="mapels.length === 0" class="text-xs text-neutral-400 text-center py-2">
                            Belum ada mata pelajaran aktif.
                        </div>
                    </div>
                    <InputError :message="addForm.errors.mapel_peminatan" />
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

    <!-- Modal Form: Edit Jurusan -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Edit Jurusan</h3>
                <button @click="isEditModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitEdit" class="p-6 space-y-4 overflow-y-auto flex-1">
                <div class="grid gap-2">
                    <Label for="edit_kode_jurusan">Kode Jurusan <span class="text-rose-500">*</span></Label>
                    <Input id="edit_kode_jurusan" v-model="editForm.kode_jurusan" required placeholder="Contoh: IPA, IPS, RPL" />
                    <InputError :message="editForm.errors.kode_jurusan" />
                </div>

                <div class="grid gap-2">
                    <Label for="edit_nama_jurusan">Nama Jurusan <span class="text-rose-500">*</span></Label>
                    <Input id="edit_nama_jurusan" v-model="editForm.nama_jurusan" required placeholder="Contoh: Ilmu Pengetahuan Alam" />
                    <InputError :message="editForm.errors.nama_jurusan" />
                </div>

                <div class="grid gap-2">
                    <Label>Mata Pelajaran Peminatan / Pilihan</Label>
                    <div class="border border-neutral-250 dark:border-zinc-800 rounded-lg p-3 space-y-2 max-h-40 overflow-y-auto bg-neutral-50/50 dark:bg-zinc-900/50">
                        <div v-for="mapel in mapels" :key="mapel.id" class="flex items-center gap-2">
                            <input
                                :id="'edit_mapel_' + mapel.id"
                                type="checkbox"
                                :value="mapel.id"
                                v-model="editForm.mapel_peminatan"
                                class="rounded border-neutral-300 dark:border-zinc-700 text-blue-600 focus:ring-blue-500 cursor-pointer"
                            />
                            <label :for="'edit_mapel_' + mapel.id" class="text-sm text-neutral-700 dark:text-neutral-300 cursor-pointer select-none">
                                {{ mapel.nama_mapel }} <span class="text-xs text-neutral-400">({{ mapel.kode }})</span>
                            </label>
                        </div>
                    </div>
                    <InputError :message="editForm.errors.mapel_peminatan" />
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
