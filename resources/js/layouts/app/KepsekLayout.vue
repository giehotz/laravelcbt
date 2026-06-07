<script setup lang="ts">
import { watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import KepsekSidebar from '@/components/KepsekSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
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

watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.success) addToast(flash.success, 'success');
        if (flash?.error) addToast(flash.error, 'error');
        if (flash?.info) addToast(flash.info, 'info');
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <AppShell variant="sidebar">
        <KepsekSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="p-4 lg:p-6">
                <slot />
            </div>
        </AppContent>
        <Toast />
    </AppShell>
</template>
