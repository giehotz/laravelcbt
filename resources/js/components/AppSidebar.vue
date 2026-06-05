<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutGrid, 
    Calendar, 
    Clock, 
    School, 
    UserCheck, 
    GraduationCap, 
    ShieldAlert,
    Layers,
    Milestone,
    BookOpen,
    Users,
    Compass,
    Database,
    Clock4,
    MonitorPlay,
    Hash,
    Map,
    Library,
    Key,
    Activity,
    CheckSquare,
    Printer
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
    useSidebar,
} from '@/components/ui/sidebar';
import { index as tpIndex } from '@/routes/master/tahun-pelajaran';
import { index as levelIndex } from '@/routes/master/level-kelas';
import { index as jurusanIndex } from '@/routes/master/jurusan';
import { index as mapelIndex } from '@/routes/master/mapel';
import { index as kelasIndex } from '@/routes/master/kelas';
import { index as ekstraIndex } from '@/routes/master/ekstra';
import { index as smtIndex } from '@/routes/master/semester';
import { edit as schoolEdit } from '@/routes/setting/sekolah';
import { index as adminIndex } from '@/routes/setting/user/admin';
import { index as guruIndex } from '@/routes/setting/user/guru';
import { index as siswaIndex } from '@/routes/setting/user/siswa';
import { index as cbtJenisIndex } from '@/routes/cbt/jenis';
import { index as cbtSesiIndex } from '@/routes/cbt/sesi';
import { index as cbtRuangIndex } from '@/routes/cbt/ruang';
import { index as cbtNomorPesertaIndex } from '@/routes/cbt/nomor-peserta';
import { index as cbtAturRuangIndex } from '@/routes/cbt/atur-ruang';
import { index as cbtBankSoalIndex } from '@/routes/cbt/bank-soal';
import { index as cbtTokenIndex } from '@/routes/cbt/token';
import { index as cbtJadwalIndex } from '@/routes/cbt/jadwal';
import { index as cbtPengawasIndex } from '@/routes/cbt/pengawas';
import { index as cbtAlokasiWaktuIndex } from '@/routes/cbt/alokasi-waktu';
import { index as cbtMonitoringIndex } from '@/routes/cbt/monitoring';
import { index as cbtKoreksiIndex } from '@/routes/cbt/koreksi';
import { index as cbtReportIndex } from '@/routes/cbt/report';
import { dashboard } from '@/routes';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { computed } from 'vue';

const page = usePage();
const { isCurrentUrl } = useCurrentUrl();
const { state } = useSidebar();

// Retrieve roles from shared Inertia props
const roles = computed<string[]>(() => page.props.auth?.roles ?? []);

const hasRole = (roleList: string[]) => {
    return roleList.some(role => roles.value.includes(role));
};

