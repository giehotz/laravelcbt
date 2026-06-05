<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Calendar, Clock, Trash2, CheckCircle, Plus, X, Edit2 } from 'lucide-vue-next';

import { store as storeYear, update as updateYear, activate as activateYear, destroy as destroyYear } from '@/routes/master/tahun-pelajaran';
import { store as storeSmt, update as updateSmt, activate as activateSmt, destroy as destroySmt } from '@/routes/master/semester';

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
const selectedSmt = ref<{ id: number; smt: string; nama_smt: string } | null>(null);

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

const openEditSmtModal = (semester: { id: number; smt: string; nama_smt: string }) => {
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

    <div class="px-6 py-6 max-w-7xl mx-auto space-y-6">
        <Heading
            title="Periode Akademik"
            description="Atur tahun pelajaran dan semester aktif untuk mempartisi data jadwal ujian, mata pelajaran, dan penilaian siswa."
        />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Card: Tahun Pelajaran (takes 7 cols on large screens) -->
            <div class="lg:col-span-7 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col transition-all">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center bg-neutral-50/50 dark:bg-zinc-800/30">
                    <div class="flex items-center gap-2">
                        <Calendar class="w-5 h-5 text-zinc-500" />
                        <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-sm uppercase tracking-wider">Tahun Pelajaran</h3>
                    </div>
                    <Button @click="isYearModalOpen = true" size="sm" class="bg-zinc-900 hover:bg-zinc-850 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold flex items-center gap-1 shadow-sm transition-colors">
                        <Plus class="w-4 h-4" />
                        <span>Tambah</span>
                    </Button>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                                <th class="px-4 py-3.5 w-16 text-center">No</th>
                                <th class="px-4 py-3.5">Tahun Pelajaran</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-4 py-3.5 text-right w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                            <tr v-for="(year, idx) in years" :key="year.id" class="hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                                <td class="px-4 py-4 text-center font-medium text-neutral-400 dark:text-neutral-500">{{ idx + 1 }}</td>
                                <td class="px-4 py-4 font-semibold text-neutral-800 dark:text-neutral-200">{{ year.tahun }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span v-if="year.active" class="inline-flex items-center gap-1 px-3 py-1 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/30 text-xs font-bold uppercase tracking-wider">
                                        <CheckCircle class="w-3.5 h-3.5" />
                                        <span>Aktif</span>
                                    </span>
                                    <Button
                                        v-else
                                        size="sm"
                                        @click="handleActivateYear(year.id)"
                                        class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-bold px-3 py-1 text-[10px] uppercase tracking-wider shadow-sm transition-all"
                                    >
                                        Aktifkan
                                    </Button>
                                </td>
                                <td class="px-4 py-4 text-right space-x-2 whitespace-nowrap">
                                    <Button
                                        size="sm"
                                        @click="openEditYearModal(year)"
                                        class="bg-amber-500 hover:bg-amber-600 text-white dark:bg-amber-600 dark:hover:bg-amber-700 font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        @click="handleDeleteYear(year.id)"
                                        :disabled="year.active"
                                        class="bg-rose-600 hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800 text-white font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        Hapus
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="years.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-neutral-500">
                                    Belum ada data tahun pelajaran.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Card: Semester (takes 5 cols on large screens) -->
            <div class="lg:col-span-5 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col transition-all">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center bg-neutral-50/50 dark:bg-zinc-800/30">
                    <div class="flex items-center gap-2">
                        <Clock class="w-5 h-5 text-zinc-500" />
                        <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-sm uppercase tracking-wider">Semester</h3>
                    </div>
                    <Button @click="isSmtModalOpen = true" size="sm" class="bg-zinc-900 hover:bg-zinc-855 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold flex items-center gap-1 shadow-sm transition-colors">
                        <Plus class="w-4 h-4" />
                        <span>Tambah</span>
                    </Button>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                                <th class="px-4 py-3.5 w-16 text-center">No</th>
                                <th class="px-4 py-3.5">Semester</th>
                                <th class="px-4 py-3.5 text-center">Status</th>
                                <th class="px-4 py-3.5 text-right w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                            <tr v-for="(semester, idx) in semesters" :key="semester.id" class="hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                                <td class="px-4 py-4 text-center font-medium text-neutral-400 dark:text-neutral-500">{{ idx + 1 }}</td>
                                <td class="px-4 py-4 font-semibold text-neutral-800 dark:text-neutral-200">
                                    {{ semester.nama_smt }} <span class="text-neutral-400 dark:text-neutral-500 font-normal">({{ semester.smt }})</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span v-if="semester.active" class="inline-flex items-center gap-1 px-3 py-1 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/30 text-xs font-bold uppercase tracking-wider">
                                        <CheckCircle class="w-3.5 h-3.5" />
                                        <span>Aktif</span>
                                    </span>
                                    <Button
                                        v-else
                                        size="sm"
                                        @click="handleActivateSmt(semester.id)"
                                        class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-bold px-3 py-1 text-[10px] uppercase tracking-wider shadow-sm transition-all"
                                    >
                                        Aktifkan
                                    </Button>
                                </td>
                                <td class="px-4 py-4 text-right space-x-2 whitespace-nowrap">
                                    <Button
                                        size="sm"
                                        @click="openEditSmtModal(semester)"
                                        class="bg-amber-500 hover:bg-amber-600 text-white dark:bg-amber-600 dark:hover:bg-amber-700 font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        @click="handleDeleteSmt(semester.id)"
                                        :disabled="semester.active"
                                        class="bg-rose-600 hover:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-800 text-white font-semibold text-xs px-2.5 py-1.5 h-8 rounded shadow-sm transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        Hapus
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="semesters.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-neutral-500">
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
    <div v-if="isYearModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Tambah Tahun Pelajaran</h3>
                <button @click="isYearModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-250 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitYear" class="p-6 space-y-4">
                <div class="grid gap-2">
                    <Label for="tahun">Tahun Pelajaran <span class="text-rose-500">*</span></Label>
                    <Input id="tahun" v-model="yearForm.tahun" required placeholder="Contoh: 2025/2026" />
                    <InputError :message="yearForm.errors.tahun" />
                </div>

                <div class="flex items-center gap-2">
                    <input id="active_year" type="checkbox" v-model="yearForm.active" class="rounded border-neutral-300 dark:border-zinc-700 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                    <Label for="active_year" class="text-sm font-medium text-neutral-750 dark:text-neutral-300 cursor-pointer">Jadikan Tahun Pelajaran Aktif</Label>
                    <InputError :message="yearForm.errors.active" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-zinc-800">
                    <Button type="button" variant="outline" @click="isYearModalOpen = false">Batal</Button>
                    <Button type="submit" :disabled="yearForm.processing" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold">
                        Simpan
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Edit Tahun Pelajaran -->
    <div v-if="isEditYearModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Edit Tahun Pelajaran</h3>
                <button @click="isEditYearModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-255 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitEditYear" class="p-6 space-y-4">
                <div class="grid gap-2">
                    <Label for="edit_tahun">Tahun Pelajaran <span class="text-rose-500">*</span></Label>
                    <Input id="edit_tahun" v-model="editYearForm.tahun" required placeholder="Contoh: 2025/2026" />
                    <InputError :message="editYearForm.errors.tahun" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-zinc-800">
                    <Button type="button" variant="outline" @click="isEditYearModalOpen = false">Batal</Button>
                    <Button type="submit" :disabled="editYearForm.processing" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold">
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Tambah Semester -->
    <div v-if="isSmtModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Tambah Semester Baru</h3>
                <button @click="isSmtModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-255 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitSmt" class="p-6 space-y-4">
                <div class="grid gap-2">
                    <Label for="smt">Kode Semester <span class="text-rose-500">*</span></Label>
                    <Input id="smt" v-model="smtForm.smt" required placeholder="Contoh: 1, 2, ganjil, genap" />
                    <InputError :message="smtForm.errors.smt" />
                </div>

                <div class="grid gap-2">
                    <Label for="nama_smt">Nama Semester <span class="text-rose-500">*</span></Label>
                    <Input id="nama_smt" v-model="smtForm.nama_smt" required placeholder="Contoh: Ganjil, Genap" />
                    <InputError :message="smtForm.errors.nama_smt" />
                </div>

                <div class="flex items-center gap-2">
                    <input id="active_smt" type="checkbox" v-model="smtForm.active" class="rounded border-neutral-300 dark:border-zinc-700 text-blue-600 focus:ring-blue-500 cursor-pointer" />
                    <Label for="active_smt" class="text-sm font-medium text-neutral-750 dark:text-neutral-300 cursor-pointer">Jadikan Semester Aktif</Label>
                    <InputError :message="smtForm.errors.active" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-zinc-800">
                    <Button type="button" variant="outline" @click="isSmtModalOpen = false">Batal</Button>
                    <Button type="submit" :disabled="smtForm.processing" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold">
                        Simpan
                    </Button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form: Edit Semester -->
    <div v-if="isEditSmtModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
        <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 flex justify-between items-center">
                <h3 class="font-bold text-neutral-800 dark:text-neutral-200">Edit Semester</h3>
                <button @click="isEditSmtModalOpen = false" class="text-neutral-400 hover:text-neutral-650 dark:hover:text-neutral-255 cursor-pointer p-1 rounded-lg">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <form @submit.prevent="submitEditSmt" class="p-6 space-y-4">
                <div class="grid gap-2">
                    <Label for="edit_smt">Kode Semester <span class="text-rose-500">*</span></Label>
                    <Input id="edit_smt" v-model="editSmtForm.smt" required placeholder="Contoh: 1, 2, ganjil, genap" />
                    <InputError :message="editSmtForm.errors.smt" />
                </div>

                <div class="grid gap-2">
                    <Label for="edit_nama_smt">Nama Semester <span class="text-rose-500">*</span></Label>
                    <Input id="edit_nama_smt" v-model="editSmtForm.nama_smt" required placeholder="Contoh: Ganjil, Genap" />
                    <InputError :message="editSmtForm.errors.nama_smt" />
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-zinc-800">
                    <Button type="button" variant="outline" @click="isEditSmtModalOpen = false">Batal</Button>
                    <Button type="submit" :disabled="editSmtForm.processing" class="bg-zinc-900 hover:bg-zinc-800 dark:bg-zinc-50 dark:hover:bg-zinc-200 dark:text-zinc-950 text-white font-semibold">
                        Simpan Perubahan
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
