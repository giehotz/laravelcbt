<script setup lang="ts">
import { ref } from 'vue';
import { BarChart3, TrendingUp, TrendingDown, Minus, AlertCircle } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { analisisSoal } from '@/routes/guru';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: analisisSoal() },
            { title: 'Analisis Soal', href: analisisSoal() },
        ],
    },
});

const props = defineProps<{
    jadwals: { id: number; bank_soal: { nama: string } | null; tgl_mulai: string; tgl_selesai: string }[];
}>();

const selectedJadwal = ref('');
const soalList = ref<any[]>([]);
const summary = ref<{ total_soal: number; mudah: number; sedang: number; sulit: number; rata_rata: number } | null>(null);
const isLoading = ref(false);

async function fetchData() {
    if (!selectedJadwal.value) return;
    isLoading.value = true;
    try {
        const res = await fetch(`/guru/analisis-soal/${selectedJadwal.value}/data`, {
            headers: { Accept: 'application/json' },
        });
        const json = await res.json();
        soalList.value = json.data;
        summary.value = json.summary;
    } catch {
        soalList.value = [];
        summary.value = null;
    } finally {
        isLoading.value = false;
    }
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <Heading
            title="Analisis Soal"
            description="Analisis perbutir soal: tingkat kesulitan, ketuntasan, dan kesesuaian."
        />

        <div class="rounded-xl border bg-card p-6 shadow-sm">
            <div class="flex flex-wrap items-end gap-4">
                <div class="min-w-72">
                    <label class="mb-2 block text-sm font-medium text-foreground">Pilih Jadwal Ujian</label>
                    <select
                        v-model="selectedJadwal"
                        @change="fetchData"
                        class="block w-full rounded-lg border border-input bg-background px-3 py-2 text-sm shadow-xs focus:border-ring focus:outline-hidden focus:ring-1 focus:ring-ring"
                    >
                        <option value="">-- Pilih Jadwal --</option>
                        <option v-for="j in jadwals" :key="j.id" :value="j.id">
                            {{ j.bank_soal?.nama }} ({{ j.tgl_mulai }})
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div v-if="summary" class="grid grid-cols-5 gap-4">
            <div class="rounded-xl border bg-card p-4 text-center shadow-sm">
                <p class="text-sm text-muted-foreground">Total Soal</p>
                <p class="mt-1 text-2xl font-bold">{{ summary.total_soal }}</p>
            </div>
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-center shadow-sm">
                <TrendingUp class="mx-auto h-5 w-5 text-green-600" />
                <p class="mt-1 text-sm font-medium text-green-700">Mudah</p>
                <p class="text-xl font-bold text-green-800">{{ summary.mudah }}</p>
            </div>
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-center shadow-sm">
                <Minus class="mx-auto h-5 w-5 text-yellow-600" />
                <p class="mt-1 text-sm font-medium text-yellow-700">Sedang</p>
                <p class="text-xl font-bold text-yellow-800">{{ summary.sedang }}</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-center shadow-sm">
                <TrendingDown class="mx-auto h-5 w-5 text-red-600" />
                <p class="mt-1 text-sm font-medium text-red-700">Sulit</p>
                <p class="text-xl font-bold text-red-800">{{ summary.sulit }}</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-center shadow-sm">
                <BarChart3 class="mx-auto h-5 w-5 text-blue-600" />
                <p class="mt-1 text-sm font-medium text-blue-700">Rata-rata</p>
                <p class="text-xl font-bold text-blue-800">{{ summary.rata_rata.toFixed(1) }}%</p>
            </div>
        </div>

        <div
            v-if="soalList.length"
            class="overflow-hidden rounded-xl border bg-card shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-muted/50">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">Soal</th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Jenis</th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Benar</th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">%</th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Kategori</th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground uppercase">Kesesuaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="s in soalList" :key="s.soal_id" class="hover:bg-muted/30">
                            <td class="px-4 py-3 text-sm text-center whitespace-nowrap text-muted-foreground">{{ s.nomor }}</td>
                            <td class="max-w-xs truncate px-4 py-3 text-sm text-card-foreground">{{ s.teks || '—' }}</td>
                            <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium">{{ { 0: 'PG', 1: 'Esai' }[s.jenis] ?? '?' }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-center whitespace-nowrap text-muted-foreground">{{ s.total_siswa }}</td>
                            <td class="px-4 py-3 text-sm text-center whitespace-nowrap text-muted-foreground">{{ s.benar }}</td>
                            <td class="px-4 py-3 text-sm text-center whitespace-nowrap font-medium">{{ s.persentase_benar.toFixed(1) }}%</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span
                                    :class="{
                                        'rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700': s.kategori === 'Mudah',
                                        'rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-700': s.kategori === 'Sedang',
                                        'rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700': s.kategori === 'Sulit',
                                    }"
                                >
                                    {{ s.kategori }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span
                                    :class="{
                                        'rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700': s.kesesuaian === 'Sesuai',
                                        'rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-700': s.kesesuaian !== 'Sesuai',
                                    }"
                                >
                                    {{ s.kesesuaian }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div
            v-else-if="selectedJadwal && !isLoading"
            class="rounded-xl border bg-card p-12 text-center shadow-sm"
        >
            <AlertCircle class="mx-auto h-12 w-12 text-muted-foreground" />
            <h3 class="mt-2 text-sm font-medium text-card-foreground">Belum Ada Data</h3>
            <p class="mt-1 text-sm text-muted-foreground">
                Belum ada jawaban siswa untuk jadwal ini.
            </p>
        </div>
    </div>
</template>
