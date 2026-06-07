<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    User,
    Bell,
    ClipboardList,
    Library,
    Calendar,
    Printer,
    Users,
    Award,
    LineChart,
    FileSpreadsheet,
} from 'lucide-vue-next';
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
import { edit as profileEdit } from '@/routes/profile';
import { index as pengumumanIndex } from '@/routes/pengumuman';
import { index as cbtBankSoalIndex } from '@/routes/cbt/bank-soal';
import { index as cbtJadwalIndex } from '@/routes/cbt/jadwal';
import {
    ulanganUjian,
    cetak,
    statusSiswa,
    hasilUjian,
    analisisSoal,
    rekapNilai,
} from '@/routes/guru';
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
                    >HOME</SidebarGroupLabel
                >
                <SidebarMenu>
                    <!-- Beranda -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(dashboard())"
                            tooltip="Beranda"
                            class="transition-all duration-200"
                        >
                            <Link :href="dashboard()">
                                <LayoutGrid class="h-4 w-4" />
                                <span>Beranda</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Profile -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(profileEdit.url())"
                            tooltip="Profile"
                            class="transition-all duration-200"
                        >
                            <Link :href="profileEdit.url()">
                                <User class="h-4 w-4" />
                                <span>Profile</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Pengumuman -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/pengumuman')"
                            tooltip="Pengumuman"
                            class="transition-all duration-200"
                        >
                            <Link :href="pengumumanIndex.url()">
                                <Bell class="h-4 w-4" />
                                <span>Pengumuman</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Ulangan / Ujian -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="
                                $page.url.startsWith('/guru/ulangan-ujian')
                            "
                            tooltip="Ulangan / Ujian"
                            class="transition-all duration-200"
                        >
                            <Link :href="ulanganUjian.url()">
                                <ClipboardList class="h-4 w-4" />
                                <span>Ulangan / Ujian</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Bank Soal -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/cbt/bank-soal')"
                            tooltip="Bank Soal"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtBankSoalIndex.url()">
                                <Library class="h-4 w-4" />
                                <span>Bank Soal</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Jadwal -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/cbt/jadwal')"
                            tooltip="Jadwal"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtJadwalIndex.url()">
                                <Calendar class="h-4 w-4" />
                                <span>Jadwal</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Cetak -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/guru/cetak')"
                            tooltip="Cetak"
                            class="transition-all duration-200"
                        >
                            <Link :href="cetak.url()">
                                <Printer class="h-4 w-4" />
                                <span>Cetak</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Status Siswa -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="
                                $page.url.startsWith('/guru/status-siswa')
                            "
                            tooltip="Status Siswa"
                            class="transition-all duration-200"
                        >
                            <Link :href="statusSiswa.url()">
                                <Users class="h-4 w-4" />
                                <span>Status Siswa</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Hasil Ujian -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="
                                $page.url.startsWith('/guru/hasil-ujian')
                            "
                            tooltip="Hasil Ujian"
                            class="transition-all duration-200"
                        >
                            <Link :href="hasilUjian.url()">
                                <Award class="h-4 w-4" />
                                <span>Hasil Ujian</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Analisis Soal -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="
                                $page.url.startsWith('/guru/analisis-soal')
                            "
                            tooltip="Analisis Soal"
                            class="transition-all duration-200"
                        >
                            <Link :href="analisisSoal.url()">
                                <LineChart class="h-4 w-4" />
                                <span>Analisis Soal</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Rekap Nilai -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="
                                $page.url.startsWith('/guru/rekap-nilai')
                            "
                            tooltip="Rekap Nilai"
                            class="transition-all duration-200"
                        >
                            <Link :href="rekapNilai.url()">
                                <FileSpreadsheet class="h-4 w-4" />
                                <span>Rekap Nilai</span>
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
