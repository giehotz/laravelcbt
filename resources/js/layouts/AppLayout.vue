<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/app/AdminLayout.vue';
import GuruLayout from '@/layouts/app/GuruLayout.vue';
import ProktorLayout from '@/layouts/app/ProktorLayout.vue';
import KepsekLayout from '@/layouts/app/KepsekLayout.vue';
import SiswaLayout from '@/layouts/app/SiswaLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const page = usePage();
const roles = computed<string[]>(() => page.props.auth?.roles ?? []);

const activeLayout = computed(() => {
    if (roles.value.includes('guru')) {
        return GuruLayout;
    }
    if (roles.value.includes('proktor')) {
        return ProktorLayout;
    }
    if (roles.value.includes('kepsek')) {
        return KepsekLayout;
    }
    if (roles.value.includes('siswa')) {
        return SiswaLayout;
    }
    return AdminLayout; // fallback for superadmin, operator, etc.
});
</script>

<template>
    <component :is="activeLayout" :breadcrumbs="breadcrumbs">
        <slot />
    </component>
</template>
