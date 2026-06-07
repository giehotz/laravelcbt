<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { LucideIcon } from 'lucide-vue-next';

interface QuickAction {
    label: string;
    icon: any; // Lucide icon
    href: string;
    variant?:
        | 'default'
        | 'outline'
        | 'secondary'
        | 'ghost'
        | 'link'
        | 'destructive';
}

defineProps<{
    actions: QuickAction[];
}>();
</script>

<template>
    <Card>
        <CardHeader class="pb-3">
            <CardTitle class="text-sm font-medium">Akses Cepat</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                <Button
                    v-for="(action, index) in actions"
                    :key="index"
                    :variant="action.variant || 'outline'"
                    class="h-auto w-full justify-start px-4 py-3"
                    as-child
                >
                    <Link
                        :href="action.href"
                        class="flex h-auto flex-col items-center gap-2 text-center"
                    >
                        <component :is="action.icon" class="mb-1 h-5 w-5" />
                        <span
                            class="text-xs leading-tight font-medium whitespace-normal"
                            >{{ action.label }}</span
                        >
                    </Link>
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
