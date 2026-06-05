<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Compass, Plus, X, Edit2, Trash2 } from 'lucide-vue-next';

import { store as storeEkstra, update as updateEkstra, destroy as destroyEkstra } from '@/routes/master/ekstra';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Data Master',
                href: '#',
            },
            {
                title: 'Ekstrakurikuler',
                href: '/master/ekstra',
            },
        ],
    },
});

const props = defineProps<{
    ekstras: Array<{
        id: number;
        nama_ekstra: string;
        kode_ekstra: string;
    }>;
}>();

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedEkstra = ref<typeof props.ekstras[0] | null>(null);

const addForm = useForm({
    nama_ekstra: '',
    kode_ekstra: '',
});

const editForm = useForm({
    nama_ekstra: '',
    kode_ekstra: '',
});

const submitAdd = () => {
    addForm.post(storeEkstra.url(), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (ex: typeof props.ekstras[0]) => {
    selectedEkstra.value = ex;
    editForm.nama_ekstra = ex.nama_ekstra;
    editForm.kode_ekstra = ex.kode_ekstra;
    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    if (!selectedEkstra.value) return;
    editForm.put(updateEkstra.url(selectedEkstra.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
            selectedEkstra.value = null;
        },
    });
};

const handleDelete = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus ekstrakurikuler ini?')) {
        addForm.delete(destroyEkstra.url(id));
    }
};
</script>

<template>
    <Head title="Ekstrakurikuler" />

    <div class="px-6 py-6 max-w-4xl mx-auto space-y-6">
        <Heading
            title="Ekstrakurikuler"
            description="Atur kegiatan ekstrakurikuler sekolah untuk pendataan nilai rapor siswa (contoh: Pramuka, OSIS, Paskibra)."
        />

        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col transition-all">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center bg-neutral-50/50 dark:bg-zinc-800/30">
                <div class="flex items-center gap-2">
                    <Compass class="w-5 h-5 text-zinc-500" />
                    <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-sm uppercase tracking-wider">Daftar Ekstrakurikuler</h3>
                </div>
                <Button @click="isAddModalOpen = true" size="sm" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold flex items-center gap-1 shadow-sm transition-colors">
                    <Plus class="w-4 h-4" />
                    <span>Tambah Ekstra</span>
                </Button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                            <th class="px-6 py-3.5 w-20 text-center">No</th>
                            <th class="px-6 py-3.5 w-40">Kode</th>
                            <th class="px-6 py-3.5">Nama Kegiatan</th>
                            <th class="px-6 py-3.5 text-right w-44">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                        <tr v-for="(ex, idx) in ekstras" :key="ex.id" class="hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                            <td class="px-6 py-4 text-center font-medium text-neutral-400 dark:text-neutral-500">{{ idx + 1 }}</td>
                            <td class="px-6 py-4 font-mono font-semibold text-zinc-600 dark:text-zinc-400">{{ ex.kode_ekstra }}</td>
                            <td class="px-6 py-4 font-bold text-neutral-800 dark:text-neutral-200">{{ ex.nama_ekstra }}</td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <Button
                                    size="sm"
                                    @click="openEditModal(ex)"
                                    class="bg-amber-500 hover:bg-amber-600 text-white dark:bg-amber-600 dark:hover:bg-amber-700 font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                >
                                    <Edit2 class="w-3.5 h-3.5 mr-1" />
                                    Edit
                                </Button>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="handleDelete(ex.id)"
                                    class="bg-rose-600 hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800 text-white font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                >
                                    <Trash2 class="w-3.5 h-3.5 mr-1" />
                                    Hapus
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="ekstras.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center text-neutral-500">
                                Belum ada data ekstrakurikuler.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah Ekstra -->
    <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Tambah Ekstrakurikuler</h3>
                <button @click="isAddModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitAdd" class="p-6 space-y-4">
                <div class="grid gap-2">
                    <Label for="kode_ekstra">Kode Ekstrakurikuler <span class="text-rose-500">*</span></Label>
                    <Input id="kode_ekstra" v-model="addForm.kode_ekstra" required placeholder="Contoh: PRAM, OSIS, PASK" />
                    <InputError :message="addForm.errors.kode_ekstra" />
                </div>

                <div class="grid gap-2">
                    <Label for="nama_ekstra">Nama Kegiatan <span class="text-rose-500">*</span></Label>
                    <Input id="nama_ekstra" v-model="addForm.nama_ekstra" required placeholder="Contoh: Pramuka, OSIS, Paskibra" />
                    <InputError :message="addForm.errors.nama_ekstra" />
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

    <!-- Modal Form: Edit Ekstra -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Edit Ekstrakurikuler</h3>
                <button @click="isEditModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                <div class="grid gap-2">
                    <Label for="edit_kode_ekstra">Kode Ekstrakurikuler <span class="text-rose-500">*</span></Label>
                    <Input id="edit_kode_ekstra" v-model="editForm.kode_ekstra" required placeholder="Contoh: PRAM, OSIS, PASK" />
                    <InputError :message="editForm.errors.kode_ekstra" />
                </div>

                <div class="grid gap-2">
                    <Label for="edit_nama_ekstra">Nama Kegiatan <span class="text-rose-500">*</span></Label>
                    <Input id="edit_nama_ekstra" v-model="editForm.nama_ekstra" required placeholder="Contoh: Pramuka, OSIS, Paskibra" />
                    <InputError :message="editForm.errors.nama_ekstra" />
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
