<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Eye, Clock, Key } from 'lucide-vue-next';
import cbt from '@/routes/cbt';

defineProps<{
    jadwal: any[];
    role: string;
    globalToken?: string;
}>();

const formatTime = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Jadwal Ujian Hari Ini</CardTitle>
            <CardDescription>Jadwal ujian yang sedang berlangsung hari ini</CardDescription>
        </CardHeader>
        <CardContent>
            <div v-if="!jadwal || jadwal.length === 0" class="text-center py-8">
                <div class="h-12 w-12 rounded-full bg-muted flex items-center justify-center mx-auto mb-3">
                    <Clock class="h-6 w-6 text-muted-foreground" />
                </div>
                <h3 class="text-lg font-medium">Tidak ada ujian hari ini</h3>
                <p class="text-sm text-muted-foreground mt-1 mb-4">Belum ada ujian yang dijadwalkan untuk hari ini.</p>
                <Button variant="outline" as-child v-if="['superadmin', 'operator', 'guru'].includes(role)">
                    <Link :href="cbt.jadwal.index.url()">Lihat Semua Jadwal</Link>
                </Button>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-muted-foreground uppercase bg-muted/50 border-b border-border">
                        <tr>
                            <th class="px-4 py-3 font-medium">Mata Pelajaran</th>
                            <th class="px-4 py-3 font-medium">Jenis Ujian</th>
                            <th class="px-4 py-3 font-medium">Waktu</th>
                            <th class="px-4 py-3 font-medium text-center">Status</th>
                            <th class="px-4 py-3 font-medium text-center" v-if="['superadmin', 'proktor'].includes(role)">Token</th>
                            <th class="px-4 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="item in jadwal" :key="item.id" class="hover:bg-muted/50 transition-colors">
                            <td class="px-4 py-3 font-medium">
                                {{ item.bank_soal?.mapel?.nama_mapel || 'N/A' }}
                                <div class="text-xs text-muted-foreground font-normal">{{ item.bank_soal?.kode || '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <Badge variant="outline">{{ item.jenis?.nama_jenis || 'N/A' }}</Badge>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1.5 text-muted-foreground">
                                    <Clock class="h-3.5 w-3.5" />
                                    <span>{{ formatTime(item.tgl_mulai) }} - {{ formatTime(item.tgl_selesai) }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <Badge :variant="item.status === 1 ? 'default' : 'secondary'">
                                    {{ item.status === 1 ? 'Aktif' : 'Tutup' }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-center" v-if="['superadmin', 'proktor'].includes(role)">
                                <div v-if="item.token" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-primary/10 text-primary font-mono text-xs font-semibold">
                                    <Key class="h-3 w-3" />
                                    {{ globalToken || 'Belum Dibuat' }}
                                </div>
                                <span v-else class="text-muted-foreground text-xs">-</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Button variant="ghost" size="icon" as-child v-if="['superadmin', 'proktor'].includes(role)">
                                    <Link href="#">
                                        <Eye class="h-4 w-4" />
                                    </Link>
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </CardContent>
    </Card>
</template>
