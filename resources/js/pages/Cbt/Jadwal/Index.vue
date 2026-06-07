<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import {
    index,
    create,
    edit,
    destroy,
    toggleStatus,
} from '@/routes/cbt/jadwal';
import {
    Plus,
    Edit2,
    Trash2,
    Power,
    PowerOff,
    Clock,
    FileText,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    jadwals: any[];
}>();

const toggleForm = useForm({});

const deleteForm = useForm({});

const toggleJadwalStatus = (id: number) => {
    toggleForm.post(toggleStatus.url({ jadwal: id }), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Status jadwal berhasil diubah');
        },
    });
};

const deleteJadwal = (id: number) => {
    if (confirm('Yakin ingin menghapus jadwal ini?')) {
        deleteForm.delete(destroy.url({ jadwal: id }), {
            onSuccess: () => {
                toast.success('Jadwal berhasil dihapus');
            },
        });
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
        <div class="flex items-center justify-between">
            <Heading
                title="Jadwal Ujian"
                description="Kelola jadwal ujian CBT dan aktivasinya."
            />
            <Button as-child>
                <Link :href="create.url()">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Jadwal
                </Link>
            </Button>
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b border-border bg-muted/50 text-xs text-muted-foreground uppercase"
                    >
                        <tr>
                            <th class="px-6 py-4 font-medium">Bank Soal</th>
                            <th class="px-6 py-4 font-medium">Jenis Ujian</th>
                            <th class="px-6 py-4 font-medium">Tanggal Ujian</th>
                            <th class="px-6 py-4 font-medium">Durasi</th>
                            <th class="px-6 py-4 text-center font-medium">
                                Status
                            </th>
                            <th class="px-6 py-4 text-right font-medium">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="jadwals.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-8 text-center text-muted-foreground"
                            >
                                Tidak ada jadwal ujian di semester ini.
                            </td>
                        </tr>
                        <tr
                            v-for="jadwal in jadwals"
                            :key="jadwal.id"
                            class="border-b border-border transition-colors last:border-0 hover:bg-muted/30"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-start space-x-3">
                                    <div
                                        class="mt-0.5 rounded-lg bg-primary/10 p-2 text-primary"
                                    >
                                        <FileText class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <p class="font-medium">
                                            {{
                                                jadwal.bank_soal?.nama ||
                                                'Unknown'
                                            }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ jadwal.bank_soal?.kode || '-' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <Badge variant="outline">{{
                                    jadwal.jenis?.nama_jenis || 'Unknown'
                                }}</Badge>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="flex items-center text-xs">
                                        <span class="w-12 text-muted-foreground"
                                            >Mulai:</span
                                        >
                                        <span class="font-medium">{{
                                            formatDate(jadwal.tgl_mulai)
                                        }}</span>
                                    </div>
                                    <div class="flex items-center text-xs">
                                        <span class="w-12 text-muted-foreground"
                                            >Selesai:</span
                                        >
                                        <span
                                            class="font-medium text-destructive"
                                            >{{
                                                formatDate(jadwal.tgl_selesai)
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <Clock
                                        class="mr-1.5 h-3.5 w-3.5 text-muted-foreground"
                                    />
                                    <span>{{ jadwal.durasi_ujian }} Menit</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <Badge
                                    :variant="
                                        jadwal.status === 1
                                            ? 'default'
                                            : 'secondary'
                                    "
                                >
                                    {{
                                        jadwal.status === 1 ? 'Aktif' : 'Tutup'
                                    }}
                                </Badge>
                            </td>
                            <td class="space-x-2 px-6 py-4 text-right">
                                <Button
                                    size="sm"
                                    :variant="
                                        jadwal.status === 1
                                            ? 'destructive'
                                            : 'default'
                                    "
                                    @click="toggleJadwalStatus(jadwal.id)"
                                    :disabled="toggleForm.processing"
                                >
                                    <PowerOff
                                        v-if="jadwal.status === 1"
                                        class="mr-1.5 h-4 w-4"
                                    />
                                    <Power v-else class="mr-1.5 h-4 w-4" />
                                    {{
                                        jadwal.status === 1
                                            ? 'Tutup'
                                            : 'Aktifkan'
                                    }}
                                </Button>

                                <Button size="icon" variant="ghost" as-child>
                                    <Link
                                        :href="edit.url({ jadwal: jadwal.id })"
                                    >
                                        <Edit2 class="h-4 w-4 text-primary" />
                                    </Link>
                                </Button>

                                <Button
                                    size="icon"
                                    variant="ghost"
                                    @click="deleteJadwal(jadwal.id)"
                                    :disabled="deleteForm.processing"
                                >
                                    <Trash2 class="h-4 w-4 text-destructive" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
