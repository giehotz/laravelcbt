<script setup lang="ts">
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import Toast from '@/components/Toast.vue';
import { useToast } from '@/composables/useToast';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const { addToast } = useToast();

// Watch flash messages and add toast notifications dynamically
watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.success) {
            addToast(flash.success, 'success');
        }
        if (flash?.error) {
            addToast(flash.error, 'error');
        }
        if (flash?.info) {
            addToast(flash.info, 'info');
        }
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <AppShell variant="header">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent variant="header">
            <slot />
        </AppContent>
        <Toast />
    </AppShell>
</template>
