<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Calendar,
    Clock,
    Trash2,
    CheckCircle,
    Plus,
    X,
    Edit2,
} from 'lucide-vue-next';

import {
    store as storeYear,
    update as updateYear,
    activate as activateYear,
    destroy as destroyYear,
} from '@/routes/master/tahun-pelajaran';
import {
    store as storeSmt,
    update as updateSmt,
    activate as activateSmt,
    destroy as destroySmt,
} from '@/routes/master/semester';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Data Master',
                href: '#',
            },
            {
                title: 'Tahun & Semester',
                href: '/master/tahun-pelajaran',
            },
        ],
    },
});

defineProps<{
    years: Array<{
        id: number;
        tahun: string;
        active: boolean;
    }>;
    semesters: Array<{
        id: number;
        smt: string;
        nama_smt: string;
        active: boolean;
    }>;
}>();

const isYearModalOpen = ref(false);
const isSmtModalOpen = ref(false);
const isEditYearModalOpen = ref(false);
const isEditSmtModalOpen = ref(false);

const selectedYear = ref<{ id: number; tahun: string } | null>(null);
const selectedSmt = ref<{ id: number; smt: string; nama_smt: string } | null>(
    null,
);

const yearForm = useForm({
    tahun: '',
    active: false,
});

const smtForm = useForm({
    smt: '',
    nama_smt: '',
    active: false,
});

const editYearForm = useForm({
    tahun: '',
});

const editSmtForm = useForm({
    smt: '',
    nama_smt: '',
});

const submitYear = () => {
    yearForm.post(storeYear.url(), {
        onSuccess: () => {
            isYearModalOpen.value = false;
            yearForm.reset();
        },
    });
};

const submitSmt = () => {
    smtForm.post(storeSmt.url(), {
        onSuccess: () => {
            isSmtModalOpen.value = false;
            smtForm.reset();
        },
    });
};

const handleActivateYear = (id: number) => {
    yearForm.post(activateYear.url(id));
};

const handleActivateSmt = (id: number) => {
    smtForm.post(activateSmt.url(id));
};

const handleDeleteYear = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus tahun pelajaran ini?')) {
        yearForm.delete(destroyYear.url(id));
    }
};

const handleDeleteSmt = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus semester ini?')) {
        smtForm.delete(destroySmt.url(id));
    }
};

const openEditYearModal = (year: { id: number; tahun: string }) => {
    selectedYear.value = year;
    editYearForm.tahun = year.tahun;
    editYearForm.clearErrors();
    isEditYearModalOpen.value = true;
};

const openEditSmtModal = (semester: {
    id: number;
    smt: string;
    nama_smt: string;
}) => {
    selectedSmt.value = semester;
    editSmtForm.smt = semester.smt;
    editSmtForm.nama_smt = semester.nama_smt;
    editSmtForm.clearErrors();
    isEditSmtModalOpen.value = true;
};

const submitEditYear = () => {
    if (!selectedYear.value) return;
    editYearForm.put(updateYear.url(selectedYear.value.id), {
        onSuccess: () => {
            isEditYearModalOpen.value = false;
            editYearForm.reset();
            selectedYear.value = null;
        },
    });
};

const submitEditSmt = () => {
    if (!selectedSmt.value) return;
    editSmtForm.put(updateSmt.url(selectedSmt.value.id), {
        onSuccess: () => {
            isEditSmtModalOpen.value = false;
            editSmtForm.reset();
            selectedSmt.value = null;
        },
    });
};
</script>

