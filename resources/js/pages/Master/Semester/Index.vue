<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Clock, Trash2, CheckCircle, AlertTriangle } from 'lucide-vue-next';

import { store, activate, destroy } from '@/routes/master/semester';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Data Master',
                href: '#',
            },
            {
                title: 'Semester',
                href: '/master/semester',
            },
        ],
    },
});

defineProps<{
    semesters: Array<{
        id: number;
        smt: string;
        nama_smt: string;
        active: boolean;
    }>;
}>();

const form = useForm({
    smt: '',
    nama_smt: '',
    active: false,
});

const submit = () => {
    form.post(store.url(), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const activateSemester = (id: number) => {
    form.post(activate.url(id));
};

const deleteSemester = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus semester ini?')) {
        form.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Semester" />

    <div class="px-6 py-6 max-w-5xl mx-auto space-y-6">
        <Heading
            title="Semester"
            description="Kelola semester aktif untuk mempartisi jadwal ujian, mata pelajaran, dan data rekap penilaian siswa."
        />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Left Side: Table List -->
            <div class="lg:col-span-2 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-neutral-50 dark:bg-zinc-800/50 border-b border-neutral-200 dark:border-zinc-800 text-xs font-semibold uppercase text-neutral-500 tracking-wider">
                            <th class="px-6 py-4">Kode Semester</th>
                            <th class="px-6 py-4">Nama Semester</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                        <tr v-for="semester in semesters" :key="semester.id" class="hover:bg-neutral-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                            <td class="px-6 py-4 font-medium text-neutral-800 dark:text-neutral-200 flex items-center gap-2">
                                <Clock class="w-4 h-4 text-neutral-400" />
                                <span>{{ semester.smt }}</span>
                            </td>
                            <td class="px-6 py-4 text-neutral-600 dark:text-neutral-400">
                                {{ semester.nama_smt }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span v-if="semester.active" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                                    <CheckCircle class="w-3 h-3" />
                                    <span>Aktif</span>
                                </span>
                                <span v-else class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    <span>Inaktif</span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <Button
                                    v-if="!semester.active"
                                    size="sm"
                                    variant="outline"
                                    @click="activateSemester(semester.id)"
                                    class="text-xs font-semibold text-emerald-600 hover:text-emerald-500 border-emerald-200 hover:border-emerald-300 bg-emerald-50/50 hover:bg-emerald-50"
                                >
                                    Aktifkan
                                </Button>
                                <Button
                                    v-if="!semester.active"
                                    size="sm"
                                    variant="ghost"
                                    @click="deleteSemester(semester.id)"
                                    class="text-rose-600 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="semesters.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-neutral-500">
                                <AlertTriangle class="w-8 h-8 mx-auto text-amber-500 mb-2" />
                                Belum ada data semester. Silakan tambahkan baru di sebelah kanan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Right Side: Form Create -->
            <div class="bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-500">Tambah Semester</h3>
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="smt">Kode Semester <span class="text-rose-500">*</span></Label>
                        <Input id="smt" v-model="form.smt" required placeholder="Contoh: 1, 2, atau ganjil, genap" />
                        <InputError :message="form.errors.smt" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="nama_smt">Nama Semester <span class="text-rose-500">*</span></Label>
                        <Input id="nama_smt" v-model="form.nama_smt" required placeholder="Contoh: Ganjil, Genap" />
                        <InputError :message="form.errors.nama_smt" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="active" type="checkbox" v-model="form.active" class="rounded border-neutral-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-500" />
                        <Label for="active" class="text-sm font-medium text-neutral-700 dark:text-neutral-300 cursor-pointer">Jadikan Semester Aktif</Label>
                        <InputError :message="form.errors.active" />
                    </div>

                    <Button type="submit" :disabled="form.processing" class="w-full bg-zinc-900 hover:bg-zinc-800 text-white font-semibold shadow">
                        Tambah Data
                    </Button>
                </form>
            </div>
        </div>
    </div>
</template>
