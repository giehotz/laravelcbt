<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Info, RefreshCw } from 'lucide-vue-next';

defineProps<{
    title?: string;
}>();

const items = ref<any[]>([]);
const isLoading = ref(false);
let interval: ReturnType<typeof setInterval> | null = null;

async function fetchData() {
    isLoading.value = true;
    try {
        const res = await fetch('/siswa/dashboard/pengumuman', {
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
    interval = setInterval(fetchData, 60000);
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});
</script>

<template>
    <div class="rounded-xl border bg-card shadow-xs">
        <div class="flex items-center justify-between rounded-t-xl bg-emerald-500 px-4 py-3">
            <h3 class="text-sm font-semibold text-white">{{ title || 'INFO / PENGUMUMAN' }}</h3>
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
                <div v-for="item in items" :key="item.id" class="border-b border-border pb-2 last:border-0 last:pb-0">
                    <p class="text-sm font-semibold text-card-foreground">{{ item.judul }}</p>
                    <p class="mt-0.5 line-clamp-2 text-xs text-muted-foreground">{{ item.isi }}</p>
                    <p class="mt-1 text-[10px] text-muted-foreground/60">{{ item.created_at }}</p>
                </div>
            </div>
            <div v-else class="flex flex-col items-center gap-2 py-6 text-center">
                <Info class="h-8 w-8 text-muted-foreground/40" />
                <p class="text-xs text-muted-foreground/60">Belum ada pengumuman</p>
            </div>
        </div>
    </div>
</template>
