<script setup lang="ts">
import { ref } from 'vue';
import {
    Award,
    CheckCircle2,
    XCircle,
    FileSpreadsheet,
    ExternalLink,
} from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { hasilUjian } from '@/routes/guru';
import { dataSiswa, koreksiSiswa } from '@/routes/cbt/koreksi';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: hasilUjian() },
            { title: 'Hasil Ujian', href: hasilUjian() },
        ],
    },
});

const props = defineProps<{
    jadwals: {
        id: number;
        tgl_mulai: string;
        bank_soal?: { nama: string; kkm?: number };
    }[];
}>();

type Tab = 'koreksi' | 'nilai';
const activeTab = ref<Tab>('koreksi');
const selectedJadwalId = ref('');
const siswaList = ref<any[]>([]);
const nilaiList = ref<any[]>([]);
const isLoading = ref(false);

async function fetchDataSiswa() {
    if (!selectedJadwalId.value) return;
    isLoading.value = true;
    try {
        const res = await fetch(dataSiswa({ jadwal: selectedJadwalId.value }).url, {
            headers: { Accept: 'application/json' },
        });
        const json = await res.json();
        siswaList.value = json.data;
    } catch {
        siswaList.value = [];
    } finally {
        isLoading.value = false;
    }
}

async function fetchNilai() {
    if (!selectedJadwalId.value) return;
    isLoading.value = true;
    try {
        const res = await fetch(`/guru/hasil-ujian/${selectedJadwalId.value}/nilai`, {
            headers: { Accept: 'application/json' },
        });
        const json = await res.json();
        nilaiList.value = json.data;
    } catch {
        nilaiList.value = [];
    } finally {
        isLoading.value = false;
    }
}

function onJadwalChange() {
    if (!selectedJadwalId.value) {
        siswaList.value = [];
        nilaiList.value = [];
        return;
    }
    if (activeTab.value === 'koreksi') fetchDataSiswa();
    else fetchNilai();
}

function onTabChange(tab: Tab) {
    activeTab.value = tab;
    if (!selectedJadwalId.value) return;
    if (tab === 'koreksi') fetchDataSiswa();
    else fetchNilai();
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <Heading
            title="Hasil Ujian"
            description="Koreksi jawaban esai dan lihat nilai akhir siswa."
        />

        <div class="rounded-xl border bg-card p-6 shadow-sm">
            <div class="max-w-xl">
                <label class="mb-2 block text-sm font-medium text-foreground">
                    Pilih Jadwal Ujian
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
        </div>

        <div v-if="selectedJadwalId">
            <!-- Tab Navigation -->
            <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                <div class="flex border-b border-border">
                    <button
                        @click="onTabChange('koreksi')"
                        class="px-6 py-3 text-sm font-semibold transition-colors"
                        :class="
                            activeTab === 'koreksi'
                                ? 'border-b-2 border-primary text-primary'
                                : 'text-muted-foreground hover:text-foreground'
                        "
                    >
                        Koreksi Esai
                    </button>
                    <button
                        @click="onTabChange('nilai')"
                        class="px-6 py-3 text-sm font-semibold transition-colors"
                        :class="
                            activeTab === 'nilai'
                                ? 'border-b-2 border-primary text-primary'
                                : 'text-muted-foreground hover:text-foreground'
                        "
                    >
                        Lihat Nilai
                    </button>
                </div>

                <!-- Tab Content: Koreksi Esai -->
                <div v-if="activeTab === 'koreksi'" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Siswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Nilai PG</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Nilai Esai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-muted-foreground uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-if="isLoading && !siswaList.length" class="animate-pulse">
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                    Memuat data siswa...
                                </td>
                            </tr>
                            <tr v-else-if="!siswaList.length">
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                    Belum ada data siswa.
                                </td>
                            </tr>
                            <tr
                                v-for="(siswa, index) in siswaList"
                                :key="siswa.id"
                                class="hover:bg-muted/30"
                            >
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">{{ index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-card-foreground">{{ siswa.nama }}</div>
                                    <div class="text-sm text-muted-foreground">{{ siswa.nis }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold whitespace-nowrap">{{ siswa.pg_nilai ?? 0 }}</td>
                                <td class="px-6 py-4 text-sm font-semibold whitespace-nowrap">{{ siswa.esai_nilai ?? 0 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        v-if="siswa.dikoreksi"
                                        class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700"
                                    >
                                        <CheckCircle2 class="h-3 w-3" />
                                        Sudah Dikoreksi
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700"
                                    >
                                        <XCircle class="h-3 w-3" />
                                        Belum Dikoreksi
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a
                                        :href="koreksiSiswa({ jadwal: selectedJadwalId, siswa: siswa.id }).url"
                                        class="inline-flex items-center gap-1 rounded-lg bg-primary/10 px-3 py-1.5 text-sm font-medium text-primary transition-colors hover:bg-primary/20"
                                    >
                                        <ExternalLink class="h-3.5 w-3.5" />
                                        Koreksi
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tab Content: Lihat Nilai -->
                <div v-if="activeTab === 'nilai'" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Nama</th>
                                <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">PG</th>
                                <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Esai</th>
                                <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Kompleks</th>
                                <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Jodohkan</th>
                                <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Isian</th>
                                <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-if="isLoading && !nilaiList.length" class="animate-pulse">
                                <td colspan="9" class="px-6 py-12 text-center text-muted-foreground">
                                    Memuat data nilai...
                                </td>
                            </tr>
                            <tr v-else-if="!nilaiList.length">
                                <td colspan="9" class="px-6 py-12 text-center text-muted-foreground">
                                    Belum ada data nilai.
                                </td>
                            </tr>
                            <tr
                                v-for="(n, index) in nilaiList"
                                :key="n.siswa_id"
                                class="hover:bg-muted/30"
                            >
                                <td class="px-4 py-3 text-sm whitespace-nowrap text-muted-foreground">{{ index + 1 }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-card-foreground">{{ n.nama }}</td>
                                <td class="px-4 py-3 text-sm text-center whitespace-nowrap">{{ n.pg_nilai }}</td>
                                <td class="px-4 py-3 text-sm text-center whitespace-nowrap">{{ n.esai_nilai }}</td>
                                <td class="px-4 py-3 text-sm text-center whitespace-nowrap">{{ n.kompleks_nilai }}</td>
                                <td class="px-4 py-3 text-sm text-center whitespace-nowrap">{{ n.jodohkan_nilai }}</td>
                                <td class="px-4 py-3 text-sm text-center whitespace-nowrap">{{ n.isian_nilai }}</td>
                                <td class="px-4 py-3 text-sm text-center whitespace-nowrap font-bold">{{ n.total }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span
                                        v-if="n.lulus"
                                        class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700"
                                    >
                                        Lulus
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700"
                                    >
                                        Tidak Lulus
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div
            v-else
            class="rounded-xl border bg-card p-12 text-center shadow-sm"
        >
            <Award class="mx-auto h-12 w-12 text-muted-foreground" />
            <h3 class="mt-2 text-sm font-medium text-card-foreground">
                Pilih Jadwal
            </h3>
            <p class="mt-1 text-sm text-muted-foreground">
                Pilih jadwal untuk melihat hasil ujian dan melakukan koreksi.
            </p>
        </div>
    </div>
</template>
