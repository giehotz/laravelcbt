<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Calendar, RefreshCw, Frown } from 'lucide-vue-next';

defineProps<{
    kelas: string;
}>();

const items = ref<any[]>([]);
const isLoading = ref(false);
let interval: ReturnType<typeof setInterval> | null = null;

async function fetchData() {
    isLoading.value = true;
    try {
        const res = await fetch('/siswa/dashboard/jadwal-hari-ini', {
            headers: { Accept: 'application/json' },
        });
        const json = await res.json();
        items.value = json.data ?? [];
    } catch {
        items.value = [];
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    fetchData();
    interval = setInterval(fetchData, 30000);
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});
</script>

<template>
    <div class="rounded-xl border bg-card shadow-xs">
        <div class="flex items-center justify-between rounded-t-xl bg-red-500 px-4 py-3">
            <h3 class="text-sm font-semibold text-white">JADWAL HARI INI</h3>
            <button
                @click="fetchData"
                class="rounded-md bg-white/20 p-1 text-white transition-colors hover:bg-white/30 active:scale-95"
            >
                <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': isLoading }" />
            </button>
        </div>
        <div class="p-4">
            <div v-if="isLoading && !items.length" class="space-y-2">
                <div class="h-4 animate-pulse rounded bg-muted" />
                <div class="h-4 w-3/4 animate-pulse rounded bg-muted" />
            </div>
            <div v-else-if="items.length" class="space-y-3">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="flex items-center justify-between border-b border-border pb-2 last:border-0 last:pb-0"
                >
                    <div>
                        <p class="text-sm font-semibold text-card-foreground">{{ item.nama }}</p>
                        <p class="text-xs text-muted-foreground">{{ item.mapel }}</p>
                    </div>
                    <span
                        class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="item.status ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'"
                    >
                        {{ item.status ? 'Buka' : 'Tutup' }}
                    </span>
                </div>
            </div>
            <div v-else class="flex flex-col items-center gap-2 py-6 text-center">
                <Frown class="h-8 w-8 text-muted-foreground/40" />
                <p class="text-xs text-muted-foreground/60">
                    Jadwal untuk kelas {{ kelas }} belum dibuat
                </p>
            </div>
        </div>
    </div>
</template>
