<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { BookOpen, Plus, X, Edit2, Trash2 } from 'lucide-vue-next';

import {
    store as storeMapel,
    update as updateMapel,
    destroy as destroyMapel,
} from '@/routes/master/mapel';

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
const selectedMapel = ref<(typeof props.mapels)[0] | null>(null);

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

const openEditModal = (mapel: (typeof props.mapels)[0]) => {
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

    <div class="mx-auto max-w-6xl space-y-6 px-6 py-6">
        <Heading
            title="Mata Pelajaran"
            description="Atur mata pelajaran sekolah, kelompok kurikulum, serta bobot nilai pengetahuan (P) dan keterampilan (K)."
        />

        <div
            class="flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition-all dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
            >
                <div class="flex items-center gap-2">
                    <BookOpen class="h-5 w-5 text-zinc-500" />
                    <h3
                        class="text-sm font-bold tracking-wider text-neutral-800 uppercase dark:text-neutral-200"
                    >
                        Daftar Mata Pelajaran
                    </h3>
                </div>
                <Button
                    @click="isAddModalOpen = true"
                    size="sm"
                    class="flex items-center gap-1 bg-zinc-900 font-semibold text-white shadow-sm transition-colors hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                >
                    <Plus class="h-4 w-4" />
                    <span>Tambah Mapel</span>
                </Button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50/30 text-[11px] font-bold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-900/50"
                        >
                            <th class="w-14 px-4 py-3.5 text-center">No</th>
                            <th class="w-32 px-4 py-3.5">Kode</th>
                            <th class="px-4 py-3.5">Nama Mapel</th>
                            <th class="w-24 px-4 py-3.5 text-center">
                                Kelompok
                            </th>
                            <th class="w-20 px-4 py-3.5 text-center">
                                Bobot P
                            </th>
                            <th class="w-20 px-4 py-3.5 text-center">
                                Bobot K
                            </th>
                            <th class="w-20 px-4 py-3.5 text-center">Status</th>
                            <th class="w-44 px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                    >
                        <tr
                            v-for="(mapel, idx) in mapels"
                            :key="mapel.id"
                            class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20"
                        >
                            <td
                                class="px-4 py-4 text-center font-medium text-neutral-400 dark:text-neutral-500"
                            >
                                {{ idx + 1 }}
                            </td>
                            <td
                                class="px-4 py-4 font-mono font-semibold text-zinc-600 dark:text-zinc-400"
                            >
                                {{ mapel.kode }}
                            </td>
                            <td
                                class="px-4 py-4 font-bold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ mapel.nama_mapel }}
                            </td>
                            <td
                                class="px-4 py-4 text-center font-semibold text-neutral-500"
                            >
                                {{ mapel.kelompok }}
                            </td>
                            <td
                                class="px-4 py-4 text-center font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                {{ mapel.bobot_p }}%
                            </td>
                            <td
                                class="px-4 py-4 text-center font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                {{ mapel.bobot_k }}%
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span
                                    v-if="mapel.status"
                                    class="inline-flex items-center gap-0.5 rounded border border-emerald-200/30 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400"
                                >
                                    AKTIF
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-0.5 rounded bg-neutral-100 px-2 py-0.5 text-[10px] font-bold text-neutral-500 dark:bg-zinc-800 dark:text-zinc-400"
                                >
                                    NON-AKTIF
                                </span>
                            </td>
                            <td
                                class="space-x-1.5 px-4 py-4 text-right whitespace-nowrap"
                            >
                                <Button
                                    size="sm"
                                    @click="openEditModal(mapel)"
                                    class="h-8 rounded bg-amber-500 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700"
                                >
                                    <Edit2 class="mr-1 h-3.5 w-3.5" />
                                    Edit
                                </Button>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="handleDelete(mapel.id)"
                                    :disabled="!mapel.deletable"
                                    class="h-8 rounded bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-rose-700 dark:hover:bg-rose-800"
                                >
                                    <Trash2 class="mr-1 h-3.5 w-3.5" />
                                    Hapus
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="mapels.length === 0">
                            <td
                                colspan="8"
                                class="px-6 py-8 text-center text-neutral-500"
                            >
                                Belum ada data mata pelajaran.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah Mapel -->
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
                    Tambah Mata Pelajaran
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
                        <Label for="kode"
                            >Kode Mapel
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="kode"
                            v-model="addForm.kode"
                            required
                            placeholder="Contoh: MTK, INDO, IPA"
                        />
                        <InputError :message="addForm.errors.kode" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="kelompok"
                            >Kelompok
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="kelompok"
                            v-model="addForm.kelompok"
                            required
                            placeholder="Contoh: A, B, C, Peminatan"
                        />
                        <InputError :message="addForm.errors.kelompok" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="nama_mapel"
                        >Nama Mata Pelajaran
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="nama_mapel"
                        v-model="addForm.nama_mapel"
                        required
                        placeholder="Contoh: Matematika"
                    />
                    <InputError :message="addForm.errors.nama_mapel" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="bobot_p"
                            >Bobot Pengetahuan (%)
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="bobot_p"
                            type="number"
                            v-model.number="addForm.bobot_p"
                            min="0"
                            max="100"
                            required
                        />
                        <InputError :message="addForm.errors.bobot_p" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="bobot_k"
                            >Bobot Keterampilan (%)
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="bobot_k"
                            type="number"
                            v-model.number="addForm.bobot_k"
                            min="0"
                            max="100"
                            required
                        />
                        <InputError :message="addForm.errors.bobot_k" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="jenjang">Jenjang Kelas (Pilihan)</Label>
                        <Input
                            id="jenjang"
                            type="number"
                            v-model.number="addForm.jenjang"
                            min="0"
                        />
                        <InputError :message="addForm.errors.jenjang" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="urutan">Urutan Tampil (Pilihan)</Label>
                        <Input
                            id="urutan"
                            type="number"
                            v-model.number="addForm.urutan"
                            min="0"
                        />
                        <InputError :message="addForm.errors.urutan" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="status"
                        type="checkbox"
                        v-model="addForm.status"
                        class="cursor-pointer rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-700"
                    />
                    <Label
                        for="status"
                        class="text-neutral-750 cursor-pointer text-sm font-medium dark:text-neutral-300"
                        >Status Aktif</Label
                    >
                    <InputError :message="addForm.errors.status" />
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

    <!-- Modal Form: Edit Mapel -->
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
                    Edit Mata Pelajaran
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
                        <Label for="edit_kode"
                            >Kode Mapel
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="edit_kode"
                            v-model="editForm.kode"
                            required
                            placeholder="Contoh: MTK, INDO, IPA"
                        />
                        <InputError :message="editForm.errors.kode" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_kelompok"
                            >Kelompok
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="edit_kelompok"
                            v-model="editForm.kelompok"
                            required
                            placeholder="Contoh: A, B, C, Peminatan"
                        />
                        <InputError :message="editForm.errors.kelompok" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="edit_nama_mapel"
                        >Nama Mata Pelajaran
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="edit_nama_mapel"
                        v-model="editForm.nama_mapel"
                        required
                        placeholder="Contoh: Matematika"
                    />
                    <InputError :message="editForm.errors.nama_mapel" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_bobot_p"
                            >Bobot Pengetahuan (%)
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="edit_bobot_p"
                            type="number"
                            v-model.number="editForm.bobot_p"
                            min="0"
                            max="100"
                            required
                        />
                        <InputError :message="editForm.errors.bobot_p" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_bobot_k"
                            >Bobot Keterampilan (%)
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="edit_bobot_k"
                            type="number"
                            v-model.number="editForm.bobot_k"
                            min="0"
                            max="100"
                            required
                        />
                        <InputError :message="editForm.errors.bobot_k" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_jenjang"
                            >Jenjang Kelas (Pilihan)</Label
                        >
                        <Input
                            id="edit_jenjang"
                            type="number"
                            v-model.number="editForm.jenjang"
                            min="0"
                        />
                        <InputError :message="editForm.errors.jenjang" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="edit_urutan">Urutan Tampil (Pilihan)</Label>
                        <Input
                            id="edit_urutan"
                            type="number"
                            v-model.number="editForm.urutan"
                            min="0"
                        />
                        <InputError :message="editForm.errors.urutan" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="edit_status"
                        type="checkbox"
                        v-model="editForm.status"
                        class="cursor-pointer rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-700"
                    />
                    <Label
                        for="edit_status"
                        class="text-neutral-750 cursor-pointer text-sm font-medium dark:text-neutral-300"
                        >Status Aktif</Label
                    >
                    <InputError :message="editForm.errors.status" />
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
