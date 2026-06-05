<script setup lang="ts">
import { computed, watch } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Link } from '@inertiajs/vue3';
import { AlertCircle, Clock, Info, ChevronRight, Bell } from 'lucide-vue-next';
import { useNotifikasiStore } from '@/stores/notifikasi';
import cbt from '@/routes/cbt';

const props = defineProps<{
    jadwal: any[];
    globalToken?: string;
    serverTime: string;
}>();

const notifikasi = computed(() => {
    const hasil: any[] = [];
    const now = new Date(props.serverTime); // Use server time as base

    if (!props.jadwal || props.jadwal.length === 0) return hasil;

    props.jadwal.forEach(item => {
        const mulai = new Date(item.tgl_mulai);
        const selisih = (mulai.getTime() - now.getTime()) / 60000; // in minutes

        const mapel = item.bank_soal?.mapel?.nama_mapel || 'Ujian';

        // 1. Ujian akan dimulai dalam 15 menit
        if (selisih > 0 && selisih <= 15) {
            hasil.push({
                id: `start-${item.id}`,
                type: 'warning',
                icon: Clock,
                title: 'Ujian Segera Dimulai',
                pesan: `${mapel} akan dimulai dalam ${Math.round(selisih)} menit lagi.`,
                href: cbt.jadwal.index.url(),
            });
        }

        // 2. Jadwal pakai token tapi token belum di-generate
        if (item.token && !props.globalToken) {
            hasil.push({
                id: `token-${item.id}`,
                type: 'destructive',
                icon: AlertCircle,
                title: 'Token Belum Dibuat',
                pesan: `Jadwal ${mapel} membutuhkan token akses.`,
                href: cbt.token.index.url(),
            });
        }

        // 3. Jadwal sudah lewat tapi status masih buka
        const selesai = new Date(item.tgl_selesai);
        if (selesai < now && item.status === 1) {
            hasil.push({
                id: `close-${item.id}`,
                type: 'default',
                icon: Info,
                title: 'Jadwal Belum Ditutup',
                pesan: `Waktu ujian ${mapel} telah habis namun status masih aktif.`,
                href: cbt.jadwal.index.url(),
            });
        }
    });

    return hasil;
});

// Sync to Pinia store for Navbar Badge
const notifikasiStore = useNotifikasiStore();
watch(notifikasi, (val) => {
    notifikasiStore.set(val);
}, { immediate: true });
</script>

<template>
    <Card>
        <CardHeader class="pb-3">
            <CardTitle class="text-sm font-medium flex items-center gap-2">
                <Bell class="h-4 w-4" />
                Notifikasi
                <span v-if="notifikasi.length > 0" class="bg-destructive text-destructive-foreground text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-auto">
                    {{ notifikasi.length }}
                </span>
            </CardTitle>
        </CardHeader>
        <CardContent>
            <div v-if="notifikasi.length === 0" class="text-sm text-muted-foreground text-center py-4">
                Tidak ada notifikasi saat ini.
            </div>
            <div v-else class="space-y-3">
                <Link v-for="notif in notifikasi" :key="notif.id" :href="notif.href" class="block group">
                    <Alert :variant="notif.type" class="group-hover:bg-muted/50 transition-colors cursor-pointer relative pr-8">
                        <component :is="notif.icon" class="h-4 w-4" />
                        <AlertTitle class="text-xs font-semibold">{{ notif.title }}</AlertTitle>
                        <AlertDescription class="text-xs mt-1">
                            {{ notif.pesan }}
                        </AlertDescription>
                        <ChevronRight class="h-4 w-4 absolute right-3 top-1/2 -translate-y-1/2 opacity-0 group-hover:opacity-100 transition-opacity text-muted-foreground" />
                    </Alert>
                </Link>
            </div>
        </CardContent>
    </Card>
</template>
