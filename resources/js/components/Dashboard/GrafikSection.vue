<script setup lang="ts">
import { computed } from 'vue';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { Bar, Doughnut, Line } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
);

const props = defineProps<{
    dataGrafik: {
        distribusi_nilai: any[];
        status_peserta: Record<string, number>;
        tren_ujian: any[];
    };
}>();

// 1. Bar Chart: Rata-rata Nilai per Mapel
const barChartData = computed(() => {
    const data = props.dataGrafik?.distribusi_nilai || [];
    return {
        labels: data.map((d) => d.nama_mapel),
        datasets: [
            {
                label: 'Rata-rata Nilai',
                backgroundColor: '#4F46E5', // Indigo 600
                data: data.map((d) => d.rata_rata),
            },
        ],
    };
});

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: { beginAtZero: true, max: 100 },
    },
};

// 2. Doughnut Chart: Status Peserta
const donutChartData = computed(() => {
    const data = props.dataGrafik?.status_peserta || {};
    return {
        labels: Object.keys(data),
        datasets: [
            {
                backgroundColor: ['#9CA3AF', '#3B82F6', '#10B981'], // Gray, Blue, Green
                data: Object.values(data),
            },
        ],
    };
});

const donutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
};

// 3. Line Chart: Tren Ujian
const lineChartData = computed(() => {
    const data = props.dataGrafik?.tren_ujian || [];
    return {
        labels: data.map((d) => d.bulan),
        datasets: [
            {
                label: 'Jumlah Ujian',
                borderColor: '#F59E0B', // Amber 500
                backgroundColor: 'rgba(245, 158, 11, 0.2)',
                data: data.map((d) => d.total),
                tension: 0.3,
                fill: true,
            },
        ],
    };
});

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
};
</script>

<template>
    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        <!-- Grafik Rata-rata Nilai -->
        <Card class="lg:col-span-2">
            <CardHeader>
                <CardTitle>Distribusi Rata-rata Nilai</CardTitle>
                <CardDescription
                    >Rata-rata nilai peserta per Mata Pelajaran</CardDescription
                >
            </CardHeader>
            <CardContent class="h-[300px]">
                <div
                    v-if="!dataGrafik?.distribusi_nilai?.length"
                    class="flex h-full items-center justify-center text-sm text-muted-foreground"
                >
                    Belum ada data nilai ujian.
                </div>
                <Bar v-else :data="barChartData" :options="barChartOptions" />
            </CardContent>
        </Card>

        <!-- Grafik Status Peserta -->
        <Card>
            <CardHeader>
                <CardTitle>Status Peserta Ujian</CardTitle>
                <CardDescription
                    >Persentase status durasi siswa</CardDescription
                >
            </CardHeader>
            <CardContent class="h-[300px]">
                <div
                    v-if="!Object.keys(dataGrafik?.status_peserta || {}).length"
                    class="flex h-full items-center justify-center text-sm text-muted-foreground"
                >
                    Belum ada data status peserta.
                </div>
                <Doughnut
                    v-else
                    :data="donutChartData"
                    :options="donutChartOptions"
                />
            </CardContent>
        </Card>

        <!-- Grafik Tren Bulanan -->
        <Card class="lg:col-span-3">
            <CardHeader>
                <CardTitle>Tren Pelaksanaan Ujian</CardTitle>
                <CardDescription
                    >Jumlah jadwal ujian yang dilaksanakan setiap
                    bulan</CardDescription
                >
            </CardHeader>
            <CardContent class="h-[300px]">
                <div
                    v-if="!dataGrafik?.tren_ujian?.length"
                    class="flex h-full items-center justify-center text-sm text-muted-foreground"
                >
                    Belum ada riwayat pelaksanaan ujian.
                </div>
                <Line
                    v-else
                    :data="lineChartData"
                    :options="lineChartOptions"
                />
            </CardContent>
        </Card>
    </div>
</template>
