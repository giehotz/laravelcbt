<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';
import {
    Calendar,
    Clock,
    AlertTriangle,
    Sun,
    Moon,
    Monitor,
} from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
    DropdownMenuItem,
} from '@/components/ui/dropdown-menu';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const { appearance, updateAppearance } = useAppearance();

// Retrieve active year and semester from shared Inertia props
const tpAktif = computed(
    () => page.props.tp_aktif as { id: number; tahun: string } | null,
);
const smtAktif = computed(
    () =>
        page.props.smt_aktif as {
            id: number;
            nama_smt: string;
            smt: string;
        } | null,
);
</script>

<template>
    <header
        class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 bg-white/50 px-6 backdrop-blur-md transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4 dark:bg-zinc-900/50"
    >
        <!-- Left Side: Sidebar Trigger and Breadcrumbs -->
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <!-- Right Side: Active Period Status Indicators and Theme Switcher -->
        <div class="flex items-center gap-3">
            <!-- Tahun Pelajaran Badge -->
            <div
                v-if="tpAktif"
                class="flex items-center gap-1.5 rounded-full border border-emerald-200/50 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 transition-all duration-200 hover:scale-105 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300"
            >
                <Calendar class="h-3.5 w-3.5" />
                <span>TP: {{ tpAktif.tahun }}</span>
            </div>
            <div
                v-else
                class="flex animate-pulse items-center gap-1.5 rounded-full border border-rose-200/50 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300"
            >
                <AlertTriangle class="h-3.5 w-3.5" />
                <span>TP Inaktif</span>
            </div>

            <!-- Semester Badge -->
            <div
                v-if="smtAktif"
                class="flex items-center gap-1.5 rounded-full border border-blue-200/50 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 transition-all duration-200 hover:scale-105 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-300"
            >
                <Clock class="h-3.5 w-3.5" />
                <span>Semester: {{ smtAktif.nama_smt }}</span>
            </div>
            <div
                v-else
                class="flex animate-pulse items-center gap-1.5 rounded-full border border-rose-200/50 bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300"
            >
                <AlertTriangle class="h-3.5 w-3.5" />
                <span>Smt Inaktif</span>
            </div>

            <!-- Theme Toggle Dropdown -->
            <DropdownMenu>
                <DropdownMenuTrigger :as-child="true">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-9 w-9 cursor-pointer"
                    >
                        <Sun
                            v-if="appearance === 'light'"
                            class="size-4 opacity-80"
                        />
                        <Moon
                            v-else-if="appearance === 'dark'"
                            class="size-4 opacity-80"
                        />
                        <Monitor v-else class="size-4 opacity-80" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-36">
                    <DropdownMenuItem
                        class="cursor-pointer"
                        @click="updateAppearance('light')"
                    >
                        <Sun class="mr-2 h-4 w-4" />
                        <span>Light</span>
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        class="cursor-pointer"
                        @click="updateAppearance('dark')"
                    >
                        <Moon class="mr-2 h-4 w-4" />
                        <span>Dark</span>
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        class="cursor-pointer"
                        @click="updateAppearance('system')"
                    >
                        <Monitor class="mr-2 h-4 w-4" />
                        <span>System</span>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
