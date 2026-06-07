<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Compass, Plus, X, Edit2, Trash2 } from 'lucide-vue-next';

import {
    store as storeEkstra,
    update as updateEkstra,
    destroy as destroyEkstra,
} from '@/routes/master/ekstra';

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
const selectedEkstra = ref<(typeof props.ekstras)[0] | null>(null);

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

const openEditModal = (ex: (typeof props.ekstras)[0]) => {
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

    <div class="mx-auto max-w-4xl space-y-6 px-6 py-6">
        <Heading
            title="Ekstrakurikuler"
            description="Atur kegiatan ekstrakurikuler sekolah untuk pendataan nilai rapor siswa (contoh: Pramuka, OSIS, Paskibra)."
        />

        <div
            class="flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition-all dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
            >
                <div class="flex items-center gap-2">
                    <Compass class="h-5 w-5 text-zinc-500" />
                    <h3
                        class="text-sm font-bold tracking-wider text-neutral-800 uppercase dark:text-neutral-200"
                    >
                        Daftar Ekstrakurikuler
                    </h3>
                </div>
                <Button
                    @click="isAddModalOpen = true"
                    size="sm"
                    class="flex items-center gap-1 bg-zinc-900 font-semibold text-white shadow-sm transition-colors hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                >
                    <Plus class="h-4 w-4" />
                    <span>Tambah Ekstra</span>
                </Button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50/30 text-[11px] font-bold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-900/50"
                        >
                            <th class="w-20 px-6 py-3.5 text-center">No</th>
                            <th class="w-40 px-6 py-3.5">Kode</th>
                            <th class="px-6 py-3.5">Nama Kegiatan</th>
                            <th class="w-44 px-6 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                    >
                        <tr
                            v-for="(ex, idx) in ekstras"
                            :key="ex.id"
                            class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20"
                        >
                            <td
                                class="px-6 py-4 text-center font-medium text-neutral-400 dark:text-neutral-500"
                            >
                                {{ idx + 1 }}
                            </td>
                            <td
                                class="px-6 py-4 font-mono font-semibold text-zinc-600 dark:text-zinc-400"
                            >
                                {{ ex.kode_ekstra }}
                            </td>
                            <td
                                class="px-6 py-4 font-bold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ ex.nama_ekstra }}
                            </td>
                            <td
                                class="space-x-2 px-6 py-4 text-right whitespace-nowrap"
                            >
                                <Button
                                    size="sm"
                                    @click="openEditModal(ex)"
                                    class="h-8 rounded bg-amber-500 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700"
                                >
                                    <Edit2 class="mr-1 h-3.5 w-3.5" />
                                    Edit
                                </Button>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="handleDelete(ex.id)"
                                    class="h-8 rounded bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800"
                                >
                                    <Trash2 class="mr-1 h-3.5 w-3.5" />
                                    Hapus
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="ekstras.length === 0">
                            <td
                                colspan="4"
                                class="px-6 py-8 text-center text-neutral-500"
                            >
                                Belum ada data ekstrakurikuler.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah Ekstra -->
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
                    Tambah Ekstrakurikuler
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
                    <Label for="kode_ekstra"
                        >Kode Ekstrakurikuler
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="kode_ekstra"
                        v-model="addForm.kode_ekstra"
                        required
                        placeholder="Contoh: PRAM, OSIS, PASK"
                    />
                    <InputError :message="addForm.errors.kode_ekstra" />
                </div>

                <div class="grid gap-2">
                    <Label for="nama_ekstra"
                        >Nama Kegiatan
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="nama_ekstra"
                        v-model="addForm.nama_ekstra"
                        required
                        placeholder="Contoh: Pramuka, OSIS, Paskibra"
                    />
                    <InputError :message="addForm.errors.nama_ekstra" />
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

    <!-- Modal Form: Edit Ekstra -->
    <div
        v-if="isEditModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
            >
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">
                    Edit Ekstrakurikuler
                </h3>
                <button
                    @click="isEditModalOpen = false"
                    class="hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer rounded-lg p-1 text-neutral-400"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitEdit" class="space-y-4 p-6">
                <div class="grid gap-2">
                    <Label for="edit_kode_ekstra"
                        >Kode Ekstrakurikuler
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="edit_kode_ekstra"
                        v-model="editForm.kode_ekstra"
                        required
                        placeholder="Contoh: PRAM, OSIS, PASK"
                    />
                    <InputError :message="editForm.errors.kode_ekstra" />
                </div>

                <div class="grid gap-2">
                    <Label for="edit_nama_ekstra"
                        >Nama Kegiatan
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="edit_nama_ekstra"
                        v-model="editForm.nama_ekstra"
                        required
                        placeholder="Contoh: Pramuka, OSIS, Paskibra"
                    />
                    <InputError :message="editForm.errors.nama_ekstra" />
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
