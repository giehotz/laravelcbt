<script setup lang="ts">
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
