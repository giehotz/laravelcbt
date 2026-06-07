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
    };
}>();

const form = useForm({
    auto: props.token.auto,
});

const handleGenerate = () => {
    form.post(generate.url(), {
        onSuccess: () => {
            toast.success('Token berhasil diperbarui');
        },
    });
};

const handleToggleAuto = (checked: boolean) => {
    form.auto = checked;
    form.post(toggleAuto.url(), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Pengaturan auto-generate berhasil disimpan');
        },
    });
};
</script>

<template>
    <Head title="Token Ujian" />

    <div class="flex h-full flex-1 flex-col gap-4 p-4 lg:p-6">
        <Heading
            title="Token Ujian"
            description="Kelola token akses untuk pelaksanaan ujian CBT."
            variant="small"
        />

        <div class="max-w-3xl space-y-6">
            <!-- Token Display Card -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-sm"
            >
                <div
                    class="flex flex-col items-center justify-center space-y-4 border-b border-border bg-primary/5 p-6 text-center"
                >
                    <div class="rounded-full bg-primary/10 p-3 text-primary">
                        <Key class="h-8 w-8" />
                    </div>
                    <div class="space-y-1">
                        <p
                            class="text-sm font-medium tracking-widest text-muted-foreground uppercase"
                        >
                            Token Ujian Saat Ini
                        </p>
                        <h2
                            class="font-mono text-6xl font-black tracking-widest text-foreground"
                        >
                            {{ token.token }}
                        </h2>
                    </div>

                    <p
                        class="mt-4 flex items-center justify-center text-xs text-muted-foreground"
                    >
                        <Clock class="mr-1 h-3 w-3" />
                        Terakhir diupdate:
                        {{ new Date(token.updated_at).toLocaleString('id-ID') }}
                    </p>
                </div>

                <div
                    class="flex flex-col items-center justify-between gap-4 bg-card p-6 sm:flex-row"
                >
                    <div class="flex items-center space-x-3">
                        <Switch
                            id="auto-token"
                            :checked="form.auto"
                            @update:checked="handleToggleAuto"
                        />
                        <div class="space-y-0.5">
                            <Label
                                for="auto-token"
                                class="text-base font-semibold"
                                >Auto-Generate Token</Label
                            >
                            <p class="text-sm text-muted-foreground">
                                Otomatis perbarui token setiap 15 menit
                            </p>
                        </div>
                    </div>

                    <Button
                        @click="handleGenerate"
                        :disabled="form.processing"
                        size="lg"
                        class="w-full sm:w-auto"
                    >
                        <RefreshCcw
                            class="mr-2 h-4 w-4"
                            :class="{ 'animate-spin': form.processing }"
                        />
                        Generate Token Baru
                    </Button>
                </div>
            </div>

            <!-- Information Alert -->
            <div
                class="flex items-start rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-800 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-300"
            >
                <Settings class="mt-0.5 mr-3 h-5 w-5 flex-shrink-0" />
                <div class="text-sm">
                    <p class="mb-1 font-semibold">Informasi Token Ujian</p>
                    <ul class="ml-4 list-disc space-y-1">
                        <li>
                            Token ujian digunakan sebagai kunci masuk (password
                            sekunder) bagi peserta sebelum memulai ujian.
                        </li>
                        <li>
                            Token hanya diwajibkan untuk jadwal ujian yang
                            mengaktifkan fitur token.
                        </li>
                        <li>
                            Fitur <strong>Auto-Generate</strong> akan secara
                            otomatis mengganti token setiap 15 menit melalui
                            task scheduler server (Cron Job).
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
