<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Users, Play, CheckCircle2, Clock } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { statusSiswa } from '@/routes/guru';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: statusSiswa() },
            { title: 'Status Siswa', href: statusSiswa() },
        ],
    },
});

const props = defineProps<{
    jadwals: {
        id: number;
        tgl_mulai: string;
        bank_soal?: { nama: string };
    }[];
}>();

const selectedJadwalId = ref('');
const siswaList = ref<any[]>([]);
const isLoading = ref(false);
const lastRefresh = ref('');
let interval: ReturnType<typeof setInterval> | null = null;

async function fetchData() {
    if (!selectedJadwalId.value) return;
    isLoading.value = true;
    try {
        const res = await fetch(`/guru/status-siswa/${selectedJadwalId.value}/data`, {
            headers: { Accept: 'application/json' },
        });
        const json = await res.json();
        siswaList.value = json.data;
        lastRefresh.value = new Date().toLocaleTimeString();
    } catch {
        // silent
    } finally {
        isLoading.value = false;
    }
}

function startPolling() {
    stopPolling();
    fetchData();
    interval = setInterval(fetchData, 30000);
}

function stopPolling() {
    if (interval) {
        clearInterval(interval);
        interval = null;
    }
}

onMounted(() => {
    if (selectedJadwalId.value) startPolling();
});

onUnmounted(() => {
    stopPolling();
});

function onJadwalChange() {
    if (selectedJadwalId.value) {
        startPolling();
    } else {
        stopPolling();
        siswaList.value = [];
    }
}

function statusBadge(status: number): { label: string; class: string } {
    switch (status) {
        case 0:
            return { label: 'Belum Mulai', class: 'bg-gray-100 text-gray-700' };
        case 1:
            return { label: 'Sedang Ujian', class: 'bg-blue-100 text-blue-700' };
        case 2:
            return { label: 'Selesai', class: 'bg-green-100 text-green-700' };
        default:
            return { label: 'Unknown', class: 'bg-gray-100 text-gray-700' };
    }
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <Heading
            title="Status Siswa"
            description="Pantau status peserta ujian secara real-time."
        />

        <div class="rounded-xl border bg-card p-6 shadow-sm">
            <div class="flex flex-wrap items-end gap-4">
                <div class="flex-1 max-w-xl">
                    <label class="mb-2 block text-sm font-medium text-foreground">
                        Pilih Jadwal Aktif
                    </label>
                    <select
                        v-model="selectedJadwalId"
                        @change="onJadwalChange"
                        class="block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-xs focus:border-ring focus:outline-hidden focus:ring-1 focus:ring-ring"
                    >
                        <option value="">-- Pilih Jadwal --</option>
                        <option
                            v-for="jadwal in jadwals"
                            :key="jadwal.id"
                            :value="jadwal.id"
                        >
                            {{ jadwal.bank_soal?.nama ?? '-' }} ({{
                                jadwal.tgl_mulai
                            }})
                        </option>
                    </select>
                </div>
                <div v-if="lastRefresh" class="text-xs text-muted-foreground">
                    Terakhir: {{ lastRefresh }}
                </div>
            </div>
        </div>

        <div
            v-if="selectedJadwalId"
            class="overflow-hidden rounded-xl border bg-card shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">NIS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Mulai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-if="isLoading && !siswaList.length" class="animate-pulse">
                            <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                Memuat data...
                            </td>
                        </tr>
                        <tr v-else-if="!siswaList.length">
                            <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                Belum ada peserta untuk jadwal ini.
                            </td>
                        </tr>
                        <tr
                            v-for="(siswa, index) in siswaList"
                            :key="siswa.id"
                            class="hover:bg-muted/30"
                        >
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">{{ index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-card-foreground">{{ siswa.nama }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">{{ siswa.nis }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :class="statusBadge(siswa.status).class"
                                >
                                    <Play v-if="siswa.status === 1" class="h-3 w-3" />
                                    <CheckCircle2 v-else-if="siswa.status === 2" class="h-3 w-3" />
                                    <Clock v-else class="h-3 w-3" />
                                    {{ statusBadge(siswa.status).label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 rounded-full bg-muted overflow-hidden">
                                        <div
                                            class="h-full rounded-full bg-primary transition-all duration-500"
                                            :style="{ width: siswa.total_soal > 0 ? `${(siswa.dijawab / siswa.total_soal) * 100}%` : '0%' }"
                                        />
                                    </div>
                                    <span class="text-xs text-muted-foreground">
                                        {{ siswa.dijawab }}/{{ siswa.total_soal }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">
                                {{ siswa.mulai ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-else
            class="rounded-xl border bg-card p-12 text-center shadow-sm"
        >
            <Users class="mx-auto h-12 w-12 text-muted-foreground" />
            <h3 class="mt-2 text-sm font-medium text-card-foreground">
                Pilih Jadwal Aktif
            </h3>
            <p class="mt-1 text-sm text-muted-foreground">
                Pilih jadwal untuk melihat status peserta secara real-time.
            </p>
        </div>
    </div>
</template>
