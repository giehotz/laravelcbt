<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Ruler,
    Globe,
    FileText,
    Trophy,
    ClipboardList,
    UserCheck,
} from 'lucide-vue-next';

interface MenuItem {
    label: string;
    icon: string;
    href: string;
    color: string;
    bgClass: string;
    textClass: string;
}

defineProps<{
    menus: MenuItem[];
}>();

const iconMap: Record<string, any> = {
    BookOpen,
    Ruler,
    Globe,
    FileText,
    Trophy,
    ClipboardList,
    UserCheck,
};
</script>

<template>
    <div class="rounded-xl border bg-card shadow-xs">
        <div class="rounded-t-xl bg-primary px-4 py-3">
            <h3 class="text-sm font-semibold text-primary-foreground">MENU UTAMA</h3>
        </div>
        <div class="grid grid-cols-3 gap-3 p-4 sm:grid-cols-4 lg:grid-cols-7">
            <Link
                v-for="m in menus"
                :key="m.label"
                :href="m.href"
                class="flex flex-col items-center gap-2 rounded-xl border bg-background p-3 shadow-xs transition-all duration-150 hover:shadow-md hover:-translate-y-1 active:scale-95"
            >
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-full sm:h-16 sm:w-16"
                    :class="m.bgClass"
                >
                    <component :is="iconMap[m.icon]" class="h-7 w-7 sm:h-8 sm:w-8" :class="m.textClass" />
                </div>
                <span class="text-center text-xs font-semibold leading-tight text-card-foreground sm:text-sm">
                    {{ m.label }}
                </span>
            </Link>
        </div>
    </div>
</template>
