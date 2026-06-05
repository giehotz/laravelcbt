<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Plus, X, Edit2, Trash2 } from 'lucide-vue-next';
import { store as storeRuang, update as updateRuang, destroy as destroyRuang } from '@/routes/cbt/ruang';

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
const selectedItem = ref<{ id: number; nama_ruang: string; kode_ruang: string } | null>(null);

const addForm = useForm({
    nama_ruang: '',
    kode_ruang: '',
});

const editForm = useForm({
    nama_ruang: '',
    kode_ruang: '',
});

// Auto-generate code from name
watch(() => addForm.nama_ruang, (newVal) => {
    if (newVal && !addForm.kode_ruang) {
        addForm.kode_ruang = newVal.trim().substring(0, 15).toUpperCase().replace(/\s+/g, '-');
    }
});

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

    <div class="px-6 py-6 max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <Heading
                title="Ruang Ujian"
                description="Kelola daftar ruang yang digunakan untuk pelaksanaan ujian."
            />
            <Button @click="isAddModalOpen = true" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold flex items-center gap-1 shadow-sm transition-colors">
                <Plus class="w-4 h-4" />
                <span>Tambah Ruang</span>
            </Button>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                            <th class="px-6 py-3 w-16 text-center">No</th>
                            <th class="px-6 py-3">Nama Ruang</th>
                            <th class="px-6 py-3">Kode</th>
                            <th class="px-6 py-3 text-right w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                        <tr v-for="(item, idx) in ruang.data" :key="item.id" class="hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                            <td class="px-6 py-3.5 text-center font-medium text-neutral-400 dark:text-neutral-500">{{ idx + 1 }}</td>
                            <td class="px-6 py-3.5 font-bold text-neutral-800 dark:text-neutral-200">{{ item.nama_ruang }}</td>
                            <td class="px-6 py-3.5 font-mono text-xs font-semibold text-neutral-500 dark:text-neutral-405">{{ item.kode_ruang }}</td>
                            <td class="px-6 py-3.5 text-right space-x-1.5 whitespace-nowrap">
                                <Button
                                    size="sm"
                                    @click="openEditModal(item)"
                                    class="bg-amber-500 hover:bg-amber-600 text-white dark:bg-amber-600 dark:hover:bg-amber-700 font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                >
                                    <Edit2 class="w-3.5 h-3.5 mr-1" />
                                    Edit
                                </Button>
                                <Button
                                    size="sm"
                                    variant="destructive"
                                    @click="handleDelete(item.id)"
                                    class="bg-rose-600 hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800 text-white font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                >
                                    <Trash2 class="w-3.5 h-3.5 mr-1" />
                                    Hapus
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="ruang.data.length === 0">
                            <td colspan="4" class="px-6 py-8 text-center text-neutral-500">
                                Belum ada data ruang ujian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah -->
    <div v-if="isAddModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Tambah Ruang Ujian</h3>
                <button type="button" @click="isAddModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitAdd" class="p-6 space-y-4">
                <div class="grid gap-2">
                    <Label for="nama_ruang">Nama Ruang <span class="text-rose-500">*</span></Label>
                    <Input id="nama_ruang" v-model="addForm.nama_ruang" required placeholder="Contoh: Lab Komputer 1" />
                    <InputError :message="addForm.errors.nama_ruang" />
                </div>
                <div class="grid gap-2">
                    <Label for="kode_ruang">Kode Ruang <span class="text-rose-500">*</span></Label>
                    <Input id="kode_ruang" v-model="addForm.kode_ruang" required placeholder="Contoh: LAB-01" />
                    <InputError :message="addForm.errors.kode_ruang" />
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

    <!-- Modal Form: Edit -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Edit Ruang Ujian</h3>
                <button type="button" @click="isEditModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                <div class="grid gap-2">
                    <Label for="edit_nama_ruang">Nama Ruang <span class="text-rose-500">*</span></Label>
                    <Input id="edit_nama_ruang" v-model="editForm.nama_ruang" required placeholder="Contoh: Lab Komputer 1" />
                    <InputError :message="editForm.errors.nama_ruang" />
                </div>
                <div class="grid gap-2">
                    <Label for="edit_kode_ruang">Kode Ruang <span class="text-rose-500">*</span></Label>
                    <Input id="edit_kode_ruang" v-model="editForm.kode_ruang" required placeholder="Contoh: LAB-01" />
                    <InputError :message="editForm.errors.kode_ruang" />
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
