<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    ArrowLeft,
    Search,
    Plus,
    Trash2,
    Save,
    GraduationCap,
} from 'lucide-vue-next';

import students from '@/routes/master/kelas/students';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Data Master',
                href: '#',
            },
            {
                title: 'Rombel & Kelas',
                href: '/master/kelas',
            },
            {
                title: 'Kelola Anggota Kelas',
                href: '#',
            },
        ],
    },
});

const props = defineProps<{
    kelas: {
        id: number;
        nama_kelas: string;
        kode_kelas: string;
        tahun_pelajaran_id: number;
        semester_id: number;
        level_kelas?: { level: string };
        jurusan?: { nama_jurusan: string; kode_jurusan: string };
        wali_kelas?: { nama_guru: string };
        tahun_pelajaran?: { tahun: string };
        semester?: { nama_smt: string };
    };
    assigned: Array<{
        id: number;
        nama: string;
        nis: string;
        nisn: string;
    }>;
    unassigned: {
        data: Array<{
            id: number;
            nama: string;
            nis: string;
            nisn: string;
        }>;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    filters: {
        q: string;
    };
}>();

const localAssigned = ref([...props.assigned]);
const localUnassignedData = ref([...props.unassigned.data]);
const searchInput = ref(props.filters.q ?? '');

// Track search input and reload unassigned list with debounce or search click
const handleSearch = () => {
    router.get(
        window.location.pathname,
        { q: searchInput.value },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['unassigned', 'filters'],
            onSuccess: () => {
                // Filter out students that are already in localAssigned from the new unassigned list
                const assignedIds = localAssigned.value.map((s) => s.id);
                localUnassignedData.value = props.unassigned.data.filter(
                    (s) => !assignedIds.includes(s.id),
                );
            },
        },
    );
};

// Sync local data if props change (e.g. on pagination)
watch(
    () => props.unassigned.data,
    (newData) => {
        const assignedIds = localAssigned.value.map((s) => s.id);
        localUnassignedData.value = newData.filter(
            (s) => !assignedIds.includes(s.id),
        );
    },
    { deep: true },
);

const addStudent = (student: (typeof props.assigned)[0]) => {
    // Add to assigned
    if (!localAssigned.value.some((s) => s.id === student.id)) {
        localAssigned.value.push(student);
    }
    // Remove from unassigned list
    localUnassignedData.value = localUnassignedData.value.filter(
        (s) => s.id !== student.id,
    );
};

const removeStudent = (student: (typeof props.assigned)[0]) => {
    // Remove from assigned
    localAssigned.value = localAssigned.value.filter(
        (s) => s.id !== student.id,
    );
    // Put back to unassigned if it matches search
    const matchesSearch =
        !searchInput.value ||
        student.nama.toLowerCase().includes(searchInput.value.toLowerCase()) ||
        student.nis.includes(searchInput.value) ||
        student.nisn.includes(searchInput.value);

    if (
        matchesSearch &&
        !localUnassignedData.value.some((s) => s.id === student.id)
    ) {
        localUnassignedData.value.unshift(student);
    }
};

const submitAssignment = () => {
    const form = useForm({
        siswa_ids: localAssigned.value.map((s) => s.id),
    });

    form.put(students.update.url(props.kelas.id), {
        onSuccess: () => {
            // Success redirect is handled by controller
        },
    });
};
</script>

