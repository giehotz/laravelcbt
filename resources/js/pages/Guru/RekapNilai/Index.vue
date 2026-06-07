<script setup lang="ts">
import { ref } from 'vue';
import { FileSpreadsheet, CheckCircle2, XCircle } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { rekapNilai } from '@/routes/guru';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: rekapNilai() },
            { title: 'Rekap Nilai', href: rekapNilai() },
        ],
    },
});

const props = defineProps<{
    kelas: { id: number; nama_kelas: string }[];
    mapels: { id: number; nama_mapel: string }[];
    jenis: { id: number; nama_jenis: string }[];
}>();

const filterKelas = ref('');
const filterMapel = ref('');
const filterJenis = ref('');
const filterStatus = ref('');
const nilaiList = ref<any[]>([]);
const isLoading = ref(false);

async function fetchData() {
    isLoading.value = true;
    try {
        const params = new URLSearchParams();
        if (filterKelas.value) params.set('kelas_id', filterKelas.value);
        if (filterMapel.value) params.set('mapel_id', filterMapel.value);
        if (filterJenis.value) params.set('jenis_id', filterJenis.value);
        if (filterStatus.value) params.set('status_kkm', filterStatus.value);

        const res = await fetch(`/guru/rekap-nilai/data?${params}`, {
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
</script>

<template>
    <div class="flex flex-col gap-6">
        <Heading
            title="Rekap Nilai"
            description="Nilai agregat siswa per kelas dan mata pelajaran."
        />

        <div class="rounded-xl border bg-card p-6 shadow-sm">
            <div class="flex flex-wrap items-end gap-4">
                <div class="w-48">
                    <label class="mb-2 block text-sm font-medium text-foreground">Kelas</label>
                    <select
                        v-model="filterKelas"
                        class="block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-xs focus:border-ring focus:outline-hidden focus:ring-1 focus:ring-ring"
                    >
                        <option value="">Semua Kelas</option>
                        <option v-for="k in kelas" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                    </select>
                </div>
                <div class="w-48">
                    <label class="mb-2 block text-sm font-medium text-foreground">Mapel</label>
                    <select
                        v-model="filterMapel"
                        class="block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-xs focus:border-ring focus:outline-hidden focus:ring-1 focus:ring-ring"
                    >
                        <option value="">Semua Mapel</option>
                        <option v-for="m in mapels" :key="m.id" :value="m.id">{{ m.nama_mapel }}</option>
                    </select>
                </div>
                <div class="w-48">
                    <label class="mb-2 block text-sm font-medium text-foreground">Jenis Ujian</label>
                    <select
                        v-model="filterJenis"
                        class="block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-xs focus:border-ring focus:outline-hidden focus:ring-1 focus:ring-ring"
                    >
                        <option value="">Semua Jenis</option>
                        <option v-for="j in jenis" :key="j.id" :value="j.id">{{ j.nama_jenis }}</option>
                    </select>
                </div>
                <div class="w-44">
                    <label class="mb-2 block text-sm font-medium text-foreground">Status KKM</label>
                    <select
                        v-model="filterStatus"
                        class="block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-xs focus:border-ring focus:outline-hidden focus:ring-1 focus:ring-ring"
                    >
                        <option value="">Semua</option>
                        <option value="tuntas">Tuntas</option>
                        <option value="belum">Belum Tuntas</option>
                    </select>
                </div>
                <button
                    @click="fetchData"
                    class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-xs transition-colors hover:bg-primary/90"
                >
                    Tampilkan
                </button>
            </div>
        </div>

        <div
            v-if="nilaiList.length"
            class="overflow-hidden rounded-xl border bg-card shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">NIS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Kelas</th>
                            <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Total Nilai</th>
                            <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="(n, index) in nilaiList" :key="n.siswa_id" class="hover:bg-muted/30">
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">{{ index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-card-foreground">{{ n.nama }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">{{ n.nis }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">{{ n.kelas }}</td>
                            <td class="px-6 py-4 text-sm text-center whitespace-nowrap font-bold">{{ n.total }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span
                                    v-if="n.lulus"
                                    class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700"
                                >
                                    <CheckCircle2 class="h-3 w-3" />
                                    Tuntas
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700"
                                >
                                    <XCircle class="h-3 w-3" />
                                    Belum Tuntas
                                </span>
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
            <FileSpreadsheet class="mx-auto h-12 w-12 text-muted-foreground" />
            <h3 class="mt-2 text-sm font-medium text-card-foreground">
                Filter dan Tampilkan Data
            </h3>
            <p class="mt-1 text-sm text-muted-foreground">
                Pilih filter lalu klik Tampilkan untuk melihat rekap nilai.
            </p>
        </div>
    </div>
</template>