<template>
    <Head title="Periode Akademik" />

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">
        <Heading
            title="Periode Akademik"
            description="Atur tahun pelajaran dan semester aktif untuk mempartisi data jadwal ujian, mata pelajaran, dan penilaian siswa."
        />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- Left Card: Tahun Pelajaran (takes 7 cols on large screens) -->
            <div
                class="flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition-all lg:col-span-7 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
                >
                    <div class="flex items-center gap-2">
                        <Calendar class="h-5 w-5 text-zinc-500" />
                        <h3
                            class="text-sm font-bold tracking-wider text-neutral-800 uppercase dark:text-neutral-200"
                        >
                            Tahun Pelajaran
                        </h3>
                    </div>
                    <Button
                        @click="isYearModalOpen = true"
                        size="sm"
                        class="hover:bg-zinc-850 flex items-center gap-1 bg-zinc-900 font-semibold text-white shadow-sm transition-colors dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                    >
                        <Plus class="h-4 w-4" />
                        <span>Tambah</span>
                    </Button>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-neutral-200 bg-neutral-50/30 text-[11px] font-bold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <th class="w-16 px-4 py-3.5 text-center">No</th>
                                <th class="px-4 py-3.5">Tahun Pelajaran</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="w-36 px-4 py-3.5 text-right">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                        >
                            <tr
                                v-for="(year, idx) in years"
                                :key="year.id"
                                class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20"
                            >
                                <td
                                    class="px-4 py-4 text-center font-medium text-neutral-400 dark:text-neutral-500"
                                >
                                    {{ idx + 1 }}
                                </td>
                                <td
                                    class="px-4 py-4 font-semibold text-neutral-800 dark:text-neutral-200"
                                >
                                    {{ year.tahun }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span
                                        v-if="year.active"
                                        class="inline-flex items-center gap-1 rounded border border-emerald-200/50 bg-emerald-50 px-3 py-1 text-xs font-bold tracking-wider text-emerald-700 uppercase dark:border-emerald-900/30 dark:bg-emerald-950/30 dark:text-emerald-400"
                                    >
                                        <CheckCircle class="h-3.5 w-3.5" />
                                        <span>Aktif</span>
                                    </span>
                                    <Button
                                        v-else
                                        size="sm"
                                        @click="handleActivateYear(year.id)"
                                        class="bg-blue-600 px-3 py-1 text-[10px] font-bold tracking-wider text-white uppercase shadow-sm transition-all hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                                    >
                                        Aktifkan
                                    </Button>
                                </td>
                                <td
                                    class="space-x-2 px-4 py-4 text-right whitespace-nowrap"
                                >
                                    <Button
                                        size="sm"
                                        @click="openEditYearModal(year)"
                                        class="h-8 rounded bg-amber-500 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        @click="handleDeleteYear(year.id)"
                                        :disabled="year.active"
                                        class="h-8 rounded bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-rose-700 dark:hover:bg-rose-800"
                                    >
                                        Hapus
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="years.length === 0">
                                <td
                                    colspan="4"
                                    class="px-6 py-8 text-center text-neutral-500"
                                >
                                    Belum ada data tahun pelajaran.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Card: Semester (takes 5 cols on large screens) -->
            <div
                class="flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition-all lg:col-span-5 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
                >
                    <div class="flex items-center gap-2">
                        <Clock class="h-5 w-5 text-zinc-500" />
                        <h3
                            class="text-sm font-bold tracking-wider text-neutral-800 uppercase dark:text-neutral-200"
                        >
                            Semester
                        </h3>
                    </div>
                    <Button
                        @click="isSmtModalOpen = true"
                        size="sm"
                        class="hover:bg-zinc-855 flex items-center gap-1 bg-zinc-900 font-semibold text-white shadow-sm transition-colors dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                    >
                        <Plus class="h-4 w-4" />
                        <span>Tambah</span>
                    </Button>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-neutral-200 bg-neutral-50/30 text-[11px] font-bold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <th class="w-16 px-4 py-3.5 text-center">No</th>
                                <th class="px-4 py-3.5">Semester</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="w-36 px-4 py-3.5 text-right">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                        >
                            <tr
                                v-for="(semester, idx) in semesters"
                                :key="semester.id"
                                class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20"
                            >
                                <td
                                    class="px-4 py-4 text-center font-medium text-neutral-400 dark:text-neutral-500"
                                >
                                    {{ idx + 1 }}
                                </td>
                                <td
                                    class="px-4 py-4 font-semibold text-neutral-800 dark:text-neutral-200"
                                >
                                    {{ semester.nama_smt }}
                                    <span
                                        class="font-normal text-neutral-400 dark:text-neutral-500"
                                        >({{ semester.smt }})</span
                                    >
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span
                                        v-if="semester.active"
                                        class="inline-flex items-center gap-1 rounded border border-emerald-200/50 bg-emerald-50 px-3 py-1 text-xs font-bold tracking-wider text-emerald-700 uppercase dark:border-emerald-900/30 dark:bg-emerald-950/30 dark:text-emerald-400"
                                    >
                                        <CheckCircle class="h-3.5 w-3.5" />
                                        <span>Aktif</span>
                                    </span>
                                    <Button
                                        v-else
                                        size="sm"
                                        @click="handleActivateSmt(semester.id)"
                                        class="bg-blue-600 px-3 py-1 text-[10px] font-bold tracking-wider text-white uppercase shadow-sm transition-all hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                                    >
                                        Aktifkan
                                    </Button>
                                </td>
                                <td
                                    class="space-x-2 px-4 py-4 text-right whitespace-nowrap"
                                >
                                    <Button
                                        size="sm"
                                        @click="openEditSmtModal(semester)"
                                        class="h-8 rounded bg-amber-500 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        @click="handleDeleteSmt(semester.id)"
                                        :disabled="semester.active"
                                        class="h-8 rounded bg-rose-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-rose-700 dark:hover:bg-rose-800"
                                    >
                                        Hapus
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="semesters.length === 0">
                                <td
                                    colspan="4"
                                    class="px-6 py-8 text-center text-neutral-500"
                                >
                                    Belum ada data semester.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form: Tambah Tahun Pelajaran -->
    <div
        v-if="isYearModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
            >
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">
                    Tambah Tahun Pelajaran
                </h3>
                <button
                    @click="isYearModalOpen = false"
                    class="hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer rounded-lg p-1 text-neutral-400"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitYear" class="space-y-4 p-6">
                <div class="grid gap-2">
                    <Label for="tahun"
                        >Tahun Pelajaran
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="tahun"
                        v-model="yearForm.tahun"
                        required
                        placeholder="Contoh: 2025/2026"
                    />
                    <InputError :message="yearForm.errors.tahun" />
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="active_year"
                        type="checkbox"
                        v-model="yearForm.active"
                        class="cursor-pointer rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-700"
                    />
                    <Label
                        for="active_year"
                        class="text-neutral-750 cursor-pointer text-sm font-medium dark:text-neutral-300"
                        >Jadikan Tahun Pelajaran Aktif</Label
                    >
                    <InputError :message="yearForm.errors.active" />
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-zinc-800"
                >
                    <Button
                        type="button"
                        variant="outline"
                        @click="isYearModalOpen = false"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="yearForm.processing"
                        class="bg-zinc-900 font-semibold text-white hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                    >
                        Simpan
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Edit Tahun Pelajaran -->
    <div
        v-if="isEditYearModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
            >
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">
                    Edit Tahun Pelajaran
                </h3>
                <button
                    @click="isEditYearModalOpen = false"
                    class="hover:text-neutral-650 dark:hover:text-neutral-255 cursor-pointer rounded-lg p-1 text-neutral-400"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitEditYear" class="space-y-4 p-6">
                <div class="grid gap-2">
                    <Label for="edit_tahun"
                        >Tahun Pelajaran
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="edit_tahun"
                        v-model="editYearForm.tahun"
                        required
                        placeholder="Contoh: 2025/2026"
                    />
                    <InputError :message="editYearForm.errors.tahun" />
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-zinc-800"
                >
                    <Button
                        type="button"
                        variant="outline"
                        @click="isEditYearModalOpen = false"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="editYearForm.processing"
                        class="bg-zinc-900 font-semibold text-white hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                    >
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Tambah Semester -->
    <div
        v-if="isSmtModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
            >
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">
                    Tambah Semester Baru
                </h3>
                <button
                    @click="isSmtModalOpen = false"
                    class="hover:text-neutral-650 dark:hover:text-neutral-255 cursor-pointer rounded-lg p-1 text-neutral-400"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitSmt" class="space-y-4 p-6">
                <div class="grid gap-2">
                    <Label for="smt"
                        >Kode Semester
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="smt"
                        v-model="smtForm.smt"
                        required
                        placeholder="Contoh: 1, 2, ganjil, genap"
                    />
                    <InputError :message="smtForm.errors.smt" />
                </div>

                <div class="grid gap-2">
                    <Label for="nama_smt"
                        >Nama Semester
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="nama_smt"
                        v-model="smtForm.nama_smt"
                        required
                        placeholder="Contoh: Ganjil, Genap"
                    />
                    <InputError :message="smtForm.errors.nama_smt" />
                </div>

                <div class="flex items-center gap-2">
                    <input
                        id="active_smt"
                        type="checkbox"
                        v-model="smtForm.active"
                        class="cursor-pointer rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-zinc-700"
                    />
                    <Label
                        for="active_smt"
                        class="text-neutral-750 cursor-pointer text-sm font-medium dark:text-neutral-300"
                        >Jadikan Semester Aktif</Label
                    >
                    <InputError :message="smtForm.errors.active" />
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-zinc-800"
                >
                    <Button
                        type="button"
                        variant="outline"
                        @click="isSmtModalOpen = false"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="smtForm.processing"
                        class="bg-zinc-900 font-semibold text-white hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                    >
                        Simpan
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Edit Semester -->
    <div
        v-if="isEditSmtModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm transition-opacity"
    >
        <div
            class="flex w-full max-w-md flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-zinc-800"
            >
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">
                    Edit Semester
                </h3>
                <button
                    @click="isEditSmtModalOpen = false"
                    class="hover:text-neutral-650 dark:hover:text-neutral-255 cursor-pointer rounded-lg p-1 text-neutral-400"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <form @submit.prevent="submitEditSmt" class="space-y-4 p-6">
                <div class="grid gap-2">
                    <Label for="edit_smt"
                        >Kode Semester
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="edit_smt"
                        v-model="editSmtForm.smt"
                        required
                        placeholder="Contoh: 1, 2, ganjil, genap"
                    />
                    <InputError :message="editSmtForm.errors.smt" />
                </div>

                <div class="grid gap-2">
                    <Label for="edit_nama_smt"
                        >Nama Semester
                        <span class="text-rose-500">*</span></Label
                    >
                    <Input
                        id="edit_nama_smt"
                        v-model="editSmtForm.nama_smt"
                        required
                        placeholder="Contoh: Ganjil, Genap"
                    />
                    <InputError :message="editSmtForm.errors.nama_smt" />
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-neutral-200 pt-4 dark:border-zinc-800"
                >
                    <Button
                        type="button"
                        variant="outline"
                        @click="isEditSmtModalOpen = false"
                        >Batal</Button
                    >
                    <Button
                        type="submit"
                        :disabled="editSmtForm.processing"
                        class="bg-zinc-900 font-semibold text-white hover:bg-zinc-800 dark:bg-zinc-50 dark:text-zinc-950 dark:hover:bg-zinc-200"
                    >
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
