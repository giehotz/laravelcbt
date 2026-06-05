<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowLeft, Search, Plus, Trash2, Save, GraduationCap } from 'lucide-vue-next';

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
                const assignedIds = localAssigned.value.map(s => s.id);
                localUnassignedData.value = props.unassigned.data.filter(s => !assignedIds.includes(s.id));
            }
        }
    );
};

// Sync local data if props change (e.g. on pagination)
watch(() => props.unassigned.data, (newData) => {
    const assignedIds = localAssigned.value.map(s => s.id);
    localUnassignedData.value = newData.filter(s => !assignedIds.includes(s.id));
}, { deep: true });

const addStudent = (student: typeof props.assigned[0]) => {
    // Add to assigned
    if (!localAssigned.value.some(s => s.id === student.id)) {
        localAssigned.value.push(student);
    }
    // Remove from unassigned list
    localUnassignedData.value = localUnassignedData.value.filter(s => s.id !== student.id);
};

const removeStudent = (student: typeof props.assigned[0]) => {
    // Remove from assigned
    localAssigned.value = localAssigned.value.filter(s => s.id !== student.id);
    // Put back to unassigned if it matches search
    const matchesSearch = !searchInput.value || 
        student.nama.toLowerCase().includes(searchInput.value.toLowerCase()) ||
        student.nis.includes(searchInput.value) ||
        student.nisn.includes(searchInput.value);
    
    if (matchesSearch && !localUnassignedData.value.some(s => s.id === student.id)) {
        localUnassignedData.value.unshift(student);
    }
};

const submitAssignment = () => {
    const form = useForm({
        siswa_ids: localAssigned.value.map(s => s.id),
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

    <div class="px-6 py-6 max-w-7xl mx-auto space-y-6">
        <div class="flex items-center gap-3">
            <Link href="/master/kelas" class="p-2 border border-neutral-200 dark:border-zinc-800 rounded-lg hover:bg-neutral-50 dark:hover:bg-zinc-800 text-neutral-500 dark:text-neutral-400 transition-colors">
                <ArrowLeft class="w-4 h-4" />
            </Link>
            <Heading
                :title="'Kelola Roster Siswa — ' + kelas.nama_kelas"
                :description="'Atur siswa terdaftar pada kelas tingkat ' + (kelas.level_kelas?.level ?? '-') + ' (' + (kelas.jurusan?.nama_jurusan ?? 'Umum') + ') untuk tahun pelajaran ' + (kelas.tahun_pelajaran?.tahun ?? '-') + ' / ' + (kelas.semester?.nama_smt ?? '-')"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Pane: Unassigned Students (takes 7 cols) -->
            <div class="lg:col-span-7 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col transition-all">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 bg-neutral-50/50 dark:bg-zinc-800/30">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <GraduationCap class="w-5 h-5 text-zinc-500" />
                            <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-sm uppercase tracking-wider">Siswa Belum Punya Kelas</h3>
                        </div>
                        <div class="relative max-w-xs w-full">
                            <Input
                                v-model="searchInput"
                                @keyup.enter="handleSearch"
                                placeholder="Cari nama, NIS..."
                                class="pl-8 text-xs h-8"
                            />
                            <Search class="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-neutral-400" />
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto min-h-[300px]">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-neutral-50/30 dark:bg-zinc-900/50 border-b border-neutral-200 dark:border-zinc-800 text-[11px] font-bold uppercase text-neutral-500 tracking-wider">
                                <th class="px-4 py-3 text-center w-14">No</th>
                                <th class="px-4 py-3 w-32">NIS / NISN</th>
                                <th class="px-4 py-3">Nama Lengkap</th>
                                <th class="px-4 py-3 text-right w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-zinc-800 text-sm">
                            <tr v-for="(student, idx) in localUnassignedData" :key="student.id" class="hover:bg-neutral-50/30 dark:hover:bg-zinc-800/20 transition-colors">
                                <td class="px-4 py-3 text-center font-medium text-neutral-400 dark:text-neutral-500">{{ idx + 1 }}</td>
                                <td class="px-4 py-3 text-xs font-mono text-neutral-500 dark:text-neutral-400">
                                    <div>{{ student.nis }}</div>
                                    <div class="text-[10px] text-neutral-400">{{ student.nisn }}</div>
                                </td>
                                <td class="px-4 py-3 font-semibold text-neutral-800 dark:text-neutral-200">{{ student.nama }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Button
                                        size="sm"
                                        @click="addStudent(student)"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold h-7 w-7 p-0 rounded-full flex items-center justify-center shadow"
                                    >
                                        <Plus class="w-4 h-4" />
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="localUnassignedData.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-neutral-400">
                                    Tidak ada siswa yang belum terdaftar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination for Unassigned list -->
                <div v-if="unassigned.links && unassigned.links.length > 3" class="px-6 py-4 border-t border-neutral-200 dark:border-zinc-800 flex justify-between items-center bg-neutral-50/20 dark:bg-zinc-900/20">
                    <div class="flex items-center gap-1">
                        <template v-for="link in unassigned.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-2.5 py-1 rounded text-[10px] font-bold border transition-all"
                                :class="link.active ? 'bg-zinc-900 border-zinc-900 text-white dark:bg-zinc-50 dark:border-zinc-50 dark:text-zinc-950' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50 dark:bg-zinc-900 dark:border-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-800'"
                                v-html="link.label"
                                preserve-state
                                preserve-scroll
                                :only="['unassigned']"
                            />
                            <span
                                v-else
                                class="px-2.5 py-1 rounded text-[10px] font-bold border border-neutral-100 text-neutral-300 dark:border-zinc-800/45 dark:text-zinc-655 cursor-not-allowed select-none"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right Pane: Currently Assigned (takes 5 cols) -->
            <div class="lg:col-span-5 bg-white dark:bg-zinc-900 border border-neutral-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden flex flex-col transition-all">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-800 bg-neutral-50/50 dark:bg-zinc-800/30 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <GraduationCap class="w-5 h-5 text-emerald-600" />
                        <h3 class="font-bold text-neutral-800 dark:text-neutral-200 text-sm uppercase tracking-wider">
                            Anggota Kelas ({{ localAssigned.length }})
                        </h3>
                    </div>
                    <Button
                        @click="submitAssignment"
                        size="sm"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold flex items-center gap-1 shadow-sm transition-colors"
                    >
                        <Save class="w-4 h-4" />
                        <span>Simpan</span>
                    </Button>
                </div>

                <div class="overflow-y-auto max-h-[500px] min-h-[300px] divide-y divide-neutral-200 dark:divide-zinc-800">
                    <div
                        v-for="student in localAssigned"
                        :key="student.id"
                        class="px-6 py-3 flex items-center justify-between hover:bg-neutral-50/30 dark:hover:bg-zinc-850/20 transition-colors"
                    >
                        <div class="space-y-0.5">
                            <div class="font-bold text-neutral-800 dark:text-neutral-200 text-sm">{{ student.nama }}</div>
                            <div class="text-xs font-mono text-neutral-400">NIS: {{ student.nis }}</div>
                        </div>
                        <Button
                            size="sm"
                            variant="ghost"
                            @click="removeStudent(student)"
                            class="text-rose-600 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/20 h-8 w-8 p-0 rounded-full"
                        >
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                    <div v-if="localAssigned.length === 0" class="px-6 py-12 text-center text-neutral-400">
                        Belum ada siswa di kelas ini.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
