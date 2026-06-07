<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import WelcomeBanner from '@/components/Dashboard/WelcomeBanner.vue';
import StatCards from '@/components/Dashboard/StatCards.vue';
import QuickActions from '@/components/Dashboard/QuickActions.vue';
import JadwalHariIni from '@/components/Dashboard/JadwalHariIni.vue';
import NotifikasiPanel from '@/components/Dashboard/NotifikasiPanel.vue';
import GrafikSection from '@/components/Dashboard/GrafikSection.vue';
import cbt from '@/routes/cbt';
import setting from '@/routes/setting';
import master from '@/routes/master';
import {
    Users,
    UserCheck,
    Database,
    Calendar,
    PlusCircle,
    KeyRound,
    MonitorPlay,
    FileText,
    Settings,
    BookOpen,
    Book,
    School,
    Activity,
    GraduationCap,
} from 'lucide-vue-next';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user as Record<string, any>);
const roles = computed(() => page.props.auth.roles as string[]);
const primaryRole = computed(() => roles.value[0] || '');
const tp = computed(
    () => page.props.tp as { id: number; tahun: string } | null,
);
const smt = computed(
    () => page.props.smt as { id: number; nama_smt: string } | null,
);
const serverTime = computed(() => page.props.serverTime as string);
const stats = computed(() => page.props.stats as Record<string, number>);
const grafik = computed(() => page.props.grafik as any);
const jadwalHariIni = computed(() => page.props.jadwalHariIni as any[]);
const globalToken = computed(
    () => page.props.globalToken as string | undefined,
);

// Stat Cards Logic
const statCards = computed(() => {
    const role = primaryRole.value;
    const s = stats.value;

    if (role === 'superadmin') {
        return [
            {
                label: 'Total Siswa',
                value: s.total_siswa || 0,
                icon: Users,
                colorClass: 'text-blue-500',
            },
            {
                label: 'Rombel',
                value: s.total_rombel || 0,
                icon: School,
                colorClass: 'text-indigo-500',
            },
            {
                label: 'Total Guru',
                value: s.total_guru || 0,
                icon: UserCheck,
                colorClass: 'text-green-500',
            },
            {
                label: 'Wali Kelas',
                value: s.total_wali || 0,
                icon: GraduationCap,
                colorClass: 'text-teal-500',
            },
            {
                label: 'Total Mapel',
                value: s.total_mapel || 0,
                icon: Book,
                colorClass: 'text-yellow-500',
            },
            {
                label: 'Ekstrakurikuler',
                value: s.total_ekstra || 0,
                icon: Activity,
                colorClass: 'text-rose-500',
            },
            {
                label: 'Total Bank Soal',
                value: s.total_bank || 0,
                icon: Database,
                colorClass: 'text-purple-500',
            },
            {
                label: 'Jadwal Aktif Sekarang',
                value: s.jadwal_aktif_sekarang || 0,
                icon: Calendar,
                colorClass: 'text-orange-500',
            },
        ];
    } else if (role === 'operator') {
        return [
            {
                label: 'Total Siswa',
                value: s.total_siswa || 0,
                icon: Users,
                colorClass: 'text-blue-500',
            },
            {
                label: 'Total Guru',
                value: s.total_guru || 0,
                icon: UserCheck,
                colorClass: 'text-green-500',
            },
            {
                label: 'Total Bank Soal',
                value: s.total_bank || 0,
                icon: Database,
                colorClass: 'text-purple-500',
            },
            {
                label: 'Jadwal Aktif Sekarang',
                value: s.jadwal_aktif_sekarang || 0,
                icon: Calendar,
                colorClass: 'text-orange-500',
            },
        ];
    } else if (role === 'guru') {
        return [
            {
                label: 'Bank Soal Saya',
                value: s.total_bank_saya || 0,
                icon: Database,
                colorClass: 'text-purple-500',
            },
            {
                label: 'Total Soal Saya',
                value: s.total_soal_saya || 0,
                icon: FileText,
                colorClass: 'text-blue-500',
            },
            {
                label: 'Jadwal Ujian Saya',
                value: s.jadwal_aktif_saya || 0,
                icon: Calendar,
                colorClass: 'text-orange-500',
            },
            {
                label: 'Nilai Belum Koreksi',
                value: 0,
                icon: BookOpen,
                colorClass: 'text-red-500',
            },
        ];
    } else if (role === 'proktor') {
        return [
            {
                label: 'Jadwal Hari Ini',
                value: s.jadwal_hari_ini || 0,
                icon: Calendar,
                colorClass: 'text-orange-500',
            },
            {
                label: 'Total Peserta',
                value: s.total_siswa || 0,
                icon: Users,
                colorClass: 'text-blue-500',
            },
            {
                label: 'Sedang Ujian',
                value: 0,
                icon: MonitorPlay,
                colorClass: 'text-green-500',
            },
            {
                label: 'Sudah Selesai',
                value: 0,
                icon: UserCheck,
                colorClass: 'text-purple-500',
            },
        ];
    } else if (role === 'kepsek') {
        return [
            {
                label: 'Total Siswa',
                value: s.total_siswa || 0,
                icon: Users,
                colorClass: 'text-blue-500',
            },
            {
                label: 'Rombel',
                value: s.total_rombel || 0,
                icon: School,
                colorClass: 'text-indigo-500',
            },
            {
                label: 'Total Guru',
                value: s.total_guru || 0,
                icon: UserCheck,
                colorClass: 'text-green-500',
            },
            {
                label: 'Wali Kelas',
                value: s.total_wali || 0,
                icon: GraduationCap,
                colorClass: 'text-teal-500',
            },
            {
                label: 'Total Mapel',
                value: s.total_mapel || 0,
                icon: Book,
                colorClass: 'text-yellow-500',
            },
            {
                label: 'Ekstrakurikuler',
                value: s.total_ekstra || 0,
                icon: Activity,
                colorClass: 'text-rose-500',
            },
            {
                label: 'Jadwal Aktif Sekarang',
                value: s.jadwal_aktif_sekarang || 0,
                icon: Calendar,
                colorClass: 'text-orange-500',
            },
        ];
    }
    return [];
});

