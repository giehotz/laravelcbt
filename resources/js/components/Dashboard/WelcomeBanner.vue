<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Card, CardContent } from '@/components/ui/card';

const props = defineProps<{
    serverTime: string;
    tp: { id: number; tahun: string } | null;
    smt: { id: number; nama_smt: string } | null;
    user: any;
}>();

const now = ref(new Date(props.serverTime));
let timer: ReturnType<typeof setInterval> | null = null;

onMounted(() => {
    timer = setInterval(() => {
        now.value = new Date(now.value.getTime() + 1000);
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

const jamTampil = computed(() => {
    return now.value.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
});

const getGreeting = () => {
    const hour = now.value.getHours();
    if (hour < 11) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
};
</script>

<template>
    <Card class="bg-primary text-primary-foreground">
        <CardContent
            class="flex flex-col items-start justify-between gap-4 p-6 md:flex-row md:items-center"
        >
            <div>
                <h2 class="text-2xl font-bold tracking-tight">
                    {{ getGreeting() }}, {{ user.name }}
                </h2>
                <p class="mt-1 text-primary-foreground/80">
                    Tahun Pelajaran: {{ tp?.tahun || 'Belum Diatur' }} |
                    Semester: {{ smt?.nama_smt || 'Belum Diatur' }}
                </p>
            </div>
            <div
                class="rounded-lg bg-primary-foreground/10 px-4 py-2 text-right"
            >
                <p
                    class="text-sm font-medium tracking-wider text-primary-foreground/80 uppercase"
                >
                    Waktu Server
                </p>
                <div class="font-mono text-3xl font-bold">{{ jamTampil }}</div>
            </div>
        </CardContent>
    </Card>
</template>