const isAdminOrOperator = computed(() => hasRole(['superadmin', 'operator']));
const isSuperAdmin = computed(() => hasRole(['superadmin']));
const canAccessCbt = computed(() => hasRole(['superadmin', 'operator', 'proktor', 'guru', 'kepsek']));
const canAccessMonitoring = computed(() => hasRole(['superadmin', 'operator', 'proktor', 'kepsek']));
const canAccessKoreksi = computed(() => hasRole(['superadmin', 'guru']));
const canAccessReport = computed(() => hasRole(['superadmin', 'operator', 'kepsek']));
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="hover:bg-sidebar-accent/50 transition-colors">
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
                <SidebarGroupLabel class="text-xs font-semibold uppercase tracking-wider text-sidebar-foreground/40">Home</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(dashboard())"
                            tooltip="Dashboard"
                            class="transition-all duration-200"
                        >
                            <Link :href="dashboard()">
                                <LayoutGrid class="w-4 h-4" />
                                <span>Dashboard</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- DATA MASTER SECTION (superadmin & operator only) -->
            <SidebarGroup v-if="isAdminOrOperator" class="px-2 py-1">
                <SidebarGroupLabel class="text-xs font-semibold uppercase tracking-wider text-sidebar-foreground/40">Data Master</SidebarGroupLabel>
                <SidebarMenu>
                    <!-- Tahun & Semester -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(tpIndex.url())"
                            tooltip="Tahun & Semester"
                            class="transition-all duration-200"
                        >
                            <Link :href="tpIndex.url()">
                                <Calendar class="w-4 h-4" />
                                <span>Tahun & Semester</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Level Kelas -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(levelIndex.url())"
                            tooltip="Level Kelas"
                            class="transition-all duration-200"
                        >
                            <Link :href="levelIndex.url()">
                                <Layers class="w-4 h-4" />
                                <span>Level Kelas</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Jurusan -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(jurusanIndex.url())"
                            tooltip="Jurusan"
                            class="transition-all duration-200"
                        >
                            <Link :href="jurusanIndex.url()">
                                <Milestone class="w-4 h-4" />
                                <span>Jurusan</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Mata Pelajaran -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(mapelIndex.url())"
                            tooltip="Mata Pelajaran"
                            class="transition-all duration-200"
                        >
                            <Link :href="mapelIndex.url()">
                                <BookOpen class="w-4 h-4" />
                                <span>Mata Pelajaran</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Rombel / Kelas -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(kelasIndex.url())"
                            tooltip="Rombel / Kelas"
                            class="transition-all duration-200"
                        >
                            <Link :href="kelasIndex.url()">
                                <Users class="w-4 h-4" />
                                <span>Rombel / Kelas</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- Ekstrakurikuler -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(ekstraIndex.url())"
                            tooltip="Ekstrakurikuler"
                            class="transition-all duration-200"
                        >
                            <Link :href="ekstraIndex.url()">
                                <Compass class="w-4 h-4" />
                                <span>Ekstrakurikuler</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- CBT SECTION -->
            <SidebarGroup v-if="canAccessCbt" class="px-2 py-1">
                <SidebarGroupLabel class="text-xs font-semibold uppercase tracking-wider text-sidebar-foreground/40">CBT (Computer Based Test)</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(cbtJenisIndex.url())"
                            tooltip="Jenis Ujian"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtJenisIndex.url()">
                                <Database class="w-4 h-4" />
                                <span>Jenis Ujian</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(cbtSesiIndex.url())"
                            tooltip="Sesi Ujian"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtSesiIndex.url()">
                                <Clock4 class="w-4 h-4" />
                                <span>Sesi Ujian</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(cbtRuangIndex.url())"
                            tooltip="Ruang Ujian"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtRuangIndex.url()">
                                <MonitorPlay class="w-4 h-4" />
                                <span>Ruang Ujian</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(cbtNomorPesertaIndex.url())"
                            tooltip="Nomor Peserta"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtNomorPesertaIndex.url()">
                                <Hash class="w-4 h-4" />
                                <span>Nomor Peserta</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(cbtAturRuangIndex.url())"
                            tooltip="Atur Ruang & Sesi"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtAturRuangIndex.url()">
                                <Map class="w-4 h-4" />
                                <span>Atur Ruang & Sesi</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(cbtBankSoalIndex.url())"
                            tooltip="Bank Soal"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtBankSoalIndex.url()">
                                <Library class="w-4 h-4" />
                                <span>Bank Soal</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(cbtTokenIndex.url())"
                            tooltip="Token Ujian"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtTokenIndex.url()">
                                <Key class="w-4 h-4" />
                                <span>Token Ujian</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/cbt/jadwal')"
                            tooltip="Jadwal Ujian"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtJadwalIndex.url()">
                                <Calendar class="w-4 h-4" />
                                <span>Jadwal Ujian</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/cbt/pengawas')"
                            tooltip="Pengawas Ujian"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtPengawasIndex.url()">
                                <Users class="w-4 h-4" />
                                <span>Pengawas Ujian</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/cbt/alokasi-waktu')"
                            tooltip="Alokasi Waktu"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtAlokasiWaktuIndex.url()">
                                <Clock class="w-4 h-4" />
                                <span>Alokasi Waktu</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    <SidebarMenuItem v-if="canAccessMonitoring">
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/cbt/monitoring')"
                            tooltip="Monitoring Ujian"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtMonitoringIndex.url()">
                                <Activity class="w-4 h-4" />
                                <span>Monitoring Ujian</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem v-if="canAccessKoreksi">
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/cbt/koreksi')"
                            tooltip="Koreksi Essai"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtKoreksiIndex.url()">
                                <CheckSquare class="w-4 h-4" />
                                <span>Koreksi Essai</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem v-if="canAccessReport">
                        <SidebarMenuButton
                            as-child
                            :is-active="$page.url.startsWith('/cbt/report')"
                            tooltip="Cetak Laporan"
                            class="transition-all duration-200"
                        >
                            <Link :href="cbtReportIndex.url()">
                                <Printer class="w-4 h-4" />
                                <span>Cetak Laporan</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <!-- PENGATURAN SECTION (superadmin & operator only) -->
            <SidebarGroup v-if="isAdminOrOperator" class="px-2 py-1">
                <SidebarGroupLabel class="text-xs font-semibold uppercase tracking-wider text-sidebar-foreground/40">Pengaturan</SidebarGroupLabel>
                <SidebarMenu>
                    <!-- School Profile (superadmin only) -->
                    <SidebarMenuItem v-if="isSuperAdmin">
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(schoolEdit.url())"
                            tooltip="Profil Sekolah"
                            class="transition-all duration-200"
                        >
                            <Link :href="schoolEdit.url()">
                                <School class="w-4 h-4" />
                                <span>Profil Sekolah</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- User Management (superadmin & operator only) -->
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(adminIndex.url())"
                            tooltip="User Admin"
                            class="transition-all duration-200"
                        >
                            <Link :href="adminIndex.url()">
                                <ShieldAlert class="w-4 h-4" />
                                <span>User Admin</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(guruIndex.url())"
                            tooltip="User Guru"
                            class="transition-all duration-200"
                        >
                            <Link :href="guruIndex.url()">
                                <UserCheck class="w-4 h-4" />
                                <span>User Guru</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <SidebarMenuItem>
                        <SidebarMenuButton
                            as-child
                            :is-active="isCurrentUrl(siswaIndex.url())"
                            tooltip="User Siswa"
                            class="transition-all duration-200"
                        >
                            <Link :href="siswaIndex.url()">
                                <GraduationCap class="w-4 h-4" />
                                <span>User Siswa</span>
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
