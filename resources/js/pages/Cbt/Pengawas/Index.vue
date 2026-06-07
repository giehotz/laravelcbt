<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { show as showPengawasRoute } from '@/routes/cbt/pengawas';
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
import { Users } from 'lucide-vue-next';

defineProps<{
    jadwals: any[];
}>();
</script>

<template>
    <Head title="Pengawas Ujian" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
        <div class="mb-4">
            <h2 class="text-xl leading-tight font-semibold text-gray-800">
                Pengawas Ujian
            </h2>
        </div>

        <div>
            <div class="mx-auto max-w-7xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Daftar Jadwal Ujian</CardTitle>
                        <CardDescription>
                            Pilih jadwal ujian untuk mengatur guru pengawas pada
                            masing-masing ruang dan sesi.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Mata Pelajaran</TableHead>
                                    <TableHead>Jenis Ujian</TableHead>
                                    <TableHead>Waktu Pelaksanaan</TableHead>
                                    <TableHead class="text-center"
                                        >Jumlah Pengawas</TableHead
                                    >
                                    <TableHead class="text-right"
                                        >Aksi</TableHead
                                    >
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-if="jadwals.length === 0">
                                    <TableCell
                                        colspan="5"
                                        class="py-8 text-center text-muted-foreground"
                                    >
                                        Tidak ada jadwal ujian aktif.
                                    </TableCell>
                                </TableRow>
                                <TableRow
                                    v-for="jadwal in jadwals"
                                    :key="jadwal.id"
                                >
                                    <TableCell class="font-medium">
                                        {{
                                            jadwal.bankSoal?.mapel
                                                ?.nama_mapel || '-'
                                        }}
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            jadwal.bankSoal?.jenis
                                                ?.nama_jenis || '-'
                                        }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="text-sm">
                                            {{ jadwal.tgl_mulai }} <br />
                                            s/d {{ jadwal.tgl_selesai }}
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <Badge
                                            variant="secondary"
                                            class="gap-1"
                                        >
                                            <Users class="h-3 w-3" />
                                            {{ jadwal.pengawas_count }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <Button
                                            as-child
                                            size="sm"
                                            variant="outline"
                                        >
                                            <Link
                                                :href="
                                                    showPengawasRoute.url({
                                                        jadwal: jadwal.id,
                                                    })
                                                "
                                            >
                                                Atur Pengawas
                                            </Link>
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
</template>
