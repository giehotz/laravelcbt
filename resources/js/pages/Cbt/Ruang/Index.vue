<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Plus, X, Edit2, Trash2 } from 'lucide-vue-next';
import {
    store as storeRuang,
    update as updateRuang,
    destroy as destroyRuang,
} from '@/routes/cbt/ruang';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'CBT',
                href: '#',
            },
            {
                title: 'Ruang Ujian',
                href: '/cbt/ruang',
            },
        ],
    },
});

const props = defineProps<{
    ruang: {
        data: Array<{
            id: number;
            nama_ruang: string;
            kode_ruang: string;
        }>;
        links: any[];
    };
}>();

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedItem = ref<{
    id: number;
    nama_ruang: string;
    kode_ruang: string;
} | null>(null);

const addForm = useForm({
    nama_ruang: '',
    kode_ruang: '',
});

const editForm = useForm({
    nama_ruang: '',
    kode_ruang: '',
});

// Auto-generate code from name
watch(
    () => addForm.nama_ruang,
    (newVal) => {
        if (newVal && !addForm.kode_ruang) {
            addForm.kode_ruang = newVal
                .trim()
                .substring(0, 15)
                .toUpperCase()
                .replace(/\s+/g, '-');
        }
    },
);

const submitAdd = () => {
    addForm.post(storeRuang.url(), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (item: any) => {
    selectedItem.value = item;
    editForm.nama_ruang = item.nama_ruang;
    editForm.kode_ruang = item.kode_ruang;
    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    if (!selectedItem.value) return;
    editForm.put(updateRuang.url(selectedItem.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
            selectedItem.value = null;
        },
    });
};

const handleDelete = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus ruang ujian ini?')) {
        router.delete(destroyRuang.url(id));
    }
};
</script>

<template>
    <Head title="Ruang Ujian CBT" />

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Ruang Ujian"
                description="Kelola daftar ruang yang digunakan untuk pelaksanaan ujian."
            />
            <Button
                @click="isAddModalOpen = true"
                class="flex items-center gap-1 bg-zinc-900 font-semibold text-white shadow-sm transition-colors hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
            >
                <Plus class="h-4 w-4" />
                <span>Tambah Ruang</span>
            </Button>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-neutral-200 bg-neutral-50/30 text-[11px] font-bold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-900/50"
                        >
                            <th class="w-16 px-6 py-3 text-center">No</th>
                            <th class="px-6 py-3">Nama Ruang</th>
                            <th class="px-6 py-3">Kode</th>
                            <th class="w-48 px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                    >
                        <tr
                            v-for="(item, idx) in ruang.data"
                            :key="item.id"
                            class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20"
                        >
                            <td
                                class="px-6 py-3.5 text-center font-medium text-neutral-400 dark:text-neutral-500"
                            >
                                {{ idx + 1 }}
                            </td>
                            <td
                                class="px-6 py-3.5 font-bold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ item.nama_ruang }}
                            </td>
                            <td
                                class="dark:text-neutral-405 px-6 py-3.5 font-mono text-xs font-semibold text-neutral-500"
                            >
                                {{ item.kode_ruang }}
                            </td>
                            <td
                                class="space-x-1.5 px-6 py-3.5 text-right whitespace-nowrap"
                            >
                                <Button
                                    size="sm"
                                    @click="openEditModal(item)"
                                    class="h-8 rounded bg-amber-500 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700"
                                >
                                    <Edit2 class="mr-1 h-3.5 w-3.5" />
                                    Edit
                                </Button>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="handleDelete(item.id)"
                                    class="h-8 rounded bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800"
                                >
                                    <Trash2 class="mr-1 h-3.5 w-3.5" />
                                    Hapus
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="ruang.data.length === 0">
                            <td
                                colspan="4"
                                class="px-6 py-8 text-center text-neutral-500"
                            >
                                Belum ada data ruang ujian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah -->
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
                    Tambah Ruang Ujian
                </h3>
                <button
                    type="button"
                    @click="isAddModalOpen = false"
                    class="hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer rounded-lg p-1 text-neutral-400"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitAdd" class="space-y-4 p-6">
                <div class="grid gap-2">
                    <Label for="nama_ruang"
                        >Nama Ruang <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="nama_ruang"
                        v-model="addForm.nama_ruang"
                        required
                        placeholder="Contoh: Lab Komputer 1"
                    />
                    <InputError :message="addForm.errors.nama_ruang" />
                </div>
                <div class="grid gap-2">
                    <Label for="kode_ruang"
                        >Kode Ruang <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="kode_ruang"
                        v-model="addForm.kode_ruang"
                        required
                        placeholder="Contoh: LAB-01"
                    />
                    <InputError :message="addForm.errors.kode_ruang" />
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

    <!-- Modal Form: Edit -->
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
                    Edit Ruang Ujian
                </h3>
                <button
                    type="button"
                    @click="isEditModalOpen = false"
                    class="hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer rounded-lg p-1 text-neutral-400"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitEdit" class="space-y-4 p-6">
                <div class="grid gap-2">
                    <Label for="edit_nama_ruang"
                        >Nama Ruang <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="edit_nama_ruang"
                        v-model="editForm.nama_ruang"
                        required
                        placeholder="Contoh: Lab Komputer 1"
                    />
                    <InputError :message="editForm.errors.nama_ruang" />
                </div>
                <div class="grid gap-2">
                    <Label for="edit_kode_ruang"
                        >Kode Ruang <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="edit_kode_ruang"
                        v-model="editForm.kode_ruang"
                        required
                        placeholder="Contoh: LAB-01"
                    />
                    <InputError :message="editForm.errors.kode_ruang" />
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
