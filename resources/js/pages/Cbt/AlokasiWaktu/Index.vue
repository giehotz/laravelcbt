<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { generate as generateAlokasiWaktuRoute } from '@/routes/cbt/alokasi-waktu';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { RefreshCw, Play } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    data: {
        tanggal: string;
        jadwals: any[];
    }[];
}>();

const form = useForm({});

const generateAlokasi = (jadwalId: number) => {
    form.post(generateAlokasiWaktuRoute.url({ jadwal: jadwalId }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Alokasi Waktu Ujian" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
        <div class="mb-4">
            <h2 class="text-xl leading-tight font-semibold text-gray-800">
                Alokasi Waktu Ujian
            </h2>
        </div>

        <div>
            <div class="mx-auto max-w-7xl space-y-6">
                <Card v-if="data.length === 0">
                    <CardContent
                        class="py-12 text-center text-muted-foreground"
                    >
                        Tidak ada jadwal ujian yang aktif pada semester ini.
                    </CardContent>
                </Card>

                <div
                    v-for="group in data"
                    :key="group.tanggal"
                    class="space-y-4"
                >
                    <h3 class="border-b pb-2 text-lg font-medium text-gray-900">
                        Tanggal: {{ group.tanggal }}
                    </h3>

                    <Card>
                        <CardContent class="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Mata Pelajaran</TableHead>
                                        <TableHead>Jenis</TableHead>
                                        <TableHead>Waktu</TableHead>
                                        <TableHead class="text-center"
                                            >Total Siswa</TableHead
                                        >
                                        <TableHead class="text-center"
                                            >Teralokasi</TableHead
                                        >
                                        <TableHead class="text-right"
                                            >Aksi</TableHead
                                        >
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="jadwal in group.jadwals"
                                        :key="jadwal.id"
                                    >
                                        <TableCell class="font-medium">
                                            {{ jadwal.mapel }}
                                        </TableCell>
                                        <TableCell>
                                            {{ jadwal.jenis }}
                                        </TableCell>
                                        <TableCell>
                                            {{ jadwal.waktu }}
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <Badge variant="outline">{{
                                                jadwal.total_siswa
                                            }}</Badge>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <Badge
                                                :variant="
                                                    jadwal.total_alokasi ===
                                                        jadwal.total_siswa &&
                                                    jadwal.total_siswa > 0
                                                        ? 'default'
                                                        : 'secondary'
                                                "
                                            >
                                                {{ jadwal.total_alokasi }} /
                                                {{ jadwal.total_siswa }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Button
                                                v-if="
                                                    jadwal.total_alokasi <
                                                    jadwal.total_siswa
                                                "
                                                size="sm"
                                                @click="
                                                    generateAlokasi(jadwal.id)
                                                "
                                                :disabled="form.processing"
                                            >
                                                <RefreshCw
                                                    class="mr-2 h-4 w-4"
                                                    :class="{
                                                        'animate-spin':
                                                            form.processing,
                                                    }"
                                                />
                                                Generate
                                            </Button>
                                            <Button
                                                v-else
                                                size="sm"
                                                variant="outline"
                                                disabled
                                            >
                                                Teralokasi
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>
