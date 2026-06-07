<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid, Activity, Printer } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarGroup,
    SidebarGroupLabel,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as cbtMonitoringIndex } from '@/routes/cbt/monitoring';
import { index as cbtReportIndex } from '@/routes/cbt/report';
import { useCurrentUrl } from '@/composables/useCurrentUrl';

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="transition-colors hover:bg-sidebar-accent/50"
                    >
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="py-2">
            <!-- HOME SECTION -->
            <SidebarGroup class="px-2 py-1">
                <SidebarGroupLabel
                    class="text-xs font-semibold tracking-wider text-sidebar-foreground/40 uppercase"
                    >Home</SidebarGroupLabel
                >
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(dashboard())"
                            tooltip="Dashboard"
                            class="transition-all duration-200"
                        >
                            <Link :href="dashboard()">
                                <LayoutGrid class="h-4 w-4" />
                                <span>Dashboard</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- CBT SECTION -->
            <SidebarGroup class="px-2 py-1">
                <SidebarGroupLabel
                    class="text-xs font-semibold tracking-wider text-sidebar-foreground/40 uppercase"
                    >CBT (Computer Based Test)</SidebarGroupLabel
                >
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/cbt/monitoring')"
                            tooltip="Monitoring Ujian"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtMonitoringIndex.url()">
                                <Activity class="h-4 w-4" />
                                <span>Monitoring Ujian</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/cbt/report')"
                            tooltip="Cetak Laporan"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtReportIndex.url()">
                                <Printer class="h-4 w-4" />
                                <span>Cetak Laporan</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter class="gap-2">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
