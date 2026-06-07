<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Plus, X, Edit2, Trash2 } from 'lucide-vue-next';
import {
    store as storeSesi,
    update as updateSesi,
    destroy as destroySesi,
} from '@/routes/cbt/sesi';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'CBT',
                href: '#',
            },
            {
                title: 'Sesi Ujian',
                href: '/cbt/sesi',
            },
        ],
    },
});

const props = defineProps<{
    sesi: {
        data: Array<{
            id: number;
            nama_sesi: string;
            kode_sesi: string;
            waktu_mulai: string;
            waktu_akhir: string;
            aktif: boolean;
        }>;
        links: any[];
    };
}>();

const isAddModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedItem = ref<any>(null);

const addForm = useForm({
    nama_sesi: '',
    kode_sesi: '',
    waktu_mulai: '07:30',
    waktu_akhir: '09:30',
    aktif: true,
});

const editForm = useForm({
    nama_sesi: '',
    kode_sesi: '',
    waktu_mulai: '',
    waktu_akhir: '',
    aktif: true,
});

const submitAdd = () => {
    addForm.post(storeSesi.url(), {
        onSuccess: () => {
            isAddModalOpen.value = false;
            addForm.reset();
        },
    });
};

const openEditModal = (item: any) => {
    selectedItem.value = item;
    editForm.nama_sesi = item.nama_sesi;
    editForm.kode_sesi = item.kode_sesi;
    editForm.waktu_mulai = item.waktu_mulai;
    editForm.waktu_akhir = item.waktu_akhir;
    editForm.aktif = !!item.aktif;
    editForm.clearErrors();
    isEditModalOpen.value = true;
};

const submitEdit = () => {
    if (!selectedItem.value) return;
    editForm.put(updateSesi.url(selectedItem.value.id), {
        onSuccess: () => {
            isEditModalOpen.value = false;
            editForm.reset();
            selectedItem.value = null;
        },
    });
};

const handleDelete = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus sesi ujian ini?')) {
        router.delete(destroySesi.url(id));
    }
};

const toggleAktif = (item: any) => {
    router.put(updateSesi.url(item.id), {
        ...item,
        aktif: !item.aktif,
    });
};
</script>

<template>
    <Head title="Sesi Ujian CBT" />

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Sesi Ujian"
                description="Kelola sesi ujian harian dan rentang waktunya."
            />
            <Button
                @click="isAddModalOpen = true"
                class="flex items-center gap-1 bg-zinc-900 font-semibold text-white shadow-sm transition-colors hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
            >
                <Plus class="h-4 w-4" />
                <span>Tambah Sesi</span>
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
                            <th class="px-6 py-3">Nama Sesi</th>
                            <th class="px-6 py-3">Kode</th>
                            <th class="px-6 py-3 text-center">Jam Mulai</th>
                            <th class="px-6 py-3 text-center">Jam Akhir</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="w-48 px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                    >
                        <tr
                            v-for="(item, idx) in sesi.data"
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
                                {{ item.nama_sesi }}
                            </td>
                            <td
                                class="dark:text-neutral-405 px-6 py-3.5 font-mono text-xs font-semibold text-neutral-500"
                            >
                                {{ item.kode_sesi }}
                            </td>
                            <td
                                class="px-6 py-3.5 text-center font-semibold text-neutral-700 dark:text-neutral-300"
                            >
                                {{ item.waktu_mulai }}
                            </td>
                            <td
                                class="px-6 py-3.5 text-center font-semibold text-neutral-700 dark:text-neutral-300"
                            >
                                {{ item.waktu_akhir }}
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <button
                                    type="button"
                                    @click="toggleAktif(item)"
                                    class="inline-flex items-center justify-center rounded-full px-2.5 py-0.5 text-xs font-semibold transition-colors focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none"
                                    :class="
                                        item.aktif
                                            ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400'
                                            : 'bg-neutral-100 text-neutral-800 hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-400'
                                    "
                                >
                                    {{ item.aktif ? 'Aktif' : 'Nonaktif' }}
                                </button>
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
                        <tr v-if="sesi.data.length === 0">
                            <td
                                colspan="7"
                                class="px-6 py-8 text-center text-neutral-500"
                            >
                                Belum ada data sesi ujian.
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
                    Tambah Sesi Ujian
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
                    <Label for="nama_sesi"
                        >Nama Sesi <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="nama_sesi"
                        v-model="addForm.nama_sesi"
                        required
                        placeholder="Contoh: Sesi 1"
                    />
                    <InputError :message="addForm.errors.nama_sesi" />
                </div>
                <div class="grid gap-2">
                    <Label for="kode_sesi"
                        >Kode Sesi <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="kode_sesi"
                        v-model="addForm.kode_sesi"
                        required
                        placeholder="Contoh: S1"
                    />
                    <InputError :message="addForm.errors.kode_sesi" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="waktu_mulai"
                            >Jam Mulai
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="waktu_mulai"
                            type="time"
                            v-model="addForm.waktu_mulai"
                            required
                        />
                        <InputError :message="addForm.errors.waktu_mulai" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="waktu_akhir"
                            >Jam Akhir
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="waktu_akhir"
                            type="time"
                            v-model="addForm.waktu_akhir"
                            required
                        />
                        <InputError :message="addForm.errors.waktu_akhir" />
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <input
                        type="checkbox"
                        id="aktif"
                        v-model="addForm.aktif"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                    />
                    <Label for="aktif" class="cursor-pointer"
                        >Status Aktif</Label
                    >
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
                    Edit Sesi Ujian
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
                    <Label for="edit_nama_sesi"
                        >Nama Sesi <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="edit_nama_sesi"
                        v-model="editForm.nama_sesi"
                        required
                        placeholder="Contoh: Sesi 1"
                    />
                    <InputError :message="editForm.errors.nama_sesi" />
                </div>
                <div class="grid gap-2">
                    <Label for="edit_kode_sesi"
                        >Kode Sesi <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="edit_kode_sesi"
                        v-model="editForm.kode_sesi"
                        required
                        placeholder="Contoh: S1"
                    />
                    <InputError :message="editForm.errors.kode_sesi" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="edit_waktu_mulai"
                            >Jam Mulai
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="edit_waktu_mulai"
                            type="time"
                            v-model="editForm.waktu_mulai"
                            required
                        />
                        <InputError :message="editForm.errors.waktu_mulai" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="edit_waktu_akhir"
                            >Jam Akhir
                            <span class="text-rose-500">*</span></Label
                        >
                        <Input
                            id="edit_waktu_akhir"
                            type="time"
                            v-model="editForm.waktu_akhir"
                            required
                        />
                        <InputError :message="editForm.errors.waktu_akhir" />
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <input
                        type="checkbox"
                        id="edit_aktif"
                        v-model="editForm.aktif"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                    />
                    <Label for="edit_aktif" class="cursor-pointer"
                        >Status Aktif</Label
                    >
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