<template>
    <Head :title="'Kelola Siswa - ' + kelas.nama_kelas" />

    <div class="mx-auto max-w-7xl space-y-6 px-6 py-6">
        <div class="flex items-center gap-3">
            <Link
                href="/master/kelas"
                class="rounded-lg border border-neutral-200 p-2 text-neutral-500 transition-colors hover:bg-neutral-50 dark:border-zinc-800 dark:text-neutral-400 dark:hover:bg-zinc-800"
            >
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <Heading
                :title="'Kelola Roster Siswa — ' + kelas.nama_kelas"
                :description="
                    'Atur siswa terdaftar pada kelas tingkat ' +
                    (kelas.level_kelas?.level ?? '-') +
                    ' (' +
                    (kelas.jurusan?.nama_jurusan ?? 'Umum') +
                    ') untuk tahun pelajaran ' +
                    (kelas.tahun_pelajaran?.tahun ?? '-') +
                    ' / ' +
                    (kelas.semester?.nama_smt ?? '-')
                "
            />
        </div>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
            <!-- Left Pane: Unassigned Students (takes 7 cols) -->
            <div
                class="flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition-all lg:col-span-7 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="border-b border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
                >
                    <div
                        class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-2">
                            <GraduationCap class="h-5 w-5 text-zinc-500" />
                            <h3
                                class="text-sm font-bold tracking-wider text-neutral-800 uppercase dark:text-neutral-200"
                            >
                                Siswa Belum Punya Kelas
                            </h3>
                        </div>
                        <div class="relative w-full max-w-xs">
                            <Input
                                v-model="searchInput"
                                @keyup.enter="handleSearch"
                                placeholder="Cari nama, NIS..."
                                class="h-8 pl-8 text-xs"
                            />
                            <Search
                                class="absolute top-2.5 left-2.5 h-3.5 w-3.5 text-neutral-400"
                            />
                        </div>
                    </div>
                </div>

                <div class="min-h-[300px] overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-neutral-200 bg-neutral-50/30 text-[11px] font-bold tracking-wider text-neutral-500 uppercase dark:border-zinc-800 dark:bg-zinc-900/50"
                            >
                                <th class="w-14 px-4 py-3 text-center">No</th>
                                <th class="w-32 px-4 py-3">NIS / NISN</th>
                                <th class="px-4 py-3">Nama Lengkap</th>
                                <th class="w-20 px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-neutral-200 text-sm dark:divide-zinc-800"
                        >
                            <tr
                                v-for="(student, idx) in localUnassignedData"
                                :key="student.id"
                                class="transition-colors hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20"
                            >
                                <td
                                    class="px-4 py-3 text-center font-medium text-neutral-400 dark:text-neutral-500"
                                >
                                    {{ idx + 1 }}
                                </td>
                                <td
                                    class="px-4 py-3 font-mono text-xs text-neutral-500 dark:text-neutral-400"
                                >
                                    <div>{{ student.nis }}</div>
                                    <div class="text-[10px] text-neutral-400">
                                        {{ student.nisn }}
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 font-semibold text-neutral-800 dark:text-neutral-200"
                                >
                                    {{ student.nama }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Button
                                        size="sm"
                                        @click="addStudent(student)"
                                        class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 p-0 font-bold text-white shadow hover:bg-blue-700"
                                    >
                                        <Plus class="h-4 w-4" />
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="localUnassignedData.length === 0">
                                <td
                                    colspan="4"
                                    class="px-6 py-12 text-center text-neutral-400"
                                >
                                    Tidak ada siswa yang belum terdaftar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination for Unassigned list -->
                <div
                    v-if="unassigned.links && unassigned.links.length > 3"
                    class="flex items-center justify-between border-t border-neutral-200 bg-neutral-50/20 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-900/20"
                >
                    <div class="flex items-center gap-1">
                        <template
                            v-for="link in unassigned.links"
                            :key="link.label"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="rounded border px-2.5 py-1 text-[10px] font-bold transition-all"
                                :class="
                                    link.active
                                        ? 'border-zinc-900 bg-zinc-900 text-white dark:border-zinc-50 dark:bg-zinc-50 dark:text-zinc-950'
                                        : 'border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800'
                                "
                                v-html="link.label"
                                preserve-state
                                preserve-scroll
                                :only="['unassigned']"
                            />
                            <span
                                v-else
                                class="dark:text-zinc-655 cursor-not-allowed rounded border border-neutral-100 px-2.5 py-1 text-[10px] font-bold text-neutral-300 select-none dark:border-zinc-800/45"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right Pane: Currently Assigned (takes 5 cols) -->
            <div
                class="flex flex-col overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm transition-all lg:col-span-5 dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50/50 px-6 py-4 dark:border-zinc-800 dark:bg-zinc-800/30"
                >
                    <div class="flex items-center gap-2">
                        <GraduationCap class="h-5 w-5 text-emerald-600" />
                        <h3
                            class="text-sm font-bold tracking-wider text-neutral-800 uppercase dark:text-neutral-200"
                        >
                            Anggota Kelas ({{ localAssigned.length }})
                        </h3>
                    </div>
                    <Button
                        @click="submitAssignment"
                        size="sm"
                        class="flex items-center gap-1 bg-emerald-600 font-bold text-white shadow-sm transition-colors hover:bg-emerald-700"
                    >
                        <Save class="h-4 w-4" />
                        <span>Simpan</span>
                    </Button>
                </div>

                <div
                    class="max-h-[500px] min-h-[300px] divide-y divide-neutral-200 overflow-y-auto dark:divide-zinc-800"
                >
                    <div
                        v-for="student in localAssigned"
                        :key="student.id"
                        class="dark:hover:bg-zinc-850/20 flex items-center justify-between px-6 py-3 transition-colors hover:bg-neutral-50/30"
                    >
                        <div class="space-y-0.5">
                            <div
                                class="text-sm font-bold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ student.nama }}
                            </div>
                            <div class="font-mono text-xs text-neutral-400">
                                NIS: {{ student.nis }}
                            </div>
                        </div>
                        <Button
                            size="sm"
                            variant="ghost"
                            @click="removeStudent(student)"
                            class="h-8 w-8 rounded-full p-0 text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-950/20"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                    <div
                        v-if="localAssigned.length === 0"
                        class="px-6 py-12 text-center text-neutral-400"
                    >
                        Belum ada siswa di kelas ini.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
