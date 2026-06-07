<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { LucideIcon } from 'lucide-vue-next';

interface StatCard {
    label: string;
    value: string | number;
    icon: any; // Lucide icon
    colorClass?: string;
}

defineProps<{
    cards: StatCard[];
}>();
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card v-for="(card, index) in cards" :key="index">
            <CardHeader class="flex flex-row items-center justify-between pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    {{ card.label }}
                </CardTitle>
                <component
                    :is="card.icon"
                    class="h-4 w-4"
                    :class="card.colorClass || 'text-muted-foreground'"
                />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">{{ card.value }}</div>
            </CardContent>
        </Card>
    </div>
</template>