// Quick Actions Logic
interface QuickAction {
    label: string;
    icon: any;
    href: string;
    variant?:
        | 'default'
        | 'outline'
        | 'secondary'
        | 'ghost'
        | 'link'
        | 'destructive';
}

const quickActions = computed<QuickAction[]>(() => {
    const role = primaryRole.value;

    if (role === 'superadmin' || role === 'operator') {
        return [
            {
                label: 'Tambah Jadwal',
                icon: PlusCircle,
                href: cbt.jadwal.create.url(),
                variant: 'default',
            },
            {
                label: 'Kelola Token',
                icon: KeyRound,
                href: cbt.token.index.url(),
                variant: 'outline',
            },
            {
                label: 'Bank Soal',
                icon: Database,
                href: cbt.bankSoal.index.url(),
                variant: 'outline',
            },
            {
                label: 'Kelola Siswa',
                icon: Users,
                href: setting.user.siswa.index.url(),
                variant: 'outline',
            },
        ];
    } else if (role === 'guru') {
        return [
            {
                label: 'Buat Bank Soal',
                icon: PlusCircle,
                href: cbt.bankSoal.create.url(),
                variant: 'default',
            },
            {
                label: 'Lihat Jadwal',
                icon: Calendar,
                href: cbt.jadwal.index.url(),
                variant: 'outline',
            },
            {
                label: 'Lihat Nilai',
                icon: FileText,
                href: '#',
                variant: 'outline',
            },
        ];
    } else if (role === 'proktor') {
        return [
            {
                label: 'Kelola Token',
                icon: KeyRound,
                href: cbt.token.index.url(),
                variant: 'default',
            },
            {
                label: 'Monitor Ujian',
                icon: MonitorPlay,
                href: '#',
                variant: 'outline',
            },
            {
                label: 'Status Peserta',
                icon: Users,
                href: '#',
                variant: 'outline',
            },
        ];
    }
    return [];
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6">
        <!-- Banner & Server Time -->
        <WelcomeBanner
            v-if="serverTime && user"
            :server-time="serverTime"
            :tp="tp"
            :smt="smt"
            :user="user"
        />

        <!-- Stat Cards -->
        <StatCards :cards="statCards" v-if="statCards.length > 0" />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left Column (Jadwal Hari Ini) -->
            <div class="space-y-6 lg:col-span-2">
                <JadwalHariIni
                    :jadwal="jadwalHariIni"
                    :role="primaryRole"
                    :global-token="globalToken"
                />
            </div>

            <!-- Right Column (Quick Actions & Notifications) -->
            <div class="space-y-6">
                <QuickActions
                    :actions="quickActions"
                    v-if="quickActions.length > 0"
                />
                <NotifikasiPanel
                    :jadwal="jadwalHariIni"
                    :global-token="globalToken"
                    :server-time="serverTime"
                />
            </div>
        </div>

        <!-- Layer 3: Grafik (Hanya untuk Admin dan Kepsek) -->
        <GrafikSection
            v-if="grafik && ['superadmin', 'kepsek'].includes(primaryRole)"
            :dataGrafik="grafik"
        />
    </div>
</template>
