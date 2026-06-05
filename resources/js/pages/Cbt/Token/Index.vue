<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { generate, toggleAuto } from '@/routes/cbt/token';
import { RefreshCcw, Settings, Clock, Key } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const props = defineProps<{
    token: {
        id: number;
        token: string;
        auto: boolean;
        updated_at: string;
    }
}>();

const form = useForm({
    auto: props.token.auto
});

const handleGenerate = () => {
    form.post(generate.url(), {
        onSuccess: () => {
            toast.success('Token berhasil diperbarui');
        }
    });
};

const handleToggleAuto = (checked: boolean) => {
    form.auto = checked;
    form.post(toggleAuto.url(), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Pengaturan auto-generate berhasil disimpan');
        }
    });
};
</script>

<template>
    <Head title="Token Ujian" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
        <Heading title="Token Ujian" description="Kelola token akses untuk pelaksanaan ujian CBT." variant="small" />

        <div class="max-w-3xl space-y-6">
            <!-- Token Display Card -->
            <div class="bg-card rounded-xl border border-border overflow-hidden shadow-sm">
                <div class="bg-primary/5 p-6 border-b border-border flex flex-col items-center justify-center text-center space-y-4">
                    <div class="p-3 bg-primary/10 rounded-full text-primary">
                        <Key class="w-8 h-8" />
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium text-muted-foreground uppercase tracking-widest">Token Ujian Saat Ini</p>
                        <h2 class="text-6xl font-black tracking-widest text-foreground font-mono">
                            {{ token.token }}
                        </h2>
                    </div>
                    
                    <p class="text-xs text-muted-foreground mt-4 flex items-center justify-center">
                        <Clock class="w-3 h-3 mr-1" />
                        Terakhir diupdate: {{ new Date(token.updated_at).toLocaleString('id-ID') }}
                    </p>
                </div>
                
                <div class="p-6 bg-card flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <Switch id="auto-token" :checked="form.auto" @update:checked="handleToggleAuto" />
                        <div class="space-y-0.5">
                            <Label for="auto-token" class="font-semibold text-base">Auto-Generate Token</Label>
                            <p class="text-sm text-muted-foreground">Otomatis perbarui token setiap 15 menit</p>
                        </div>
                    </div>
                    
                    <Button @click="handleGenerate" :disabled="form.processing" size="lg" class="w-full sm:w-auto">
                        <RefreshCcw class="w-4 h-4 mr-2" :class="{ 'animate-spin': form.processing }" />
                        Generate Token Baru
                    </Button>
                </div>
            </div>

            <!-- Information Alert -->
            <div class="bg-blue-50 dark:bg-blue-950/30 text-blue-800 dark:text-blue-300 p-4 rounded-lg flex items-start border border-blue-200 dark:border-blue-900">
                <Settings class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" />
                <div class="text-sm">
                    <p class="font-semibold mb-1">Informasi Token Ujian</p>
                    <ul class="list-disc ml-4 space-y-1">
                        <li>Token ujian digunakan sebagai kunci masuk (password sekunder) bagi peserta sebelum memulai ujian.</li>
                        <li>Token hanya diwajibkan untuk jadwal ujian yang mengaktifkan fitur token.</li>
                        <li>Fitur <strong>Auto-Generate</strong> akan secara otomatis mengganti token setiap 15 menit melalui task scheduler server (Cron Job).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
